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

@if(!empty(trim((string)$value->body)))
    <div class="card-body" style="white-space: pre-wrap;">{!! $value->body !!}</div>
@endif

<style>
    .card-body a {
        color: #0d6efd;
        text-decoration: none;
    }

    .card-body a:hover {
        text-decoration: underline;
    }
</style>
