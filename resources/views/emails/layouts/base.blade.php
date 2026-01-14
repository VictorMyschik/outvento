<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>@yield('email_title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background-color:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;padding:12px 0;">
    <tr>
        <td align="center">

            <table width="100%" cellpadding="0" cellspacing="0"
                   style="max-width:600px;background-color:#ffffff;border-radius:8px;overflow:hidden;">

                <!-- Header -->
                <tr>
                    <td style="padding:16px 20px;">
                        <img src="{{ env('FRONT_HOST') }}/src/assets/images/email_600.png"
                             alt="{{ config('app.name') }}"
                             style="display:block;margin-bottom:10px;max-width:100%;height:auto;">
                        <h1 style="margin:0;font-size:20px;line-height:1.3;color:#333;">
                            @yield('email_header')
                        </h1>
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding:20px;">
                        @yield('email_content')
                    </td>
                </tr>

                <!-- Unsubscribe -->
                <tr>
                    <td style="padding:20px;">
                        @yield('email_unsubscribe')
                    </td>
                </tr>


                <!-- Footer -->
                <tr>
                    <td style="padding:16px 20px;background:#f0f0f0;font-size:12px;color:#777;">
                        <p style="margin:0;">
                            {{ __('emails.regards') }},<br>
                            <strong>{{ config('app.name') }}</strong>
                        </p>
                        <p style="margin:6px 0 0;">
                            © {{ date('Y') }} {{ config('app.name') }}
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
