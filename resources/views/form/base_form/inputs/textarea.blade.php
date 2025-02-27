<div class="">
    <h6><label for="editor1" class="font-weight-bold">{{ $item['#title'] }}</label></h6>
    <textarea type="text" name="{{ $name }}"
              id="editor1"
              {{ $item['#required']??null }}
              rows="{{$item['#rows'] ?? 1}}"
              class="form-control mr-border-radius-5 @if(isset($item['#class'])) @foreach($item['#class'] as $class){{ $class }}@endforeach @endif"
              style="width: 100%">{{ old($name, $item['#value']) }}
  </textarea>
</div>
