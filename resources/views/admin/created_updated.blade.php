Создано: {{$model?->created_at->format('d/m/Y H:i:s')}}

@if($model?->updated_at)
    | обновлено: {{$model?->updated_at?->format('d/m/Y H:i:s')}}
@endif

@if($model?->user_id)
    | {{$model?->getUser()?->name}}
@endif
