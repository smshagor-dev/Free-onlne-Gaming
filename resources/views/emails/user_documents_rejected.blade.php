<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document Submission Rejected</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; padding: 20px; color: #333;">
    <div style="max-width: 600px; margin: auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div style="background-color: #dc3545; color: #fff; padding: 15px 20px; text-align: center;">
            <h2 style="margin: 0;">❌ Document Submission Rejected</h2>
        </div>

        <div style="padding: 20px;">
            <p>Dear <strong>{{ $user->name ?? $user->username }}</strong>,</p>

            <p>We regret to inform you that some of your submitted documents for unban request have been <strong style="color:#dc3545;">rejected</strong>.</p>

            <p>Please review the rejection comments provided by the admin and resubmit the required documents for further review.</p>

            @if(isset($comments) && $comments)
                <p><strong>Comments:</strong> {{ $comments }}</p>
            @endif

            <!-- Resubmit Button -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('user.unban.form') }}" 
                   style="display: inline-block; padding: 12px 24px; background-color: #007bff; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 6px; transition: background 0.3s;">
                    Resubmit Documents
                </a>
            </div>

            <p style="margin-top: 20px;">Thank you for your understanding and cooperation.</p>

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
