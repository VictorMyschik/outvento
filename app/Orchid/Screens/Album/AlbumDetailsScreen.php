<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Album;

use App\Models\Albums\Album;
use App\Models\User;
use App\Orchid\Layouts\Album\AddAlbumTravelLayout;
use App\Orchid\Layouts\Album\AlbumEditLayout;
use App\Orchid\Layouts\Lego\AvatarUploadLayout;
use App\Orchid\Screens\User\UserBaseScreen;
use App\Services\Albums\Enum\Visibility;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Attach;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Modal;
use Orchid\Support\Facades\Layout;

class AlbumDetailsScreen extends UserBaseScreen
{
    public ?User $user = null;
    public ?Album $album = null;

    public function name(): string
    {
        return 'Album ' . $this->album->title;
    }

    public function description(): string
    {
        return View('admin.created_updated', ['value' => $this->user])->toHtml();
    }

    public function query(User $user, Album $album): iterable
    {
        $this->setAvatar($album->getAvatar());

        return [
            'user'  => $user,
            'album' => $album,
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('add media')
                ->icon('plus')
                ->class('mr-btn-success')
                ->modal('media_edit_modal')
                ->method('saveAlbum', ['id' => 0]),
            Link::make('Назад')->class('mr-btn mr-btn-route')->icon('arrow-up')->route('profiles.albums.list', ['user' => $this->user->id]),
        ];
    }

    public function layout(): iterable
    {
        $out = [
            Layout::columns([
                Layout::rows($this->getBaseLayout()),
                Layout::tabs([
                    'Info'   => Layout::rows($this->getInfoTabLayout()),
                    'Linked' => Layout::rows($this->getLinkedTabLayout()),
                ])
            ])
        ];

        $out[] = Layout::rows($this->getActionLayout());

        $out[] = Layout::rows($this->getActionBottomLayout());
        $out[] = Layout::modal('media_edit_modal', AlbumEditLayout::class)->async('asyncGetAlbum')->size(Modal::SIZE_LG);
        $out[] = Layout::modal('album_edit_modal', AlbumEditLayout::class)->async('asyncGetAlbum')->size(Modal::SIZE_LG);
        $out[] = Layout::modal('upload_album_avatar', AvatarUploadLayout::class);
        $out[] = Layout::modal('add_travel_modal', AddAlbumTravelLayout::class);

        return $out;
    }

    private function getActionLayout(): array
    {
        return [
            Attach::make('attachments')
                ->maxCount(3)
                ->multiple()
        ];
    }

    private function getBaseLayout(): array
    {
        return [
            Label::make('visibility')->title('Visibility')->value($this->album->getVisibility()->getLabel()),
            Label::make('title')->title('Title')->value($this->album->title),
            Label::make('description')->title('Description')->value($this->album->description),
            ViewField::make('')->view('hr'),
            ModalToggle::make('edit')
                ->modal('album_edit_modal')
                ->modalTitle('Edit Album')
                ->method('saveAlbum', ['id' => $this->album->id])
                ->class('mr-btn-primary pull-right'),
        ];
    }

    public function getInfoTabLayout(): array
    {
        $out = $this->avatarTab();

        $out[] = ViewField::make('')->view('hr');
        $out[] = ViewField::make('')->view('admin.created_updated')->value($this->album);

        return $out;
    }

    private function avatarTab(): array
    {
        $hasLogo = (bool)$this->album->getAvatar();

        $photoTab = [
            Group::make([
                ModalToggle::make('add avatar')
                    ->class('mr-btn-success')
                    ->modal('upload_album_avatar')
                    ->modalTitle('Add avatar')
                    ->method('saveAlbumAvatar', ['albumId' => $this->album->id]),
                Button::make('delete')
                    ->class('mr-btn-danger')
                    ->method('removeAvatar')
                    ->hidden(!$hasLogo)
                    ->confirm('Delete avatar?')
                    ->parameters(['albumIdId' => $this->album->id]),
            ])->autoWidth(),
        ];

        $group = ['avatar' => ViewField::make('#')->view('admin.raw')->value('<i>No avatar</i>')];
        if ($this->album->avatar) {
            $group['avatar'] = ViewField::make('#')->view('admin.avatar')->value(['path' => $this->album->getAvatar()]);
        }

        return array_merge($photoTab, [ViewField::make('')->view('space')], $group);
    }

    private function getLinkedTabLayout(): array
    {
        $list = $this->albumService->getLinkedTravels($this->album->id);

        $rows['header'] = ['ID', 'Travel ID', 'Title', '#'];

        foreach ($list as $item) {
            $link = route('profiles.travel.details', ['user' => $item->owner_id, 'travel' => $item->travel_id]);

            $rows['body'][] = [
                'ID'        => $item->id,
                'Travel ID' => $item->travel_id,
                'Title'     => Link::make($item->travel_title)->href($link)->target('_blank'),
                '#'         => Button::make('delete')
                    ->icon('bs.trash3')
                    ->confirm('Delink album from this travel?')
                    ->method('delinkAlbumTravel', ['travelId' => $item->travel_id]),
            ];
        }

        $out[] = Group::make([
            ModalToggle::make('Add travel')
                ->class('mr-btn-success')
                ->modal('add_travel_modal')
                ->modalTitle('Add travel')
                ->method('saveAlbumTravel'),
            Button::make('delete')
                ->class('mr-btn-danger')
                ->method('removeAvatar')
                ->hidden(empty($list))
                ->confirm('Delete avatar?')
                ->parameters(['albumIdId' => $this->album->id]),
        ])->autoWidth();


        $out[] = ViewField::make('')->view('admin.table')->value($rows);

        $out[] = ViewField::make('')->view('hr');
        $out[] = ViewField::make('')->view('admin.created_updated')->value($this->album);

        return $out;
    }

    private function getActionBottomLayout(): array
    {
        return [
            Group::make([
                Button::make('Delete album')
                    ->class('mr-btn-danger pull-right')
                    ->icon('trash')
                    ->method('removeAlbum')
                    ->confirm('Are you sure you want to delete this album? This action cannot be undone.'),
            ])->alignCenter()
        ];
    }

    public function delinkAlbumTravel(int $travelId): void
    {
        $this->albumService->delinkAlbumTravel($travelId, $this->album->id);
    }

    public function saveAlbumTravel(Request $request): void
    {
        $this->albumService->addAlbumTravel($this->album->id, (int)$request->input('travel_id'));
    }

    public function saveAlbum(Request $request, int $id): RedirectResponse
    {
        $input = $request->validate([
            'album.title'       => ['required', 'string', 'max:255'],
            'album.visibility'  => ['required', 'string', Rule::enum(Visibility::class)],
            'album.description' => ['nullable', 'string'],
        ])['album'];

        $id = $this->albumService->saveAlbum($id, $input['title'], Visibility::from($input['visibility']), $this->user, $input['description']);

        return redirect()->route('profiles.albums.details', ['user' => $this->user->id, 'album' => $id]);
    }

    public function asyncGetAlbum(int $id): array
    {
        return [
            'album' => Album::loadBy($id),
        ];
    }

    public function removeAlbum(): RedirectResponse
    {
        $this->albumService->deleteAlbum($this->album);

        return redirect()->route('profiles.albums.list', ['user' => $this->user->id]);
    }

    public function saveAlbumAvatar(Request $request): void
    {
        $file = $request->file('avatar');

        $this->albumService->addAvatar($this->album->id, $file);
    }

    public function removeAvatar(): void
    {
        $this->albumService->removeAvatar($this->album);
    }
}