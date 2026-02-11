<table class="table table-compact table-hover">
    <thead>
    <tr>
        @foreach($value['header'] ?? [] as $head)
            <th class="fw-bold">{{$head}}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($value['body'] ?? [] as $row)
        <tr>
            @foreach($value['header'] as $head)
                <td>
                    {!! $row[$head] !!}
                </td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>

