<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Album;

use App\Helpers\Linkify;
use App\Models\Albums\AlbumMediaComment;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class AlbumCommentListLayout extends Table
{
    public $target = 'comment-list';

    protected function columns(): iterable
    {
        return [
            TD::make('body')->render(function (AlbumMediaComment $comment) {
                $comment->avatar = route('api.v1.user.avatar', ['user' => $comment->user_id]);
                $comment->created = $comment->created_at->format('H:i:s d/m/Y');
                $comment->edited = $comment->edited_at?->format('H:i:s d/m/Y');
                $comment->body = Linkify::linkify($comment->body);

                $comment->btns = DropDown::make()->icon('options-vertical')->list([
                    ViewField::make('')->view('admin.raw')->value(Group::make([
                        ModalToggle::make('edit')
                            ->icon('pencil')
                            ->modal('comment_edit_modal')
                            ->modalTitle('Edit Comment')
                            ->method('saveMediaComment', ['mediaId' => $comment->media_id, 'commentId' => $comment->id])
                            ->asyncParameters(['mediaId' => $comment->media_id, 'commentId' => $comment->id]),
                        Button::make('for all')
                            ->icon('bs.trash3')
                            ->confirm('Are you sure you want to delete this message?')
                            ->method('deleteComment', ['mediaId' => $comment->media_id, 'commentId' => $comment->id]),
                    ])->autoWidth())
                ]);

                return ViewField::make('')->view('admin.users.albums.comment')->value($comment);
            })->sort(),
        ];
    }
}