<div>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div id="errors"></div>
    @foreach($form as $item)
        <div class="mb-2">
            @include("form.base_form.inputs.{$item->getType()}", ['item' => $item])
        </div>
    @endforeach
</div>
