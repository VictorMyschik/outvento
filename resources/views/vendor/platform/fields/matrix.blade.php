@component($typeForm, get_defined_vars())
    <table class="table table-bordered border-light"
           data-controller="matrix"
           data-matrix-index="{{ $index }}"
           data-matrix-rows="{{ $maxRows }}"
           data-matrix-key-value="{{ var_export($keyValue) }}"
    >
        <thead>
        <tr class="border-light-subtle">
            @foreach($columns as $key => $column)
                <th scope="col" class="fw-bolder">
                    {{ is_int($key) ? $column : $key }}
                </th>
            @endforeach
                <th class="text-center align-middle">
                    #
                </th>
        </tr>
        </thead>
        <tbody class=" overflow-x-auto">

        @foreach($value as $key => $row)
            @include('platform::partials.fields.matrixRow',['row' => $row, 'key' => $key])
        @endforeach

        <tr class="add-row">
            <th colspan="{{ count($columns) }}" class="text-center p-0">
                <a href="#" data-action="matrix#addRow" class="btn btn-block small text-muted">
                    <x-orchid-icon path="bs.plus-circle" class="me-2"/>

                    <span>{{ __($addRowLabel) }}</span>
                </a>
            </th>
        </tr>

        <template class="matrix-template">
            @include('platform::partials.fields.matrixRow',['row' => [], 'key' => '{index}'])
        </template>
        </tbody>
    </table>
@endcomponent
