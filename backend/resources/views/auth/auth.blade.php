<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CareerGuide') — Luwinga Secondary School</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        navy: '#1e40af',
                        brand: '#2563eb',
                    }
                }
            }
        }
    </script>
    <style>
        * { -webkit-font-smoothing: antialiased; }
        body { font-family: 'Inter', sans-serif; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp .4s ease both; }
        .btn-primary {
            display: flex; width: 100%; align-items: center; justify-content: center;
            background: #2563eb; color: #ffffff; font-weight: 600;
            padding: 0.875rem 1.5rem; border-radius: 0.75rem;
            transition: background .15s, box-shadow .15s;
            font-size: 0.9rem; letter-spacing: 0.01em;
        }
        .btn-primary:hover { background: #1d4ed8; }
    </style>
</head>
<body class="bg-white min-h-screen flex flex-col items-center justify-center px-4 py-10">

    <!-- Brand header -->
    <a href="{{ route('welcome') }}" class="flex items-center gap-2.5 mb-8 fade-up">
        <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <div>
            <span class="font-bold text-black text-base leading-tight block">CareerGuide</span>
            <span class="text-xs text-black/40 leading-tight block">Luwinga Secondary School</span>
        </div>
    </a>

    <!-- Form card -->
    <div class="fade-up w-full max-w-md bg-white rounded-2xl border border-black/15 shadow-sm p-8">
        @yield('auth-content')
    </div>

    <!-- Footer -->
    <p class="text-black/40 text-xs mt-6 text-center fade-up">
        &copy; {{ date('Y') }} Smart Career &amp; Subject Guidance Tool &middot; Luwinga Secondary School
    </p>

</body>
</html>
