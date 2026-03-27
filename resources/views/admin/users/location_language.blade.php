<div class="row">
    <div class="col">
        <div class="form-group">
            <div class="form-label">Location</div>
            @if($value['location']['city'])
                {{ $value['location']['city'] }}, {{ $value['location']['country'] }}
            @else
                -
            @endif
        </div>
        <div class="form-group mt-2">
            {!! $value['location']['btns'] !!}
        </div>
    </div>
    <div class="col">
        <div class="form-group">
            <div class="form-label">Languages</div>
            @if(!empty($value['languages']['list']))
                @foreach($value['languages']['list'] as $lang)
                    {{ $lang }}
                @endforeach
            @else
                -
            @endif
        </div>
        <div class="form-group mt-2">
            {!! $value['languages']['btns'] !!}
        </div>
    </div>
</div>
