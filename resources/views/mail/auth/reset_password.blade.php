<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('emails.reset_password_subject') }}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color:#333; line-height:1.5;">

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
    {{ __('emails.reset_password_expire') }}
    <strong>{{ $expireMinutes }}</strong>
    {{ __('emails.minutes') }}.
</p>

<p>
    {{ __('emails.reset_password_ignore') }}
</p>

<p>
    {{ __('emails.regards') }},<br>
    <strong>{{ config('app.name') }}</strong>
</p>

</body>
</html>
