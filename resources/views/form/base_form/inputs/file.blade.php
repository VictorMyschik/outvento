<div id="{{ $name }}"></div>
<input type="file" name="{{ $name }}"
       class="col-sm-12 mr-border-radius-5 padding-horizontal
@if(isset($item['#class'])) @foreach($item['#class'] as $class){{ $class }}@endforeach @endif">