@extends('mail.layout')

@section('content')
    <p>
        Ваш код для подтверждения регистрации: {{ $code }}
    </p>
@endsection
