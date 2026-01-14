@extends('emails.layouts.base')

@section('email_title', __('emails.news_digest.subject'))
@section('email_header', __('emails.news_digest.subject'))

@section('email_content')
    <p style="font-size:15px;color:#333;">
        {{ __('emails.news_digest.intro') }}
    </p>

    <ul style="padding-left:18px;margin:20px 0;">
        @foreach($newsDataList as $news)
            <li style="margin-bottom:12px;">
                <a href="{{ $news['url'] }}"
                   style="color:#1e88e5;text-decoration:none;font-size:15px;">
                    {{ $news['title'] }}
                </a>
            </li>
        @endforeach
    </ul>

    <p style="font-size:14px;color:#555;">
        {{ __('emails.news_digest.footer_text') }}
    </p>
@endsection

@section('email_unsubscribe')
    <a href="{{ $unsubscribeUrl }}"
       style="color:#777;text-decoration:underline;">
        {{ __('emails.unsubscribe') }}
    </a>
@endsection
