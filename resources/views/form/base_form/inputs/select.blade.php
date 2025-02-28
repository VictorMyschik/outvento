<span id="{{ $item->name }}"></span>
<label><span class="mr-bold mr-form-size-09">{{ $item->title }}</span>
    <select class="form-control mr-border-radius-5" style="margin-top: 7px;"
            name="{{ $item->name }}">
        @foreach($item->options as $key => $value)
            @if(isset($item->value) && (old($item->name, $item->value)) == $key)
                <option selected="selected" value="{{ $key }}">{{ $value }}</option>
            @else
                <option value="{{ $key }}">{{ $value }}</option>
            @endif
        @endforeach
    </select>
</label>
