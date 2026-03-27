@foreach($value as $item)
    <li>{{ $item->locale }}: {{ $item->name }}</li>
@endforeach