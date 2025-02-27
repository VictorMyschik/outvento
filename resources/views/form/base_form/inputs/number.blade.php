<label><span class="mr-form-size-09">{{ $item['#title'] }}</span>
    <input type="number" name="{{ $name }}" value="{{ $item['#value'] }}"
           @if(isset($item['#attributes']))
               @include('layouts.Elements.input_attr',['attributes'=>$item['#attributes']])
           @endif
           @if(isset($item['#class']))class="form-control form-control-sm @foreach($item['#class'] as $class){{ $class }}@endforeach"@endif>
</label>
