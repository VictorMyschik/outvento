<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Album;

use App\Helpers\FileSizeConverter;
use App\Models\Albums\Album;
use App\Models\Albums\AlbumMedia;
use App\Models\Albums\AlbumMediaComment;
use App\Models\Orchid\Attachment;
use App\Models\User;
use App\Orchid\Filters\Album\AlbumCommentListFilter;
use App\Orchid\Filters\Album\AlbumMediaListFilter;
use App\Orchid\Layouts\Album\AddAlbumTravelLayout;
use App\Orchid\Layouts\Album\AlbumCommentListLayout;
use App\Orchid\Layouts\Album\AlbumEditLayout;
use App\Orchid\Layouts\Album\AlbumMediaEditLayout;
use App\Orchid\Layouts\Album\CommentEditLayout;
use App\Orchid\Layouts\Lego\AvatarUploadLayout;
use App\Orchid\Screens\User\UserBaseScreen;
use App\Services\Albums\Enum\Visibility;
use App\Services\Image\Enum\Size;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

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

        $out[] = Layout::accordion($this->getImageActionLayout());

        $out[] = $this->getMediaLayout();

        $out[] = Layout::rows($this->getActionBottomLayout());

        $out[] = Layout::modal('media_edit_modal', AlbumEditLayout::class)->async('asyncGetAlbum')->size(Modal::SIZE_LG);
        $out[] = Layout::modal('album_edit_modal', AlbumEditLayout::class)->async('asyncGetAlbum')->size(Modal::SIZE_LG);
        $out[] = Layout::modal('upload_album_avatar', AvatarUploadLayout::class);
        $out[] = Layout::modal('add_travel_modal', AddAlbumTravelLayout::class);
        $out[] = Layout::modal('album_media_edit_modal', AlbumMediaEditLayout::class)->async('asyncGetMedia')->size(Modal::SIZE_LG);
        $out[] = Layout::modal('comment_edit_modal', CommentEditLayout::class)->async('asyncGetComment')->size(Modal::SIZE_LG);

        return $out;
    }

    private function getMediaLayout(): \Orchid\Screen\Layout
    {
        $mediaId = (int)request()->input('mediaId');

        if ($mediaId) {
            return Layout::split([
                Layout::rows($this->getImagesLayout()),
                Layout::rows($this->getCommentsLayout($mediaId)),
            ])->ratio('60/40');
        }

        return Layout::rows($this->getImagesLayout());
    }

    private function getCommentsLayout(int $mediaId): array
    {
        $object = new \stdClass();

        $object->textarea = TextArea::make('message')
            ->rows(5)
            ->maxlength(10000);
        $object->viewField = ViewField::make('')->view('space');
        $object->location = Input::make('attachments')->type('file')->name('file')->multiple();
        $object->btn = Button::make('save')
            ->class('mr-btn-success pull-right')
            ->method('saveMediaComment', ['mediaId' => $mediaId, 'commentId' => 0]);

        $media = AlbumMedia::loadByOrDie($mediaId);

        $list = AlbumCommentListFilter::runQuery($mediaId)->paginate(10, pageName: 'comments');

        $table = new AlbumCommentListLayout()->build(new Repository(['comment-list' => $list->items()]));

        return [
            Group::make([
                ViewField::make('comments')->view('admin.h5')->value('Comments (' . $media->comments_count . ')'),
                Link::make('')
                    ->icon('close')
                    ->class('pull-right button-link ')
                    ->route('profiles.albums.details', ['user' => $this->user->id, 'album' => $this->album->id]),
            ]),

            ViewField::make('')->view('admin.users.albums.images_comment')->value(
                $this->buildMediaBlock($media)
            ),

            AlbumCommentListFilter::displayFilterCard(request()),
            Group::make([
                Button::make('Filter')->icon('filter')->name('filter')->novalidate()->method('runFiltering', ['mediaId' => $media->id])->class('mr-btn-success'),
                Button::make('Clear')->icon('close')->name('clear')->method('clearFilter', ['mediaId' => $media->id])->class('mr-btn-route'),
            ])->autoWidth(),
            ViewField::make('')->view('space'),
            ViewField::make('')->view('admin.row')->value($table),
            ViewField::make('')->view('admin.pagination')->value($list),
            ViewField::make('')->view('admin.users.conversations.add_message')->value($object)
        ];
    }

    public function asyncGetComment(int $commentId): array
    {
        return [
            'message' => AlbumMediaComment::loadByOrDie($commentId)->body,
        ];
    }

    public function deleteComment(int $mediaId, int $commentId): void
    {
        $this->albumCommentService->deleteComment($mediaId, $commentId);
    }

    public function saveMediaComment(Request $request, int $mediaId, int $commentId): void
    {
        $input = $request->validate([
            'message' => ['required', 'string', 'max:10000'],
        ])['message'];

        if ($commentId) {
            $this->albumCommentService->updateComment($commentId, $input);
            Toast::message('Updated comment');

            return;
        }

        $this->albumCommentService->createComment($mediaId, $this->user, $input);

        Toast::message('Added comment');
    }

    private function getImagesLayout(): array
    {
        $list = AlbumMediaListFilter::runQuery($this->album->id)->paginate(100);

        foreach ($list as &$media) {
            $media->preview = $this->albumService->generateMediaUrl($media, Size::Preview);
            $media->original = $this->albumService->generateMediaUrl($media, Size::Original);

            $media->btn = Group::make([
                ModalToggle::make('')
                    ->icon('pencil')
                    ->modalTitle('Edit')
                    ->modal('album_media_edit_modal', ['mediaId' => $media->id])
                    ->method('saveAlbumMedia'),
                Link::make((string)$media->comments_count)->icon('chat')->route('profiles.albums.details', ['user' => $this->user->id, 'album' => $this->album->id, 'mediaId' => $media->id]),
                Button::make('')
                    ->icon('trash')
                    ->confirm('Delete this media?')
                    ->method('deleteMedia', ['mediaId' => $media->id]),
            ])->autoWidth()->render();
        }

        return [
            ViewField::make('')->view('admin.users.albums.images')->value($list),
            ViewField::make('')->view('admin.pagination')->value($list),
        ];
    }

    private function buildMediaBlock(AlbumMedia $media): array
    {
        $media->preview = $this->albumService->generateMediaUrl($media, Size::Preview);
        $media->original = $this->albumService->generateMediaUrl($media, Size::Original);

        $media->btn = Group::make([
            ModalToggle::make('')
                ->icon('pencil')
                ->modalTitle('Edit')
                ->modal('album_media_edit_modal', ['mediaId' => $media->id])
                ->method('saveAlbumMedia'),
            Link::make('')->icon('chat')->route('profiles.albums.details', ['user' => $this->user->id, 'album' => $this->album->id, 'mediaId' => $media->id]),
            Button::make('')
                ->icon('trash')
                ->confirm('Delete this media?')
                ->method('deleteMedia', ['mediaId' => $media->id]),
        ])->autoWidth()->render();

        return [$media];
    }

    private function getImageActionLayout(): array
    {
        return [
            'Add images' => Layout::rows([
                Upload::make('images')
                    ->storage('albums')
                    ->maxFiles(20)
                    ->path('/tmp/' . $this->album->id),
                Button::make('save')->class('mr-btn-success')->method('saveImages'),
            ]),
        ];
    }

    public function asyncGetMedia(int $mediaId): array
    {
        $media = AlbumMedia::loadByOrDie($mediaId);

        if ($media->point) {
            $coords = DB::selectOne(
                'SELECT ST_Y(?) as lat, ST_X(?) as lng',
                [$media->point, $media->point]
            );

            $out['lat'] = $coords->lat;
            $out['lng'] = $coords->lng;
        } else {
            $point = $this->user->getUserLocation()?->getCity();
            $out['lat'] = $point?->lat;
            $out['lng'] = $point?->lng;
        }

        $out['languageCode'] = $this->user->getLanguage()->getCode();
        $out['languageLabel'] = $this->user->getLanguage()->getLabel();
        $out['address'] = $media->address;
        $out['sort'] = $media->sort;
        $out['description'] = $media->description;

        return $out;
    }

    public function saveAlbumMedia(Request $request, int $mediaId): void
    {
        $input = [
            'address'     => $request->input('address'),
            'sort'        => (int)$request->input('sort'),
            'lat'         => (float)$request->input('lat'),
            'lng'         => (float)$request->input('lng'),
            'description' => $request->input('description'),
        ];

        $this->albumService->updateMediaInfo($mediaId, $input);
    }

    public function saveImages(Request $request): void
    {
        foreach ($request->input('images', []) as $file) {
            $attachment = Attachment::loadByOrDie((int)$file);

            $path = Storage::disk('albums')->path($attachment->getFullPath());

            if (!file_exists($path) || !is_file($path)) {
                Attachment::where('hash', $attachment->getHash())->delete();
                $this->albumService->deleteTempFile($attachment->getFullPath());
                throw new Exception('Ошибка при загрузке файла. Попробуйте ещё раз.');
            }

            $media = new UploadedFile($path, $attachment->getOriginalName(), $attachment->getMime());

            $this->albumService->uploadMedia($this->album->id, $media);

            $this->albumService->deleteTempFile($attachment->getFullPath());
            $attachment->delete();
        }
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

        $out[] = ViewField::make('')->view('admin.raw')->value(
            'Total file size: ' . FileSizeConverter::bytesTo($this->albumService->getAlbumFileSize($this->album->id)) . ' Mb'
        );
        $out[] = ViewField::make('')->view('hr');
        $out[] = ViewField::make('')->view('admin.created_updated')->value($this->album);

        return $out;
    }

    public function deleteMedia(int $mediaId): void
    {
        $this->albumService->deleteMedia($this->album->id, $mediaId);
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
                Button::make('Purge media')
                    ->class('mr-btn-danger pull-right')
                    ->icon('trash')
                    ->method('purgeAlbumMedia')
                    ->confirm('Are you sure you want to delete all media in this album? This action cannot be undone.'),
                Button::make('Delete album')
                    ->class('mr-btn-danger pull-right')
                    ->icon('trash')
                    ->method('removeAlbum')
                    ->confirm('Are you sure you want to delete this album? This action cannot be undone.'),
            ])->autoWidth()
        ];
    }

    public function purgeAlbumMedia(): void
    {
        $this->albumService->purgeAlbumMedia($this->album->id);
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

    public function runFiltering(Request $request, int $mediaId): RedirectResponse
    {
        $list = [];
        foreach (AlbumCommentListFilter::FIELDS as $item) {
            if (!is_null($request->input($item))) {
                $list[$item] = $request->input($item);
            }
        }

        $parameters = [
            'user'    => $this->user->id,
            'album'   => $this->album->id,
            'mediaId' => $mediaId,
        ];

        return redirect()->route('profiles.albums.details', $list + $parameters);
    }

    public function clearFilter(int $mediaId): RedirectResponse
    {
        $parameters = [
            'user'    => $this->user->id,
            'album'   => $this->album->id,
            'mediaId' => $mediaId,
        ];

        return redirect()->route('profiles.albums.details', $parameters);
    }
}