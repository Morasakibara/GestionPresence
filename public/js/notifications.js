/**
 * Le Pharaon — Notifications temps réel (polling intelligent)
 * Vérifie les nouvelles notifications toutes les 30s avec backoff adaptatif
 */
(function() {
    'use strict';

    let pollInterval = 30000;
    let lastCount = 0;
    let timer = null;
    let isPaused = false;

    // Le premier appel de chaque page sert seulement de référence : sans lui, un
    // simple changement de page pouvait déclencher un toast et le son.
    let primed = false;

    /**
     * Joue le son de notification. On passe par AlertSound quand il est chargé :
     * le bouton « son » du bandeau coupe alors aussi ces alertes.
     */
    function playSound() {
        if (window.AlertSound && typeof window.AlertSound.notification === 'function') {
            window.AlertSound.notification();
            return;
        }

        try {
            const audio = new Audio('data:audio/wav;base64,UklGRl4FAABXQVZFZm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YToFAACAgICAgICAgI=');
            audio.volume = 0.1;
            audio.play().catch(() => {});
        } catch (e) { /* audio indisponible */ }
    }

    const BADGE_SELECTORS = [
        '.notif-badge-count',
        '[data-notif-count]',
    ];

    function getBadgeElements() {
        const els = [];
        BADGE_SELECTORS.forEach(sel => {
            document.querySelectorAll(sel).forEach(el => els.push(el));
        });
        return els;
    }

    function showToast(message, type) {
        const existing = document.getElementById('pharaoh-toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.id = 'pharaoh-toast';
        toast.className = 'fixed top-4 right-4 z-[9999] max-w-sm w-full bg-white rounded-xl shadow-lg border border-gray-100 p-4 transform transition-all duration-300 translate-x-full opacity-0';
        toast.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center ${type === 'success' ? 'bg-green-100 text-green-600' : type === 'warning' ? 'bg-yellow-100 text-yellow-600' : 'bg-pharaoh-gold/10 text-pharaoh-gold'}">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-[#080808]">Nouvelle notification</p>
                    <p class="text-sm text-gray-500 mt-0.5 truncate">${message}</p>
                </div>
                <button onclick="this.closest('#pharaoh-toast').remove()" class="flex-shrink-0 text-gray-400 hover:text-gray-600">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        `;
        document.body.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
            toast.classList.add('translate-x-0', 'opacity-100');
        });

        setTimeout(() => {
            if (toast.parentNode) {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);
    }

    function updateBadges(count) {
        const badges = getBadgeElements();
        badges.forEach(badge => {
            badge.textContent = count > 9 ? '9+' : count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        });
    }

    async function checkNotifications() {
        if (isPaused) return;

        try {
            const response = await fetch('/api/notifications/check', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) return;

            const data = await response.json();
            const newCount = data.unread_count || 0;

            const isBaseline = !primed;
            primed = true;

            if (!isBaseline && newCount > lastCount) {
                const diff = newCount - lastCount;
                const msg = diff === 1
                    ? (data.latest_message || 'Vous avez une nouvelle notification')
                    : `Vous avez ${diff} nouvelles notifications`;
                showToast(msg, data.latest_type || 'info');
                playSound();
            }

            // Son « après connexion » : drapeau à usage unique posé par le serveur
            // dans la session (session()->pull), joué au premier passage seulement.
            if (isBaseline && window.__notifSoundArmed) {
                playSound();
            }
            window.__notifSoundArmed = false;

            updateBadges(newCount);
            lastCount = newCount;
            pollInterval = 30000;
        } catch (err) {
            // Backoff en cas d'erreur
            pollInterval = Math.min(pollInterval * 2, 120000);
        }
    }

    function startPolling() {
        clearInterval(timer);
        timer = setInterval(checkNotifications, pollInterval);
        checkNotifications();
    }

    // Pause quand l'onglet est caché
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            isPaused = true;
            clearInterval(timer);
        } else {
            isPaused = false;
            startPolling();
        }
    });

    // Init
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startPolling);
    } else {
        startPolling();
    }
})();
