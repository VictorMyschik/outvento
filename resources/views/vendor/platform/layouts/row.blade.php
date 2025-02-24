<fieldset class="mb-2" data-async>
    <div class="bg-white rounded shadow-sm p-4 py-4 d-flex flex-column">
        @empty(!$title)
            <div class="col">
                <legend class="text-black">
                    {{ $title }}
                </legend>
            </div>
        @endempty
        {!! $form ?? '' !!}
    </div>
</fieldset>
