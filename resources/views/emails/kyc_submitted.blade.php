<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KYC Submission Received</title>
</head>
<body style="background-color:#f3f4f6; margin:0; padding:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td align="center" style="padding:50px 0;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" 
                       style="background:#ffffff; border-radius:16px; box-shadow:0 6px 16px rgba(0,0,0,0.08); overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td style="background-color:#3b82f6; padding:30px; text-align:center;">
                            <h1 style="color:#ffffff; font-size:26px; margin:0;">{{ config('app.name') }}</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:35px 30px;">
                            <h2 style="font-size:22px; color:#111827; margin-bottom:16px;">Hello {{ $user->name }},</h2>
                            
                            <p style="font-size:16px; color:#374151; margin-bottom:24px;">
                                Your KYC submission has been received and is now under review.
                            </p>

                            <p style="font-size:16px; color:#374151; margin-bottom:24px;">
                                <strong>Status:</strong> 
                                <span style="display:inline-block; padding:6px 12px; background-color:#fef3c7; color:#78350f; border-radius:12px; font-weight:bold;">
                                    Pending
                                </span>
                            </p>

                            <p style="font-size:16px; color:#374151; margin-bottom:32px;">
                                We will notify you once it has been verified.
                            </p>

                            <!-- Button (Optional) -->
                            <p style="text-align:center; margin-bottom:32px;">
                                <a href="{{ route('user.kyc.index') }}" 
                                   style="background:#3b82f6; color:#ffffff; text-decoration:none; padding:14px 28px; border-radius:12px; display:inline-block; font-weight:bold; font-size:16px;">
                                    Check KYC Status
                                </a>
                            </p>

                            <p style="font-size:16px; color:#374151; margin-bottom:4px;">Thank you,</p>
                            <p style="font-weight:bold; color:#111827; margin:0;">{{ config('app.name') }}</p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f9fafb; text-align:center; padding:18px; font-size:12px; color:#6b7280;">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
                            This is an automated message. Please do not reply.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
