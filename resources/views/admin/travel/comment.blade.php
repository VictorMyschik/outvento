@php use Orchid\Screen\Actions\Button;use Orchid\Screen\Actions\ModalToggle;use Orchid\Screen\Fields\Group; @endphp
<div class="comment" style="margin-left:  20px;">

    <div class="comment-header mr-table-head">
         <span class="comment-score">
             <button class="mr-btn-route">↑</button> {{ $value->score }}
        </span>
        <strong><a href="{{route('profiles.details', ['user' => $value->user_id])}}"
                   target="_blank">{{ $value->name }}</a></strong>
        <span class="comment-date">
            {{ \Carbon\Carbon::parse($value->created_at)->format('H:i:s d/m/Y') }}
        </span>
    </div>

    <div class="comment-body">
        @if($value->is_deleted)
            <em>Комментарий удалён</em>
        @else
            {!! $value->content !!}
        @endif
    </div>

    <div class="comment-footer">
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
        ?>
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
    .comment {
        border-left: 2px solid #ddd;
        border-top: 2px solid #ddd;
        padding-left: 10px;
        margin-top: 10px;
    }

    .comment-header {
        font-size: 14px;
        color: #666;
    }

    .comment-body {
        margin: 5px 0;
    }

    .comment-footer {
        font-size: 12px;
        color: #888;
    }
</style>
