<div class="slider-wrapper">
    @foreach($value as $image)
        <div class="slider-item border">
            <a class="slider-item--link" href="{{$image->getUrlExt()}}" target="_blank">
                <img src="{{$image->getUrlExt()}}" alt="">
            </a>
            <div class="slider-item--block">
                <div class="slider-item--desc p-1">

                </div>
                <div class="slider-item--footer p-1">
                    {{$image->btn}}
                </div>
            </div>
        </div>
    @endforeach
</div>
<style>
    .slider-wrapper {
        display: flex;
        margin: 0 1px;
        overflow-x: auto;
        height: 250px;
    }

    .slider-item {
        flex: 0 0 300px;
        height: 120px;
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
