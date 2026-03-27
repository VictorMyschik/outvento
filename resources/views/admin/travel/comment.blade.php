@php use Orchid\Screen\Actions\Button;use Orchid\Screen\Actions\ModalToggle;use Orchid\Screen\Fields\Group; @endphp
<?php
$value->btns = Group::make([
    ModalToggle::make('add')
        ->class('mr-btn-success pull-left')
        ->modal('edit_comment_modal')
        ->modalTitle('Create comment')
        ->method('saveTravelComment', ['commentId' => 0, 'parentId' => $value->id]),
    ModalToggle::make('edit')
        ->class('mr-btn-primary pull-left')
        ->modal('edit_comment_modal')
        ->modalTitle('Edit comment')
        ->method('saveTravelComment', ['commentId' => $value->id, 'parentId' => $value->parent_id]),
    Button::make('delete')
        ->class('mr-btn-danger pull-right')
        ->confirm('Delete comment?')
        ->method('deleteTravelComment', ['commentId' => $value->id]),
])->autoWidth();

$value->scoreBtn = Button::make('↑ '. $value->score)
    ->class('mr-btn-route')
    ->method('toggleUpVote', ['commentId' => $value->id]);
?>

<div class="comment" style="margin-left:  20px; margin-top: 15px;">
    <div class="comment-header mr-table-head">

        <div class="comment-header-left">
            <div class="comment-score">
                {!! $value->scoreBtn !!}
            </div>

            <strong class="comment-user">
                <a href="{{ route('profiles.details', ['user' => $value->user_id]) }}"
                   target="_blank">
                    {{ $value->name }}
                </a>
            </strong>
        </div>

        <div class="comment-date">
            {{ \Carbon\Carbon::parse($value->created_at)->format('H:i:s d/m/Y') }}
        </div>

    </div>

    <div class="comment-body">
        @if($value->is_deleted)
            <em>Комментарий удалён</em>
        @else
            {!! $value->content !!}
        @endif
    </div>

    <div class="comment-footer">
        {!! $value->btns !!}

        @if($value->replies_count > 0)
            <span class="comment-replies">
                {{ $value->replies_count }} replies
            </span>
        @endif

    </div>

    @if(!empty($value->children))
        <div class="comment-children">
            @foreach($value->children as $child)
                @include('admin.travel.comment', ['value' => $child])
            @endforeach
        </div>
    @endif

</div>

<style>
    .comment-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .comment-header-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .comment-score {
        display: flex;
        align-items: center;
    }

    .comment-score button {
        margin-left: 4px;
    }

    .comment-user a {
        text-decoration: none;
    }

    .comment-date {
        font-size: 12px;
        color: #c55;
        white-space: nowrap;
        margin-right: 10px;
    }
</style>
