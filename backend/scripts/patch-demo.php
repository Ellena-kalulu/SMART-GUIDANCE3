<?php
$base = 'c:/laragon/www/SMART-GUIDANCE-TOOL-main/backend';

$sidebar = file_get_contents("$base/resources/views/dashboard/counsellor/partials/sidebar.blade.php");
$sidebar = preg_replace('/\s*<x-sidebar-nav :route="\'counsellor\.at\.risk\'".*?<\/x-sidebar-nav>\s*/s', "\n", $sidebar);
file_put_contents("$base/resources/views/dashboard/counsellor/partials/sidebar.blade.php", $sidebar);
echo "sidebar ok\n";

$panel = file_get_contents("$base/resources/views/partials/accessibility-panel.blade.php");
$panel = preg_replace('/\s*<button type="button" @click="speakPage\(\)".*?<\/button>\s*<button type="button" @click="startDictation\(\)".*?<\/button>\s*/s', "\n", $panel);
file_put_contents("$base/resources/views/partials/accessibility-panel.blade.php", $panel);
echo "panel ok\n";

$js = file_get_contents("$base/public/js/accessibility.js");
if (!str_contains($js, 'injectAccessibilityStyles')) {
$inject = <<<'JS'

    injectAccessibilityStyles() {
        let el = document.getElementById('cg-a11y-styles');
        if (!el) {
            el = document.createElement('style');
            el.id = 'cg-a11y-styles';
            document.head.appendChild(el);
        }
        const hc = this.highContrast ? `
            html.high-contrast, html.high-contrast body { background:#000 !important; color:#fff !important; }
            html.high-contrast main, html.high-contrast header, html.high-contrast aside,
            html.high-contrast .card, html.high-contrast .bg-white, html.high-contrast [class*="bg-white"] {
                background:#000 !important; color:#fff !important; border-color:#fff !important;
            }
            html.high-contrast a, html.high-contrast button, html.high-contrast label { color:#ffff00 !important; }
        ` : '';
        const kb = this.keyboardNav ? `
            html.keyboard-nav *:focus { outline: 4px solid #2563eb !important; outline-offset: 3px !important; }
            html.keyboard-nav a, html.keyboard-nav button { box-shadow: 0 0 0 1px rgba(37,99,235,.35); }
        ` : '';
        const sm = this.simplified ? `
            html.simplified-lang body { line-height: 1.9 !important; letter-spacing: 0.03em !important; }
            html.simplified-lang p, html.simplified-lang li, html.simplified-lang span, html.simplified-lang td {
                font-size: 1.05em !important;
            }
            html.simplified-lang .tech-term { display: none !important; }
        ` : '';
        el.textContent = hc + kb + sm;
    },
JS;
$js = str_replace('        applyAll() {', "        applyAll() {\n            this.injectAccessibilityStyles();", $js);
$js = str_replace("        applyContrast() {\n            document.documentElement.classList.toggle('high-contrast', this.highContrast);\n        },", "        applyContrast() {\n            document.documentElement.classList.toggle('high-contrast', this.highContrast);\n            this.injectAccessibilityStyles();\n        },", $js);
$js = str_replace("        applyKeyboardNav() {\n            document.documentElement.classList.toggle('keyboard-nav', this.keyboardNav);\n        },", "        applyKeyboardNav() {\n            document.documentElement.classList.toggle('keyboard-nav', this.keyboardNav);\n            this.injectAccessibilityStyles();\n        },", $js);
$js = str_replace("        applySimplified() {\n            document.documentElement.classList.toggle('simplified-lang', this.simplified);\n        },", "        applySimplified() {\n            document.documentElement.classList.toggle('simplified-lang', this.simplified);\n            this.injectAccessibilityStyles();\n        },", $js);
$js = str_replace('        applyDisabilityAdaptations() {', $inject . "\n        applyDisabilityAdaptations() {", $js);
file_put_contents("$base/public/js/accessibility.js", $js);
echo "accessibility js ok\n";
}

$voice = file_get_contents("$base/public/js/voice-mode.js");
$old = "        r.onresult = function (e) {\n            var result = e.results[e.results.length - 1];\n            if (!result.isFinal || isSpeaking) return;\n            processCommand(result[0].transcript);\n        };";
$new = "        r.onresult = function (e) {\n            var result = e.results[e.results.length - 1];\n            if (isSpeaking) {\n                synth.cancel();\n                isSpeaking = false;\n                setMicIcon('listening');\n            }\n            if (!result.isFinal) return;\n            processCommand(result[0].transcript);\n        };";
if (str_contains($voice, $old)) {
    file_put_contents("$base/public/js/voice-mode.js", str_replace($old, $new, $voice));
    echo "voice-mode ok\n";
} else { echo "voice-mode skip\n"; }

$users = file_get_contents("$base/resources/views/dashboard/admin/users.blade.php");
$oldJs = "                if (res.ok && data.success) {";
$newJs = "                if (res.ok && (data.success || data.message)) {";
if (str_contains($users, $oldJs) && !str_contains($users, 'data.success || data.message')) {
    file_put_contents("$base/resources/views/dashboard/admin/users.blade.php", str_replace($oldJs, $newJs, $users));
    echo "admin users js ok\n";
}
