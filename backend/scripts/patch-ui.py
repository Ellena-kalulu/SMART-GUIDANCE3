import re, pathlib

# dashboard layout
p = pathlib.Path(r"c:\laragon\www\SMART-GUIDANCE-TOOL-main\backend\resources\views\layouts\dashboard.blade.php")
t = p.read_text(encoding="utf-8")
old = """@php $accessMode = session('access_mode', 'normal'); @endphp
@if($accessMode === 'voice')
<script src=\"{{ asset('js/voice-mode.js') }}\" defer></script>
@elseif($accessMode === 'visual')"""
new = """@php $accessMode = session('access_mode', 'normal'); @endphp
@if($accessMode === 'voice' && auth()->check() && auth()->user()->isStudent())
<script>
window.VOICE_CONFIG = {
    csrf: @json(csrf_token()),
    askUrl: @json(route('student.voice.ask')),
    routes: {
        dashboard: @json(route('student.dashboard')),
        assessment: @json(route('student.assessment')),
        progress: @json(route('student.progress')),
        career: @json(route('student.career.path')),
        recommendation: @json(route('student.recommendations')),
        subject: @json(route('student.subject.combinations')),
        university: @json(route('student.universities')),
        chatbot: @json(route('student.chatbot')),
        appointment: @json(route('student.appointments')),
    }
};
</script>
@endif
@if($accessMode === 'voice')
<script src=\"{{ asset('js/voice-mode.js') }}\" defer></script>
@elseif($accessMode === 'visual')"""
if old not in t:
    print('dashboard: pattern not found')
else:
    p.write_text(t.replace(old, new), encoding="utf-8")
    print('dashboard: ok')

# web.php
p = pathlib.Path(r"c:\laragon\www\SMART-GUIDANCE-TOOL-main\backend\routes\web.php")
t = p.read_text(encoding="utf-8")
t2 = t.replace(
    "        Route::post('/chatbot/send',                    [StudentController::class, 'chatbotSend'])->name('chatbot.send');",
    "        Route::post('/chatbot/send',                    [StudentController::class, 'chatbotSend'])->name('chatbot.send');\n        Route::post('/voice/ask',                       [StudentController::class, 'voiceAsk'])->name('voice.ask');"
)
t2 = re.sub(r"\n\s*// Career Roadmap Planner\n\s*Route::get\('/career-roadmap'.*?\n", "\n", t2)
p.write_text(t2, encoding="utf-8")
print('web.php: ok')

# sidebar
p = pathlib.Path(r"c:\laragon\www\SMART-GUIDANCE-TOOL-main\backend\resources\views\dashboard\student\partials\sidebar.blade.php")
t = p.read_text(encoding="utf-8")
t2 = re.sub(r"\n\s*<x-sidebar-nav :route=\"'student\.career\.roadmap'\".*?</x-sidebar-nav>", "", t, flags=re.S)
p.write_text(t2, encoding="utf-8")
print('sidebar: ok')
