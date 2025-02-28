<div class="col-12 col-md form-group mb-md-2 @if(isset($item->classes)){{ implode(' ', $item->classes) }}@endif">
    <label for="{{ $item->name }}" class="mr-bold">{{ $item->title }}</label>
    <input id="{{ $item->name }}" type="text" name="{{ $item->name }}" value="{{ $item->value }}"
           placeholder='{{ $item->placeholder }}'
           class="form-control" @if(!empty($item->autofocus)) autofocus @endif>
</div>
