# 📋 Le Pharaon — Application de Gestion des Présences

## 1. Présentation du projet

**Le Pharaon** est une application web de **gestion des présences en entreprise** permettant de :

- Pointer son **arrivée** et son **départ** depuis le navigateur à **toute heure** (y compris le week-end), avec **validation géolocalisée** (l'employé doit être physiquement sur son lieu de travail).
- Remplir une **fiche de rendement obligatoire** au départ (tâches effectuées dans la journée).
- Détecter automatiquement les **retards** (après 8h00) et les **absences** (système d'alertes maintenu).
- Notifier par **email + notification interne** le superviseur direct et l'administrateur principal.
- **Évaluer la discipline et le rendement** de chaque employé avec une notation /20 et des couleurs 🟢 vert / 🟠 orange / 🔴 rouge, intégrée aux rapports.
- Suivre la présence des équipes (superviseurs), générer des **rapports PDF détaillés** (admin/fondateurs et superviseurs/directeurs) incluant les **réalisations** de chaque employé.
- Gérer les **lieux de travail** (géofencing : centre GPS + rayon en mètres).

L'application repose sur une architecture **multi-rôles** : Administrateur (Fondateur), Superviseur (Directeur / Directeur adjoint), Employé.

L'application repose sur une architecture **multi-rôles** : Administrateur, Superviseur, Employé.

---

## 2. Stack technique

| Composant | Technologie | Version |
|---|---|---|
| **Backend** | PHP (Laravel) | PHP ≥ 8.2 — **Laravel 12.66** |
| **Base de données** | MySQL | 5.7+ / 8.x |
| **Frontend** | Blade + Tailwind CSS + Alpine.js | Tailwind 3.4, Alpine 3.14 |
| **Build frontend** | Vite | 6.x |
| **Graphiques** | Chart.js | 4.4 |
| **Génération PDF** | barryvdh/laravel-dompdf (dompdf 3.x) | ^3.0 |
| **API tokens** | Laravel Sanctum | ^4.0 |
| **Auth** | Sessions Laravel (login/mot de passe, bcrypt) | — |
| **Emails** | SMTP (Mailpit en dev) | — |
| **Tests** | PHPUnit 11.5 | ^11.5 |
| **Outils dev** | Laravel Tinker, Breeze, Sail, Pint, Ignition | — |

### Points forts techniques
- **Laravel 12** : framework moderne, compatible PHP 8.2–8.4, aucune vulnérabilité connue (`composer audit` propre).
- **dompdf 3.x** : mise à jour de sécurité majeure (les versions 2.x présentaient des failles documentées).
- **Blade + Tailwind** : rendu côté serveur rapide, design responsive, aucune dépendance JS lourde au chargement (Alpine pour l'interactivité).

---

## 3. Architecture applicative

### Structure (conventions Laravel 12)
```
app/
├── Console/Commands/          # Commandes artisan (auto-absences, ...)
├── Exceptions/Handler.php
├── Exports/                   # Exports (préparés pour Excel/CSV)
├── Http/
│   ├── Controllers/           # Contrôleurs (Auth, Admin, Superviseur, Utilisateur, Pre, Geo...)
│   └── Middleware/            # IsAdmin, CheckUserRoleAndNetwork, RegistrationAccess...
├── Models/                    # Eloquent (Utilisateur, Employer, Superviseur, Presence...)
├── Notifications/             # AbsenceNotification, RetardNotification
└── Providers/
database/
├── migrations/                # Schéma versionné (22 migrations)
├── factories/                 # Factories de test
└── seeders/                   # Données de démonstration réalistes
resources/views/               # Vues Blade (admin, superviseur, user, auth, layouts)
routes/web.php                 # Routes web
bootstrap/app.php              # Configuration Laravel 12 (middleware, schedule)
```

### Middlewares personnalisés (enregistrés dans `bootstrap/app.php`)
| Middleware | Rôle |
|---|---|
| `isAdmin` | Restreint l'accès aux routes admin aux utilisateurs `Administrateur`. |
| `check.role.network` | Vérifie que l'utilisateur connecté est `Employer` ou `Superviseur`, contrôle le rôle courant en session (`current_role`) et redirige vers le bon espace (prêt pour une future restriction Wi-Fi d'entreprise — code en commentaire). |
| `registration.access` | Protège le formulaire d'inscription admin par un **code d'accès** (voir §8). |

### Authentification & rôles
- Table `utilisateur` avec colonne `role` (`Employer`, `Superviseur`, `Administrateur`).
- Les comptes sont créés via le formulaire admin ou l'auto-inscription protégée.
- Les **superviseurs ont un double rôle** : à la connexion, une modale leur demande de choisir entre *Employé* et *Superviseur* (`current_role` en session). Un lien « Changer de rôle » permet de basculer à tout moment.

---

## 4. Modèle de données

```
utilisateur (id, nom, email, motDePasse, role, avatar, remember_token, timestamps)
   │
   ├── administrateur (id = utilisateur.id FK, poste, timestamps)
   ├── superviseur (id = utilisateur.id FK, equipe, timestamps)
   │     └── employer (id = utilisateur.id FK, Sup_id → superviseur.id, poste, equipe, timestamps)
   │           └── presence (id, employerID → employer.id, Sup_id, heureArrivee, heureDepart,
   │                          date, status, coordonnées GPS arrivée/départ, workplace_location_id)
   │                 └── marquer (id = presence.id FK, Empl_id → employer.id)
   ├── rapport (id, Adm_id → administrateur.id, Sup_id → superviseur.id, periode, contenu)
   └── workplace_locations (id, nom, latitude, longitude, rayon (m), actif)

notifications (uuid, type, notifiable morph, data, read_at)   -- notifications internes Laravel
```

### Tables principales
| Table | Rôle |
|---|---|
| `utilisateur` | Comptes de tous les rôles (une seule table, champ `role`). |
| `administrateur` / `superviseur` / `employer` | Profils spécifiques à chaque rôle (FK `id` = `utilisateur.id`). |
| `presence` | Pointages : arrivée, départ, statut (`en attente` → `présent` / `Absent`), géolocalisation. |
| `workplace_locations` | Zones de géofencing (centre GPS + rayon). |
| `rapport` | Historique des rapports PDF générés. |
| `marquer` | Table de jonction présence ↔ employer. |
| `notifications` | Notifications internes (Laravel Notifications). |

### Statuts de présence
`en attente` (arrivée pointée) → `présent` (départ pointé avec fiche de rendement) | `Absent` (auto-absence ou arrivée sans départ).

### Fiche de rendement (colonne `presence.rendement`)
Remplie obligatoirement au départ : description des tâches effectuées dans la journée. Affichée dans les **rapports** (admin + superviseur, HTML et PDF) pour le suivi du rendement hebdomadaire / mensuel.

### Évaluation de discipline et de notation (table `evaluations`)
- Note **/20** calculée automatiquement (`App\Services\EvaluationService`) à partir des retards, absences, présences suspectes, présences complètes et fiches de rendement remplies.
- **Couleur** : 🟢 Vert ≥ 14/20 · 🟠 Orange 10-13/20 · 🔴 Rouge < 10/20.
- **Évaluation manuelle** : l'admin (fondateur) et le superviseur (directeur / directeur adjoint) peuvent enregistrer une note + couleur + commentaire par employé et par mois (elle prime sur l'auto-calcul).
- **Intégrée aux rapports** : badge coloré par employé dans les rapports HTML et PDF (admin et superviseur).

---

## 5. Fonctionnalités par rôle

### 👑 Administrateur (`/admin/*`)
| Fonctionnalité | Détail |
|---|---|
| **Dashboard** | Statistiques globales : employés, superviseurs, présences/absences/retards du jour et du mois. |
| **Ajouter employé** | Formulaire (nom, email, mot de passe, rôle Employer/Superviseur + équipe pour un superviseur). |
| **Liste des employés** | Recherche + filtres par rôle, suppression depuis la liste. |
| **Générer un rapport** | Rapport de présence et de **rendement** sur une période : total présences, **évaluation colorée /20** (🟢/🟠/🔴), **réalisations** (fiches de rendement de la période) + enregistrement d'une **évaluation manuelle** → **PDF** téléchargeable + enregistré. |
| **Lieux de travail** | CRUD complet des zones de géofencing (nom, GPS, rayon, actif/inactif). |
| **Profil** | Modification du profil et de l'avatar (modal). |

### 🧑‍💼 Superviseur (`/superviseur/*`)
| Fonctionnalité | Détail |
|---|---|
| **Dashboard d'équipe** | Statistiques de son équipe (membres, présents, absents, retards du jour). |
| **Suivre les présences** | Liste des membres de son équipe + détail par utilisateur (frise graphique Chart.js). |
| **Ajouter / retirer un membre** | Gestion de l'équipe (rattache `Sup_id` + `equipe` de façon cohérente). |
| **Rapport d'équipe** | Rapport mensuel de son équipe avec **évaluations colorées /20** et **réalisations** (fiches de rendement) + **export PDF** + évaluation manuelle des membres. |
| **Changer de rôle** | Bascule Employé ↔ Superviseur. |
| **Notifications** | Reçoit les retards/absences des membres de son équipe. |

### 🧑‍🔧 Employé (`/user/*`)
| Fonctionnalité | Détail |
|---|---|
| **Dashboard** | Présences du mois, dernière arrivée / dernier départ. |
| **Pointer** | Marquage d'**arrivée** et de **départ** à **toute heure** (y compris le week-end), validé par géolocalisation. Au départ, **fiche de rendement obligatoire** (tâches effectuées). |
| **Bilan de présence** | Historique du mois + total présences/absences. |
| **Profil** | Modification du profil et de l'avatar. |

### ⚙️ Toutes les personnes connectées
- **Notifications** : centre de notifications (marquage lu / tout marquer lu).
- **Pointage partagé** : les routes `/presence`, `/mark-arrival`, `/mark-departure` sont communes aux Employés et aux Superviseurs en mode Employé.

---

## 6. Flux métier détaillés

### 6.1 Pointage avec géofencing
1. L'employé ouvre la page « Présence », le navigateur récupère sa position GPS.
2. `POST /check-location` vérifie (formule de **Haversine**) que la position est dans le rayon d'un lieu actif → réponse JSON `valid: true/false`.
3. Arrivée (`POST /mark-arrival`) : créé une `presence` avec statut `en attente`, heure d'arrivée et coordonnées.
   - **Aucune restriction horaire** : pointage possible à toute heure, y compris le week-end (arriver à 8h, 9h, 10h, 14h…).
   - **Retard** : arrivée après 8h00 → notifications automatiques (système d'alertes maintenu).
4. Départ (`POST /mark-departure`) : exige la **fiche de rendement** (`rendement` requis), puis met à jour la présence avec statut `présent`.
   - **Aucune restriction horaire** pour le départ.

### 6.2 Notifications (retard & absence)
- **Retard** : notifié au superviseur direct + à l'administrateur principal, en **email** et **notification interne**.
- **Absence** : traitée par la commande planifiée (voir §7).
- Chaque notification contient : nom de l'employé, date, heure (pour les retards), préfixe `[Admin]` ou `[Équipe]`.
- Le **centre de notifications** filtre selon le rôle : l'admin voit tout, le superviseur uniquement son équipe, l'employé ses propres notifications.

### 6.3 Rapports PDF
- **Admin** : période libre (date de début → fin), compte les statuts `présent` par employé, génère un PDF signé (dompdf), l'enregistre dans `storage/app/public/rapports/` et l'archivie dans la table `rapport`.
- **Superviseur** : rapport mensuel de son équipe, mêmes mécanismes (PDF + archivage).

---

## 7. Tâches planifiées (schedule)

| Commande | Cadence | Effet |
|---|---|---|
| `presence:auto-absences` | **Tous les jours à 18h45** | ① Passe en `Absent` les présences « arrivée sans départ » ; ② crée des absences pour les employés sans aucune présence du jour ; notifie le superviseur + l'admin principal pour chaque cas. |

Configuré dans `bootstrap/app.php` (`withSchedule`). En production, un cron `* * * * * php artisan schedule:run` est requis.

---

## 8. Sécurité & accès

- **Mots de passe** : hachés **bcrypt** (mutator `setMotDePasseAttribute` sur le modèle `Utilisateur`).
- **Formulaire d'inscription admin protégé** : code d'accès `3hcig2023` (configurable dans `TestController.php`), valable **30 minutes** en session. Saisi sur la page d'accueil.
- **CSRF** : protection Laravel standard sur tous les formulaires (`@csrf`, tokens de session).
- **Validation** : `Validator` Laravel sur tous les formulaires (email unique, longueurs, fichiers image ≤ 2 Mo, etc.).
- **Accès par rôle** : middleware `isAdmin` (admin), `check.role.network` (employé/superviseur avec rôle courant).
- **Audit de sécurité** : `composer audit` → **aucune vulnérabilité** (dompdf 3.x, Laravel 12.66).
- Note : le middleware `check.role.network` contient un bloc commenté pour restreindre l'accès au **réseau Wi-Fi de l'entreprise** (à activer selon le besoin client).

---

## 9. Comptes de démonstration (seeders)

Mot de passe commun : **`password`**

| Rôle | Email |
|---|---|
| Administrateur (principal) | `admin@3hcig.com` |
| Administrateur (secondaire) | `admin2@3hcig.com` |
| Superviseur — Équipe Alpha | `superviseur.alpha@3hcig.com` |
| Superviseur — Équipe Beta | `superviseur.beta@3hcig.com` |
| Superviseur — Équipe Gamma | `superviseur.gamma@3hcig.com` |
| Employés (15) | `employe.1@3hcig.com` → `employe.15@3hcig.com` |

Données de démo : 2 admins, 3 superviseurs, 15 employés (3 équipes de 5), ~180 présences sur 12 jours ouvrés, 3 lieux de travail (Paris actif, Lyon actif, Marseille inactif).

---

## 10. Installation & démarrage

### Prérequis
- PHP ≥ 8.2 avec extensions MySQL
- Composer 2
- MySQL (base nommée `gestionpresence` par défaut — voir `.env`)
- Node.js + npm (pour Vite)

### Installation
```bash
# 1. Dépendances PHP
composer install

# 2. Configuration
cp .env.example .env
php artisan key:generate
#   → éditer .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD, MAIL_*)

# 3. Base de données
php artisan migrate --seed

# 4. Stockage public (avatars + rapports)
php artisan storage:link

# 5. Assets frontend (optionnel en dev)
npm install
npm run dev        # ou : npm run build
```

### Démarrage (développement)
Une commande lance tout (serveur Laravel + Mailpit pour les emails) :
```bash
bash start-dev.sh
#   Application : http://127.0.0.1:8000
#   Mailpit      : http://127.0.0.1:8025   (inbox des emails envoyés)
```
Ou manuellement : `php artisan serve` (+ `tools/mailpit/mailpit.exe` pour les emails).

### Vérifications de base
```bash
php artisan migrate:status     # toutes les migrations "Ran"
php artisan test               # tests PHPUnit
php artisan route:cache        # cache des routes (doit réussir)
composer audit --locked        # sécurité des dépendances
```

---

## 11. Tests

Suite PHPUnit (`tests/`) — tests de base Laravel (réponse 200, assertion simple).
Pour étendre la couverture : tests Feature pour les flux de pointage, géolocalisation et notifications.

---

## 12. Limites & recommandations (pour la mise en production)

| Sujet | État actuel | Recommandation |
|---|---|---|
| **Queue** | `QUEUE_CONNECTION=sync` (envoi immédiat) | Passer à une queue (Redis / database) si volume important. |
| **Emails** | SMTP configurable, Mailpit en dev | Configurer un vrai SMTP (transactionnel) en production. |
| **Restriction réseau** | Code prêt mais commenté | Activer si l'usage est limité au Wi-Fi de l'entreprise. |
| **Notifications** | Limitée au superviseur direct + admin principal (optimisation faite) | Vérifier le volume si beaucoup d'équipes. |
| **Backups** | Non configurés | Planifier des sauvegardes MySQL + `storage/app/public/rapports`. |
| **HTTPS** | Non forcé | Activer `APP_URL` en HTTPS + HSTS en production. |
| **Géolocalisation** | Basée sur la position du navigateur | Le client peut tricher (ex. devtools) — éventuellement app native ou signature de confiance si exigence forte. |
| **`APP_DEBUG`** | `true` en dev | Passer à `false` en production. |
