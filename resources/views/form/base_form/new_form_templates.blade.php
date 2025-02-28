<meta name="csrf-token" content="{{ csrf_token() }}">
<div id="errors"></div>
@foreach($form as $item)
    <div class="mb-2">
        <div class="row d-flex grid d-md-grid form-group">
            @if($item->getType() === 'group')
                @foreach($item->fields as $itemFromGroup)
                    @include("form.base_form.inputs.{$itemFromGroup->getType()}", ['item' => $itemFromGroup])
                @endforeach
            @else
                @include("form.base_form.inputs.{$item->getType()}", ['item' => $item])
            @endif
        </div>
    </div>
@endforeach
