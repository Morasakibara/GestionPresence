/*
 * Actualisation automatique des dashboards toutes les 15 s pour synchroniser
 * les données (présence, caisse, évaluations) sans action de l'utilisateur.
 *
 * Garde-fous indispensables pour ne jamais gêner la saisie :
 *   - uniquement sur une page « dashboard » ;
 *   - jamais si un modal est ouvert ;
 *   - jamais si un champ (input/textarea/select) a le focus ou si une saisie
 *     est en cours ;
 *   - jamais si l'onglet est masqué (on rattrape au retour) ;
 *   - jamais pendant une soumission de formulaire.
 */
(function () {
    'use strict';

    var INTERVALLE_MS = 15000;

    function estDashboard() {
        var p = (window.location.pathname || '').toLowerCase();
        return p.indexOf('dashboard') !== -1;
    }

    function modalOuvert() {
        // Modals de l'app : .hidden retiré sur l'overlay fixed inset-0.
        var overlays = document.querySelectorAll('[id$="Modal"], .fixed.inset-0');
        for (var i = 0; i < overlays.length; i++) {
            var el = overlays[i];
            if (el.classList && el.classList.contains('hidden')) continue;
            var visible = window.getComputedStyle(el).display !== 'none'
                && window.getComputedStyle(el).visibility !== 'hidden'
                && el.offsetParent !== null;
            if (visible && (el.getAttribute('role') === 'dialog' || el.id && /Modal$/.test(el.id))) {
                return true;
            }
        }
        return false;
    }

    function saisieEnCours() {
        var a = document.activeElement;
        if (!a) return false;
        var tag = (a.tagName || '').toLowerCase();
        return tag === 'input' || tag === 'textarea' || tag === 'select';
    }

    var soumissionEnCours = false;
    document.addEventListener('submit', function () { soumissionEnCours = true; });
    window.addEventListener('pageshow', function () { soumissionEnCours = false; });

    function peutRafraichir() {
        if (!estDashboard()) return false;
        if (document.hidden) return false;
        if (modalOuvert()) return false;
        if (saisieEnCours()) return false;
        if (soumissionEnCours) return false;
        return true;
    }

    setInterval(function () {
        if (peutRafraichir()) {
            window.location.reload();
        }
    }, INTERVALLE_MS);

    // Rattrapage au retour dans l'onglet : si la page est restée visible
    // plus de 15 s pendant la masquage, on resynchronise immédiatement.
    var masqueDepuis = null;
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            masqueDepuis = Date.now();
        } else if (masqueDepuis && Date.now() - masqueDepuis >= INTERVALLE_MS) {
            masqueDepuis = null;
            if (peutRafraichir()) window.location.reload();
        }
    });
})();
