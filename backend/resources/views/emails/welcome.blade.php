<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to CareerGuide</title>
    <style>
        /* Base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            background-color: #eff6ff;
            margin: 0;
            padding: 0;
        }

        /* Container */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.01);
        }

        /* Header */
        .email-header {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            padding: 48px 32px;
            text-align: center;
        }

        .logo {
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .logo svg {
            width: 40px;
            height: 40px;
            color: text-white;
        }

        .email-header h1 {
            color: text-white;
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .email-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
        }

        /* Content */
        .email-content {
            padding: 40px 32px;
        }

        .greeting {
            font-size: 22px;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 20px;
        }

        .intro {
            color: #000000;
            font-size: 16px;
            margin-bottom: 30px;
            line-height: 1.7;
        }

        /* Features Grid */
        .features-grid {
            background: #000000;
            border-radius: 16px;
            padding: 24px;
            margin: 30px 0;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #dbeafe;
        }

        .feature-item:last-child {
            border-bottom: none;
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .feature-icon span {
            font-size: 20px;
        }

        .feature-text {
            flex: 1;
        }

        .feature-text strong {
            color: #000000;
            font-size: 15px;
            display: block;
            margin-bottom: 4px;
        }

        .feature-text p {
            color: #000000;
            font-size: 13px;
            margin: 0;
        }

        /* Button */
        .btn-primary {
            display: inline-block;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: border-white;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            transition: transform 0.2s;
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
        }

        /* Info Box */
        .info-box {
            background: #eff6ff;
            border-left: 4px solid #2563eb;
            padding: 20px;
            border-radius: 12px;
            margin: 30px 0;
        }

        .info-box p {
            color: #1e40af;
            font-size: 14px;
            margin: 0 0 10px 0;
        }

        .info-box p:last-child {
            margin-bottom: 0;
        }

        /* Footer */
        .email-footer {
            background: #000000;
            padding: 32px;
            text-align: center;
            border-top: 1px solid #dbeafe;
        }

        .social-links {
            margin-bottom: 20px;
        }

        .social-link {
            display: inline-block;
            margin: 0 10px;
            color: #000000;
            text-decoration: none;
            font-size: 20px;
        }

        .copyright {
            color: #000000;
            font-size: 12px;
            margin-top: 15px;
        }

        .copyright a {
            color: #2563eb;
            text-decoration: none;
        }

        @media (max-width: 600px) {
            .email-content {
                padding: 30px 20px;
            }
            .email-header {
                padding: 40px 20px;
            }
            .greeting {
                font-size: 20px;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 20px 0; background-color: #eff6ff;">
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <div class="logo">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h1>Welcome to CareerGuide!</h1>
            <p>Your journey to a brighter future starts here</p>
        </div>

        <!-- Content -->
        <div class="email-content">
            <div class="greeting">
                Hello, {{ $user->name }}! 👋
            </div>

            <div class="intro">
                Thank you for joining the <strong>Smart Career and Subject Guidance Tool</strong> at Luwinga Secondary School. We're excited to help you discover your perfect career path!
            </div>

            <!-- Features -->
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon"><span>🎯</span></div>
                    <div class="feature-text">
                        <strong>Personalized Career Matching</strong>
                        <p>Discover careers that match your unique strengths and interests</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><span>📚</span></div>
                    <div class="feature-text">
                        <strong>Subject Combination Guide</strong>
                        <p>Get expert advice on the right subjects for your dream career</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><span>🏛️</span></div>
                    <div class="feature-text">
                        <strong>University Pathways</strong>
                        <p>See exactly which university programs you qualify for</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><span>🤖</span></div>
                    <div class="feature-text">
                        <strong>24/7 AI Assistant</strong>
                        <p>Get instant answers to your career questions anytime</p>
                    </div>
                </div>
            </div>

            <!-- CTA Button -->
            <div style="text-align: center;">
                <a href="{{ url('/student/assessment') }}" class="btn-primary">
                    🚀 Start Your Career Assessment
                </a>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <p>💡 <strong>Your Next Step:</strong> Complete the career assessment (it takes only 15 minutes!) to unlock your personalized recommendations.</p>
                <p style="margin-top: 10px;">📧 <strong>Need help?</strong> Chat with our AI assistant or contact your guidance counsellor at school.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <div class="social-links">
                <span style="margin: 0 5px;">📘</span>
                <span style="margin: 0 5px;">🐦</span>
                <span style="margin: 0 5px;">📸</span>
            </div>
            <p style="color: #000000; font-size: 13px; margin-bottom: 10px;">
                Luwinga Secondary School, Mzuzu City, Malawi
            </p>
            <div class="copyright">
                © {{ date('Y') }} Smart Career & Subject Guidance Tool. All rights reserved.<br>
                <a href="{{ url('/') }}">Visit our website</a> |
                <a href="mailto:guidance@luwinga.edu.mw">Contact Support</a>
            </div>
        </div>
    </div>
</body>
</html>
