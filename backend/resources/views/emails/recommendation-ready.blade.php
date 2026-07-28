<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Career Recommendations Are Ready - CareerGuide</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            background: #eff6ff;
            margin: 0;
            padding: 20px;
        }

        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #2563eb 0%, #2563eb 100%);
            padding: 48px 32px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -30%;
            width: 80%;
            height: 80%;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -30%;
            width: 80%;
            height: 80%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .celebration-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            position: relative;
            z-index: 1;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .celebration-icon span {
            font-size: 40px;
        }

        .hero-section h1 {
            color: text-white;
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
            letter-spacing: -0.5px;
        }

        .hero-section p {
            color: rgba(255, 255, 255, 0.95);
            font-size: 16px;
            position: relative;
            z-index: 1;
        }

        /* Content Area */
        .content-area {
            padding: 48px 32px;
        }

        /* Greeting */
        .greeting {
            text-align: center;
            margin-bottom: 32px;
        }

        .greeting h2 {
            font-size: 24px;
            font-weight: 800;
            color: #000000;
            margin-bottom: 8px;
        }

        .greeting p {
            color: #000000;
            font-size: 16px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin: 32px 0;
        }

        .stat-item {
            text-align: center;
            padding: 20px 12px;
            background: linear-gradient(135deg, #000000 0%, #000000 100%);
            border-radius: 20px;
            border: 1px solid #000000;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 800;
            background: linear-gradient(135deg, #2563eb 0%, #2563eb 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 4px;
        }

        .stat-label {
            color: #000000;
            font-size: 13px;
            font-weight: 500;
        }

        /* Section Styles */
        .section {
            margin: 40px 0;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .section-title .emoji {
            font-size: 28px;
        }

        .section-title h3 {
            font-size: 20px;
            font-weight: 700;
            color: #000000;
        }

        /* Career Cards */
        .career-card {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            padding: 20px;
            border-radius: 20px;
            margin-bottom: 16px;
            border-left: 4px solid #3b82f6;
            transition: transform 0.2s;
        }

        .career-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .career-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e3a8a;
        }

        .match-score {
            background: border-white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            color: #3b82f6;
        }

        .career-category {
            display: inline-block;
            background: rgba(59, 130, 246, 0.2);
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            color: #1e40af;
            margin-bottom: 12px;
        }

        .career-description {
            color: #000000;
            font-size: 14px;
            line-height: 1.5;
        }

        /* University Cards */
        .uni-card {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            padding: 20px;
            border-radius: 20px;
            margin-bottom: 16px;
            border-left: 4px solid #2563eb;
        }

        .uni-title {
            font-size: 18px;
            font-weight: 700;
            color: #1d4ed8;
            margin-bottom: 8px;
        }

        .uni-name {
            color: #1d4ed8;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .eligibility-badge {
            display: inline-block;
            background: #2563eb;
            color: border-white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 12px;
        }

        /* Subject Cards */
        .subject-card {
            background: linear-gradient(135deg, #dbeafe 0%, #dbeafe 100%);
            padding: 20px;
            border-radius: 20px;
            margin-bottom: 16px;
            border-left: 4px solid #000000;
        }

        .subject-title {
            font-size: 18px;
            font-weight: 700;
            color: #000000;
            margin-bottom: 8px;
        }

        .subject-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .subject-tag {
            background: border-white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            color: #000000;
            font-weight: 500;
        }

        /* CTA Section */
        .cta-section {
            text-align: center;
            margin: 40px 0 30px;
        }

        .btn-primary {
            display: inline-block;
            background: linear-gradient(135deg, #2563eb 0%, #2563eb 100%);
            color: border-white;
            text-decoration: none;
            padding: 16px 40px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.5);
        }

        .btn-secondary {
            display: inline-block;
            background: border-white;
            color: #2563eb;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            margin-left: 12px;
            border: 2px solid #000000;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            border-color: #2563eb;
            transform: translateY(-2px);
        }

        /* Info Box */
        .info-box {
            background: #dbeafe;
            border-radius: 20px;
            padding: 20px;
            margin: 32px 0;
            border-left: 4px solid #2563eb;
        }

        .info-box h4 {
            color: #1d4ed8;
            font-size: 16px;
            margin-bottom: 12px;
        }

        .info-box p {
            color: #1d4ed8;
            font-size: 14px;
            line-height: 1.5;
        }

        /* Divider */
        .divider {
            text-align: center;
            margin: 32px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #000000, transparent);
        }

        .divider span {
            background: text-white;
            padding: 0 16px;
            position: relative;
            color: #000000;
            font-size: 14px;
        }

        /* Footer */
        .footer {
            background: #000000;
            padding: 32px;
            text-align: center;
        }

        .footer p {
            color: #000000;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: #000000;
            text-decoration: none;
            font-size: 13px;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: text-white;
        }

        .social-icons {
            margin-bottom: 20px;
        }

        .social-icons span {
            display: inline-block;
            margin: 0 8px;
            font-size: 20px;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .social-icons span:hover {
            opacity: 1;
        }

        .copyright {
            color: #000000;
            font-size: 11px;
        }

        /* Responsive */
        @media (max-width: 500px) {
            .content-area {
                padding: 32px 20px;
            }

            .stats-grid {
                gap: 10px;
            }

            .stat-number {
                font-size: 28px;
            }

            .stat-label {
                font-size: 11px;
            }

            .career-header {
                flex-direction: column;
                gap: 8px;
            }

            .btn-primary, .btn-secondary {
                display: block;
                margin: 10px 0;
            }

            .btn-secondary {
                margin-left: 0;
            }

            .footer-links {
                gap: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- Hero Section -->
        <div class="hero-section">
            <div class="celebration-icon">
                <span>🎉</span>
            </div>
            <h1>Your Future Awaits!</h1>
            <p>Your personalized career recommendations are ready to explore</p>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Greeting -->
            <div class="greeting">
                <h2>Hello, {{ $student->name }}! 👋</h2>
                <p>Great news! We've analyzed your assessment and found amazing opportunities tailored just for you.</p>
            </div>

            <!-- Stats Dashboard -->
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">{{ $careerCount }}</div>
                    <div class="stat-label">Career Matches</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $uniCount }}</div>
                    <div class="stat-label">University Programs</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">100%</div>
                    <div class="stat-label">Personalized</div>
                </div>
            </div>

            <!-- Top Career Recommendations -->
            <div class="section">
                <div class="section-title">
                    <span class="emoji">🌟</span>
                    <h3>Your Top Career Matches</h3>
                </div>

                <div class="career-card">
                    <div class="career-header">
                        <span class="career-title">Software Engineer</span>
                        <span class="match-score">92% Match</span>
                    </div>
                    <span class="career-category">💻 Technology</span>
                    <p class="career-description">
                        Design and build software applications that millions of people use daily. High demand career with excellent growth potential in Malawi and globally.
                    </p>
                </div>

                <div class="career-card">
                    <div class="career-header">
                        <span class="career-title">Data Scientist</span>
                        <span class="match-score">88% Match</span>
                    </div>
                    <span class="career-category">📊 Technology</span>
                    <p class="career-description">
                        Turn data into valuable insights that drive business decisions. One of the fastest-growing careers in the digital economy.
                    </p>
                </div>

                <div class="career-card">
                    <div class="career-header">
                        <span class="career-title">Civil Engineer</span>
                        <span class="match-score">85% Match</span>
                    </div>
                    <span class="career-category">🏗️ Engineering</span>
                    <p class="career-description">
                        Design and oversee construction of roads, bridges, buildings, and other infrastructure projects critical to Malawi's development.
                    </p>
                </div>
            </div>

            <!-- University Programs -->
            <div class="section">
                <div class="section-title">
                    <span class="emoji">🏛️</span>
                    <h3>University Programs You Qualify For</h3>
                </div>

                <div class="uni-card">
                    <div class="uni-title">Bachelor of Science in Computer Science</div>
                    <div class="uni-name">University of Malawi (UNIMA)</div>
                    <p style="color: #1d4ed8; font-size: 13px; margin: 8px 0;">Requirements: Mathematics (C), English (C), Physics (D)</p>
                    <span class="eligibility-badge">✓ You're Eligible!</span>
                </div>

                <div class="uni-card">
                    <div class="uni-title">Bachelor of Information Technology</div>
                    <div class="uni-name">Malawi University of Business and Applied Sciences (MUBAS)</div>
                    <p style="color: #1d4ed8; font-size: 13px; margin: 8px 0;">Requirements: Mathematics (C), English (C), Computer Studies (D)</p>
                    <span class="eligibility-badge">✓ You're Eligible!</span>
                </div>

                <div class="uni-card">
                    <div class="uni-title">Bachelor of Engineering (Civil)</div>
                    <div class="uni-name">Mzuzu University (MZUNI)</div>
                    <p style="color: #1d4ed8; font-size: 13px; margin: 8px 0;">Requirements: Mathematics (B), Physics (C), English (C)</p>
                    <span class="eligibility-badge">⚡ Conditional - Improve Physics</span>
                </div>
            </div>

            <!-- Subject Recommendations -->
            <div class="section">
                <div class="section-title">
                    <span class="emoji">📚</span>
                    <h3>Recommended Subject Combinations</h3>
                </div>

                <div class="subject-card">
                    <div class="subject-title">Technology & Engineering Path</div>
                    <p style="color: #000000; font-size: 13px;">Ideal for: Software Engineering, Data Science, Civil Engineering</p>
                    <div class="subject-list">
                        <span class="subject-tag">Mathematics</span>
                        <span class="subject-tag">Physics</span>
                        <span class="subject-tag">Computer Science</span>
                        <span class="subject-tag">English</span>
                    </div>
                </div>

                <div class="subject-card">
                    <div class="subject-title">Business & Commerce Path</div>
                    <p style="color: #000000; font-size: 13px;">Ideal for: Accounting, Business Administration, Marketing</p>
                    <div class="subject-list">
                        <span class="subject-tag">Mathematics</span>
                        <span class="subject-tag">Accounting</span>
                        <span class="subject-tag">Business Studies</span>
                        <span class="subject-tag">Economics</span>
                    </div>
                </div>
            </div>

            <!-- Main CTA Buttons -->
            <div class="cta-section">
                <a href="{{ url('/student/recommendations') }}" class="btn-primary">
                    🔮 View All Recommendations
                </a>
            </div>

            <!-- Action Plan Box -->
            <div class="info-box">
                <h4>📋 Your Action Plan</h4>
                <p><strong>Step 1:</strong> Review your top career matches and explore each option in detail</p>
                <p><strong>Step 2:</strong> Check subject requirements and plan your MSCE combination</p>
                <p><strong>Step 3:</strong> Research university programs and note entry requirements</p>
                <p><strong>Step 4:</strong> Speak with your guidance counsellor to finalize your plan</p>
            </div>

            <!-- Divider -->
            <div class="divider">
                <span>✨ Need More Guidance? ✨</span>
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <p style="color: #000000; margin-bottom: 16px;">
                    Have questions about your recommendations? Speak with your school counsellor.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="social-icons">
                <span>📘</span>
                <span>🐦</span>
                <span>📸</span>
                <span>💼</span>
            </div>

            <div class="footer-links">
                <a href="{{ url('/student/dashboard') }}">Dashboard</a>
                <a href="{{ url('/student/assessment') }}">Take Assessment</a>
                <a href="{{ url('/student/recommendations') }}">Recommendations</a>
            </div>

            <p>
                Luwinga Secondary School<br>
                Mzuzu City, Malawi
            </p>

            <div class="copyright">
                © {{ date('Y') }} Smart Career & Subject Guidance Tool. All rights reserved.<br>
                Empowering students to make informed career decisions.
            </div>
        </div>
    </div>
</body>
</html>
