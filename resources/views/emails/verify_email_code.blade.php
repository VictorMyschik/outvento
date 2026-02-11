@extends('emails.layouts.base')

@section('email_title', __('emails.verify_registration_email.subject'))
@section('email_header', __('emails.verify_registration_email.subject'))

@section('email_content')
    <p style="font-size:15px;color:#333;margin-top:0;">
        {{ __('emails.verify_registration_email.greeting') }}
    </p>

    <p style="font-size:15px;color:#333;">
        {{ __('emails.verify_registration_email.instruction') }}
    </p>

    <div style="text-align:center;margin:20px 0;">
    <span style="
        display:inline-block;
        padding:14px 18px;
        font-size:24px;
        font-weight:bold;
        letter-spacing:4px;
        background-color:#f4f6f8;
        border-radius:6px;
        color:#111;
        min-width:160px;
    ">
        {{ $code }}
    </span>
    </div>

    <p style="font-size:14px;color:#555;">
        {{ __('emails.verify_registration_email.expire') }}
        <strong>{{ $expireMinutes }}</strong>
        {{ __('emails.minutes') }}.
    </p>

    <p style="font-size:14px;color:#555;">
        {{ __('emails.verify_registration_email.ignore') }}
    </p>
@endsection
