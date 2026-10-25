---
name: pharaon-conventions
description: Architecture, rôles et conventions de l'application Le Pharaon (Laravel 12). Use when adding or changing controllers, services, models, migrations, routes, middleware, policies or tests in GestionPresence, especially when a change touches the Administrateur, Superviseur or Employer role model, the current_role session, caisse, presence or evaluation rules.
---

# Conventions Le Pharaon

## Vue d'ensemble

Application Laravel 12 de gestion de présence. Ce skill donne la carte du code, le modèle de rôles et les invariants métier à respecter avant toute modification non triviale. Lire aussi `AGENTS.md` à la racine.

## Stack et structure

- PHP ≥ 8.2, Laravel 12.66, MySQL 8.
- Blade + Tailwind 3.4 + Alpine.js + Vite 6 ; Chart.js et FullCalendar.
- PHPUnit 11.5 ; Pint pour le style.
- PDF dompdf ; Excel/CSV PhpSpreadsheet ; Resend pour les emails.
- Soketi/Echo pour le temps réel, avec repli polling HTTP.
- File d'attente `database` pour les envois WhatsApp.

Carte des dossiers :

```text
app/Http/Controllers/   Contrôleurs web et API
app/Http/Requests/      Validation (caisse, stock)
app/Http/Middleware/    Rôles, réseau, CSRF, super admin
app/Services/           Logique métier réutilisable
app/Models/             Modèles Eloquent
app/Policies/           Autorisations par enregistrement
app/Jobs/               EnvoyerFactureWhatsAppJob
app/Console/Commands/   Tâches planifiées et commandes artisan
routes/web.php          Routes web
routes/api.php          API Sanctum
resources/views/        Blade (layouts, partials, PDF)
resources/css/app.css   Charte et composants
bootstrap/app.php       Alias de middlewares et scheduler
tests/Feature, Unit     Suite PHPUnit
```

## Rôles et session

Valeurs exactes de `utilisateur.role` :

| Valeur | Usage |
|---|---|
| `Administrateur` | Back-office ; `IsAdmin` ; passe toujours `RoleMiddleware` |
| `Superviseur` | Équipe ou poste spécialisé |
| `Employer` | Employé — ne jamais écrire `Employé` |

Le modèle `App\Models\Superviseur` (table `Superviseur`, clé = id utilisateur, sans timestamps) porte `type_superviseur` :

- `directrice`
- `secretaire`
- `gestionnaire_stock`
- `superviseur_a`
- `null` pour un superviseur classique

Chaque connexion pose `session('current_role')` à `Employer` ou `Superviseur`. `CheckUserRoleAndNetwork` :

1. exige un utilisateur authentifié ;
2. n'accepte que les rôles `Employer` et `Superviseur` ;
3. redirige vers `auth.role_selection` si `current_role` est absent ;
4. redirige selon le préfixe d'URL (`user/...`, `superviseur/...`, `directrice/...`, `secretaire/...`, `gestionnaire/...`).

Conséquences :

- Les routes admin ne passent pas par ce middleware.
- Dans un test, définir `session(['current_role' => 'Superviseur'])` ou `'Employer'`.
- `current_role` est une aide au routage, pas une autorisation métier.

## Middlewares

| Alias | Classe | Effet |
|---|---|---|
| `isAdmin` | `IsAdmin` | `Administrateur` uniquement, sinon redirection `/` |
| `role:...` | `RoleMiddleware` | Rôles listés ; admin toujours autorisé |
| `check.role.network` | `CheckUserRoleAndNetwork` | Auth + rôle + `current_role` + préfixe |
| `superadmin` | `SuperAdminOnly` | `Utilisateur::isSuperAdmin()` via `config('app.super_admins')` |
| `registration.access` | `RegistrationAccessMiddleware` | Accès à l'inscription |

Les policies de `app/Policies/` couvrent commande, congé, présence, retrait, service fourni et utilisateur. Toute route paramétrée doit vérifier l'appartenance ou le rôle via une policy.

## Flux de requête typique

1. `routes/web.php` choisit le groupe de middleware.
2. Le contrôleur valide via un `FormRequest` ou `$request->validate()`.
3. Le service métier effectue les écritures et transactions.
4. La policy vérifie les droits sur l'enregistrement.
5. Le contrôleur retourne une vue Blade ou un export.
6. Un test Feature couvre le chemin heureux et au moins un refus.

Pour une nouvelle fonctionnalité, suivre cet ordre : route → autorisation → validation → service → contrôleur → vue → test.

## Invariants métier

### Présence  (`app/Models/Presence.php`, `PresenceAlertController`, `HandleAutoAbsences`)
- Pointage arrivée/départ avec géolocalisation obligatoire.
- Vérification du rayon autour de `WorkplaceLocation`.
- Retard après 08:00.
- Départ conditionné à une fiche de rendement.
- Pointage possible le week-end et à toute heure.
- Commande `presence:auto-absences` planifiée à 18:45.

### Caisse (`AbstractCaisseController`, `CaisseService`, `CommandeSuperviseurService`)
- `DirectriceController` et `SecretaireController` héritent du contrôleur abstrait et ne définissent que `module()`, `types()` et `viewPrefix()`.
- Commandes avec statuts payé, partiel, à payer à la livraison ; encaissement du solde.
- Services fournis facturés séparément ; retraits avec motif.
- Les dashboards utilisent un cache par utilisateur, à invalider après écriture (`flushDashboardCache`).
- Les factures passent par `FactureService`, `ServiceFactureService`, `FacturePdfService`, puis `EnvoyerFactureWhatsAppJob`.

### Évaluations (`EvaluationService`, `AdminReportController`, `SuperviseurEvaluationController`)
- Note sur 20 ; vert ≥ 14, orange 10–13, rouge < 10.
- Évaluation manuelle prioritaire sur l'évaluation automatique.
- Les exports et bulletins doivent respecter les droits de l'équipe.

### Stock (`GestionnaireStockController`, modèles `StockTshirt`, `StockPapier`)
- T-shirts par couleur/taille ; papier par imprimante, en mètres.
- Alertes de seuil bas.

### Notifications et files
- Notifications internes + diffusion temps réel Soketi ; repli polling.
- `QUEUE_CONNECTION=database` ; `queue:work --stop-when-empty` est planifié chaque minute.
- Les jobs doivent être idempotents, car la file peut rejouer une tâche.

### Métriques
- `/admin/metrics` est protégé par `superadmin`.
- La liste blanche vient de `SUPER_ADMIN_EMAILS` via `config('app.super_admins')`.

## Conventions de code

- Domaine en français, code technique en anglais.
- Contrôleurs fins, services testables, pas de requêtes lourdes dans les vues.
- Conserver `Utilisateur`, `motDePasse`, table `utilisateur`, `SoftDeletes`, champ `pin_code` masqué.
- Le mutateur `motDePasse` hash une valeur en clair ; ne pas hasher deux fois.
- Les montants utilisent les casts/services existants ; éviter les flottants ad hoc.
- Ne pas ajouter de nouvelles classes `3hcig-*` ; utiliser `pharaoh-*` pour l'UI.
- Les libellés et messages utilisateur restent en français.

## Pièges fréquents

- Utiliser `Employé` au lieu de `Employer`.
- Oublier `current_role` dans un test : redirection silencieuse vers la sélection de rôle.
- Croire que `role:...` protège les admins : ils passent toujours.
- Ajouter une route admin dans le groupe `check.role.network` : elle est inaccessible aux admins.
- Mettre la logique de caisse dans un contrôleur concret au lieu de `AbstractCaisseController`/services.
- Lancer les tests sur une base non seedée : `firstOrFail()` échoue.
- Écrire un job non idempotent dans la file `database`.
- Exposer un PDF ou un export sans policy.

## Commandes utiles

```bash
php artisan test
php artisan test --filter=CaisseFlow
vendor/bin/pint --dirty
npm run build
php artisan migrate:status
php artisan route:list --path=admin
```
