<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password — CareerGuide</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            background-color: #eff6ff;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,.05), 0 10px 10px -5px rgba(0,0,0,.01);
        }
        .email-header {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            padding: 48px 32px;
            text-align: center;
        }
        .logo {
            width: 70px; height: 70px;
            background: rgba(255,255,255,.15);
            border-radius: 20px;
            display: inline-flex;
            align-items: center; justify-content: center;
            margin-bottom: 20px;
            border: 1px solid rgba(255,255,255,.3);
        }
        .logo svg { width: 40px; height: 40px; }
        .email-header h1 { color: #ffffff; font-size: 26px; font-weight: 800; margin-bottom: 8px; letter-spacing: -.5px; }
        .email-header p  { color: rgba(255,255,255,.85); font-size: 15px; }
        .email-content { padding: 40px 32px; }
        .greeting { font-size: 22px; font-weight: 700; color: #1e40af; margin-bottom: 16px; }
        .intro { color: #000000; font-size: 16px; margin-bottom: 28px; line-height: 1.7; }
        .btn-wrap { text-align: center; margin: 32px 0; }
        .btn-primary {
            display: inline-block;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: #ffffff; text-decoration: none;
            padding: 16px 40px;
            border-radius: 12px; font-weight: 700; font-size: 16px;
            box-shadow: 0 10px 15px -3px rgba(37,99,235,.25);
        }
        .warning-box {
            background: #dbeafe; border-left: 4px solid #000000;
            padding: 16px 20px; border-radius: 12px; margin: 24px 0;
        }
        .warning-box p { color: #000000; font-size: 14px; margin: 0; }
        .security-box {
            background: #eff6ff; border: 1px solid #dbeafe;
            border-radius: 16px; padding: 20px 24px; margin: 24px 0;
        }
        .security-box p { font-size: 14px; font-weight: 600; color: #1d4ed8; margin-bottom: 10px; }
        .security-item { display: flex; align-items: center; gap: 8px; padding: 6px 0; color: #1d4ed8; font-size: 13px; }
        .url-fallback {
            background: #000000; border: 1px solid #dbeafe;
            border-radius: 10px; padding: 14px 18px; margin: 20px 0;
            word-break: break-all; font-size: 12px; color: #000000;
        }
        .url-fallback a { color: #2563eb; }
        .email-footer {
            background: #000000; padding: 28px 32px;
            text-align: center; border-top: 1px solid #dbeafe;
        }
        .copyright { color: #000000; font-size: 12px; margin-top: 10px; }
        .copyright a { color: #2563eb; text-decoration: none; }
        @media (max-width: 600px) {
            .email-content { padding: 28px 20px; }
            .email-header  { padding: 36px 20px; }
        }
    </style>
</head>
<body style="margin:0; padding:20px 0; background-color:#eff6ff;">
<div class="email-container">

    <!-- Header -->
    <div class="email-header">
        <div class="logo">
            <svg fill="none" viewBox="0 0 24 24" stroke="text-white" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <h1>Reset Your Password</h1>
        <p>We received a request to reset your password</p>
    </div>

    <!-- Content -->
    <div class="email-content">
        <div class="greeting">Hello, {{ $user->name }}! 🔐</div>

        <div class="intro">
            We received a password reset request for your <strong>CareerGuide</strong> account at
            Luwinga Secondary School. Click the button below to choose a new password.
        </div>

        <!-- CTA -->
        <div class="btn-wrap">
            <a href="{{ $resetUrl }}" class="btn-primary">
                🔑 &nbsp;Reset My Password
            </a>
        </div>

        <!-- Warning -->
        <div class="warning-box">
            <p>⏰ <strong>This link expires in 60 minutes.</strong> If it has expired, go back to the login page and request a new password reset link.</p>
        </div>

        <!-- Security tips -->
        <div class="security-box">
            <p>🛡️ Security tips for your new password:</p>
            <div class="security-item">✔ Use at least 8 characters</div>
            <div class="security-item">✔ Mix uppercase and lowercase letters</div>
            <div class="security-item">✔ Include at least one number</div>
            <div class="security-item">✔ Don't share your password with anyone</div>
        </div>

        <p style="color:#000000; font-size:13px; margin-bottom:8px;">
            If the button doesn't work, copy and paste this link into your browser:
        </p>
        <div class="url-fallback">
            <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
        </div>

        <p style="color:#000000; font-size:12px; margin-top:24px;">
            If you did not request a password reset, no action is required — your password will remain unchanged.
            If you're concerned about unauthorised access, please contact your school's guidance counsellor.
        </p>
    </div>

    <!-- Footer -->
    <div class="email-footer">
        <p style="color:#000000; font-size:13px; margin-bottom:6px;">
            Luwinga Secondary School, Mzuzu City, Malawi
        </p>
        <div class="copyright">
            © {{ date('Y') }} Smart Career &amp; Subject Guidance Tool. All rights reserved.<br>
            <a href="{{ url('/') }}">Visit our website</a> &middot;
            <a href="mailto:guidance@luwinga.edu.mw">Contact Support</a>
        </div>
    </div>

</div>
</body>
</html>
