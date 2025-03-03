Создано: {{$value?->created_at->format('d/m/Y H:i:s')}}

@if($value?->updated_at)
    | обновлено: {{$value?->updated_at?->format('d/m/Y H:i:s')}}
@endif

@if($value?->user_id)
    | {{$value?->getUser()?->name}}
@endif
