<tr class="border">
    <td class="align-top" style="min-width: 250px;">
        <div>Тип: видео (ссылка)</div>
        <div>Сортировка: {{ $value->getSort() }}</div>
        <div class="mt-4"> {!! $value->btn !!} </div>
    </td>
    <td class="align-top">
        <iframe width="560" height="315" src="{{$value->url}}"
                title="YouTube video player" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
        <div>{{$value->getDescription()}}</div>
    </td>
</tr>