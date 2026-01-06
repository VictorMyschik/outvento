<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('emails.news_digest.subject') }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;padding:20px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0"
                   style="background-color:#ffffff;border-radius:8px;overflow:hidden;">
                <!-- Header -->
                <tr>
                    <td style="padding:20px 30px;">
                        <img src="{{env('FRONT_HOST')}}/src/assets/images/email_600.png"
                             alt="{{ config('app.name') }} Logo">
                        <h1 style="margin:0;font-size:22px;">
                            {{ __('emails.news_digest.title') }}
                        </h1>
                        <p style="margin:5px 0 0;font-size:14px;">
                            {{ __('emails.news_digest.subtitle') }}
                        </p>
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding:30px;">
                        <p style="font-size:15px;color:#333333;">
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
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="padding:20px 30px;background:#f0f0f0;font-size:12px;color:#777;">
                        <p style="margin:0;">
                            © {{ date('Y') }} {{ config('app.name') }}
                        </p>
                        <p style="margin:6px 0 0;">
                            <a href="{{ $unsubscribeUrl }}"
                               style="color:#777;text-decoration:underline;">
                                {{ __('emails.unsubscribe') }}
                            </a>
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
