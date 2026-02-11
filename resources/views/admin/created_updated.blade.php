Создано: {{$value?->created_at->format('d/m/Y H:i:s')}}

@if($value?->updated_at)
    | обновлено: {{$value?->updated_at?->format('d/m/Y H:i:s')}}
@endif

@if($value?->deleted_at)
    | удалено: {{$value?->deleted_at?->format('d/m/Y H:i:s')}}
@endif
