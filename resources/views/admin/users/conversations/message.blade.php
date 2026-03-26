<div class="card">
    <div class="card-header d-flex align-items-center gap-2 py-2">
        <a href="{{$value->avatar}}" target="_blank">
            <img src="{{$value->avatar}}"
                 class="rounded-circle border"
                 style="width:50px;height:50px;object-fit:cover"
                 alt="Avatar">
        </a>

        <div class="d-flex flex-column">
            <div class="fw-semibold">{{$value->user_name}}</div>
            <div class="text-muted small">
                {{$value->created_at->format('H:i:s d/m/Y')}}
            </div>
        </div>
    </div>
    <div class="card-body" style="white-space: pre-wrap;">
        {{ $value->content }}
    </div>
    <div class="card-footer">
        {!! $value->btns !!}
    </div>
</div>