<div class="row">
    <table class="table table-compact">
        <tr>
            <td>Сортировка</td>
            <td>{{$value['photo']->sort}}</td>
        </tr>
        <tr>
            <td>Обновлён</td>
            <td>{{$value['photo']->updated_at ? $value['photo']->updated_at->format('d/m/Y h:i:s') : $value['photo']->created_at->format('d/m/Y h:i:s')}}</td>
        </tr>
    </table>
</div>
