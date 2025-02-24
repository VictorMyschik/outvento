<div id="accordion-{{$templateSlug}}" class="accordion mb-3">
    @foreach($manyForms as $name => $forms)
        <div class="accordion-heading collapsed"
             id="heading-{{\Illuminate\Support\Str::slug($name)}}"
             data-bs-toggle="collapse"
             data-bs-target="#collapse-{{\Illuminate\Support\Str::slug($name)}}"
             aria-expanded="true"
             aria-controls="collapse-{{\Illuminate\Support\Str::slug($name)}}">
            <div class="pt-2 pb-2 mb-1 px-4 mt-2 bg-white" style="cursor: pointer">
                {!! $name !!}
            </div>
        </div>

        <div id="collapse-{{\Illuminate\Support\Str::slug($name)}}"
             class=" collapse"
             aria-labelledby="heading-{{\Illuminate\Support\Str::slug($name)}}"
             @if (!$stayOpen)
                 data-bs-parent="#accordion-{{$templateSlug}}"
            @endif
        >
            @foreach($forms as $form)
                {!! $form !!}
            @endforeach
        </div>
    @endforeach
</div>
