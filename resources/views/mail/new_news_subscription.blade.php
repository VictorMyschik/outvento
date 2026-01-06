<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('emails.new_news_subscription') }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f5f7fa; font-family: Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f7fa; padding:20px 0;">
    <tr>
        <td align="center">

            <table width="100%" cellpadding="0" cellspacing="0"
                   style="max-width:600px; background:#ffffff; border-radius:8px; overflow:hidden;">

                <!-- Header -->
                <tr>
                    <td style="padding:10px; text-align:center;">
                        <h1 style="margin:0; font-size:22px;">
                            <img src="{{env('FRONT_HOST')}}/src/assets/images/email_600.png"
                                 alt="{{ config('app.name') }} Logo">
                            {{ __('emails.new_news_subscription') }}
                        </h1>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:30px; color:#333; font-size:15px; line-height:1.6;">

                        <p>{{ __('emails.greeting') }}</p>

                        <p>{{ __('emails.intro') }}</p>

                        <p><strong>{{ __('emails.benefits_title') }}</strong></p>

                        <ul style="padding-left:20px;">
                            @foreach(__('emails.benefits') as $benefit)
                                <li>{{ $benefit }}</li>
                            @endforeach
                        </ul>

                        <p>{{ __('emails.ignore') }}</p>

                        <p style="text-align:center; margin:30px 0;">
                            <a href="{{ $unsubscribeUrl }}"
                               style="display:inline-block; padding:12px 22px; background:#eaeaea;
                                      color:#555; text-decoration:none; border-radius:4px;">
                                {{ __('emails.unsubscribe') }}
                            </a>
                        </p>

                        <p style="font-size:13px; color:#777;">
                            {{ __('emails.regards') }}<br>
                            <strong>{{ config('app.name') }}</strong>
                        </p>

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background:#f0f2f5; padding:15px; text-align:center;
                               font-size:12px; color:#999;">
                        © {{ date('Y') }} {{ config('app.name') }}
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
