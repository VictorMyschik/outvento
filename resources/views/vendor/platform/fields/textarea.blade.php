@component($typeForm, get_defined_vars())
    <textarea style="max-width: 100%; position: relative;" {{ $attributes }}>{{ $value ?? '' }}</textarea>
    <style>
        .cke_notifications_area {
            display: none !important;
        }
    </style>
@endcomponent
