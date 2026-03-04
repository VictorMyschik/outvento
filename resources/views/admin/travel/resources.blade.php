<table class="table table-compact table-hover">
    <tr class="text-center">
        <td>ID</td>
        <td>Sort</td>
        <td>Title</td>
        <td>Link</td>
        <td>Size</td>
        <td>Created</td>
        <td>Updated</td>
        <td style="text-align: right"> #</td>
    </tr>
    @foreach($value as $resource)
        <tr>
            <td class="text-nowrap text-end">
                {{ $resource->id }}
            </td>
            <td class="text-nowrap text-center">
                {{ $resource->sort }}
            </td>
            <td class="text-nowrap text-end">
                {{ $resource->title }}
            </td>
            <td class="text-nowrap text-end">
                {!! $resource->linkAction !!}
            </td>
            <td>
                {{ $resource->size }}
            </td>
            <td class="text-nowrap text-end">
                {{ $resource->created_at->format('H:i d/m/Y') }}
            </td>
            <td class="text-nowrap text-end">
                {{ $resource->updated_at?->format('H:i d/m/Y') }}
            </td>
            <td style="text-align: right">
                <div class="text-right">{!! $resource->btn !!}</div>
            </td>
        </tr>
    @endforeach
</table>
