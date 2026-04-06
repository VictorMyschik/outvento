<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Album;

use App\Models\Albums\Album;
use App\Models\User;
use App\Orchid\Filters\Album\AlbumListFilter;
use App\Orchid\Layouts\Album\AlbumEditLayout;
use App\Orchid\Layouts\Album\AlbumListLayout;
use App\Orchid\Screens\User\UserBaseScreen;
use App\Services\Albums\Enum\Visibility;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Modal;
use Orchid\Support\Facades\Layout;

class AlbumScreen extends UserBaseScreen
{
    public ?User $user = null;

    public function name(): string
    {
        return 'ID ' . $this->user->id . ($this->user->getFullName() ? ' | ' . $this->user->getFullName() : ' | ' . $this->user->name);
    }

    public function description(): string
    {
        return $this->user->getRolesDisplay() . ' | ' . View('admin.created_updated', ['value' => $this->user])->toHtml();
    }

    public function query(User $user): iterable
    {
        $this->setAvatar($user->getAvatar());

        return [
            'user' => $user,
            'list' => AlbumListFilter::runQuery($user->id)->paginate(10)
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('add')
                ->icon('plus')
                ->class('mr-btn-success')
                ->modal('album_edit_modal')
                ->modalTitle('Add Album')
                ->method('saveAlbum', ['id' => 0]),
            Link::make('Назад')->class('mr-btn mr-btn-route')->icon('arrow-up')->route('profiles.list'),
        ];
    }

    public function layout(): iterable
    {
        return [
            AlbumListFilter::displayFilterCard(request()),
            AlbumListLayout::class,
            Layout::modal('album_edit_modal', AlbumEditLayout::class)->size(Modal::SIZE_LG)
        ];
    }

    public function saveAlbum(Request $request, int $id): RedirectResponse
    {
        $input = $request->validate([
            'album.title'      => ['required', 'string', 'max:255'],
            'album.visibility' => ['required', 'string', Rule::enum(Visibility::class)],
            'album.description' => ['nullable', 'string'],
        ])['album'];

        $id = $this->albumService->saveAlbum($id, $input['title'], Visibility::from($input['visibility']), $this->user, $input['description']);

        return redirect()->route('profiles.albums.details', ['user' => $this->user->id, 'album' => $id]);
    }

    public function runFiltering(Request $request): RedirectResponse
    {
        $list = [];
        foreach (AlbumListFilter::FIELDS as $item) {
            if (!is_null($request->input($item))) {
                $list[$item] = $request->input($item);
            }
        }

        return redirect()->route('profiles.albums.list', $list + ['user' => $this->user->id]);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('profiles.albums.list', ['user' => $this->user->id]);
    }
}