<div class="">
    <label>
        {{ $item['#title'] }}
        <div id="{{ $name }}"></div>
        <input type="time" name="{{ $name }}" value="{{ $item['#value'] ?? null }}"
               @if(isset($item['#attributes']))
                   @foreach($item['#attributes'] as $attribute)
                       {{ $attribute }}
                   @endforeach
               @endif
               step="1"
               class='mr-border-radius-5 @if(isset($item['#class'])) @foreach($item['#class'] as $class){{ $class }}@endforeach @endif'>
    </label>
</div>
