<div class="bg-white rounded shadow-sm p-3 py-4 d-flex flex-column mb-3 full-height">
    <div class="row col-md-12 pb-4" style="height: 350px;">
        <div class="">
            Travel Images {{ count($images) ?: '' }}
        </div>
        <div class="">
            <form method="POST" action="{{ route('api.travel.image.upload') }}">
                @csrf

            </form>
        </div>
        @if(!count($images))
            <div class="form-label">Images not found</div>
        @else
            <div class="row">
                @foreach($images as $image)
                    <div class="col-md-4">
                        <div class="card">
                            <img
                                src="{{ route('admin.show.image', ['travel_id'=>$image->travel_id, 'image_name'=>$image->name]) }}"
                                class="card-img-top"
                                alt="{{ $image->description }}">
                            <div class="card-body">
                                <h5 class="card-title">{{ $image->name }}</h5>
                                <p class="card-text">{{ $image->description }}</p>
                                <div class="row">
                                    <a onclick="return confirm('Are you sure?');"
                                       href="{{route('admin.delete.travel.image', ['image_id'=>$image->id()])}}"
                                       class="col m-3 btn btn-danger btn-sm">delete</a>
                                    <a href="{{route('admin.show.image', ['travel_id'=>$image->travel_id, 'image_name'=>$image->name])}}"
                                       target="_blank"
                                       class="col m-3 btn btn-warning btn-sm"
                                       style="">
                                        <i class="fa fa-download"></i> download</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
