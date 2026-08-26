# 🏛️ Le Pharaon — Système de Gestion de Présence

Application web de gestion de présence pour entreprise, développée avec **Laravel 11** et **Tailwind CSS**. Géolocalisation, évaluations, caisse, rapports PDF/CSV, alertes temps réel.

---

## 📋 Table des matières

- [Fonctionnalités](#fonctionnalités)
- [Stack technique](#stack-technique)
- [Architecture](#architecture)
- [Installation](#installation)
- [Comptes de test](#comptes-de-test)
- [Routes principales](#routes-principales)
- [Structure de la DB](#structure-de-la-db)
- [Configuration](#configuration)
- [Tests](#tests)
- [Déploiement](#déploiement)

---

## ✨ Fonctionnalités

### 👥 Gestion des utilisateurs
- Authentification email/mot de passe avec **rate limiting** (5/min) + **lockout 15 min** après 10 échecs
- 3 rôles : Administrateur, Superviseur, Employé
- 4 types de superviseur : Classique (A), Directrice, Secrétaire, Gestionnaire de stock
- Gestion des profils avec avatar upload
- **Soft Deletes** pour la rétention des données

### 📍 Présence & Géolocalisation
- Pointage arrivée/départ avec **géolocalisation obligatoire**
- Vérification de rayon par lieu de travail configuré
- Fiche de rendement obligatoire au départ
- **Détection automatique des retards** (après 8h)
- Alerte automatique des absences (scheduler)
- **WebSocket temps réel** (Soketi/Pusher) avec fallback polling

### 🏆 Évaluations
- Notation 0-20 par mois (automatique + manuelle)
- Système couleur : 🟢 Vert (≥14) / 🟠 Orange (10-13) / 🔴 Rouge (<10)
- Graphiques d'évolution sur 6 mois (Chart.js)
- Export PDF des bulletins d'évaluation

### 💰 Caisse (Directrice & Secrétaire)
- Enregistrement de commandes (avec statuts : payé/partiel/à payer)
- Enregistrement de services fournis
- Gestion des retraits avec motif
- Suivi des impayés et encaissements partiels
- Tableau de bord avec graphiques 7 jours

### 📦 Gestion de stock (Gestionnaire)
- T-shirts (couleur, taille, quantité, seuil d'alerte)
- Papier d'impression (par imprimante, en mètres)
- Alertes stock bas

### 📊 Rapports & Exports
- PDF avec charte graphique Le Pharaon
- CSV avec en-tête professionnel
- HTML pour impression directe
- Graphiques d'évolution (Chart.js)
- Filtres par date, type, statut, couleur

### 🔔 Notifications & Alertes
- Notifications push (Retard, Absence, Évaluation rouge, Congé)
- **Alertes temps réel** via WebSocket avec animations CSS premium
- Bouton on/off son personnalisé (Web Audio API)
- Notifications navigateur (nouveaux arrivés/départs)

### 🔒 Sécurité
- CSRF protection (Laravel middleware)
- Policies Laravel pour l'autorisation (6 policies)
- Audit trail complet (table `audit_logs`)
- Géolocalisation anti-triche (signature serveur)
- Rate limiting sur authentification

---

## 🛠️ Stack technique

| Composant | Technologie |
|---|---|
| **Backend** | Laravel 11 (PHP 8.1+) |
| **Frontend** | Tailwind CSS 3 + Blade |
| **JS** | Alpine.js, Chart.js, Laravel Echo |
| **DB** | MySQL 8.0 |
| **Temps réel** | Soketi (WebSocket Pusher-compatible) |
| **PDF** | DomPDF (barryvdh/laravel-dompdf) |
| **Auth** | Laravel Sanctum (API) |
| **Build** | Vite |
| **Tests** | PHPUnit 10 |

---

## 🏗️ Architecture

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AbstractCaisseController.php    ← Template Method (CRUD caisse)
│   │   ├── DirectriceController.php        ← extends Abstract (18 lignes)
│   │   ├── SecretaireController.php        ← extends Abstract (20 lignes)
│   │   ├── AdminController.php             ← Dashboard + métriques
│   │   ├── AdminEmployeeController.php     ← CRUD employés
│   │   ├── AdminReportController.php       ← Rapports + évaluations
│   │   ├── AdminFinanceController.php      ← Finance + stock
│   │   ├── SuperviseurController.php       ← Dashboard + team mgmt
│   │   ├── SuperviseurEvaluationController.php ← Évaluations + rapports
│   │   ├── PreController.php               ← Pointage présence
│   │   └── ...
│   ├── Middleware/
│   │   ├── IsAdmin.php
│   │   ├── CheckUserRoleAndNetwork.php     ← Routing par rôle
│   │   └── ...
│   └── Policies/                           ← 6 policies
├── Models/                                 ← 17 modèles
├── Services/                               ← 10 services métier
├── Events/                                 ← 3 events broadcast
├── Notifications/                          ← 7 notifications
└── Console/
    └── Commands/                           ← 3 commands artisan
```

### Patterns utilisés
- **Template Method** : `AbstractCaisseController` mutualise Directrice/Secrétaire
- **Service Layer** : `CaisseService`, `EvaluationService`, `ExportService`
- **Policy** : Autorisation centralisée par modèle
- **Event/Listener** : WebSocket temps réel via Broadcast
- **Command** : Tâches artisan (alertes, auto-absences)

---

## 🚀 Installation

### Prérequis
- PHP 8.1+
- MySQL 8.0+
- Composer
- Node.js 18+
- npm

### Étapes

```bash
# 1. Cloner le projet
git clone <repo-url>
cd GestionPresence

# 2. Installer les dépendances PHP
composer install

# 3. Installer les dépendances JS
npm install

# 4. Configurer l'environnement
cp .env.example .env
php artisan key:generate

# 5. Configurer la base de données dans .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestionpresence
DB_USERNAME=root
DB_PASSWORD=

# 6. Lancer les migrations et seeds
php artisan migrate:fresh --seed

# 7. Build du frontend
npm run build

# 8. Lancer le serveur
php artisan serve

# 9. (Optionnel) WebSocket temps réel
npm install -g @soketi/soketi
soketi start --config=soketi.config.json
```

L'app est accessible sur `http://localhost:8000`

---

## 👤 Comptes de test

| Rôle | Email | Mot de passe | Spécialité |
|---|---|---|---|
| **Admin** | admin@3hcig.com | password | Dashboard global |
| **Directrice** | directrice@lepharaon.com | password | Impressions, photocopies |
| **Secrétaire** | secretaire@lepharaon.com | password | Shooting, montage photos |
| **Gestionnaire Stock** | stock@lepharaon.com | password | T-shirts, papier |
| **Superviseur A** | superviseur_a@lepharaon.com | password | Classique (équipe) |
| **Employé** | employer1@3hcig.com | password | Pointage + rendement |

> **Note** : Pour accéder au formulaire d'ajout d'admin, il faut un token secret saisi manuellement.

---

## 🛤️ Routes principales

### Authentification
| Méthode | URI | Description |
|---|---|---|
| GET | `/login` | Formulaire de connexion |
| POST | `/login` | Traitement connexion |
| GET | `/auth/role_selection` | Sélection de rôle (superviseur) |

### Admin
| Méthode | URI | Description |
|---|---|---|
| GET | `/admin/dashboard` | Tableau de bord admin |
| GET | `/admin/add-employee` | Formulaire ajout employé |
| GET | `/admin/list-employees` | Liste des employés |
| GET | `/admin/generate-report` | Génération de rapports |
| GET | `/admin/paiements` | Stats paiements |
| GET | `/admin/caisse` | État des caisses |
| GET | `/admin/metrics` | Métriques système |
| GET | `/admin/audit` | Logs d'audit |

### Directrice
| Méthode | URI | Description |
|---|---|---|
| GET | `/directrice/dashboard` | Dashboard caisse |
| GET/POST | `/directrice/commandes` | CRUD commandes |
| GET/POST | `/directrice/services` | CRUD services |
| GET/POST | `/directrice/retraits` | CRUD retraits |
| GET | `/directrice/rapport` | Rapport hebdo |
| GET | `/directrice/rapport/export` | Export CSV |
| GET | `/directrice/rapport/export/pdf` | Export PDF |

### Secrétaire
Même structure que la directrice avec `/secretaire/` et types photo (shooting, montage, etc.)

### Superviseur
| Méthode | URI | Description |
|---|---|---|
| GET | `/superviseur/supdashboard` | Dashboard équipe |
| GET | `/superviseur/follow-presence` | Suivi présences |
| GET | `/superviseur/add-member` | Ajout membre équipe |
| GET | `/superviseur/generate-report2` | Rapport équipe |
| POST | `/superviseur/evaluations/store` | Évaluation employé |
| GET | `/superviseur/comparatif/export` | PDF comparatif notes |

### Employé
| Méthode | URI | Description |
|---|---|---|
| GET | `/user/dashboard` | Dashboard employé |
| POST | `/mark-arrival` | Marquer arrivée |
| POST | `/mark-departure` | Marquer départ + rendement |
| GET | `/user/conges` | Mes congés |
| GET | `/user/evaluations` | Mes évaluations |

---

## 🗄️ Structure de la DB

### Tables principales
- **utilisateur** — Users (id, nom, email, motDePasse, role, avatar, soft_deletes)
- **administrateur** — Profil admin (extends utilisateur)
- **superviseur** — Profil superviseur (equipe, type_superviseur)
- **employer** — Profil employé (Sup_id, equipe, poste)
- **presence** — Pointages (employerID, heureArrivee, heureDepart, date, status, rendement, géolocalisation)
- **evaluations** — Notes mensuelles (employerID, mois, note, commentaire)
- **commandes** — Commandes (superviseur_id, type, montant, montant_paye, statut_paiement)
- **services_fournis** — Services (superviseur_id, type, montant)
- **retraits** — Retraits caisse (superviseur_id, montant, motif)
- **conges** — Demandes de congé
- **stock_tshirt** — Stock T-shirts
- **stock_papier** — Stock papier impression
- **workplace_locations** — Lieux de travail géolocalisés
- **audit_logs** — Trail d'audit complet
- **notifications** — Notifications push Laravel

### Index de performance
- `presence`: `[date, status]`, `[date, employerID]`, `[employerID, date]`
- `audit_logs`: `[module, created_at]`, `[action, created_at]`, `user_id`

---

## ⚙️ Configuration

### Variables d'environnement clés

```env
# Database
DB_CONNECTION=mysql
DB_DATABASE=gestionpresence

# Broadcast (WebSocket temps réel)
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=soketi
PUSHER_APP_KEY=soketi
PUSHER_APP_SECRET=soketi
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001

# Resend (emails)
RESEND_API_KEY=re_xxxx

# App
APP_NAME="Le Pharaon"
APP_URL=http://localhost:8000
```

### Géolocalisation
Les lieux de travail sont configurables via l'admin :
- `admin/workplace-locations` — CRUD des lieux
- Chaque lieu a : nom, latitude, longitude, rayon (mètres), statut actif/inactif

---

## 🧪 Tests

```bash
# Tous les tests
php artisan test

# Par groupe
php artisan test --filter=PharaonFeatures      # 38 tests (complet flow)
php artisan test --filter=CaisseFlow            # 10 tests (module caisse)
php artisan test --filter=SuperviseurModules    # Tests superviseurs
php artisan test --filter=CaisseService         # Tests unitaires service
php artisan test --filter=LoginAttemptService   # Tests lockout login
php artisan test --filter=UtilisateurPolicy     # Tests unitaires policies
```

**Total : 207 tests / 790 assertions**

---

## 🚢 Déploiement

### Production

```bash
# 1. Mettre à jour le code
git pull origin main

# 2. Installer les dépendances
composer install --no-dev --optimize-autoloader
npm install && npm run build

# 3. Configurer .env (APP_ENV=production, DEBUG=false)

# 4. Lancer les migrations
php artisan migrate --force

# 5. Optimiser Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Lancer le WebSocket (en background)
soketi start --config=soketi.config.json &
```

### Services requis
- **PHP 8.1+** avec extensions : pdo_mysql, mbstring, openssl, tokenizer, fileinfo
- **MySQL 8.0+**
- **Node.js 18+** (pour le build Vite)
- **Redis** (optionnel, pour cache production)

---

## 📁 Fichiers clés

| Fichier | Rôle |
|---|---|
| `app/Http/Controllers/AbstractCaisseController.php` | Template CRUD caisse |
| `app/Services/CaisseService.php` | Logique métier caisse |
| `app/Services/EvaluationService.php` | Calculs évaluations |
| `app/Services/ExportService.php` | Exports CSV/HTML |
| `app/Services/RapportCaissePdfService.php` | Génération PDF |
| `app/Services/LoginAttemptService.php` | Anti brute-force |
| `app/Http/Middleware/CheckUserRoleAndNetwork.php` | Routing par rôle |
| `public/js/presence-alerts.js` | WebSocket + polling |
| `public/js/alert-sound.js` | Son Web Audio API |
| `public/css/presence-toasts.css` | Animations toasts |

---

## 📄 License

Projet propriétaire — Le Pharaon © 2026

---

*Développé avec ❤️ pour Le Pharaon*
