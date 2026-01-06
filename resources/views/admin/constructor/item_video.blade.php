<tr class="border">
    <td class="align-top" style="min-width: 250px;">
        <div>Тип: видео</div>
        <div>Сортировка: {{ $value->getSort() }}</div>
        <div class="mt-4"> {!! $value->btn !!} </div>
    </td>
    <td class="align-top">
        <div>{!! $value->getTitle() !!}</div>
        <video height="320" style="max-width: 600px;" controls>
            <source src="{{URL::asset('storage/'.$value->path .'/'. $value->file_name)}}" type="video/{{$value->extension}}">
        </video>
        <div>{!! $value->getDescription() !!}</div>
    </td>
</tr>