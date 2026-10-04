<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Lottery Prizes Available</title>
</head>
<body style="background-color:#f3f4f6; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin:0; padding:0;">

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td align="center" style="padding:50px 0;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" 
                       style="background:#ffffff; border-radius:16px; box-shadow:0 6px 16px rgba(0,0,0,0.08); overflow:hidden; padding:32px;">

                    <!-- Header -->
                    <tr>
                        <td style="text-align:center; padding-bottom:24px;">
                            <h1 style="color:#4f46e5; font-size:26px; margin:0;">🎉 New Lottery Added!</h1>
                            <p style="color:#374151; font-size:16px; margin:8px 0 0;">
                                Don’t miss the <strong>{{ $lottary->title }}</strong> draw!
                            </p>
                        </td>
                    </tr>

                    <!-- Draw Date -->
                    <tr>
                        <td style="padding:16px; background:#eef2ff; border-left:4px solid #4f46e5; border-radius:8px; margin-bottom:24px;">
                            <p style="margin:0; font-size:16px; color:#1e3a8a; text-align:center;">
                                <strong>Draw Date:</strong> {{ \Carbon\Carbon::parse($lottary->draw_date)->format('F j, Y g:i A') }}
                            </p>
                        </td>
                    </tr>

                    <!-- Message -->
                    <tr>
                        <td style="padding:16px 0; text-align:center;">
                            <p style="font-size:16px; color:#374151; line-height:1.5; margin-bottom:32px;">
                                Grab your ticket now and stand a chance to win amazing prizes!  
                                Act fast before the draw date.
                            </p>

                            <!-- Button -->
                            <a href="{{ route('user.lottaries.show', $lottary->id) }}"
                               style="background:#10b981; color:#ffffff; text-decoration:none; padding:14px 28px; border-radius:12px; display:inline-block; font-weight:bold; font-size:16px;">
                                👀 View Lottery Details
                            </a>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="text-align:center; padding-top:32px; font-size:12px; color:#6b7280;">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
                            This is an automated message. Please do not reply directly.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
