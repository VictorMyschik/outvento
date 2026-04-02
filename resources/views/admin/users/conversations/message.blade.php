@php
    use Orchid\Screen\Actions\Button;$isMine = $value->current_user_id === $value->user_id;
@endphp

<div class="d-flex {{ $isMine ? 'justify-content-end' : 'justify-content-start' }} mb-2">
    <div class="card" style="max-width: 70%;">

        <div class="card-header d-flex align-items-center gap-2 py-2">
            <a href="{{$value->avatar}}" target="_blank">
                <img src="{{$value->avatar}}"
                     class="rounded-circle border"
                     style="width:40px;height:40px;object-fit:cover"
                     alt="Avatar">
            </a>

            <div class="fw-semibold small">
                <a href="{{route('profiles.details',['user' => $value->user_id])}}"
                   target="_blank">{{$value->user_name}}</a>
                <div class="text-muted">
                    {{ $value->created ? 'Created' . ' ' . $value->created : '' }}
                    {{ $value->edited ? 'Edited' . ' | ' . $value->edited : '' }}

                </div>
            </div>

            <div class="ms-auto d-flex align-items-center gap-1">
                {!! $value->btns !!}
            </div>
        </div>

        @if($value->parent_id)
            <div class="text-muted small border-start ps-2 mb-1">
                ↩ {{ Str::limit($value->parent_content, 100) }}
            </div>
        @endif

        @if(!empty(trim((string)$value->content)))
            <div class="card-body" style="white-space: pre-wrap;">{!! $value->content !!}</div>
        @endif
        @if(count($value->files))
            <div class="card-body pt-2">
                <div class="d-flex flex-wrap gap-2 mb-2">
                    @foreach($value->files as $file)
                        @if(str_starts_with($file->mime_type, 'image/'))
                            <div class="position-relative">
                                <a href="{{ Storage::disk('conversations')->url($file->path) }}" target="_blank">
                                    <img src="{{ route('api.v1.admin.conversation.attachment.show.media', ['conversationId' => (int)$file->conversation_id, 'fileId' => $file->id]) }}"
                                         style="width:120px;height:120px;object-fit:cover;border-radius:8px;"
                                         alt="{{$file->name}}">
                                </a>
                                <div class="image-delete-btn">
                                        <?php
                                        $deleteFileBtn = Button::make('')
                                            ->icon('bs.trash3')
                                            ->class('trash-icon')
                                            ->confirm('Delete this file?')
                                            ->method('deleteMessageFile', ['messageId' => $value->id, 'fileId' => $file->id]);
                                        ?>
                                    {{$deleteFileBtn}}
                                </div>
                            </div>
                        @endif

                        @if(str_starts_with($file->mime_type, 'video/'))
                            <div class="position-relative">
                                <video controls
                                       style="width:450px;height:240px;border-radius:8px;object-fit:cover;max-width: 500px;">
                                    <source src="{{ route('api.v1.admin.conversation.attachment.get', ['conversationId' => (int)$file->conversation_id, 'hash' => $file->hash]) }}"
                                            type="{{ $file->mime_type }}">
                                </video>

                                {{-- delete --}}
                                <div class="image-delete-btn">
                                        <?php
                                        $deleteFileBtn = Button::make('')
                                            ->icon('bs.trash3')
                                            ->class('trash-icon')
                                            ->confirm('Delete this file?')
                                            ->method('deleteMessageFile', [
                                                'messageId' => $value->id,
                                                'fileId'    => $file->id
                                            ]);
                                        ?>
                                    {{$deleteFileBtn}}
                                </div>

                            </div>
                        @endif
                    @endforeach
                </div>

                {{-- Остальные файлы --}}
                <div class="d-flex flex-column gap-1">
                    @foreach($value->files as $file)
                        @if(!str_starts_with($file->mime_type, 'image/'))
                            <a href="{{ route('api.v1.admin.conversation.attachment.get', ['conversationId' => (int)$file->conversation_id, 'hash' => $file->hash]) }}"
                               target="_blank"
                               class="d-flex align-items-center gap-2 text-decoration-none border rounded p-2">
                                <i class="fa fa-file"></i>
                                <span class="text-truncate" style="max-width: 200px;">
                                    {{ basename($file->name) }}
                                </span>
                                <span class="text-muted small ms-auto">
                                    {{ number_format($file->size / (1024 * 1024), 2) }} MB
                                </span>
                                <span>
                                    <?php
                                        $deleteFileBtn = Button::make('')
                                            ->icon('bs.trash3')
                                            ->class('trash-icon')
                                            ->confirm('Are you sure you want to delete this message?')
                                            ->method('deleteMessageFile', ['messageId' => $value->id, 'fileId' => $file->id]);
                                        ?>
                                    {{$deleteFileBtn}}
                                </span>
                            </a>
                        @endif
                    @endforeach
                </div>

            </div>
        @endif

        @if(count($value->files))
            <div class="card-header d-flex align-items-center gap-2 py-2">
                Added files: {{ count($value->files) }} | Size: {{ $value->sumFileSize }}
                {{$value->downloadAllFiles}}
                {{$value->btnAllFileDelete}}
            </div>
        @endif
    </div>
</div>

<style>
    .card-body a {
        color: #0d6efd;
        text-decoration: none;
    }

    .card-body a:hover {
        text-decoration: underline;
    }
    .trash-icon {
        color: #b30000;
        border: none;
    }

    .trash-icon:hover {
        color: #12b300;
    }
    .image-delete-btn {
        position: absolute;
        top: 4px;
        right: 4px;
        z-index: 10;
        background: rgba(255,255,255,0.8);
        border-radius: 6px;
        padding: 2px;
        display: none;
    }

    .position-relative:hover .image-delete-btn {
        display: block;
    }
    .video-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
    }
</style>
