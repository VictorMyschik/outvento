@extends('emails.layouts.base')

@section('email_title', __('emails.reset_password_subject'))
@section('email_header', __('emails.reset_password_requested'))

@section('email_content')
    <p>
        {{ __('emails.reset_password_requested') }}
    </p>

    <p>
        {{ __('emails.reset_password_instruction') }}
    </p>

    <p>
        <a href="{{ $url }}" target="_blank">
            {{ $url }}
        </a>
    </p>

    <p>
        {{ __('emails.link_expire') }}
        <strong>{{ $expireMinutes }}</strong>
        {{ __('emails.minutes') }}.
    </p>

    <p>
        {{ __('emails.reset_password_ignore') }}
    </p>
@endsection

