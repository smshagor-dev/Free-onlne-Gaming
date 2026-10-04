<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KYC Approved Notification</title>
</head>
<body style="background-color:#f3f4f6; margin:0; padding:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td align="center" style="padding:50px 0;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600"
                       style="background:#ffffff; border-radius:16px; box-shadow:0 6px 16px rgba(0,0,0,0.08); overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td style="background-color:#16a34a; padding:30px; text-align:center;">
                            <h1 style="color:#ffffff; font-size:26px; margin:0;">✅ KYC Verification Approved</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:35px 30px;">
                            <h2 style="font-size:22px; color:#111827; margin-bottom:16px;">Hello {{ $user->name }},</h2>

                            <p style="font-size:16px; color:#374151; margin-bottom:24px;">
                                🎉 Congratulations! Your <strong>KYC verification</strong> has been successfully approved.
                            </p>

                            <!-- Success Notice -->
                            <div style="background:#ecfdf5; border-left:4px solid #16a34a; padding:16px; border-radius:8px; margin-bottom:16px; color:#047857; font-size:15px;">
                                ✅ Your documents have been reviewed and approved. You now have <strong>full access</strong> to all platform features.
                            </div>

                            <!-- Info Notice -->
                            <div style="background:#eff6ff; border-left:4px solid #3b82f6; padding:16px; border-radius:8px; margin-bottom:24px; color:#1e40af; font-size:15px;">
                                ℹ️ No further action is required from your side at this time.
                            </div>

                            <!-- Button -->
                            <p style="text-align:center; margin-bottom:32px;">
                                <a href="{{ url('user/dashboard') }}" 
                                   style="background:#16a34a; color:#ffffff; text-decoration:none; padding:14px 28px; border-radius:12px; display:inline-block; font-weight:bold; font-size:16px;">
                                    Go to Dashboard
                                </a>
                            </p>

                            <p style="font-size:16px; color:#374151; line-height:1.5; margin-bottom:0;">
                                If you have any questions or need assistance, please don’t hesitate to contact our support team.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f9fafb; text-align:center; padding:18px; font-size:12px; color:#6b7280;">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
                            <span style="display:block; margin-top:4px;">If you didn’t expect this email, please ignore it.</span>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
