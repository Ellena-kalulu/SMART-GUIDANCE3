/**
 * CareerGuide accessibility & theme preferences (persisted in localStorage)
 */
window.CGAccessibility = {
    keys: {
        dark: 'cg-dark',
        contrast: 'cg-contrast',
        font: 'cg-font',
        kbd: 'cg-kbd',
        simple: 'cg-simple',
    },

    initEarly() {
        const dark = localStorage.getItem(this.keys.dark) === '1';
        const contrast = localStorage.getItem(this.keys.contrast) === '1';
        const font = localStorage.getItem(this.keys.font) || 'base';
        const kbd = localStorage.getItem(this.keys.kbd) === '1';
        const simple = localStorage.getItem(this.keys.simple) === '1';

        document.documentElement.classList.toggle('dark-mode', dark);
        document.documentElement.classList.toggle('high-contrast', contrast);
        document.documentElement.classList.toggle('keyboard-nav', kbd);
        document.documentElement.classList.toggle('simplified-lang', simple);
        document.documentElement.classList.remove('font-sm', 'font-lg');
        if (font === 'sm') document.documentElement.classList.add('font-sm');
        if (font === 'lg') document.documentElement.classList.add('font-lg');
    },

    speak(text, rate) {
        if (!('speechSynthesis' in window)) {
            alert('Text-to-speech is not supported in this browser.');
            return;
        }
        window.speechSynthesis.cancel();
        const u = new SpeechSynthesisUtterance(text);
        u.lang = 'en-GB';
        u.rate = rate || (document.documentElement.classList.contains('simplified-lang') ? 0.85 : 1);
        window.speechSynthesis.speak(u);
    },

    speakAssessmentQuestion(card) {
        if (!card) return;
        const question = card.querySelector('[data-assessment-question]')?.textContent?.trim() || '';
        const hint = card.querySelector('[data-assessment-hint]')?.textContent?.trim() || '';
        const options = [...card.querySelectorAll('[data-assessment-option]')].map((el, i) => `Option ${i + 1}: ${el.textContent.trim()}`);
        const scaleLabels = card.querySelector('[data-scale-low]')?.textContent?.trim();
        const scaleHigh = card.querySelector('[data-scale-high]')?.textContent?.trim();
        let parts = ['Question.', question];
        if (hint) parts.push('How to answer.', hint);
        if (options.length) parts.push('Answer choices.', ...options);
        if (scaleLabels) parts.push(`Scale from ${scaleLabels} to ${scaleHigh}.`);
        this.speak(parts.join(' '));
    },
};

document.addEventListener('DOMContentLoaded', () => CGAccessibility.initEarly());

function accessibilityTools() {
    return {
        panelOpen: false,
        dark: false,
        highContrast: false,
        keyboardNav: false,
        simplified: false,
        fontSize: 'base',
        recognition: null,
        dictating: false,
        assessmentMode: false,

        init() {
            this.dark = localStorage.getItem(CGAccessibility.keys.dark) === '1';
            this.highContrast = localStorage.getItem(CGAccessibility.keys.contrast) === '1';
            this.keyboardNav = localStorage.getItem(CGAccessibility.keys.kbd) === '1';
            this.simplified = localStorage.getItem(CGAccessibility.keys.simple) === '1';
            this.fontSize = localStorage.getItem(CGAccessibility.keys.font) || 'base';
            this.applyAll();
            this.applyDisabilityAdaptations();
        },

        applyAll() {
            this.injectAccessibilityStyles();
            this.applyDark();
            this.applyContrast();
            this.applyKeyboardNav();
            this.applySimplified();
            this.applyFontSize(this.fontSize, true);
        },

        applyFontSize(size, silent) {
            this.setFontSize(size, silent);
        },

        toggleDark() {
            this.dark = !this.dark;
            localStorage.setItem(CGAccessibility.keys.dark, this.dark ? '1' : '0');
            this.applyDark();
        },

        applyDark() {
            document.documentElement.classList.toggle('dark-mode', this.dark);
        },

        toggleContrast() {
            this.highContrast = !this.highContrast;
            localStorage.setItem(CGAccessibility.keys.contrast, this.highContrast ? '1' : '0');
            this.applyContrast();
        },

        applyContrast() {
            document.documentElement.classList.toggle('high-contrast', this.highContrast);
            this.injectAccessibilityStyles();
        },

        setFontSize(size, silent) {
            this.fontSize = size;
            document.documentElement.classList.remove('font-sm', 'font-lg');
            if (size === 'sm') document.documentElement.classList.add('font-sm');
            if (size === 'lg') document.documentElement.classList.add('font-lg');
            if (!silent) localStorage.setItem(CGAccessibility.keys.font, size);
        },

        toggleKeyboardNav() {
            this.keyboardNav = !this.keyboardNav;
            localStorage.setItem(CGAccessibility.keys.kbd, this.keyboardNav ? '1' : '0');
            this.applyKeyboardNav();
        },

        applyKeyboardNav() {
            document.documentElement.classList.toggle('keyboard-nav', this.keyboardNav);
            this.injectAccessibilityStyles();
        },

        toggleSimplified() {
            this.simplified = !this.simplified;
            localStorage.setItem(CGAccessibility.keys.simple, this.simplified ? '1' : '0');
            this.applySimplified();
        },

        applySimplified() {
            document.documentElement.classList.toggle('simplified-lang', this.simplified);
            this.injectAccessibilityStyles();
        },

        speakPage() {
            const assessmentCard = document.querySelector('[data-assessment-card]:not([style*="display: none"])')
                || document.querySelector('[x-show][data-assessment-card]');
            if (assessmentCard && window.getComputedStyle(assessmentCard.closest('[x-show]') || assessmentCard).display !== 'none') {
                CGAccessibility.speakAssessmentQuestion(assessmentCard);
                return;
            }
            const main = document.querySelector('main') || document.body;
            const text = main.innerText.replace(/\s+/g, ' ').trim().slice(0, 4000);
            CGAccessibility.speak(text);
        },

        startDictation() {
            const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SR) return alert('Speech-to-text is not supported in this browser.');

            if (this.dictating && this.recognition) {
                this.recognition.stop();
                this.dictating = false;
                return;
            }

            if (!this.recognition) {
                this.recognition = new SR();
                this.recognition.lang = 'en-GB';
                this.recognition.continuous = false;
                this.recognition.interimResults = false;
                this.recognition.onresult = (e) => {
                    const text = e.results[0][0].transcript;
                    const active = document.activeElement;
                    if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA')) {
                        active.value = (active.value ? active.value + ' ' : '') + text;
                        active.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    window.dispatchEvent(new CustomEvent('cg-voice-input', { detail: { text } }));
                };
                this.recognition.onerror = () => { this.dictating = false; };
                this.recognition.onend = () => { this.dictating = false; };
            }

            this.dictating = true;
            try {
                this.recognition.start();
            } catch (e) {
                this.dictating = false;
            }
        },


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
        applyDisabilityAdaptations() {
            const type = document.body.dataset.disability || 'none';
            if (type === 'visual_impairment') {
                this.setFontSize('lg', true);
                localStorage.setItem(CGAccessibility.keys.font, 'lg');
                if (!this.keyboardNav) {
                    this.keyboardNav = true;
                    localStorage.setItem(CGAccessibility.keys.kbd, '1');
                    this.applyKeyboardNav();
                }
            }
            if (type === 'hearing_impairment') {
                document.documentElement.classList.add('captions-enabled');
            }
            if (type === 'physical_disability') {
                this.keyboardNav = true;
                localStorage.setItem(CGAccessibility.keys.kbd, '1');
                this.applyKeyboardNav();
            }
            if (type === 'learning_disability') {
                this.simplified = true;
                localStorage.setItem(CGAccessibility.keys.simple, '1');
                this.applySimplified();
            }
        },
    };
}

window.accessibilityTools = accessibilityTools;
document.addEventListener('alpine:init', () => {
    if (window.Alpine) {
        window.Alpine.data('accessibilityTools', accessibilityTools);
    }
});
