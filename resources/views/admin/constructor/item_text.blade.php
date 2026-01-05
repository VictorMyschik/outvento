<tr class="border">
    <td class="align-top" style="min-width: 250px;">
        <div>Тип: текст</div>
        <div>Сортировка: {{ $value->getSort() }}</div>
        <div class="mt-4"> {!! $value->btn !!} </div>
    </td>
    <td class="align-top">
        <div style="max-width: 600px;">{!! $value->getText() !!}</div>
    </td>
</tr>
