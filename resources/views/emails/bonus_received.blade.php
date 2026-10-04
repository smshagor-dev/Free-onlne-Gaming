<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Bonus Received</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f4; font-family: Arial, sans-serif;">
    <table align="center" cellpadding="0" cellspacing="0" width="100%" style="padding:20px 0;">
        <tr>
            <td align="center">
                <table cellpadding="0" cellspacing="0" width="600" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td align="center" bgcolor="#4CAF50" style="padding:20px; color:#ffffff; font-size:24px; font-weight:bold;">
                            🎁 Bonus Awarded!
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px; color:#333333; font-size:16px; line-height:1.5;">
                            <p>Hi <strong>{{ $user->name }}</strong>,</p>
                            <p>Congratulations! You have received a new bonus. Please find the details below:</p>

                            <table cellpadding="0" cellspacing="0" width="100%" style="margin:20px 0; border:1px solid #ddd; border-radius:6px;">
                                <tr>
                                    <td style="padding:12px; border-bottom:1px solid #ddd; background:#f9f9f9; width:40%;"><strong>Bonus Type</strong></td>
                                    <td style="padding:12px; border-bottom:1px solid #ddd;">{{ $bonus->bonus_type }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px; border-bottom:1px solid #ddd; background:#f9f9f9;"><strong>Bonus Amount</strong></td>
                                    <td style="padding:12px; border-bottom:1px solid #ddd;">{{ number_format($bonus->bonus_amount, 2) }}</td>
                                </tr>
                            </table>

                            <p style="margin-top:20px;">Enjoy your bonus and happy playing! 🎲</p>
                            <p style="margin:0;">Best regards,<br><strong>{{ config('app.name') }}</strong></p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" bgcolor="#f4f4f4" style="padding:15px; font-size:12px; color:#888888;">
                            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
