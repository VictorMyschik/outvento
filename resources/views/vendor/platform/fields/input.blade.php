@component($typeForm, get_defined_vars())
    <div data-controller="input" class="mb-2"
         data-input-mask="{{$mask ?? ''}}"
    >
        <input style="max-width: 100%;" {{ $attributes }}>
    </div>

    @empty(!$datalist)
        <datalist id="datalist-{{$name}}">
            @foreach($datalist as $item)
                <option value="{{ $item }}">
            @endforeach
        </datalist>
    @endempty
@endcomponent
