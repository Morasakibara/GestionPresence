/**
 * Le Pharaon — Alertes de présence temps réel
 *
 * Mode WebSocket (Soketi/Pusher) : écoute en temps réel via Laravel Echo.
 * Mode fallback : polling HTTP toutes les 10s si le WebSocket n'est pas disponible.
 *
 * Lancement Soketi (développement) :
 *   npx soketi start --config=soketi.config.json
 */
(function () {
    'use strict';

    const POLL_INTERVAL = 10000;
    let lastCheck = new Date().toISOString().slice(0, 19).replace('T', ' ');
    let timer = null;
    let isPaused = false;
    let browserNotifPermission = false;
    let wsConnected = false;

    // ── Notifications navigateur ──
    function requestNotifPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().then(p => {
                browserNotifPermission = p === 'granted';
            });
        } else if ('Notification' in window && Notification.permission === 'granted') {
            browserNotifPermission = true;
        }
    }

    function sendBrowserNotification(title, body, tag) {
        if (!browserNotifPermission) return;
        try {
            new Notification(title, {
                body,
                icon: '/storage/avatars/logo-pharaon.png',
                tag,
                requireInteraction: false,
            });
        } catch (e) { /* ignore */ }
    }

    // ── Icônes SVG ──
    const icons = {
        arrival: '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7l4 4z"/></svg>',
        departure: '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4l4 4z"/></svg>',
        retard: '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    };

    const iconClassMap = {
        arrival: 'toast-icon--arrival',
        departure: 'toast-icon--departure',
        retard: 'toast-icon--retard',
    };

    // ── Container ──
    function getOrCreateContainer() {
        let c = document.getElementById('presence-alerts-container');
        if (!c) {
            c = document.createElement('div');
            c.id = 'presence-alerts-container';
            c.className = 'fixed top-20 right-4 z-[9998] w-80';
            document.body.appendChild(c);
        }
        return c;
    }

    // ── Toast avec animations CSS ──
    function showToast(message, type) {
        const container = getOrCreateContainer();
        const toast = document.createElement('div');

        toast.className = `pharaoh-toast pharaoh-toast--${type}`;
        toast.innerHTML = `
            <div class="flex items-center gap-3 p-3">
                <div class="toast-icon ${iconClassMap[type] || iconClassMap.arrival}">
                    ${icons[type] || icons.arrival}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="toast-message">${message}</p>
                </div>
                <button class="toast-close" aria-label="Fermer">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        `;

        // Fermeture manuelle
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => dismissToast(toast));

        // Hover pause la progress bar (géré en CSS)
        container.appendChild(toast);

        // Auto-dismiss après 5s
        setTimeout(() => dismissToast(toast), 5000);

        // Limiter à 4 toasts visibles
        const allToasts = container.querySelectorAll('.pharaoh-toast');
        if (allToasts.length > 4) {
            dismissToast(allToasts[0]);
        }
    }

    function dismissToast(toast) {
        if (!toast || !toast.parentNode) return;
        toast.classList.add('toast-exit');
        setTimeout(() => toast.remove(), 350);
    }

    // ── Événements ──
    function handleArrival(data) {
        const type = data.retard ? 'retard' : 'arrival';
        const msg = data.retard
            ? `⚠️ ${data.nom} est arrivé(e) en retard à ${data.heure}`
            : `✅ ${data.nom} est arrivé(e) à ${data.heure}`;
        showToast(msg, type);
        sendBrowserNotification(data.retard ? 'Retard détecté' : 'Nouvelle arrivée', msg, 'arrival-' + data.user_id);

        // 🔊 Son custom
        if (typeof window.AlertSound !== 'undefined') {
            if (data.retard) {
                AlertSound.retard();
            } else {
                AlertSound.arrival();
            }
        }
    }

    function handleDeparture(data) {
        const msg = `🚪 ${data.nom} est parti(e) à ${data.heure}`;
        showToast(msg, 'departure');
        sendBrowserNotification('Départ enregistré', msg, 'departure-' + data.user_id);

        // 🔊 Son custom
        if (typeof window.AlertSound !== 'undefined') {
            AlertSound.departure();
        }
    }

    function updateCounts(data) {
        if (data.present !== undefined) {
            document.querySelectorAll('[data-count="present"]').forEach(el => {
                animateCounter(el, data.present);
            });
        }
        if (data.absent !== undefined) {
            document.querySelectorAll('[data-count="absent"]').forEach(el => {
                animateCounter(el, data.absent);
            });
        }
    }

    // ── Animation de compteur ──
    function animateCounter(el, target) {
        const current = parseInt(el.textContent) || 0;
        if (current === target) return;

        const diff = target - current;
        const steps = Math.min(Math.abs(diff), 10);
        const duration = 400;
        const stepTime = duration / steps;
        let step = 0;

        const interval = setInterval(() => {
            step++;
            const progress = step / steps;
            const value = Math.round(current + diff * progress);
            el.textContent = value;

            if (step >= steps) {
                el.textContent = target;
                clearInterval(interval);
                // Petit pulse sur le compteur
                el.style.transition = 'transform 0.3s ease';
                el.style.transform = 'scale(1.15)';
                setTimeout(() => { el.style.transform = 'scale(1)'; }, 300);
            }
        }, stepTime);
    }

    // ═══════════════════════════════════════════════
    // MODE WEBSOCKET (Echo + Soketi/Pusher)
    // ═══════════════════════════════════════════════
    function initWebSocket() {
        if (typeof window.Echo === 'undefined') {
            console.log('[Pharaon] Echo non disponible → mode polling');
            return false;
        }

        try {
            const channel = window.Echo.channel('presence-alerts');

            channel.listen('.PresenceArrived', (data) => {
                handleArrival(data);
            });

            channel.listen('.PresenceDeparted', (data) => {
                handleDeparture(data);
            });

            channel.listen('.PresenceCountsUpdated', (data) => {
                updateCounts(data);
            });

            // Vérifier la connexion WebSocket après 3s
            setTimeout(() => {
                const pusher = window.Echo.connector?.pusher;
                if (pusher && pusher.connection?.state === 'connected') {
                    wsConnected = true;
                    console.log('[Pharaon] ✅ WebSocket connecté (Soketi)');
                    if (timer) { clearInterval(timer); timer = null; }
                } else {
                    console.log('[Pharaon] WebSocket non connecté → fallback polling');
                }
            }, 3000);

            return true;
        } catch (e) {
            console.log('[Pharaon] Erreur Echo → fallback polling', e);
            return false;
        }
    }

    // ═══════════════════════════════════════════════
    // MODE FALLBACK : POLLING HTTP
    // ═══════════════════════════════════════════════
    async function checkPresenceAlerts() {
        if (isPaused || wsConnected) return;

        try {
            const response = await fetch(`/api/presence-alerts?since=${encodeURIComponent(lastCheck)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) return;
            const data = await response.json();

            if (data.arrivals && data.arrivals.length > 0) {
                data.arrivals.forEach(arr => {
                    handleArrival({
                        user_id: arr.id,
                        nom: arr.nom,
                        heure: arr.heure,
                        retard: arr.retard,
                    });
                });
            }

            if (data.departures && data.departures.length > 0) {
                data.departures.forEach(dep => {
                    handleDeparture({
                        user_id: dep.id,
                        nom: dep.nom,
                        heure: dep.heure,
                    });
                });
            }

            if (data.counts) {
                updateCounts(data.counts);
            }

            lastCheck = data.timestamp || new Date().toISOString().slice(0, 19).replace('T', ' ');
        } catch (err) {
            // Silencieux
        }
    }

    function startPolling() {
        if (timer) clearInterval(timer);
        timer = setInterval(checkPresenceAlerts, POLL_INTERVAL);
        checkPresenceAlerts();
    }

    // ── Pause ──
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            isPaused = true;
            if (timer) clearInterval(timer);
        } else {
            isPaused = false;
            if (!wsConnected) {
                lastCheck = new Date().toISOString().slice(0, 19).replace('T', ' ');
                startPolling();
            }
        }
    });

    // ── Init ──
    function init() {
        requestNotifPermission();
        const wsOk = initWebSocket();
        if (!wsOk) {
            startPolling();
        }

        // Préchauffer l'AudioContext au 1er clic (browser policy)
        document.addEventListener('click', function warmupAudio() {
            if (typeof window.AlertSound !== 'undefined') {
                window.AlertSound.warmup();
            }
            document.removeEventListener('click', warmupAudio);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
