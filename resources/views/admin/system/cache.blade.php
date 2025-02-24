<div class="bg-white rounded shadow-sm mb-3">

    <div class="">
        <table class="table table-striped">
            <tbody>
            @if($cacheInfoDTO)
                <tr>
                    <td>Name</td>
                    <td>{{$cacheInfoDTO->name}}</td>
                </tr>
                <tr>
                    <td>Version</td>
                    <td>{{$cacheInfoDTO->version}}</td>
                </tr>
                <tr>
                    <td>Memory Full</td>
                    <td>{{$cacheInfoDTO->memory_full}}</td>
                </tr>
                <tr>
                    <td>Objects in Cache</td>
                    <td>{{$cacheInfoDTO->objects_in_cache}}</td>
                </tr>
                <tr>
                    <td>Current Memory</td>
                    <td>{{$cacheInfoDTO->current_memory}}</td>
                </tr>
                <tr>
                    <td>Current DB</td>
                    <td>{{$cacheInfoDTO->current_db}}</td>
                </tr>
            @else
                <tr>
                    <td>Cache not found</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
</div>
