@foreach($attributes as $key => $val)
    @if($key !== 'class' && is_array($val))
        {{$key}}='{{ implode(' ', $val) }}'
    @elseif(is_string($val))
        {{$key}}='{{$val}}'
    @endif
@endforeach