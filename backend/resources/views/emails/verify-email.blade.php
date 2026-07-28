<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email — CareerGuide</title>
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
        .steps-box {
            background: #eff6ff;
            border: 1px solid #dbeafe;
            border-radius: 16px;
            padding: 24px;
            margin: 28px 0;
        }
        .steps-box p { font-size: 14px; font-weight: 600; color: #0369a1; margin-bottom: 14px; }
        .step {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #dbeafe;
        }
        .step:last-child { border-bottom: none; padding-bottom: 0; }
        .step-num {
            width: 26px; height: 26px;
            background: #2563eb; color: #ffffff;
            border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; flex-shrink: 0; margin-top: 1px;
        }
        .step span { color: #1e40af; font-size: 14px; }
        .btn-wrap { text-align: center; margin: 32px 0; }
        .btn-primary {
            display: inline-block;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: #ffffff; text-decoration: none;
            padding: 16px 40px;
            border-radius: 12px; font-weight: 700; font-size: 16px;
            box-shadow: 0 10px 15px -3px rgba(37,99,235,.25);
        }
        .note {
            background: #dbeafe; border-left: 4px solid #000000;
            padding: 16px 20px; border-radius: 12px; margin: 24px 0;
        }
        .note p { color: #000000; font-size: 13px; margin: 0; }
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
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <h1>Verify Your Email Address</h1>
        <p>One quick step before you start your career journey</p>
    </div>

    <!-- Content -->
    <div class="email-content">
        <div class="greeting">Hello, {{ $user->name }}! 👋</div>

        <div class="intro">
            Thank you for creating an account on the <strong>Smart Career and Subject Guidance Tool</strong> at
            Luwinga Secondary School. Please verify your email address to activate your account and unlock
            your personalised career recommendations.
        </div>

        <!-- Steps -->
        <div class="steps-box">
            <p>How to verify:</p>
            <div class="step">
                <div class="step-num">1</div>
                <span>Click the <strong>Verify Email Address</strong> button below</span>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <span>You'll be redirected straight to your dashboard</span>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <span>Take the career assessment and discover your perfect career path!</span>
            </div>
        </div>

        <!-- CTA -->
        <div class="btn-wrap">
            <a href="{{ $verificationUrl }}" class="btn-primary">
                ✅ &nbsp;Verify Email Address
            </a>
        </div>

        <!-- Warning -->
        <div class="note">
            <p>⏰ <strong>This link expires in 60 minutes.</strong> If it has expired, sign in and request a new one from the verification page.</p>
        </div>

        <p style="color:#000000; font-size:13px; margin-bottom:8px;">
            If the button doesn't work, copy and paste this link into your browser:
        </p>
        <div class="url-fallback">
            <a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a>
        </div>

        <p style="color:#000000; font-size:12px; margin-top:24px;">
            If you did not create an account at Luwinga Secondary School's CareerGuide, you can safely ignore this email.
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
