<table class="table table-compact table-hover">
    <tr class="text-center">
        <td>ID</td>
        <td>Type</td>
        <td title="Position on travel track">Pos.</td>
        <td>Rating</td>
        <td>Address</td>
        <td>Description</td>
        <td>Created</td>
        <td>Updated</td>
        <td style="text-align: right"> #</td>
    </tr>
    @foreach($value as $point)
        <tr>
            <td class="text-nowrap text-end">
                {{ $point->id() }}
            </td>
            <td class="text-nowrap text-center">
                {{ $point->getType()->getLabel() }}
            </td>
            <td class="text-nowrap text-end">
                {{ $point->position }}
            </td>
            <td class="text-nowrap text-end">
                {{ $point->rating }}
            </td>
            <td>
                {{ $point->address }}
            </td>
            <td>
                {!! $point->descriptionModal !!}
            </td>
            <td class="text-nowrap text-end">
                {{ $point->created_at->format('H:i d/m/Y') }}
            </td>
            <td class="text-nowrap text-end">
                {{ $point->updated_at?->format('H:i d/m/Y') }}
            </td>
            <td style="text-align: right">
                <div class="text-right">{!! $point->btn !!}</div>
            </td>
        </tr>
    @endforeach
</table>
