<footer class="pb-3 w-100 v-md-center px-4 d-flex flex-wrap">
    <div class="col-auto me-auto">
        <small class="d-block">
            {{ __('Displayed records: :from-:to of :total',[
                'from' => ($value->currentPage() -1 ) * $value->perPage() + 1,
                'to' => ($value->currentPage() -1 ) * $value->perPage() + count($value->items()),
                'total' => $value->total(),
            ]) }}
        </small>
    </div>
    <div class="col-auto overflow-auto flex-shrink-1 mt-3 mt-sm-0">
        @if($value instanceof \Illuminate\Contracts\Pagination\CursorPaginator)
            {!!
                $value->appends(request()
                    ->except(['page','_token']))
                    ->links('platform::partials.pagination')
            !!}
        @elseif($value instanceof \Illuminate\Contracts\Pagination\Paginator)
            {!!
                $value->appends(request()
                    ->except(['page','_token']))
                    ->onEachSide($onEachSide ?? 3)
                    ->links('platform::partials.pagination')
            !!}
        @endif
    </div>
</footer>
