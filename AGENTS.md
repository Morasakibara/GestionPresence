# AGENTS.md — Le Pharaon (GestionPresence)

## 1. Contexte

Application web de gestion de présence de l'entreprise **Le Pharaon**.

- **Backend** : Laravel 12.66, PHP ≥ 8.2, MySQL 8.
- **Frontend** : Blade, Tailwind CSS 3.4, Alpine.js 3.14, Vite 6, Chart.js 4.4, FullCalendar 6.
- **Temps réel** : Laravel Echo + Pusher JS via Soketi, avec repli polling HTTP.
- **Documents** : dompdf (PDF), PhpSpreadsheet (Excel/CSV).
- **Auth** : sessions web, Sanctum pour l'API mobile, PIN de récupération de session.
- **Qualité** : PHPUnit 11.5, Laravel Pint.

Rôles — valeurs exactes en base :

| `utilisateur.role` | Portée |
|---|---|
| `Administrateur` | Back-office admin ; contourne volontairement le middleware `role` |
| `Superviseur` | Équipes et postes spécialisés (`superviseur.type_superviseur` : `directrice`, `secretaire`, `gestionnaire_stock`, `superviseur_a`, ou `null` pour un superviseur classique) |
| `Employer` | Espace employé — orthographe exacte, ne pas écrire `Employé` |

## 2. Commandes

| Tâche | Commande |
|---|---|
| Installer les dépendances | `composer install` puis `npm install` |
| Configurer l'environnement | copier `.env.example` vers `.env`, puis `php artisan key:generate` |
| Base de données locale | `php artisan migrate --seed` |
| Serveur de dev | `php artisan serve` ou `bash start-dev.sh` (Laravel + Mailpit) |
| Frontend dev | `npm run dev` |
| Build frontend | `npm run build` |
| Tests | `php artisan test` |
| Test ciblé | `php artisan test --filter=CaisseFlow` |
| Formatage PHP | `vendor/bin/pint --dirty` |
| Lint CSS | `npx stylelint "resources/css/**/*.css"` |
| File d'attente | `php artisan queue:work` (connexion `database`) |
| WebSocket local | `soketi start --config=soketi.config.json` (si Soketi est installé) |

**Tests — précautions importantes**

- `phpunit.xml` ne définit pas de base de test séparée : la suite utilise la base MySQL de `.env`. Ne jamais lancer les tests contre une base de production.
- La majorité des tests utilisent `DatabaseTransactions`, mais certains (ex. `PinRecoveryTest`) créent des données persistantes : utiliser des emails uniques (`uniqid()`).
- Les tests supposent une base migrée et seedée (`NavigationHarmonieTest` fait des `firstOrFail()` sur des utilisateurs existants).
- CSRF est désactivé en environnement de test par `App\Http\Middleware\VerifyCsrfToken`.
- Ne pas remplacer `DatabaseTransactions` par `RefreshDatabase` sans validation explicite.

## 3. Architecture

| Chemin | Rôle |
|---|---|
| `app/Http/Controllers/` | Contrôleurs par domaine et par rôle. `AbstractCaisseController` centralise la caisse ; `DirectriceController` / `SecretaireController` ne définissent que module, types et préfixe de vues |
| `app/Http/Requests/` | Validation des commandes, retraits, services et stocks |
| `app/Http/Middleware/` | `IsAdmin`, `RoleMiddleware`, `CheckUserRoleAndNetwork`, `SuperAdminOnly` |
| `app/Services/` | Logique métier : caisse, factures, évaluations, exports, audit, WhatsApp |
| `app/Models/` | Modèles Eloquent ; `Utilisateur` utilise la table `utilisateur` et le champ `motDePasse` |
| `app/Policies/` | Autorisations par enregistrement (commande, congé, présence, retrait, service, utilisateur) |
| `app/Jobs/` | `EnvoyerFactureWhatsAppJob` |
| `app/Console/Commands/` | Absences automatiques, alertes évaluations rouges, rappels de fiches de rendement, backup |
| `routes/web.php` | Routes web et middlewares de rôle |
| `routes/api.php` | API mobile protégée par Sanctum |
| `resources/views/` | Blade : `layouts/app`, `partials/nav/*`, vues par rôle, `pdf/` |
| `resources/css/app.css` | Charte et composants Tailwind |
| `tailwind.config.js` | Palette, ombres, polices |
| `bootstrap/app.php` | Alias de middlewares, planification, gestion des exceptions |
| `tests/Feature/`, `tests/Unit/` | Tests fonctionnels et unitaires |

### Middlewares et autorisation

- `isAdmin` : réservé à `Administrateur`, redirige `/` sinon.
- `role:...` : contrôle les rôles spécialisés ; un `Administrateur` passe toujours.
- `check.role.network` (`CheckUserRoleAndNetwork`) : exige une session `current_role` valant `Employer` ou `Superviseur`, puis redirige selon le préfixe d'URL.
- `superadmin` : réservé aux emails de `SUPER_ADMIN_EMAILS` via `Utilisateur::isSuperAdmin()` (page métriques).
- Les policies doivent protéger l'accès aux enregistrements ; `current_role` ne sert qu'au routage, pas d'autorisation.

## 4. Conventions

- Domaine et libellés en **français** ; framework et code technique en anglais.
- Logique métier dans `app/Services/` ; contrôleurs fins.
- Utiliser les `FormRequest` existants pour valider les entrées.
- Conserver les noms de tables et colonnes existants (`utilisateur`, `motDePasse`, `Superviseur`, `type_superviseur`, etc.).
- `Utilisateur` utilise `SoftDeletes` ; le mutateur `motDePasse` hash automatiquement une valeur en clair.
- Argent : suivre les casts et services existants, ne pas introduire de calculs flottants ad hoc.
- UI : utiliser en priorité les classes `pharaoh-*` et les composants de `resources/css/app.css`. Les classes `3hcig-*` sont d'anciens noms remappés, à ne pas utiliser pour du nouveau code.
- Toute nouvelle fonctionnalité doit avoir un rendu Blade fonctionnel et des tests.

## 5. Règles métier à ne pas casser

- **Présence** : pointage géolocalisé obligatoire, vérification du rayon du lieu de travail, retard après 08:00, fiche de rendement obligatoire au départ, absences automatiques à 18:45.
- **Caisse** : commandes (payé, partiel, impayé), services fournis, retraits avec motif, encaissement du solde restant. Toute la logique passe par `AbstractCaisseController` et les services de caisse/facturation.
- **Évaluations** : note /20 ; vert ≥ 14, orange 10–13, rouge < 10 ; une évaluation manuelle prime sur l'automatique.
- **Stock** : t-shirts et papier par imprimante, alertes de seuil bas.
- **Notifications** : notifications internes + diffusion temps réel, repli polling ; la file WhatsApp utilise la connexion `database`.
- **Métriques système** : accessibles uniquement au super administrateur.

## 6. Sécurité

- Ne jamais lire, afficher ou committer `.env`, les clés Resend/WhatsApp ou les secrets.
- Valider toute entrée ; échapper les sorties Blade ; ne pas ajouter d'exception CSRF.
- Vérifier l'autorisation au niveau de l'enregistrement (policy), pas seulement de la route.
- Les exports PDF/CSV et l'API mobile doivent vérifier l'appartenance et le rôle.
- Les jobs de file doivent être idempotents et ne pas journaliser de données sensibles.

## 7. Agents et skills du projet

Agents (`.qoder/agents/`, versionnés) :

| Agent | Usage |
|---|---|
| `laravel-reviewer` | Revue backend Laravel après modification |
| `phpunit-runner` | Exécution et diagnostic des tests PHPUnit |
| `security-auditor` | Audit de sécurité en lecture seule |
| `ui-reviewer` | Revue Blade/Tailwind/Alpine et accessibilité |

Skills (`.qoder/skills/`, versionnés) : `pharaon-conventions`, `pharaon-testing`, `pharaon-ui`.

Skills de design tiers présents dans `.agents/skills/` (non versionnés) : `emil-design-eng`, `apple-design`, `improve-animations`, `review-animations`, `animation-vocabulary`, `karpathy-guidelines`.

Après ajout ou modification d'un agent/skill : `/agents reload` et `/skills reload`, puis invoquer un agent avec `@nom-agent`.

## 8. Definition of Done

- `php artisan test` passe (ou les tests ciblés + justification).
- `vendor/bin/pint --dirty` est propre.
- `npm run build` passe si une vue, du CSS ou du JS a changé.
- Le comportement a été vérifié manuellement dans le navigateur pour une modification UI.
- Aucun secret ni fichier `.env` modifié/committé.
