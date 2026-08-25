/**
 * alert-sound.js
 * ───────────────
 * Synthétise un son d'alerte retard via Web Audio API.
 * Zéro dépendance externe, zéro requête réseau, zéro fichier WAV.
 * 
 * Usage :
 *   AlertSound.retard()     → son alerte retard (triple beep grave)
 *   AlertSound.arrival()    → son arrivée (bip aigu court)
 *   AlertSound.departure()  → son départ (bip moyen doux)
 *   AlertSound.enable()     → activer le son
 *   AlertSound.disable()    → couper le son
 *   AlertSound.toggle()     → inverser l'état
 */

const AlertSound = (() => {
    let _enabled = true;
    let _ctx = null;

    /** Lazy-init de l'AudioContext (nécessite interaction utilisateur) */
    function ctx() {
        if (!_ctx) {
            _ctx = new (window.AudioContext || window.webkitAudioContext)();
        }
        if (_ctx.state === 'suspended') {
            _ctx.resume();
        }
        return _ctx;
    }

    /** Génère un beep paramétrique */
    function beep({ freq = 440, duration = 0.15, volume = 0.3, type = 'sine', ramp = true } = {}) {
        if (!_enabled) return;

        try {
            const ac = ctx();
            const osc = ac.createOscillator();
            const gain = ac.createGain();

            osc.type = type;
            osc.frequency.value = freq;

            gain.gain.value = 0;
            gain.gain.linearRampToValueAtTime(volume, ac.currentTime + 0.01);
            if (ramp) {
                gain.gain.linearRampToValueAtTime(0, ac.currentTime + duration);
            }

            osc.connect(gain);
            gain.connect(ac.destination);

            osc.start(ac.currentTime);
            osc.stop(ac.currentTime + duration);
        } catch (e) {
            console.warn('[AlertSound] Erreur audio:', e);
        }
    }

    /** Son retard : triple beep grave et alertant */
    function retard() {
        if (!_enabled) return;
        // Beep 1
        beep({ freq: 380, duration: 0.18, volume: 0.35, type: 'square' });
        // Beep 2 (après 220ms)
        setTimeout(() => {
            beep({ freq: 380, duration: 0.18, volume: 0.35, type: 'square' });
        }, 220);
        // Beep 3 (après 440ms) — double pulsation
        setTimeout(() => {
            beep({ freq: 380, duration: 0.12, volume: 0.35, type: 'square' });
        }, 440);
        setTimeout(() => {
            beep({ freq: 520, duration: 0.25, volume: 0.4, type: 'sine' });
        }, 580);
    }

    /** Son arrivée : bip aigu aigu et bref */
    function arrival() {
        if (!_enabled) return;
        beep({ freq: 880, duration: 0.1, volume: 0.2, type: 'sine' });
        setTimeout(() => {
            beep({ freq: 1100, duration: 0.12, volume: 0.25, type: 'sine' });
        }, 100);
    }

    /** Son départ : bip moyen doux et chaud */
    function departure() {
        if (!_enabled) return;
        beep({ freq: 660, duration: 0.15, volume: 0.2, type: 'sine' });
        setTimeout(() => {
            beep({ freq: 550, duration: 0.2, volume: 0.18, type: 'triangle' });
        }, 150);
    }

    /** Son notification générique */
    function notification() {
        if (!_enabled) return;
        beep({ freq: 800, duration: 0.08, volume: 0.15, type: 'sine' });
        setTimeout(() => beep({ freq: 1000, duration: 0.1, volume: 0.18, type: 'sine' }), 80);
    }

    /** Persistance dans localStorage */
    function _loadState() {
        const stored = localStorage.getItem('alertSoundEnabled');
        if (stored !== null) {
            _enabled = stored === 'true';
        }
        _syncUI();
    }

    function _syncUI() {
        const btn = document.getElementById('soundToggle');
        if (btn) {
            btn.innerHTML = _enabled
                ? '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M18.364 5.636a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>'
                : '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>';
            btn.title = _enabled ? 'Désactiver le son' : 'Activer le son';
        }
    }

    // Initialiser au chargement
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', _loadState);
    } else {
        _loadState();
    }

    return {
        retard,
        arrival,
        departure,
        notification,
        enable()  { _enabled = true;  localStorage.setItem('alertSoundEnabled', 'true');  _syncUI(); },
        disable() { _enabled = false; localStorage.setItem('alertSoundEnabled', 'false'); _syncUI(); },
        toggle()  { _enabled ? this.disable() : this.enable(); },
        isEnabled() { return _enabled; },
        /** Préchauffer l'AudioContext (appeler au 1er clic) */
        warmup()  { ctx(); }
    };
})();

// Exposer globalement
window.AlertSound = AlertSound;
