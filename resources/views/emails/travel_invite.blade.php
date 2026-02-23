@extends('emails.layouts.base')

@section('email_title', __('emails.travel_invite.title'))
@section('email_header', __('emails.travel_invite.header'))

@section('email_content')
    <p style="font-size:15px;color:#333;margin-top:0;">
        {{ __('emails.travel_invite.greeting') }}
    </p>

    <p style="font-size:15px;color:#333;">
        {{ __('emails.travel_invite.intro') }}
    </p>

    @if(!empty($dto->activities))
        <p style="font-size:15px;color:#333;">
            {{ __('emails.travel_invite.activities') }}
            @foreach($dto->activities as $label)
                <span style="display:inline-block;margin:0 5px;padding:3px 8px;font-size:14px;color:#fff;background-color:#007bff;border-radius:3px;">
                {{ $label }}
            </span>
            @endforeach
        </p>
    @endif
    @if(!empty($dto->countryLabels))
    <p style="font-size:15px;color:#333;">
        {{ __('emails.travel_invite.countries') }}
        @foreach($dto->countryLabels as $label)
            <span style="display:inline-block;margin:0 5px;padding:3px 8px;font-size:14px;color:#fff;background-color:#007bff;border-radius:3px;">
                {{ $label }}
            </span>
        @endforeach
    </p>
    @endif

    <p style="font-size:15px;color:#333;">
        <a href="{{ $dto->confirmationUrl }}" target="_blank"
           style="display:inline-block;padding:5px 20px;font-size:16px;color:#fff;background-color:#28a745;text-decoration:none;border-radius:5px;margin-top:20px;">
            {{ __('emails.travel_invite.travel_info') }}
        </a>
    </p>

    <p style="font-size:14px;color:#555; margin-top:50px;">
        {{ __('emails.travel_invite.slogan') }}
    </p>
@endsection