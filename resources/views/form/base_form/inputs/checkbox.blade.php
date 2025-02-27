<div class="mr-form-size-09">
    <label class="mr-bold">
        {{ $item['#title'] }}
        <input type="checkbox" name="{{ $name }}" value="{{ $item['#value']?:0 }}"
               @if(isset($item['#checked']) && $item['#checked'])
                   checked="checked"
               @endif
               @if(isset($item['#attributes']))
                   @foreach($item['#attributes'] as $attribute)
                       {{ $attribute }}
                   @endforeach
               @endif
               class='@if(isset($item['#class']))@foreach($item['#class'] as $class){{ $class }}@endforeach @endif'>
    </label>
</div>
