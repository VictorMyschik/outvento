<table class="table table-compact table-hover">
    <tbody>
    @foreach($value as $row)
        <tr>
            @foreach($row as $cell)
                <td>
                    {!! $cell !!}
                </td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
