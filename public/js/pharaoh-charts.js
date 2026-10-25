/**
 * Le Pharaon — thème Chart.js commun.
 *
 * Toutes les pages déclarent leurs graphiques via ce module pour obtenir
 * exactement le rendu des dashboards : or #D39B23, encre #080808, grille
 * discrète, légende à points, infobulles noires et police Inter.
 *
 * Les couleurs s'écrivent par nom — 'gold', 'goldLight', 'ink', 'success',
 * 'danger', 'warning', 'info', 'muted', 'surface' — et sont re-résolues quand
 * le thème clair/sombre change, sans recréer le graphique.
 *
 * Prérequis dans la page : Chart.js (4.4.4) puis ce fichier, enfin l'appel :
 *
 *   document.addEventListener('DOMContentLoaded', function () {
 *       PharaohCharts.line(document.getElementById('monGraphique'), {
 *           labels: @json($labels),
 *           datasets: [{ label: 'Notes', data: @json($notes), borderColor: 'gold' }],
 *       });
 *   });
 */
(function (window, document) {
    'use strict';

    if (window.PharaohCharts) {
        return;
    }

    var FONT = "'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif";
    var instances = [];

    var COLOR_KEYS = [
        'backgroundColor',
        'borderColor',
        'hoverBackgroundColor',
        'hoverBorderColor',
        'pointBackgroundColor',
        'pointBorderColor',
        'pointHoverBackgroundColor',
        'pointHoverBorderColor',
    ];

    function isDark() {
        return document.documentElement.getAttribute('data-theme') === 'dark';
    }

    /**
     * Palette courante. Les clés servent aussi de noms symboliques dans les
     * configurations : `backgroundColor: 'gold'`.
     */
    function palette() {
        var dark = isDark();

        return {
            dark: dark,
            gold: '#D39B23',
            goldLight: '#E9B533',
            goldBright: '#FACE4A',
            goldFaint: 'rgba(211, 155, 35, 0.10)',
            goldSoft: 'rgba(211, 155, 35, 0.55)',
            ink: dark ? '#F4F4F5' : '#080808',
            inkSoft: dark ? '#D4D4D8' : '#111111',
            success: dark ? '#34D399' : '#2E8B57',
            danger: dark ? '#F87171' : '#D64545',
            warning: dark ? '#FBBF24' : '#D97706',
            info: dark ? '#60A5FA' : '#3B82C4',
            muted: dark ? '#62626E' : '#9CA3AF',
            text: dark ? '#A1A1AA' : '#6B7280',
            grid: dark ? 'rgba(255, 255, 255, 0.07)' : 'rgba(0, 0, 0, 0.06)',
            surface: dark ? '#121215' : '#FFFFFF',
            tooltip: dark ? '#1B1B20' : '#080808',
            tooltipText: '#F5F5F5',
        };
    }

    /** Remplace un nom de couleur par sa valeur, y compris dans un tableau. */
    function resolveColor(value, p) {
        if (Array.isArray(value)) {
            return value.map(function (item) {
                return resolveColor(item, p);
            });
        }

        if (typeof value === 'string' && Object.prototype.hasOwnProperty.call(p, value)) {
            return p[value];
        }

        return value;
    }

    /**
     * Applique les valeurs par défaut d'un jeu de données. Les couleurs par
     * défaut sont des noms symboliques : elles suivent donc le thème.
     */
    function applyDatasetDefaults(type, dataset) {
        if (type === 'line') {
            if (dataset.borderWidth === undefined) { dataset.borderWidth = 2; }
            if (dataset.tension === undefined) { dataset.tension = 0.35; }
            if (dataset.fill === undefined) { dataset.fill = true; }
            if (dataset.pointRadius === undefined) { dataset.pointRadius = 4; }
            if (dataset.pointHoverRadius === undefined) { dataset.pointHoverRadius = 6; }
            if (dataset.pointBorderWidth === undefined) { dataset.pointBorderWidth = 2; }
            if (dataset.pointBorderColor === undefined) { dataset.pointBorderColor = 'surface'; }
            if (dataset.borderColor === undefined) { dataset.borderColor = 'gold'; }
            if (dataset.pointBackgroundColor === undefined) {
                dataset.pointBackgroundColor = dataset.borderColor;
            }
            if (dataset.backgroundColor === undefined) { dataset.backgroundColor = 'goldFaint'; }
        } else if (type === 'bar') {
            if (dataset.borderRadius === undefined) { dataset.borderRadius = 6; }
            if (dataset.maxBarThickness === undefined) { dataset.maxBarThickness = 22; }
            if (dataset.borderSkipped === undefined) { dataset.borderSkipped = false; }
            if (dataset.backgroundColor === undefined) { dataset.backgroundColor = 'gold'; }
        } else if (type === 'doughnut' || type === 'pie' || type === 'polarArea') {
            if (dataset.borderWidth === undefined) { dataset.borderWidth = 0; }
            if (dataset.hoverOffset === undefined) { dataset.hoverOffset = 6; }
            if (type === 'doughnut' && dataset.cutout === undefined) { dataset.cutout = '72%'; }
        }
    }

    /**
     * Mémorise les couleurs d'origine (noms symboliques) puis les résout.
     * Le thème peut ainsi être appliqué à nouveau sans perdre l'intention.
     */
    function syncDatasetColors(dataset, p) {
        if (!dataset.__pharaohSource) {
            var source = {};
            COLOR_KEYS.forEach(function (key) {
                if (dataset[key] !== undefined) {
                    source[key] = dataset[key];
                }
            });
            dataset.__pharaohSource = source;
        }

        COLOR_KEYS.forEach(function (key) {
            var source = dataset.__pharaohSource[key];
            if (source !== undefined) {
                dataset[key] = resolveColor(source, p);
            }
        });
    }

    function baseOptions(p) {
        return {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            layout: { padding: { top: 4, right: 4, bottom: 0, left: 0 } },
            animation: { duration: 450, easing: 'easeOutQuart' },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 8,
                        boxHeight: 8,
                        padding: 16,
                        color: p.text,
                        font: { family: FONT, size: 11, weight: '600' },
                    },
                },
                tooltip: {
                    backgroundColor: p.tooltip,
                    titleColor: p.tooltipText,
                    bodyColor: p.tooltipText,
                    borderColor: 'rgba(211, 155, 35, 0.35)',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 10,
                    displayColors: true,
                    boxPadding: 4,
                    titleFont: { family: FONT, size: 12, weight: '600' },
                    bodyFont: { family: FONT, size: 12 },
                },
            },
            scales: {
                x: {
                    grid: { display: false, drawBorder: false },
                    border: { display: false },
                    ticks: { color: p.text, font: { family: FONT, size: 11 }, maxRotation: 45 },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: p.grid, drawBorder: false },
                    border: { display: false },
                    ticks: { color: p.text, font: { family: FONT, size: 11 } },
                },
            },
        };
    }

    /** Fusion à deux niveaux (plugins, scales) : les surcharges de page gagnent. */
    function mergeOptions(base, override) {
        var out = {};
        Object.keys(base).forEach(function (key) {
            out[key] = base[key];
        });

        Object.keys(override || {}).forEach(function (key) {
            var value = override[key];
            var current = out[key];

            if (value && typeof value === 'object' && !Array.isArray(value)
                && current && typeof current === 'object' && !Array.isArray(current)) {
                var nested = {};
                Object.keys(current).forEach(function (nestedKey) {
                    nested[nestedKey] = current[nestedKey];
                });
                Object.keys(value).forEach(function (nestedKey) {
                    nested[nestedKey] = value[nestedKey];
                });
                out[key] = nested;
            } else {
                out[key] = value;
            }
        });

        return out;
    }

    function create(canvas, config) {
        if (!canvas || typeof window.Chart === 'undefined') {
            return null;
        }

        var type = config.type || 'line';
        var p = palette();
        var datasets = (config.data && config.data.datasets) ? config.data.datasets : [];

        datasets.forEach(function (dataset) {
            applyDatasetDefaults(type, dataset);
            syncDatasetColors(dataset, p);
        });

        var chart = new window.Chart(canvas, {
            type: type,
            data: config.data,
            options: mergeOptions(baseOptions(p), config.options || {}),
        });

        instances.push({ chart: chart, canvas: canvas });

        return chart;
    }

    /** Réapplique la palette sans recréer le graphique (références préservées). */
    function restyle(entry) {
        var chart = entry.chart;
        var p = palette();
        var options = chart.options;

        (chart.data.datasets || []).forEach(function (dataset) {
            syncDatasetColors(dataset, p);
        });

        if (options.plugins) {
            if (options.plugins.legend && options.plugins.legend.labels) {
                options.plugins.legend.labels.color = p.text;
            }
            if (options.plugins.tooltip) {
                options.plugins.tooltip.backgroundColor = p.tooltip;
                options.plugins.tooltip.titleColor = p.tooltipText;
                options.plugins.tooltip.bodyColor = p.tooltipText;
            }
        }

        if (options.scales) {
            Object.keys(options.scales).forEach(function (key) {
                var scale = options.scales[key];
                if (scale.ticks) { scale.ticks.color = p.text; }
                if (scale.grid && scale.grid.color) { scale.grid.color = p.grid; }
            });
        }

        chart.update('none');
    }

    function refreshAll() {
        instances.forEach(restyle);
    }

    // ── Formatage ────────────────────────────────────────────────
    function money(value) {
        return Number(value || 0).toLocaleString('fr-FR') + ' FCFA';
    }

    function number(value) {
        return Number(value || 0).toLocaleString('fr-FR');
    }

    // ── API publique ─────────────────────────────────────────────
    window.PharaohCharts = {
        colors: palette,
        money: money,
        number: number,
        create: create,
        refresh: refreshAll,

        line: function (canvas, data, options) {
            return create(canvas, { type: 'line', data: data, options: options });
        },
        bar: function (canvas, data, options) {
            return create(canvas, { type: 'bar', data: data, options: options });
        },
        doughnut: function (canvas, data, options) {
            return create(canvas, { type: 'doughnut', data: data, options: options });
        },
        pie: function (canvas, data, options) {
            return create(canvas, { type: 'pie', data: data, options: options });
        },
    };

    // Le thème clair/sombre est porté par `data-theme` sur <html>.
    if (typeof window.MutationObserver === 'function') {
        var observer = new window.MutationObserver(function () {
            refreshAll();
        });
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-theme'],
        });
    }
})(window, document);
