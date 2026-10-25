---
name: pharaon-ui
description: Construction et revue de l'interface Blade et Tailwind de GestionPresence. Use when creating or changing views, layouts, navigation, forms, tables, modals, animations or PDF templates, or when checking accessibility and consistency with the Le Pharaon noir et or charte.
---

# UI Le Pharaon

## Vue d'ensemble

L'interface est rendue par Blade, stylée avec Tailwind CSS 3.4 et animée avec Alpine.js. La charte « Le Pharaon » est noir et or, avec Inter pour le texte et Playfair Display pour les titres. Les composants réutilisables vivent dans `resources/css/app.css` (`@layer components`).

Avant de créer un style, vérifier s'il existe déjà un composant. Avant de créer une page, réutiliser `resources/views/layouts/app.blade.php` et le partial de navigation du rôle.

## Fichiers de référence

| Fichier | Rôle |
|---|---|
| `resources/css/app.css` | Variables de charte, composants, thème sombre |
| `tailwind.config.js` | Palette, ombres, polices, safelist |
| `resources/views/layouts/app.blade.php` | Layout principal, sidebar, navbar mobile |
| `resources/views/partials/nav/*.blade.php` | Navigation par rôle |
| `resources/views/{role}/` | Pages par domaine |
| `resources/views/pdf/` | Gabarits dompdf |
| `resources/js/app.js` | Alpine |
| `resources/js/notifications.js` | Notifications temps réel |

## Tokens

| Token | Valeur | Usage |
|---|---|---|
| `pharaoh-black` | `#080808` | Sidebar, en-têtes, texte principal |
| `pharaoh-gold` | `#D39B23` | Action principale, lien actif |
| `pharaoh-gold-light` | `#E9B533` | Survol |
| `pharaoh-gold-bright` | `#FACE4A` | Accent de titre |
| `pharaoh-bronze` | `#B77F1D` | Secondaire |
| `pharaoh-bronze-dark` | `#885910` | Secondaire foncé |
| `background` / `surface` | `#F8F8F8` / `#FFFFFF` | Fond et cartes |
| Succès | `#2E8B57` | Statut positif |
| Danger | `#D64545` | Erreur |
| Alerte | `#D97706` | Avertissement |
| Info | `#3B82C4` | Information |

Polices : `font-sans` = Inter, `font-display` = Playfair Display.

Ombres : `shadow-card` pour les cartes, `shadow-gold` pour les actions or.

Classes `3hcig-*` : anciens noms remappés sur la charte. Ne pas les utiliser pour du nouveau code.

## Composants disponibles

- Cartes : `pharaoh-card`, `stat-card`, `stat-card-icon`, `stat-card-value`, `stat-card-label`.
- Boutons : `btn-gold`, `btn-secondary`, `btn-primary`, `btn-danger`, `btn-success`, `btn-press`.
- Titres de page : `page-heading`, `page-heading-title`, `page-heading-sub`.
- Badges : `badge`, `badge-success`, `badge-warning`, `badge-danger`, `badge-info`, `badge-gold`.
- Tableaux : `table-wrap`, `table-scroll`, `table-head`, `table-body`.
- Formulaires : `form-group`, `form-card`, `form-card-header`, `form-card-title`, `form-card-subtitle`, `input-field`, `input-field-error`, `input-label`.
- Messages : `alert`, `alert-success`, `alert-danger`, `alert-info`, `alert-warning`, `empty-state`.
- Navigation : `nav-link`, `filter-btn`.

Ne pas dupliquer ces styles dans une vue avec des dizaines de classes utilitaires. Si un composant manque, l'ajouter dans `@layer components` avec `@apply`.

## Règles de mise en page

- Utiliser `layouts/app.blade.php` et le partial de navigation correspondant au rôle.
- Conserver la sidebar desktop et la navbar mobile existantes ; ne pas créer un second layout.
- Pour une page de liste : `page-heading`, puis `table-wrap` ou `empty-state`.
- Pour un formulaire : `form-card` + `form-group` + `input-field` + `input-label`.
- Pour un tableau de bord : grille de `stat-card`, puis graphiques Chart.js et listes.
- Modales : conteneur avec `hidden`, wrapper interne `flex`. Ne jamais mettre `hidden` et `flex` sur le même élément.
- Les vues PDF n'utilisent pas le layout web : dompdf supporte mal flex/grid complexes ; privilégier tables et CSS simple.

## Mouvement et interactions

- Courbes fournies : `--ease-out` (`cubic-bezier(0.23, 1, 0.32, 1)`) et `--ease-in-out`.
- Durées courtes : 150 à 300 ms.
- Animer `transform` et `opacity` ; éviter les propriétés coûteuses.
- Feedback d'appui : `btn-press` (léger `scale`).
- Respecter `prefers-reduced-motion` : supprimer les animations non essentielles.
- Les animations doivent être interruptibles et ne pas bloquer l'action.
- Pour un audit ou une revue d'animation, utiliser les skills de design disponibles dans `.agents/skills/` : `emil-design-eng`, `apple-design`, `review-animations`, `improve-animations`, `animation-vocabulary`.

## Accessibilité

- Chaque champ a un `label` associé ou un `aria-label`.
- Focus visible sur tous les éléments interactifs.
- Modales : `role="dialog"`, `aria-modal="true"`, fermeture Échap, focus piégé et restauré.
- Contraste : texte blanc sur or à vérifier ; préférer `pharaoh-black` sur les fonds or clairs.
- Images décoratives : `alt=""` ; images porteuses de sens : `alt` utile.
- Navigation clavier complète ; ne pas dépendre uniquement du survol.
- Respecter la langue française dans les libellés et les messages.

## Workflow

1. Identifier la vue, le layout et le partial de navigation concernés.
2. Réutiliser les composants existants et les tokens.
3. Modifier la vue Blade, puis le CSS uniquement si nécessaire.
4. Lancer `npm run build` (ou `npm run dev` pendant le développement).
5. Vérifier dans le navigateur en desktop et mobile, au clavier, avec `prefers-reduced-motion`.
6. Lancer `npx stylelint "resources/css/**/*.css"` si le CSS a changé.
7. Vérifier les tests de rendu existants (`NavigationHarmonieTest`, `PharaonFeaturesTest`).

## Checklist de revue

- [ ] Charte noir et or respectée, aucun nouveau `3hcig-*`.
- [ ] Composants existants réutilisés.
- [ ] Navigation et état actif corrects.
- [ ] Responsive et sidebar mobile fonctionnels.
- [ ] Accessibilité clavier, labels, focus, contrastes.
- [ ] Modales et menus fermables.
- [ ] Animations courtes, interruptibles, reduced-motion pris en compte.
- [ ] Aucun `{!! !!}` non justifié ni secret dans le HTML.
- [ ] `npm run build` passe.
- [ ] Rendu vérifié dans le navigateur (ne pas affirmer sans test réel).
