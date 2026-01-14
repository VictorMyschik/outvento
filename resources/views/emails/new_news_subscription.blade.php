@extends('emails.layouts.base')

@section('email_title', __('emails.new_news_subscription'))
@section('email_header', __('emails.new_news_subscription'))

@section('email_content')
    <p style="font-size:15px;color:#333;margin-top:0;">
        {{ __('emails.greeting') }}
    </p>

    <p style="font-size:15px;color:#333;">
        {{ __('emails.intro') }}
    </p>

    <p style="font-size:15px;color:#333;font-weight:bold;">
        {{ __('emails.benefits_title') }}
    </p>

    <ul style="padding-left:18px;margin:16px 0;font-size:15px;color:#333;">
        @foreach(__('emails.benefits') as $benefit)
            <li style="margin-bottom:8px;">
                {{ $benefit }}
            </li>
        @endforeach
    </ul>

    <p style="font-size:14px;color:#555;">
        {{ __('emails.ignore') }}
    </p>
@endsection

@section('email_unsubscribe')
    <a href="{{ $unsubscribeUrl }}"
       style="color:#777;text-decoration:underline;">
        {{ __('emails.unsubscribe') }}
    </a>
@endsection
