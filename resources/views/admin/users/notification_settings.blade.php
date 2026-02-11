<table class="table table-compact table-hover">
    <thead>
    <tr>
        @foreach($value ?? [] as $row)
            @foreach($row as $title => $cell)
                <th>{{ $title }}</th>
            @endforeach
            @break
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($value ?? [] as $row)
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

