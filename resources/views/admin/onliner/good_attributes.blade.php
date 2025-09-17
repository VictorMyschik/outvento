<table class="table table-hover table-compact">
    <tbody>
    @foreach($value as $groupName => $attributes)
        <tr>
            <th colspan="2">{{ $groupName }}</th>
        </tr>
        @foreach($attributes['data'] as $attribute)
            <tr>
                <td>{{ $attribute['name'] }}</td>
                <td>
                    @if($attribute['bool'] === true)
                        <x-orchid-icon path="fa.check"/>
                    @elseif($attribute['bool'] === false)
                        <x-orchid-icon path="fa.ban"/>
                    @endif
                    {!! $attribute['value'] !!}
                </td>
            </tr>
        @endforeach
    @endforeach
    </tbody>
</table>