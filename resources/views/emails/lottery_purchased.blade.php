<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lottery Ticket Confirmation</title>
</head>
<body style="background-color:#f3f4f6; margin:0; padding:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td align="center" style="padding:50px 0;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" 
                       style="background:#ffffff; border-radius:16px; box-shadow:0 6px 16px rgba(0,0,0,0.08); overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(90deg, #6366f1, #4f46e5); padding:30px; text-align:center;">
                            <h1 style="color:#ffffff; font-size:26px; margin:0;">🎟 Lottery Ticket Confirmation</h1>
                            <p style="color:#d1d5db; font-size:14px; margin:6px 0 0;">Your ticket has been successfully purchased</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:35px 30px;">
                            <h2 style="font-size:22px; color:#111827; margin-bottom:12px;">Hello {{ $user->name }},</h2>
                            
                            <p style="color:#374151; font-size:16px; margin-bottom:24px;">
                                You’ve successfully purchased a lottery ticket. Here are the details of your purchase:
                            </p>

                            <!-- Ticket Details Table -->
                            <table style="width:100%; border-collapse:collapse; margin-bottom:28px; border-radius:8px; overflow:hidden; border:1px solid #e5e7eb;">
                                <tr style="background:#f9fafb;">
                                    <td style="padding:14px; font-weight:bold; color:#111827;">Lottery</td>
                                    <td style="padding:14px; color:#4f46e5;">{{ $lottary->title }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:14px; font-weight:bold; background:#f9fafb;">Ticket Number</td>
                                    <td style="padding:14px; font-family:monospace; color:#1d4ed8;">{{ $ticketNumber }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:14px; font-weight:bold;">Transaction Number</td>
                                    <td style="padding:14px; font-family:monospace; color:#059669;">{{ $transactionNumber }}</td>
                                </tr>
                                <tr style="background:#f9fafb;">
                                    <td style="padding:14px; font-weight:bold;">Price</td>
                                    <td style="padding:14px; font-weight:bold; color:#b91c1c;">{{ $setting->currency_symble ?? '' }}{{ $lottary->price }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:14px; font-weight:bold;">Draw Date</td>
                                    <td style="padding:14px; color:#111827;">{{ \Carbon\Carbon::parse($lottary->draw_date)->format('F j, Y g:i A') }}</td>
                                </tr>
                            </table>

                            <p style="font-size:16px; color:#374151; margin-bottom:32px;">
                                Thank you for participating! 🍀 Best of luck in the upcoming draw.
                            </p>

                            <!-- Button -->
                            <p style="text-align:center;">
                                <a href="{{ route('user.lottaries.my') }}" 
                                   style="background:#4f46e5; color:#ffffff; text-decoration:none; padding:14px 28px; border-radius:12px; display:inline-block; font-weight:bold; font-size:16px;">
                                    View My Tickets
                                </a>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f9fafb; text-align:center; padding:18px; font-size:12px; color:#6b7280;">
                            This is an automated message. Please do not reply directly.<br>
                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
