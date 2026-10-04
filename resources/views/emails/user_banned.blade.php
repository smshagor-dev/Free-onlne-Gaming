<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Deposit Submitted</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; padding: 20px; color: #333;">
    <div style="max-width: 600px; margin: auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        
        <!-- Header -->
        <div style="background-color: #28a745; color: #fff; padding: 15px 20px; text-align: center;">
            <h2 style="margin: 0;">✅ Deposit Submitted</h2>
        </div>

        <!-- Content -->
        <div style="padding: 20px;">
            <p>Hi <strong>{{ $userName }}</strong>,</p>

            <p>Your deposit has been submitted successfully. Here are the details:</p>

            <!-- Deposit Details -->
            <div style="background-color: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 6px; padding: 15px; margin: 20px 0;">
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <strong>Amount:</strong> <span>{{ $amount }}</span>
                    </li>
                    <li style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <strong>Gateway:</strong> <span>{{ $gateway }}</span>
                    </li>
                    <li style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <strong>Transaction #:</strong> <span>{{ $transactionNumber }}</span>
                    </li>
                    <li style="display: flex; justify-content: space-between;">
                        <strong>Status:</strong> <span>{{ $status }}</span>
                    </li>
                </ul>
            </div>

            <!-- Notification -->
            <p style="background-color: #d4edda; border-left: 4px solid #28a745; padding: 10px 15px; border-radius: 4px; color: #155724;">
                We will notify you once your deposit is approved.
            </p>

            <p style="margin-top: 20px;">Thank you for using our service!</p>

            <!-- Optional Button -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('user.dashboard') }}" 
                   style="display: inline-block; padding: 12px 24px; background-color: #28a745; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 6px; transition: background 0.3s;">
                    Go to Dashboard
                </a>
            </div>

            <p>Regards,<br>
            <strong>{{ config('app.name') }} Support Team</strong></p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f1f1f1; padding: 15px; text-align: center; font-size: 12px; color: #555;">
            This is an automated message. Please do not reply directly to this email.<br>
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
