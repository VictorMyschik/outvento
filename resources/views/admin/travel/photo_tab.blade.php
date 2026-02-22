    <table class="table table-compact">
        <tr>
            @foreach($value as $photo)
                <td style="border:#e1e1e1 solid 2px; border-radius: 15px;">
                    {!! $photo['data'] !!}
                    {!! $photo['actions'] !!}
                </td>
            @endforeach
        </tr>
    </table>