<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cashback Credited</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family: Arial, sans-serif; color:#333;">

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f6f9;padding:30px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background:#2b6cb0;padding:20px;text-align:center;">
                            <h1 style="margin:0;font-size:22px;color:#ffffff;">{{ config('app.name') }}</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px;">
                            <h2 style="color:#2b6cb0;margin-top:0;">Hello {{ $user->name }},</h2>
                            <p style="font-size:15px;line-height:1.6;margin-bottom:20px;">
                                We are excited to let you know that <strong style="color:#2b6cb0;">{{ number_format($amount, 2) }}</strong> 
                                has just been credited to your cashback balance.
                            </p>

                            <p style="font-size:15px;line-height:1.6;margin-bottom:20px;">
                                This cashback is based on your recent activity. Keep playing and enjoy more rewards as you continue your journey with us.
                            </p>

                            <!-- Call to Action -->
                            <div style="text-align:center;margin:30px 0;">
                                <a href="{{ url('/') }}" 
                                   style="background:#2b6cb0;color:#ffffff;text-decoration:none;
                                          padding:12px 24px;border-radius:6px;font-size:16px;font-weight:bold;
                                          display:inline-block;">
                                    View My Cashback
                                </a>
                            </div>

                            <p style="font-size:13px;line-height:1.6;color:#777;">
                                If you have any questions or need help, feel free to 
                                <a href="{{ url('/contact') }}" style="color:#2b6cb0;text-decoration:none;">contact our support team</a>.
                            </p>

                            <p style="margin-top:30px;font-size:15px;">Thanks,<br>
                               <strong style="color:#2b6cb0;">The {{ config('app.name') }} Team</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f4f6f9;text-align:center;padding:20px;font-size:12px;color:#888;">
                            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
