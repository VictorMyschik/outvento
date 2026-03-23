<div class="">
    <div>
        <pre>{!! $value->content !!}</pre>
    </div>
    <div class="row">
        <div class="col">Создано: {!! $value->created_at !!}</div>

        @if($value->updated_at)
            <div class="col">Изменено: {!! $value->updated_at !!}</div>
        @endif
    </div>
</div>