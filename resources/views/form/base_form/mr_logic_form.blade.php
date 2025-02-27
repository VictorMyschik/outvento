@if(isset($form))
    @foreach($form as $key => $items)
        @if(isset($items['#type']))
            @if($items['#type'] === 'container')
                <div class="{{implode(' ', $items['#class'])}}">
                    @include('form.base_form.mr_logic_form', ['form' => $items[0]])
                </div>
            @else
                @if(isset($items['#class']))
                    <div class="{{implode(' ', $items['#class'])}}">
                        @include('form.base_form.inputs.' . $items['#type'], ['name' => $key, 'item' => $items])
                    </div>
                @else
                    <div class="mr-1">
                        @include('form.base_form.inputs.' . $items['#type'], ['name' => $key, 'item' => $items])
                    </div>
                @endif
            @endif
        @else
            <div>
                {!! $items !!}
            </div>
        @endif
    @endforeach
@endif
