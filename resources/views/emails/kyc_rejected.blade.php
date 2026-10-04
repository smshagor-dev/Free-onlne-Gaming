<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KYC Rejected Notification</title>
</head>
<body style="background-color:#f3f4f6; margin:0; padding:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td align="center" style="padding:50px 0;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" 
                       style="background:#ffffff; border-radius:16px; box-shadow:0 6px 16px rgba(0,0,0,0.08); overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td style="background-color:#dc2626; padding:30px; text-align:center;">
                            <h1 style="color:#ffffff; font-size:26px; margin:0;">⚠️ KYC Verification Requires Attention</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:35px 30px;">
                            <h2 style="font-size:22px; color:#111827; margin-bottom:16px;">Hello {{ $user->name }},</h2>
                            
                            <p style="color:#374151; font-size:16px; margin-bottom:24px;">
                                We have reviewed your submitted KYC documents, and unfortunately, some of them were 
                                <strong style="color:#dc2626;">rejected</strong> during the verification process.
                            </p>

                            <!-- Rejection Notice -->
                            <div style="background:#fee2e2; border-left:4px solid #dc2626; padding:14px 16px; border-radius:8px; margin-bottom:16px; font-size:15px; color:#b91c1c;">
                                ❌ Some of your documents did not meet the verification requirements.
                            </div>

                            <!-- Action Required -->
                            <div style="background:#fffbeb; border-left:4px solid #f59e0b; padding:14px 16px; border-radius:8px; margin-bottom:24px; font-size:15px; color:#92400e;">
                                ⚠️ Please review the comments provided by our team and resubmit your documents with the necessary corrections.
                            </div>

                            <!-- Button -->
                            <p style="text-align:center; margin-bottom:32px;">
                                <a href="{{ url('/user/kyc/create') }}" 
                                   style="background:#2563eb; color:#ffffff; text-decoration:none; padding:14px 28px; border-radius:12px; display:inline-block; font-weight:bold; font-size:16px;">
                                    Resubmit KYC Documents
                                </a>
                            </p>

                            <p style="color:#374151; font-size:16px; line-height:1.5; margin-bottom:0;">
                                If you have any questions or need clarification about the rejection reasons, our support team is available to assist you.
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
