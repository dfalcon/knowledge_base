<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'IntelliBase' }}</title>
</head>
<body style="margin:0; padding:0; background:#f4f5f7; font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5f7; padding:32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                    {{-- header --}}
                    <tr>
                        <td style="background:#4f46e5; padding:24px 32px;">
                            <span style="color:#ffffff; font-size:18px; font-weight:600;">IntelliBase</span>
                        </td>
                    </tr>
                    {{-- content --}}
                    <tr>
                        <td style="padding:32px;">
                            {{ $slot }}
                        </td>
                    </tr>
                    {{-- footer --}}
                    <tr>
                        <td style="padding:20px 32px; border-top:1px solid #f0f0f0;">
                            <p style="margin:0; font-size:12px; color:#9ca3af;">This is an automated message from IntelliBase.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
