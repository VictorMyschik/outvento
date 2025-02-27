<div class="@if(isset($item['#attributes']['class'])){{ implode(' ', $item['#attributes']['class']) }}@endif mb-3">
    <label for="{{ $name }}" class="mr-bold">{{ $item['#title'] }}</label>
    <input id="{{ $name }}" type="text" name="{{ $name }}" value="{{ $item['#value'] }}"
           placeholder='{{ $item['#placeholder'] ?? null }}'
           @if(isset($item['#attributes']))
               @include('layouts.Elements.input_attr', ['attributes'=>$item['#attributes']])
           @endif
           class="form-control" @if(!empty($item['#autofocus'])) autofocus @endif>
</div>
