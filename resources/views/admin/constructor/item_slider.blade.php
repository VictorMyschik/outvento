<tr class="border">
    <td class="align-top" style="min-width: 250px;">
        <div>Тип: слайдер</div>
        <div>Примечание: {!! $value->getDescription() !!}</div>
        <div>Сортировка: {{ $value->getSort() }}</div>
        <div class="mt-4"> {!! $value->btn !!} </div>
    </td>
    <td>
        <div class="slider-wrapper">
            @foreach($value->images as $image)
                <div class="slider-item">
                    <a class="slider-item--link" href="{{$image->getUrl()}}" target="_blank">
                        <img src="{{$image->getUrl()}}" alt="">
                    </a>
                    <div class="slider-item--block">
                        <div class="slider-item--desc p-2">
                            {{$image->getDisplayName()}}
                        </div>
                        <div class="slider-item--footer p-2">
                            {{$image->btn}}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>


    </td>
</tr>

<style>
    .slider-wrapper {
        display: flex;
        padding: 1rem;
        margin: 0 auto;
        overflow-x: auto;
        height: 300px;
    }

    .slider-item {
        flex: 0 0 300px;
        height: 150px;
        padding: 1rem;
        margin-right: 1rem;
    }

    .slider-item--link {
        width: 100%;
        display: flex;
        height: 100px;
        position: relative;
    }

    .slider-item--link img {
        width: 100%;
        height: 100%;
        position: absolute;
        object-fit: contain;
    }

    .slider-item--block {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
    }

    .slider-item--block .slider-item--desc {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 50%;
    }

    .slider-item--block .slider-item--footer {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

</style>