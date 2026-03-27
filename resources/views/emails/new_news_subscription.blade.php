@extends('emails.layouts.base')

@section('email_title', __('emails.new_news_subscription.new_news_subscription'))
@section('email_header', __('emails.new_news_subscription.new_news_subscription'))

@section('email_content')
    <p style="font-size:15px;color:#333;margin-top:0;">
        {{ __('emails.new_news_subscription.greeting') }}
    </p>

    <p style="font-size:15px;color:#333;">
        {{ __('emails.new_news_subscription.intro') }}
    </p>

    <p style="font-size:15px;color:#333;">
        <a href="{{ $confirmationUrl }}" style="display:inline-block;padding:10px 20px;font-size:16px;color:#fff;background-color:#28a745;text-decoration:none;border-radius:5px;">
            {{ __('emails.new_news_subscription.confirm_subscription') }}
        </a>
    </p>

    <p>
        {{ __('emails.link_expire') }}
        <strong>{{ $expireMinutes }}</strong>
        {{ __('emails.minutes') }}.
    </p>

    <p style="font-size:14px;color:#555;">
        {{ __('emails.new_news_subscription.ignore') }}
    </p>
@endsection
