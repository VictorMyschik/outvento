<div>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div id="errors"></div>
    @if(isset($form['#display']))
        {!! $form['#display'] !!}
    @else
        @foreach($form as $key => $items)
            @if(isset($items['#type']))
                <div class="mb-2">
                    @include("form.base_form.inputs.{$items['#type']}", ['name' => $key, 'item' => $items])
                </div>
            @else
                @if(substr($key, 0, 1) !== '#')
                    {!! $items !!}
                @endif
            @endif
        @endforeach
    @endif
</div>
