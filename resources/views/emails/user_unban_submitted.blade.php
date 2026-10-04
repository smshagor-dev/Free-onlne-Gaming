<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unban Request Notification</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; padding: 20px; color: #333;">
    <div style="max-width: 600px; margin: auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="background-color: #007bff; color: #fff; padding: 15px 20px; text-align: center;">
            <h2 style="margin: 0;">{{ $resubmitting ? 'Unban Request Resubmitted' : 'Unban Request Submitted' }}</h2>
        </div>

        <div style="padding: 20px;">
            <p>Dear <strong>{{ $user->name ?? $user->username }}</strong>,</p>

            <p>Your unban request has been {{ $resubmitting ? 'resubmitted' : 'submitted' }}. The documents are now under review by our team.</p>

            <!-- View / Manage Button -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('user.unban.form') }}" 
                   style="display: inline-block; padding: 12px 24px; background-color: #007bff; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 6px; transition: background 0.3s;">
                    View Status
                </a>
            </div>

            <p style="margin-top: 20px;">Thank you for your patience and cooperation.</p>

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
