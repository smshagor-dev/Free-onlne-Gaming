<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Unbanned Notification</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; padding: 20px; color: #333;">
    <div style="max-width: 600px; margin: auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div style="background-color: #28a745; color: #fff; padding: 15px 20px; text-align: center;">
            <h2 style="margin: 0;">✅ Account UnLocked</h2>
        </div>

        <div style="padding: 20px;">
            <p>Dear <strong>{{ $user->name ?? $user->username }}</strong>,</p>

            <p>We are happy to inform you that your submitted documents have been <strong style="color:#28a745;">approved</strong> and your account has been <strong>unbanned</strong>.</p>

            <p>You can now log in and access all features of your account.</p>

            <!-- Login / Dashboard Button -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('login') }}" 
                   style="display: inline-block; padding: 12px 24px; background-color: #28a745; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 6px; transition: background 0.3s;">
                    Log In to Your Account
                </a>
            </div>

            <p style="margin-top: 20px;">Thank you for your cooperation.</p>

            <p style="margin-top: 30px;">
                Regards,<br>
                <strong>{{ config('app.name') }} Support Team</strong>
            </p>
        </div>

        <div style="background-color: #f1f1f1; padding: 15px; text-align: center; font-size: 12px; color: #555;">
            This is an automated message. Please do not reply directly to this email.<br>
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
