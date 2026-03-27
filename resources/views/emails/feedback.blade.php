@extends('emails.layouts.base')

@section('email_content')
    <h2>New Feedback Received</h2>
    <div>
        <p><strong>Name: {{ $dto->name }}</strong></p>
        <p><strong>Email: {{ $dto->email }}</strong></p>
    </div>
    <div><strong>Message:</strong></div>
    <div>{{ $dto->message }}</div>
@endsection