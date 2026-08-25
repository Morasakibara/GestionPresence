/**
 * Le Pharaon — Alertes de présence temps réel
 *
 * Mode WebSocket (Soketi/Pusher) : écoute en temps réel via Laravel Echo.
 * Mode fallback : polling HTTP toutes les 10s si le WebSocket n'est pas disponible.
 *
 * Lancement Soketi (développement) :
 *   npx soketi start --port=6001 --appKey=pharaon-local-key --appSecret=pharaon-local-secret
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

    // ── Toasts ──
    const icons = {
        arrival: '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7l4 4z"/></svg>',
        departure: '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4l4 4z"/></svg>',
        retard: '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    };

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

    function showToast(message, type) {
        const container = getOrCreateContainer();
        const toast = document.createElement('div');
        const colorMap = {
            arrival: 'border-l-4 border-green-500',
            departure: 'border-l-4 border-purple-500',
            retard: 'border-l-4 border-yellow-500',
        };
        const iconBgMap = {
            arrival: 'bg-green-100 text-green-600',
            departure: 'bg-purple-100 text-purple-600',
            retard: 'bg-yellow-100 text-yellow-600',
        };

        toast.className = `flex items-center gap-3 bg-white rounded-lg shadow-lg p-3 mb-2 transform translate-x-full opacity-0 transition-all duration-300 ${colorMap[type] || 'border-l-4 border-pharaoh-gold'}`;
        toast.innerHTML = `
            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center ${iconBgMap[type] || 'bg-pharaoh-gold/10 text-pharaoh-gold'}">
                ${icons[type] || icons.arrival}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-[#080808]">${message}</p>
            </div>
            <button onclick="this.closest('.presence-toast').remove()" class="flex-shrink-0 text-gray-400 hover:text-gray-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        `;
        toast.classList.add('presence-toast');
        container.appendChild(toast);

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

    // ── Traitement d'un événement d'arrivée ──
    function handleArrival(data) {
        const type = data.retard ? 'retard' : 'arrival';
        const msg = data.retard
            ? `⚠️ ${data.nom} est arrivé(e) en retard à ${data.heure}`
            : `✅ ${data.nom} est arrivé(e) à ${data.heure}`;
        showToast(msg, type);
        sendBrowserNotification(data.retard ? 'Retard détecté' : 'Nouvelle arrivée', msg, 'arrival-' + data.user_id);
    }

    // ── Traitement d'un événement de départ ──
    function handleDeparture(data) {
        const msg = `🚪 ${data.nom} est parti(e) à ${data.heure}`;
        showToast(msg, 'departure');
        sendBrowserNotification('Départ enregistré', msg, 'departure-' + data.user_id);
    }

    // ── Mise à jour des compteurs ──
    function updateCounts(data) {
        if (data.present !== undefined) {
            document.querySelectorAll('[data-count="present"]').forEach(el => {
                el.textContent = data.present;
            });
        }
        if (data.absent !== undefined) {
            document.querySelectorAll('[data-count="absent"]').forEach(el => {
                el.textContent = data.absent;
            });
        }
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
                    // Arrêter le polling si WebSocket actif
                    if (timer) {
                        clearInterval(timer);
                        timer = null;
                    }
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

    // ── Pause quand l'onglet est caché ──
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
        // Tenter le WebSocket d'abord, puis fallback polling
        const wsOk = initWebSocket();
        if (!wsOk) {
            startPolling();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
