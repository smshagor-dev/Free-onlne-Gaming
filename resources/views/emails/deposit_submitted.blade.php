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
            <h2 style="margin: 0;">💰 Deposit Submitted</h2>
        </div>

        <!-- Content -->
        <div style="padding: 20px;">
            <p>Hello <strong>{{ $userName }}</strong>,</p>

            <p>Your deposit has been successfully submitted. Below are the details:</p>

            <!-- Deposit Details -->
            <div style="background-color: #f8f9fa; border: 1px solid #e1e1e1; border-radius: 6px; padding: 15px; margin: 20px 0;">
                <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold; color: #333;">Amount:</td>
                        <td style="padding: 8px 0; text-align: right; color: #555;">{{ $amount }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold; color: #333;">Gateway:</td>
                        <td style="padding: 8px 0; text-align: right; color: #555;">{{ $gateway }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold; color: #333;">Transaction No #:</td>
                        <td style="padding: 8px 0; text-align: right; color: #555;">{{ $transactionNumber }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold; color: #333;">Status:</td>
                        <td style="padding: 8px 0; text-align: right; color: #555;">{{ $status }}</td>
                    </tr>
                </table>
            </div>

            <!-- Info Box -->
            <div style="background-color: #e9f9ef; border-left: 4px solid #28a745; padding: 12px 15px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; color: #2e7d32;">
                ✅ We will notify you once your deposit is approved.
            </div>

            <p>Thank you for using our service!</p>

            <p style="margin-top: 30px;">
                Regards,<br>
                <strong>{{ config('app.name') }} Team</strong>
            </p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f1f1f1; padding: 15px; text-align: center; font-size: 12px; color: #555;">
            If you have any questions, please contact our support team.<br>
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
