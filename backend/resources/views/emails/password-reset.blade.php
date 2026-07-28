<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - CareerGuide</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            background: linear-gradient(135deg, #2563eb 0%, #2563eb 100%);
            margin: 0;
            padding: 20px;
        }
        .email-container {
            max-width: 550px;
            margin: 0 auto;
            background: border-white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .email-header {
            background: linear-gradient(135deg, #000000 0%, #000000 100%);
            padding: 48px 32px;
            text-align: center;
        }
        .lock-icon {
            width: 70px;
            height: 70px;
            background: border-white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .lock-icon svg {
            width: 35px;
            height: 35px;
            color: #000000;
        }
        .email-header h1 {
            color: text-white;
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 10px;
        }
        .email-content { padding: 40px 32px; }
        .warning-box {
            background: #dbeafe;
            border-left: 4px solid #000000;
            padding: 16px;
            border-radius: 12px;
            margin: 25px 0;
        }
        .btn-reset {
            display: inline-block;
            background: linear-gradient(135deg, #000000 0%, #000000 100%);
            color: border-white;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 600;
            margin: 20px 0;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
        }
        @media (max-width: 600px) {
            .email-content { padding: 30px 20px; }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="lock-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1>Password Reset Request</h1>
            <p>We received a request to reset your password</p>
        </div>

        <div class="email-content">
            <p style="font-size: 16px; color: #000000; margin-bottom: 20px;">
                Hello,
            </p>

            <p style="color: #000000; margin-bottom: 25px;">
            We received a request to reset the password for your CareerGuide account. Click the button below to create a new password:
            </p>

            <div style="text-align: center;">
                <a href="{{ $resetUrl ?? '#' }}" class="btn-reset">
                    🔐 Reset My Password
                </a>
            </div>

            <div class="warning-box">
                <p style="color: #000000; font-size: 14px; margin: 0;">
                    ⏰ <strong>This link will expire in 60 minutes.</strong><br>
                    If you didn't request this, please ignore this email. Your password will remain unchanged.
                </p>
            </div>

            <p style="color: #000000; font-size: 13px; margin-top: 25px; text-align: center;">
                If the button doesn't work, copy and paste this link:<br>
                <span style="color: #000000; word-break: break-all;">{{ $resetUrl ?? '#' }}</span>
            </p>
        </div>

        <div style="background: #000000; padding: 25px; text-align: center;">
            <p style="color: #000000; font-size: 12px;">
                Need help? Contact our support team at
                <a href="mailto:guidance@luwinga.edu.mw" style="color: #000000;">guidance@luwinga.edu.mw</a>
            </p>
        </div>
    </div>
</body>
</html>
