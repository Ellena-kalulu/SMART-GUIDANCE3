/**
 * Visual Mode — for deaf / hard of hearing students.
 * Disables all audio, adds strong visual alerts, and enhances visual cues.
 */
(function () {
    'use strict';

    // ── Silence all audio / speech synthesis ────────────────────────────────
    if ('speechSynthesis' in window) {
        window.speechSynthesis.cancel();
        var origSpeak = window.speechSynthesis.speak.bind(window.speechSynthesis);
        window.speechSynthesis.speak = function() {}; // suppress TTS
    }

    // ── Visual notification toast ────────────────────────────────────────────
    function notify(message, type) {
        var container = document.getElementById('vm-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'vm-toast-container';
            container.style.cssText = 'position:fixed;top:80px;right:20px;z-index:99999;display:flex;flex-direction:column;gap:10px;max-width:320px;pointer-events:none';
            document.body.appendChild(container);
        }
        var colors = {
            success: { bg: '#dcfce7', border: '#16a34a', text: '#14532d', icon: '✓' },
            warning: { bg: '#fef9c3', border: '#ca8a04', text: '#713f12', icon: '⚠' },
            error:   { bg: '#fee2e2', border: '#dc2626', text: '#7f1d1d', icon: '✕' },
            info:    { bg: '#dbeafe', border: '#2563eb', text: '#1e3a8a', icon: 'ℹ' },
        };
        var c = colors[type] || colors.info;
        var el = document.createElement('div');
        el.style.cssText = [
            'background:' + c.bg,
            'border:2px solid ' + c.border,
            'color:' + c.text,
            'border-radius:12px',
            'padding:14px 18px',
            'font-size:14px',
            'font-weight:600',
            'pointer-events:auto',
            'box-shadow:0 4px 20px rgba(0,0,0,0.15)',
            'display:flex',
            'align-items:flex-start',
            'gap:10px',
            'animation:vmSlideIn .25s ease'
        ].join(';');
        el.innerHTML = '<span style="font-size:18px;line-height:1">' + c.icon + '</span><span>' + message + '</span>';
        container.appendChild(el);
        setTimeout(function () {
            el.style.opacity = '0';
            el.style.transition = 'opacity .3s';
            setTimeout(function () { el.remove(); }, 300);
        }, 5000);
    }

    window.vmNotify = notify;

    // ── Visual Mode indicator banner ─────────────────────────────────────────
    function addBanner() {
        var banner = document.createElement('div');
        banner.id = 'visual-mode-banner';
        banner.style.cssText = [
            'position:fixed', 'bottom:0', 'left:0', 'right:0', 'z-index:99998',
            'background:linear-gradient(90deg,#0d9488,#0891b2)',
            'color:#fff', 'padding:8px 20px',
            'display:flex', 'align-items:center', 'justify-between',
            'font-size:12px', 'font-weight:600',
            'box-shadow:0 -2px 12px rgba(0,0,0,0.2)'
        ].join(';');
        banner.innerHTML = [
            '<span>👁 Visual Mode Active — Audio disabled. All alerts are shown on screen.</span>',
            '<button onclick="this.closest(\'#visual-mode-banner\').remove()" style="background:rgba(255,255,255,.2);border:none;color:#fff;padding:3px 10px;border-radius:6px;cursor:pointer;font-size:11px">Dismiss</button>'
        ].join('');
        document.body.appendChild(banner);
    }

    // ── Enhance visual contrast for important elements ───────────────────────
    function enhanceVisuals() {
        var style = document.createElement('style');
        style.textContent = [
            /* Larger, bolder alert text */
            '.bg-amber-50, .bg-red-50, .bg-rose-50 { border-width: 2px !important; }',
            /* Strong focus outlines */
            '*:focus { outline: 3px solid #0d9488 !important; outline-offset: 2px !important; }',
            /* Visual captions on key data */
            '[data-visual-caption]::before { content: attr(data-visual-caption); display:block; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; opacity:.6; margin-bottom:2px; }',
            /* Slide-in animation */
            '@keyframes vmSlideIn { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:translateX(0); } }',
            /* Body bottom padding so banner doesn't cover content */
            'body { padding-bottom: 50px !important; }',
        ].join('\n');
        document.head.appendChild(style);
    }

    // ── Replace audio alerts with visual ones ────────────────────────────────
    function patchAlerts() {
        var origAlert = window.alert;
        window.alert = function(msg) {
            notify(String(msg), 'info');
        };
    }

    // ── Boot ─────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        if (document.body.dataset.accessMode !== 'visual') return;

        enhanceVisuals();
        addBanner();
        patchAlerts();

        notify('Visual Mode is active. All system messages will appear here instead of sounds.', 'info');
    });
})();
