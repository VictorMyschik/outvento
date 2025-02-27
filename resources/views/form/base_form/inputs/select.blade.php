<span id="{{ $name }}"></span>
<label><span class="mr-bold mr-form-size-09">{{ $item['#title'] }}</span>
    <select class="form-control mr-border-radius-5" style="margin-top: 8px;"
    @if(isset($item['#attributes']))
        @foreach($item['#attributes'] as $attr_key => $attr_val)
            {{ $attr_key }}='{{ $attr_val }}'
        @endforeach
    @endif
    name="{{ $name }}">
    @foreach($item['#options'] as $key => $value)
        @if(isset($item['#default_value']) && (old($name, $item['#default_value'])) == $key))
        <option selected="selected" value="{{ $key }}">{{ $value }}</option>
        @else
            <option value="{{ $key }}">{{ $value }}</option>
            @endif
            @endforeach
            </select>
</label>
