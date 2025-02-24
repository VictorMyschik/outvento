<table class="table table-compact table-hover">
    <tr>
        <td>ID</td>
        <td>Name</td>
        <td>Email</td>
        <td style="text-align: right"> Actions</td>
    </tr>
    @foreach($value as $userInTravel)
        <tr>
            <td>
                {{ $userInTravel->getUser()->id }}
            </td>
            <td>
                {{ $userInTravel->getUser()->name }}
            </td>
            <td>
                {{ $userInTravel->getUser()->email }}
            </td>
            <td style="text-align: right">
                <div class="text-right">{!! $userInTravel->btn !!}</div>
            </td>
        </tr>
    @endforeach
</table>
