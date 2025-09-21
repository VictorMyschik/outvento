<hr>
<table class="table table-compact">
    <tbody>
    @foreach($value as $row)
        <tr>
            @foreach($row as $item)
                <td>
                    {!! $item !!}
                </td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
