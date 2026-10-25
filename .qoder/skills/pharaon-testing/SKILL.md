---
name: pharaon-testing
description: Exécution et écriture des tests PHPUnit de GestionPresence. Use when running the suite, investigating a failing test, adding regression coverage for presence, caisse, evaluations, exports, roles or PIN recovery, or preparing a delivery where PHPUnit must pass.
---

# Tests PHPUnit — Le Pharaon

## Vue d'ensemble

La suite couvre les flux métier critiques avec PHPUnit 11.5. La base de test n'est pas séparée : `phpunit.xml` laisse `DB_CONNECTION` commenté, donc les tests utilisent la base MySQL de `.env`. La plupart des tests utilisent `DatabaseTransactions`, mais pas tous. Toujours travailler sur une base locale migrée et seedée, jamais sur une base de production.

## Commandes

```bash
# Suite complète
php artisan test

# Test ou classe ciblée
php artisan test --filter=CaisseFlow
php artisan test --filter=PharaonFeatures

# S'arrêter au premier échec
php artisan test --stop-on-failure

# Vérifier la base avant de lancer
php artisan migrate:status
```

Tests disponibles par domaine :

| Fichier | Couverture |
|---|---|
| `tests/Feature/PharaonFeaturesTest.php` | Présence, retards, fiches de rendement, évaluations, exports, notifications, Resend |
| `tests/Feature/CaisseFlowTest.php` | Directrice et secrétaire : commandes, services, retraits |
| `tests/Feature/SuperviseurModulesTest.php` | Modules superviseur |
| `tests/Feature/E2ESuperviseurFlowTest.php` | Parcours superviseur de bout en bout |
| `tests/Feature/NavigationHarmonieTest.php` | Menus par rôle, pages caisse, accès aux métriques |
| `tests/Feature/PinRecoveryTest.php` | Récupération de session par PIN (sans transaction) |
| `tests/Feature/FactureAcompteTest.php` | Factures et acomptes |
| `tests/Feature/ServiceFacturationTest.php` | Facturation des services |
| `tests/Feature/AdminFinanceTest.php` | Finance admin |
| `tests/Feature/AdminReportExportTest.php` | Exports de rapports |
| `tests/Feature/ApiTest.php` | API Sanctum |
| `tests/Unit/CaisseServiceTest.php` | Service caisse |
| `tests/Unit/CommandeSuperviseurServiceTest.php` | Service commandes superviseur |
| `tests/Unit/LoginAttemptServiceTest.php` | Lockout de connexion |
| `tests/Unit/RoleMiddlewareTest.php` | Middleware de rôle |
| `tests/Unit/UtilisateurPolicyTest.php` | Policy utilisateur |

## Prérequis

1. MySQL local démarré.
2. `.env` pointe vers la base locale `gestionpresence`.
3. Migrations et seeders appliqués : `php artisan migrate --seed`.
4. Cache de configuration vidé si nécessaire : `php artisan config:clear`.

Ne jamais modifier `.env` pour les tests, ni pointer vers une base distante. Ne pas remplacer la connexion MySQL par SQLite : le schéma et certaines requêtes sont spécifiques à MySQL.

## Écrire un test

Structure type :

```php
<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MonFluxTest extends TestCase
{
    use DatabaseTransactions;

    public function test_un_superviseur_peut_acceder_a_son_dashboard(): void
    {
        $superviseur = Utilisateur::create([
            'nom' => 'Superviseur Test',
            'email' => 'sup.' . uniqid() . '@example.com',
            'motDePasse' => 'password',
            'role' => 'Superviseur',
        ]);

        $this->actingAs($superviseur);
        session(['current_role' => 'Superviseur']);

        $this->get('/superviseur/supdashboard')->assertOk();
    }
}
```

Règles :

- Étendre `Tests\TestCase`.
- Utiliser `DatabaseTransactions` dès qu'un test écrit en base.
- Toujours définir `current_role` pour les routes passant par `CheckUserRoleAndNetwork`.
- Les routes admin ne requièrent pas `current_role`.
- Pour un poste spécialisé, créer la ligne `Superviseur` avec le bon `type_superviseur` :

```php
\App\Models\Superviseur::create([
    'id' => $user->id,
    'equipe' => 'Administration',
    'type_superviseur' => 'directrice',
]);
```

- Pour les tests sans `DatabaseTransactions` (comme `PinRecoveryTest`), utiliser des emails uniques et nettoyer les enregistrements.
- Utiliser `Notification::fake()` et `Queue::fake()` pour isoler les effets de bord.
- Nommer les tests en français avec le préfixe `test_`.
- Vérifier les écritures avec `assertDatabaseHas` / `assertDatabaseMissing` sur les vrais noms de tables.
- Tester au minimum un cas autorisé et un cas refusé (403 ou redirection).

Bonnes pratiques de données :

- Ne pas dépendre d'un ID fixe ; récupérer les utilisateurs seedés avec `firstOrFail()` uniquement si le scénario l'exige.
- Éviter les collisions d'email entre tests non transactionnels.
- Ne pas désactiver les middlewares ni les policies dans le test pour le faire passer.
- Pour les dates, utiliser `Carbon::setTestNow()` et le rétablir dans `tearDown()`.

## Diagnostic des échecs

| Symptôme | Cause probable | Action |
|---|---|---|
| 302 vers `/` ou `auth.role_selection` | `current_role` absent ou rôle non autorisé | Définir la session, vérifier `Employer`/`Superviseur` |
| 403 | Policy, type de superviseur ou middleware de rôle | Vérifier la policy et les données liées |
| 419 | CSRF actif alors que le test devrait le désactiver | Vérifier `APP_ENV=testing` et le middleware CSRF |
| 429 | Rate limiter de login/API | Vider le limiter avec `RateLimiter::clear(...)` ou espacer les appels |
| 500 SQL | Migration manquante, base non seedée, SQL MySQL | `php artisan migrate --seed`, vérifier le schéma |
| `ModelNotFoundException` sur `firstOrFail` | Base vide ou seeders absents | Lancer les seeders |
| Notification absente | `Notification::fake()` ou destinataire incorrect | Vérifier l'assertion et le rôle notifié |
| Test instable en parallèle | Données partagées hors transaction | Emails uniques, nettoyage, éviter les IDs fixes |

## Garde-fous

- Ne jamais exécuter `migrate:fresh`, `db:wipe`, `truncate`, `DROP`, ni une commande destructive.
- Ne pas lancer la suite contre une base de production ou distante.
- Ne pas modifier `.env` ou la configuration de base pour les tests.
- Ne pas affaiblir une assertion ni marquer un test `skipped` pour masquer un échec.
- Ne pas commiter ni pousser après les tests sans demande explicite.

## Definition of Done

- Les tests ciblés passent.
- La suite complète passe, ou les échecs restants sont expliqués et liés à un problème externe.
- Aucune donnée de production n'a été touchée.
- Les nouveaux comportements ont une couverture Feature ou Unit.
