<table class="table table-compact table-hover">
    <tr>
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
            <td>
                {{ $point->id() }}
            </td>
            <td>
                {{ $point->getType()->getLabel() }}
            </td>
            <td style="text-align: right">
                <div class="text-right">{!! $point->btn !!}</div>
            </td>
        </tr>
    @endforeach
</table>
