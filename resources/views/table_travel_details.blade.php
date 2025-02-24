<div class="bg-white rounded shadow-sm mb-3">
    <div class="bg-white rounded shadow-sm p-4 py-4 d-flex flex-column">
        <table>
            <tbody>
            @foreach($rows as $key => $val)
                <tr>
                    <td><b>{{$key}}</b></td>
                    <td>{!! $val !!}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>
</div>
