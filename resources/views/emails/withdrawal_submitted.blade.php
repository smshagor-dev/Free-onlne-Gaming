<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Withdrawal Submitted</title>
</head>
<body style="background-color:#f3f4f6; font-family:Arial, sans-serif; margin:0; padding:0;">

    <div style="max-width:600px; margin:40px auto; background:#ffffff; border-radius:16px; box-shadow:0 4px 16px rgba(0,0,0,0.08); border:1px solid #e5e7eb; overflow:hidden;">

        <!-- Header -->
        <div style="background-color:#2563eb; padding:24px; text-align:center; color:#ffffff;">
            <h1 style="margin:0; font-size:24px;">Withdrawal Submitted</h1>
        </div>

        <!-- Content -->
        <div style="padding:32px;">
            <h2 style="margin-top:0; font-size:20px; color:#111827;">Hello {{ $withdraw->user->name }},</h2>

            <p style="color:#374151; font-size:16px; line-height:1.5; margin-bottom:20px;">
                Your withdrawal has been submitted successfully. Here are the details:
            </p>

            <!-- Withdrawal Details -->
            <div style="background-color:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:20px; margin-bottom:20px;">
                <ul style="list-style:none; padding:0; margin:0;">
                    <li style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #e5e7eb;">
                        <span style="font-weight:600; color:#111827;">Amount:</span>
                        <span style="color:#374151;">{{ $withdraw->amount }}</span>
                    </li>
                    <li style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #e5e7eb;">
                        <span style="font-weight:600; color:#111827;">Gateway:</span>
                        <span style="color:#374151;">{{ $withdraw->gateway->name }}</span>
                    </li>
                    <li style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #e5e7eb;">
                        <span style="font-weight:600; color:#111827;">Transaction #:</span>
                        <span style="color:#374151;">{{ $withdraw->transaction_number }}</span>
                    </li>
                    <li style="display:flex; justify-content:space-between; padding:8px 0;">
                        <span style="font-weight:600; color:#111827;">Status:</span>
                        <span style="color:#374151;">{{ ucfirst($withdraw->status) }}</span>
                    </li>
                </ul>
            </div>

            <!-- Info Box -->
            <div style="background-color:#dbecff; border-left:4px solid #2563eb; padding:16px; border-radius:8px; color:#1d4ed8; font-size:14px; margin-bottom:20px;">
                ✅ We will notify you once your withdrawal is approved.
            </div>

            <p style="color:#374151; font-size:16px; line-height:1.5; margin-bottom:20px;">Thank you for using our service!</p>

            <!-- Footer Note -->
            <div style="border-top:1px solid #e5e7eb; padding-top:16px; text-align:center; font-size:14px; color:#6b7280;">
                If you have any questions, please contact our support team.
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color:#f9fafb; text-align:center; padding:16px; font-size:12px; color:#6b7280;">
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>

    </div>

</body>
</html>
