<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        /* Responsive styling */
        @media only screen and (max-width: 600px) {
            .container {
                width: 100% !important;
                padding: 10px !important;
            }
            .content {
                padding: 15px !important;
            }
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            color: #333333;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #0f1923;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .header {
            background: linear-gradient(90deg, #4fd1c5, #38b2ac);
            padding: 20px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 25px;
            background-color: #1a2a3a;
            color: #ffffff;
        }
        .content p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .content .highlight {
            font-weight: bold;
            color: #4fd1c5;
        }
        .footer {
            background-color: #111827;
            color: #cccccc;
            font-size: 14px;
            text-align: center;
            padding: 15px;
        }
        .btn {
            display: inline-block;
            background-color: #4fd1c5;
            color: #0f1923;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 15px;
            transition: background 0.3s;
        }
        .btn:hover {
            background-color: #38b2ac;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>{{ $title }}</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <p>Hello <span class="highlight">{{ $user->name ?? $user->username ?? '' }}</span>,</p>

            <p>{{ $messageContent }}</p>

            <a href="{{ url('/vip-bonus-play-games') }}" class="btn">Play Now</a>
        </div>

        <!-- Footer -->
        <div class="footer">
            &copy; {{ date('Y') }} TBM System Limited. All rights reserved.<br>
            If you did not request this, please ignore this email.
        </div>
    </div>
</body>
</html>
