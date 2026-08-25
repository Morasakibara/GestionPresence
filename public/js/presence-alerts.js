/**
 * Le Pharaon — Alertes de présence temps réel
 * Poll l'API /api/presence-alerts toutes les 10s pour détecter
 * les nouvelles arrivées et départs. Affiche des toasts et notif navigateur.
 */
(function () {
    'use strict';

    const POLL_INTERVAL = 10000; // 10 secondes
    let lastCheck = new Date().toISOString().slice(0, 19).replace('T', ' ');
    let timer = null;
    let isPaused = false;
    let browserNotifPermission = false;

    // Demander la permission pour les notifications navigateur
    function requestNotifPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                browserNotifPermission = permission === 'granted';
            });
        } else if ('Notification' in window && Notification.permission === 'granted') {
            browserNotifPermission = true;
        }
    }

    // Toast Le Pharaon
    function showToast(message, type, icon) {
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
                ${icon}
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

    // Notification navigateur
    function sendBrowserNotification(title, body, tag) {
        if (!browserNotifPermission) return;
        try {
            new Notification(title, {
                body: body,
                icon: '/storage/avatars/logo-pharaon.png',
                tag: tag,
                requireInteraction: false,
            });
        } catch (e) { /* ignore */ }
    }

    // Icônes SVG
    const icons = {
        arrival: '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7l4 4z"/></svg>',
        departure: '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4l4 4z"/></svg>',
        retard: '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    };

    // Poll principal
    async function checkPresenceAlerts() {
        if (isPaused) return;

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

            // Nouvelles arrivées
            if (data.arrivals && data.arrivals.length > 0) {
                data.arrivals.forEach(arr => {
                    const type = arr.retard ? 'retard' : 'arrival';
                    const msg = arr.retard
                        ? `⚠️ ${arr.nom} est arrivé(e) en retard à ${arr.heure}`
                        : `✅ ${arr.nom} est arrivé(e) à ${arr.heure}`;
                    showToast(msg, type, icons[type]);
                    sendBrowserNotification(
                        arr.retard ? 'Retard détecté' : 'Nouvelle arrivée',
                        msg,
                        'arrival-' + arr.id
                    );
                });
            }

            // Nouveaux départs
            if (data.departures && data.departures.length > 0) {
                data.departures.forEach(dep => {
                    const msg = `🚪 ${dep.nom} est parti(e) à ${dep.heure}`;
                    showToast(msg, 'departure', icons.departure);
                    sendBrowserNotification('Départ enregistré', msg, 'departure-' + dep.id);
                });
            }

            // Mettre à jour les compteurs dans le DOM si les éléments existent
            if (data.counts) {
                const presentEls = document.querySelectorAll('[data-count="present"]');
                presentEls.forEach(el => { el.textContent = data.counts.present; });
                const absentEls = document.querySelectorAll('[data-count="absent"]');
                absentEls.forEach(el => { el.textContent = data.counts.absent; });
            }

            lastCheck = data.timestamp || new Date().toISOString().slice(0, 19).replace('T', ' ');
        } catch (err) {
            // Silencieux en cas d'erreur réseau
        }
    }

    function startPolling() {
        timer = setInterval(checkPresenceAlerts, POLL_INTERVAL);
        checkPresenceAlerts();
    }

    // Pause quand l'onglet est caché
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            isPaused = true;
            clearInterval(timer);
        } else {
            isPaused = false;
            lastCheck = new Date().toISOString().slice(0, 19).replace('T', ' ');
            checkPresenceAlerts();
            startPolling();
        }
    });

    // Init
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            requestNotifPermission();
            startPolling();
        });
    } else {
        requestNotifPermission();
        startPolling();
    }
})();
