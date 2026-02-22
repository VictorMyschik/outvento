<table class="table table-compact">
    <tr>
        <td>Sort</td>
        <td>{{$value['photo']->sort}}</td>
    </tr>
    <tr>
        <td>Updated</td>
        <td>{{$value['photo']->updated_at ? $value['photo']->updated_at->format('d/m/Y h:i:s') : $value['photo']->created_at->format('d/m/Y h:i:s')}}</td>
    </tr>
    <tr>
        <td>Description</td>
        <td>{{$value['photo']->description}}</td>
    </tr>
</table>
