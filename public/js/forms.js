/**
 * Le Pharaon — Effets communs à tous les formulaires
 * ───────────────────────────────────────────────────
 * Reporte sur chaque champ mot de passe l'œil « afficher / masquer » des
 * formulaires de connexion et d'inscription, afin que tous les formulaires
 * de l'application se comportent de la même façon.
 *
 * Sont ignorés : les champs qui possèdent déjà leur propre bouton, les codes
 * PIN (un chiffre par case, pavé numérique), et tout bloc marqué
 * `data-no-reveal`.
 */
(function () {
    'use strict';

    const EYE = '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">'
        + '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />'
        + '<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>';

    const EYE_OFF = '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">'
        + '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>';

    /** Les codes PIN ne doivent pas recevoir d'œil : le champ est déjà dédié. */
    function estCodePin(input) {
        const mode = input.getAttribute('inputmode');
        const pattern = input.getAttribute('pattern') || '';

        return input.maxLength === 1
            || mode === 'numeric'
            || pattern.indexOf('[0-9]') === 0
            || input.classList.contains('pin-digit')
            || input.classList.contains('recovery-digit')
            || input.closest('#pinModal') !== null
            || input.closest('[data-no-reveal]') !== null;
    }

    function enhance(input) {
        const wrapper = input.parentElement;
        if (!wrapper) return;
        if (input.dataset.revealReady === '1') return;

        // Un bouton existant (login, inscription) : on ne double pas l'œil.
        if (wrapper.querySelector('button')) return;

        if (getComputedStyle(wrapper).position === 'static') {
            wrapper.style.position = 'relative';
        }
        input.style.paddingRight = '2.5rem';
        input.dataset.revealReady = '1';

        const bouton = document.createElement('button');
        bouton.type = 'button';
        bouton.className = 'absolute right-2 top-1/2 inline-flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-md text-gray-400 transition-colors hover:text-pharaoh-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-pharaoh-gold/60';
        bouton.setAttribute('aria-label', 'Afficher le mot de passe');
        bouton.setAttribute('aria-pressed', 'false');
        bouton.innerHTML = EYE;

        bouton.addEventListener('click', function () {
            const visible = input.getAttribute('type') === 'text';
            input.setAttribute('type', visible ? 'password' : 'text');
            bouton.innerHTML = visible ? EYE : EYE_OFF;
            bouton.setAttribute('aria-pressed', visible ? 'false' : 'true');
            bouton.setAttribute('aria-label', visible ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
            input.focus();
        });

        wrapper.appendChild(bouton);
    }

    function init() {
        document.querySelectorAll('input[type="password"]').forEach(function (input) {
            if (estCodePin(input)) return;
            enhance(input);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
