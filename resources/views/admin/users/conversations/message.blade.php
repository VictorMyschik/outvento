@php
    $isMine = $value->current_user_id === $value->user_id;
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

            <div class="d-flex flex-column">
                <div class="fw-semibold small">
                    {{$value->user_name}}
                </div>
                <div class="text-muted small">
                    {!! $value->btns !!}
                </div>
            </div>
        </div>

        <div class="card-body" style="white-space: pre-wrap;">{{ $value->content }}</div>
    </div>
</div>
