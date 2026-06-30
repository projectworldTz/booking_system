/**
 * Global UI utilities for the booking system.
 * Imported by app.js — all helpers are attached to window for use in Blade templates.
 */

// ── Toast notification system ─────────────────────────────────────────────────

function createToastContainer() {
    let el = document.getElementById('toast-container');
    if (el) return el;
    el = document.createElement('div');
    el.id = 'toast-container';
    el.className = 'fixed bottom-4 right-4 z-[9999] flex flex-col gap-2 max-w-sm w-full pointer-events-none';
    document.body.appendChild(el);
    return el;
}

window.toast = function (message, type = 'success', duration = 5000) {
    const container = createToastContainer();
    const id = 'toast-' + Date.now();

    const colors = {
        success: 'bg-emerald-600 text-white',
        error:   'bg-rose-600 text-white',
        warning: 'bg-amber-500 text-white',
        info:    'bg-navy text-white',
    };

    const icons = {
        success: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        error:   '<path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        warning: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
        info:    '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    };

    const div = document.createElement('div');
    div.id = id;
    div.className = `pointer-events-auto flex items-start gap-3 rounded-xl px-4 py-3 shadow-lg text-sm font-medium transition-all duration-300 translate-y-2 opacity-0 ${colors[type] || colors.info}`;
    div.innerHTML = `
        <svg class="h-5 w-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            ${icons[type] || icons.info}
        </svg>
        <p class="flex-1 leading-snug">${message}</p>
        <button onclick="document.getElementById('${id}').remove()" class="shrink-0 opacity-70 hover:opacity-100 ml-1">✕</button>
    `;

    container.appendChild(div);

    // Animate in
    requestAnimationFrame(() => {
        div.classList.remove('translate-y-2', 'opacity-0');
        div.classList.add('translate-y-0', 'opacity-100');
    });

    // Auto-dismiss
    if (duration > 0) {
        setTimeout(() => {
            div.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => div.remove(), 300);
        }, duration);
    }
};


// ── Form loading state ────────────────────────────────────────────────────────
// Usage: add data-loading to any <form> or call setFormLoading(form, true/false)

window.setFormLoading = function (form, loading) {
    const btn = form.querySelector('[type=submit]');
    if (!btn) return;
    if (loading) {
        btn.dataset.originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<svg class="animate-spin h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg> Processing…`;
    } else {
        btn.disabled = false;
        btn.innerHTML = btn.dataset.originalText || btn.innerHTML;
    }
};

// Attach to all forms with data-loading attribute
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-loading]').forEach(form => {
        form.addEventListener('submit', () => setFormLoading(form, true));
    });
});


// ── Debounced live search ─────────────────────────────────────────────────────
// Usage: add data-live-search to any <input> inside a <form> to auto-submit on type

window.debounce = function (fn, delay = 350) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[data-live-search]').forEach(input => {
        input.addEventListener('input', debounce(() => {
            input.closest('form')?.submit();
        }, 400));
    });
});


// ── Auto-refresh sections ─────────────────────────────────────────────────────
// Usage: add data-refresh-url="/api/..." and data-refresh-interval="30" (seconds)
// to any element. Its innerHTML will be replaced with the response.

window.startAutoRefresh = function (el) {
    const url      = el.dataset.refreshUrl;
    const interval = parseInt(el.dataset.refreshInterval || '30', 10) * 1000;
    if (!url || !interval) return;

    const tick = async () => {
        try {
            const res  = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            const html = await res.text();
            el.innerHTML = html;
            // Re-attach Alpine to new nodes
            if (window.Alpine) Alpine.initTree(el);
        } catch (_) { /* silently ignore network errors */ }
    };

    return setInterval(tick, interval);
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-refresh-url]').forEach(el => startAutoRefresh(el));
});


// ── Payment status polling ────────────────────────────────────────────────────
// Called from booking/show when payment is pending.

window.pollPaymentStatus = function (paymentId, redirectUrl, csrfToken) {
    const interval = setInterval(async () => {
        try {
            const res  = await fetch(`/api/payments/${paymentId}/status`, {
                headers: {
                    'Accept':           'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN':     csrfToken,
                }
            });

            if (!res.ok) return;
            const data = await res.json();

            if (data.confirmed) {
                clearInterval(interval);
                toast('Payment confirmed! Your booking is now confirmed.', 'success', 8000);
                // Redirect after a short delay so user sees the toast
                setTimeout(() => { window.location.href = redirectUrl; }, 2000);
            }
        } catch (_) { /* ignore */ }
    }, 5000); // poll every 5 seconds

    // Stop polling after 10 minutes (payment probably abandoned)
    setTimeout(() => clearInterval(interval), 600_000);

    return interval;
};


// ── Confirm-dialog helper ─────────────────────────────────────────────────────
// Usage: <form data-confirm="Are you sure?"> — intercepts submit and shows confirm

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', e => {
            if (!confirm(form.dataset.confirm)) e.preventDefault();
        });
    });
});


// ── Table row highlight on hover ──────────────────────────────────────────────
// Makes entire <tr> clickable when it has data-href

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('tr[data-href]').forEach(row => {
        row.style.cursor = 'pointer';
        row.addEventListener('click', () => { window.location.href = row.dataset.href; });
    });
});
