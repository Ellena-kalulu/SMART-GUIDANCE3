<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Completed - CareerGuide</title>
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
            max-width: 600px;
            margin: 0 auto;
            background: border-white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .email-header {
            background: linear-gradient(135deg, #2563eb 0%, #2563eb 100%);
            padding: 48px 32px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .email-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 3s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        .checkmark {
            width: 80px;
            height: 80px;
            background: border-white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1;
        }
        .checkmark svg {
            width: 45px;
            height: 45px;
            color: #2563eb;
        }
        .email-header h1 {
            color: text-white;
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        .email-header p {
            color: rgba(255, 255, 255, 0.95);
            font-size: 16px;
            position: relative;
            z-index: 1;
        }
        .email-content { padding: 40px 32px; }
        .congrats {
            font-size: 24px;
            font-weight: 800;
            color: #000000;
            margin-bottom: 16px;
            text-align: center;
        }
        .completion-date {
            background: linear-gradient(135deg, #000000 0%, #dbeafe 100%);
            padding: 16px;
            border-radius: 16px;
            text-align: center;
            margin: 25px 0;
        }
        .completion-date p {
            color: #000000;
            font-size: 14px;
        }
        .completion-date .date {
            font-size: 20px;
            font-weight: 700;
            color: #2563eb;
            margin-top: 8px;
        }
        .progress-bar {
            background: #dbeafe;
            height: 8px;
            border-radius: 10px;
            margin: 30px 0;
            overflow: hidden;
        }
        .progress-fill {
            background: linear-gradient(90deg, #2563eb 0%, #2563eb 100%);
            width: 100%;
            height: 100%;
            animation: slideIn 1s ease-out;
        }
        @keyframes slideIn {
            from { width: 0; }
            to { width: 100%; }
        }
        .next-steps {
            background: #eff6ff;
            border-radius: 16px;
            padding: 24px;
            margin: 30px 0;
        }
        .next-steps h3 {
            color: #1d4ed8;
            font-size: 18px;
            margin-bottom: 16px;
        }
        .step {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        .step-number {
            width: 32px;
            height: 32px;
            background: #2563eb;
            color: border-white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
        .btn-primary {
            display: inline-block;
            background: linear-gradient(135deg, #2563eb 0%, #2563eb 100%);
            color: border-white;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
        }
        .btn-secondary {
            display: inline-block;
            background: border-white;
            color: #2563eb;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 12px;
            font-weight: 600;
            border: 2px solid #2563eb;
            margin-left: 10px;
        }
        @media (max-width: 600px) {
            .btn-primary, .btn-secondary { display: block; margin: 10px 0; text-align: center; }
            .btn-secondary { margin-left: 0; }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="checkmark">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1>Assessment Complete! 🎉</h1>
            <p>Great job taking the first step toward your future</p>
        </div>

        <div class="email-content">
            <div class="congrats">
                Congratulations, {{ $student->name }}!
            </div>

            <p style="text-align: center; color: #000000; margin-bottom: 20px;">
                You've successfully completed your career assessment. Our AI is now analyzing your responses to create personalized recommendations just for you.
            </p>

            <div class="completion-date">
                <p>📅 Assessment Submitted On</p>
                <div class="date">{{ $attempt->completed_at?->format('l, F j, Y \a\t g:i A') ?? now()->format('l, F j, Y \a\t g:i A') }}</div>
            </div>

            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>

            <div class="next-steps">
                <h3>✨ What happens next?</h3>
                <div class="step">
                    <div class="step-number">1</div>
                    <div><strong>Analysis in Progress</strong><br>Our smart engine analyzes your responses</div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div><strong>Recommendations Generated</strong><br>You'll receive career, subject, and university matches</div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div><strong>Results Ready</strong><br>Check your dashboard for personalized guidance</div>
                </div>
            </div>

            <div style="text-align: center;">
                <a href="{{ url('/student/recommendations') }}" class="btn-primary">
                    🔍 View My Recommendations
                </a>
            </div>

            <div style="background: #dbeafe; padding: 16px; border-radius: 12px; margin-top: 30px; border-left: 4px solid #000000;">
                <p style="color: #000000; font-size: 14px; margin: 0;">
                    💡 <strong>Did you know?</strong> Students who use our guidance tool are 3x more likely to choose the right subjects for their desired career path!
                </p>
            </div>
        </div>

        <div style="background: #000000; padding: 32px; text-align: center; border-top: 1px solid #dbeafe;">
            <p style="color: #000000; font-size: 13px; margin-bottom: 10px;">
                Luwinga Secondary School - Empowering Students for Success
            </p>
            <div class="copyright" style="color: #000000; font-size: 12px;">
                <a href="{{ url('/') }}" style="color: #2563eb; text-decoration: none;">Return to Dashboard</a> |
                <a href="mailto:guidance@luwinga.edu.mw" style="color: #2563eb; text-decoration: none;">Contact Counsellor</a>
            </div>
        </div>
    </div>
</body>
</html>
