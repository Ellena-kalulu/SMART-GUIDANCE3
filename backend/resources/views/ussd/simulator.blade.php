<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>USSD Simulator – Smart MSCE Guidance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family:'Inter',sans-serif; background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 50%,#1e40af 100%); min-height:100vh; }
        .phone-shell { background:linear-gradient(170deg,#111827 0%,#1e3a8a 60%,#1d4ed8 100%); box-shadow:0 35px 80px -15px rgba(0,0,0,.7), inset 0 1px 0 rgba(255,255,255,.08); }
        .phone-screen { background:linear-gradient(180deg,#020b18 0%,#040e1f 100%); font-family:'JetBrains Mono',monospace; box-shadow:inset 0 0 40px rgba(0,0,0,.8); }
        .ussd-green { color:#4ade80; text-shadow:0 0 12px rgba(74,222,128,.3); }
        .ussd-red   { color:#f87171; text-shadow:0 0 10px rgba(248,113,113,.25); }
        .key-btn { transition:transform .08s,background .12s,box-shadow .12s; }
        .key-btn:active { transform:scale(.9); }
        .key-btn:hover { box-shadow:0 0 0 2px rgba(96,165,250,.4); }
        .pulse-dot { animation:pulse 2s infinite; }
        @keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
        .glass { background:rgba(255,255,255,.06); backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,.1); }
        .card-white { background:rgba(255,255,255,.95); backdrop-filter:blur(8px); }
        .step-num { width:28px;height:28px;background:#2563eb;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;flex-shrink:0; }
        #session-ended { display:none; }
    </style>
</head>
<body class="min-h-screen py-8 px-4">

<div class="max-w-6xl mx-auto">

    {{-- ── Top Bar ─────────────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <a href="{{ route('welcome') }}" class="text-blue-300 text-sm font-semibold hover:text-blue-200 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Home
            </a>
            <h1 class="text-3xl md:text-4xl font-extrabold text-white mt-2 tracking-tight">USSD Simulator</h1>
            <p class="text-blue-200 text-sm mt-1">Luwinga Smart MSCE Guidance</p>
        </div>
        <div class="flex items-center gap-2 px-4 py-2 rounded-full glass text-green-300 text-xs font-bold">
            <span class="w-2 h-2 rounded-full bg-green-400 pulse-dot"></span> Live Simulator
        </div>
    </div>

    <div class="space-y-8">

        {{-- ── PHONE SIMULATOR (top) ─────────────────────────────────────────── --}}
        <div class="flex justify-center">
        <div class="w-full max-w-sm">

        {{-- ──────────────────────────────────────────────────────────────────── --}}
        {{-- PHONE SHELL                                                          --}}
        {{-- ──────────────────────────────────────────────────────────────────── --}}
        <div>
            <div class="phone-shell rounded-[2.8rem] p-3.5 ring-1 ring-white/10">

                {{-- Notch --}}
                <div class="flex justify-center mb-3">
                    <div class="w-20 h-1.5 rounded-full bg-white/20"></div>
                </div>

                {{-- Screen --}}
                <div class="phone-screen rounded-[2.2rem] p-5 min-h-[520px] flex flex-col border border-green-900/20">

                    {{-- Status bar --}}
                    <div class="flex items-center justify-between mb-3 pb-2.5 border-b border-green-900/40">
                        <span class="text-green-400/60 text-[9px] tracking-[0.25em] font-bold uppercase">SMART CAREER GUIDANCE</span>
                        <span class="text-green-400/50 text-[9px] font-mono">*384*12345#</span>
                    </div>

                    {{-- Content --}}
                    <div id="screen" class="ussd-green flex-1 text-[12.5px] leading-relaxed whitespace-pre-wrap break-words overflow-y-auto pr-1 min-h-[240px]"></div>

                    {{-- Session ended overlay --}}
                    <div id="session-ended" class="flex flex-col items-center justify-center gap-3 py-4">
                        <span class="text-2xl">📵</span>
                        <p class="text-red-300 text-xs font-bold text-center">Session ended</p>
                        <button id="new-session-btn" type="button"
                                class="px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-500 transition-colors">
                            Start New Session
                        </button>
                    </div>

                    {{-- Input area --}}
                    <div class="mt-4 pt-3 border-t border-green-900/40" id="input-area">
                        <label class="text-green-600/50 text-[9px] uppercase tracking-widest font-bold block mb-1.5">Type your choice</label>
                        <input type="text" id="ussd-input" placeholder="Enter number or student number..."
                               class="w-full bg-transparent text-green-200 text-sm outline-none placeholder-green-900/60 border-b border-green-800/40 pb-2 focus:border-green-500/60">
                    </div>
                </div>
            </div>

            {{-- Action row --}}
            <div class="grid grid-cols-3 gap-2 mt-4">
                <button id="back-btn" type="button"
                        class="py-3 card-white border border-white/20 text-slate-700 rounded-xl font-semibold text-sm hover:bg-white transition disabled:opacity-30 shadow-sm">
                    ← Back
                </button>
                <button id="send-btn" type="button"
                        class="py-3 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-500 shadow-lg shadow-blue-900/40 transition">
                    Send
                </button>
                <button id="reset-btn" type="button"
                        class="py-3 card-white border border-white/20 text-blue-700 rounded-xl font-semibold text-sm hover:bg-white transition shadow-sm">
                    Reset
                </button>
            </div>

            {{-- Keypad --}}
            <div class="grid grid-cols-3 gap-2 mt-3" id="keypad">
                @foreach(['1','2','3','4','5','6','7','8','9','*','0','#'] as $key)
                <button type="button" data-key="{{ $key }}"
                        class="key-btn py-3.5 card-white border border-white/20 rounded-xl text-sm font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-800 hover:border-blue-300 shadow-sm transition">
                    {{ $key }}
                </button>
                @endforeach
            </div>
        </div>

        </div>
        </div>

        {{-- ── INFO PANEL (below phone) ─────────────────────────────────────── --}}
        <div class="grid lg:grid-cols-3 gap-6">

            {{-- How to use --}}
            <div class="card-white rounded-2xl shadow-lg p-6">
                <h2 class="font-bold text-slate-900 text-lg mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-blue-600 text-white text-sm flex items-center justify-center">?</span>
                    How to Use
                </h2>
                <ol class="space-y-4 text-sm text-slate-600">
                    <li class="flex gap-3"><span class="step-num">1</span><div>Press <strong>Send</strong> with empty input — or click a student number below — to open the main menu.</div></li>
                    <li class="flex gap-3"><span class="step-num">2</span><div>Type a number (<strong>1–9</strong>) to select a feature, then enter the student number when prompted.</div></li>
                    <li class="flex gap-3"><span class="step-num">3</span><div><strong>Parent Access (6):</strong> Enter student number → PIN = last 4 digits of the student number.</div></li>
                    <li class="flex gap-3"><span class="step-num">4</span><div><strong>Smart Journey:</strong> After entering a student number once, the system remembers it for 24 hours.</div></li>
                    <li class="flex gap-3"><span class="step-num">5</span><div>Press <strong>0</strong> to exit, or <strong>Back</strong> to go to the previous step.</div></li>
                </ol>
            </div>

            {{-- Quick demo numbers --}}
            <div class="card-white rounded-2xl shadow-lg p-6">
                <h2 class="font-bold text-slate-900 mb-1 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-blue-600 text-white text-sm flex items-center justify-center">👨‍🎓</span>
                    Demo Student Numbers
                </h2>
                <p class="text-xs text-slate-500 mb-4">Click any to auto-dial and get a recommendation instantly.</p>
                <div class="grid grid-cols-2 gap-3">
                    @foreach([['LSS/2024/0001','Form 1','Science'],['LSS/2023/0001','Form 2','Humanities'],['LSS/2022/0001','Form 3','Science'],['LSS/2021/0001','Form 4','Commerce']] as $demo)
                    <button type="button" data-sn="{{ $demo[0] }}"
                            class="student-pick text-left p-3 rounded-xl bg-blue-50 hover:bg-blue-100 border border-blue-100 hover:border-blue-300 transition-all group">
                        <p class="font-mono text-xs font-bold text-blue-800 group-hover:text-blue-900">{{ $demo[0] }}</p>
                        <p class="text-[10px] text-blue-600 mt-0.5">{{ $demo[1] }} · {{ $demo[2] }}</p>
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Live conversation transcript --}}
            <div class="card-white rounded-2xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500 pulse-dot"></span>
                        Live Conversation
                    </h2>
                    <button id="clear-log" type="button" class="text-xs text-slate-400 hover:text-slate-700 transition font-semibold">Clear</button>
                </div>
                <div id="history-log" class="max-h-56 overflow-y-auto space-y-2 text-sm">
                    <p class="text-slate-400 text-xs italic text-center py-4">Conversation will appear here as you use the simulator…</p>
                </div>
            </div>
        </div>

        {{-- 9 Features + special features --}}
        <div class="card-white rounded-2xl shadow-lg p-6">
            <h2 class="font-bold text-slate-900 mb-1">8 Menu Options</h2>
            <p class="text-xs text-slate-500 mb-4">All powered by the same AI engine as the web portal.</p>
            <div class="grid sm:grid-cols-3 lg:grid-cols-8 gap-2.5">
                @foreach([
                    ['1','Recommendation','bg-blue-600','My MSCE path + confidence score + reasons'],
                    ['2','Progress','bg-blue-500','Average, top subject, trend & risk alerts'],
                    ['3','Comparison','bg-violet-600','All paths ranked: Suitable / Possible / Less Suitable'],
                    ['4','AI Advice','bg-amber-600','Personalised study advice & what to improve'],
                    ['5','Counsellor','bg-rose-600','Book appointment via USSD'],
                    ['6','Parent Access','bg-cyan-700','PIN-protected: child\'s recommendation & risk'],
                    ['7','Teacher','bg-slate-600','Quick student summary for teachers'],
                    ['9','Goal Tracker','bg-teal-600','Progress bar toward MSCE combination goal'],
                ] as $feat)
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="w-5 h-5 rounded-md {{ $feat[2] }} text-white text-[10px] font-black flex items-center justify-center">{{ $feat[0] }}</span>
                        <span class="font-bold text-slate-800 text-xs">{{ $feat[1] }}</span>
                    </div>
                    <p class="text-[10px] text-slate-500 leading-snug">{{ $feat[3] }}</p>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>

<script>
    const sessionId   = 'sim-' + Date.now();
    const csrf        = document.querySelector('meta[name="csrf-token"]').content;

    // Each demo session simulates a different caller — a fresh, never-before-seen
    // phone number — so the "remembered phone" shortcut (which normally lets a
    // returning caller skip re-entering their student number) never kicks in here
    // and every click on a service always asks for the student number first.
    function newPhoneNumber() {
        return '099' + Math.floor(1000000 + Math.random() * 8999999);
    }
    let phoneNumber = newPhoneNumber();

    let ussdText  = '';
    let history   = [''];
    let logItems  = [];
    let isEnded   = false;

    const screen      = document.getElementById('screen');
    const input       = document.getElementById('ussd-input');
    const inputArea   = document.getElementById('input-area');
    const endedOverlay= document.getElementById('session-ended');

    // ── Render helpers ──────────────────────────────────────────────────
    function setEnded(ended) {
        isEnded = ended;
        inputArea.style.display   = ended ? 'none'  : 'block';
        endedOverlay.style.display= ended ? 'flex'  : 'none';
        endedOverlay.style.flexDirection = 'column';
        const btns = document.querySelectorAll('#keypad button, .student-pick, #back-btn, #send-btn');
        btns.forEach(b => b.disabled = ended);
    }

    function display(raw) {
        const ended = raw.startsWith('END ');
        const text  = raw.replace(/^(CON|END)\s/, '');
        screen.textContent  = text;
        screen.className    = ended ? 'ussd-red flex-1 text-[12.5px] leading-relaxed whitespace-pre-wrap break-words overflow-y-auto pr-1 min-h-[240px]'
                                    : 'ussd-green flex-1 text-[12.5px] leading-relaxed whitespace-pre-wrap break-words overflow-y-auto pr-1 min-h-[240px]';
        setEnded(ended);
        if (!ended) input.focus();
    }

    function renderLog() {
        const el = document.getElementById('history-log');
        if (!logItems.length) {
            el.innerHTML = '<p class="text-slate-400 text-xs italic text-center py-4">Conversation will appear here as you use the simulator…</p>';
            return;
        }
        el.innerHTML = logItems.slice(0, 20).map(h => `
            <div class="border border-slate-100 rounded-xl p-3 ${h.ended ? 'bg-red-50' : 'bg-slate-50'}">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-[10px] text-slate-400 font-mono">${h.t}</span>
                    <span class="${h.ended ? 'text-red-600' : 'text-blue-700'} text-xs font-bold">${h.ended ? 'END' : 'CON'}</span>
                    <span class="text-slate-500 text-[10px] truncate max-w-[120px]">${h.sent || '(start)'}</span>
                </div>
                <p class="text-[11px] text-slate-700 line-clamp-2">${h.preview}</p>
            </div>`
        ).join('');
    }

    // ── USSD API call ───────────────────────────────────────────────────
    async function dial(text) {
        try {
            const res = await fetch('{{ route('ussd.simulate') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'text/plain' },
                credentials: 'same-origin',
                body: JSON.stringify({ session_id: sessionId, phone_number: phoneNumber, text }),
            });
            const body = await res.text();
            if (!res.ok) return 'END Request failed (' + res.status + '). Refresh and try again.';
            if (body.trim().startsWith('<!DOCTYPE') || body.includes('Illuminate\\'))
                return 'END Server error — ensure MySQL is running in Laragon.';
            return body;
        } catch (err) {
            return 'END Network error: ' + err.message;
        }
    }

    async function sendChoice() {
        if (isEnded) return;
        const val = input.value.trim();
        if (!val && ussdText !== '') return;

        const newText = ussdText === '' ? val : ussdText + '*' + val;
        history.push(newText);
        ussdText = newText;
        input.value = '';

        // Show loading
        screen.textContent = 'Loading…';

        const response = await dial(ussdText);
        display(response);

        const ended = response.startsWith('END ');
        logItems.unshift({
            t: new Date().toLocaleTimeString(),
            sent: ussdText,
            preview: response.replace(/^(CON|END)\s/, '').split('\n')[0].slice(0, 100),
            ended,
        });
        renderLog();

        if (ended) {
            ussdText = '';
            history  = [''];
        }
    }

    // ── Reset / New session ─────────────────────────────────────────────
    async function resetSession() {
        ussdText = '';
        history  = [''];
        input.value = '';
        phoneNumber = newPhoneNumber();
        setEnded(false);
        screen.textContent = '';
        const res = await dial('');
        display(res);
    }

    // ── Event listeners ─────────────────────────────────────────────────
    document.getElementById('send-btn').addEventListener('click', sendChoice);
    document.getElementById('reset-btn').addEventListener('click', resetSession);
    document.getElementById('new-session-btn').addEventListener('click', resetSession);

    document.getElementById('back-btn').addEventListener('click', async () => {
        if (history.length <= 1 || isEnded) return;
        history.pop();
        ussdText = history[history.length - 1];
        input.value = '';
        display(await dial(ussdText));
    });

    input.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); sendChoice(); }
    });

    document.getElementById('keypad').addEventListener('click', e => {
        const key = e.target.closest('[data-key]');
        if (!key || key.disabled || isEnded) return;
        input.value = (input.value || '') + key.dataset.key;
        input.focus();
    });

    document.querySelectorAll('.student-pick').forEach(btn => {
        btn.addEventListener('click', () => {
            if (isEnded) return;
            input.value = btn.dataset.sn;
            sendChoice();
        });
    });

    document.getElementById('clear-log').addEventListener('click', () => {
        logItems = [];
        renderLog();
    });

    // Auto-open main menu
    dial('').then(display);
</script>
</body>
</html>
