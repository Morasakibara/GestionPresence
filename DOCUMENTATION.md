# 📋 3HCIG COOP-CA — Application de Gestion des Présences

## 1. Présentation du projet

**3HCIG COOP-CA** est une application web de **gestion des présences en entreprise** permettant de :

- Pointer son **arrivée** et son **départ** depuis le navigateur, avec **validation géolocalisée** (l'employé doit être physiquement sur son lieu de travail).
- Détecter automatiquement les **retards** (après 8h00) et les **absences**.
- Notifier par **email + notification interne** le superviseur direct et l'administrateur principal.
- Suivre la présence des équipes (superviseurs), générer des **rapports PDF** (admin et superviseur).
- Gérer les **lieux de travail** (géofencing : centre GPS + rayon en mètres).

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
`en attente` (arrivée pointée) → `présent` (départ pointé) | `Absent` (auto-absence ou arrivée sans départ).

---

## 5. Fonctionnalités par rôle

### 👑 Administrateur (`/admin/*`)
| Fonctionnalité | Détail |
|---|---|
| **Dashboard** | Statistiques globales : employés, superviseurs, présences/absences/retards du jour et du mois. |
| **Ajouter employé** | Formulaire (nom, email, mot de passe, rôle Employer/Superviseur + équipe pour un superviseur). |
| **Liste des employés** | Recherche + filtres par rôle, suppression depuis la liste. |
| **Générer un rapport** | Rapport de présence sur une période → **PDF** téléchargeable + enregistré. |
| **Lieux de travail** | CRUD complet des zones de géofencing (nom, GPS, rayon, actif/inactif). |
| **Profil** | Modification du profil et de l'avatar (modal). |
| **Présences suspectes** | Page dédiée listant les pointages marqués suspects (motif, distance, vitesse) avec filtres recherche + période. **Workflow de traitement** : statut (`nouveau`/`examiné`/`justifié`/`rejeté`) + commentaire + historique de traitement (`presence_traitements`). **Export CSV/PDF** des résultats filtrés. **Badge sidebar** : compteur de présences suspectes non traitées. |
| **Statistiques des suspicions** | Page `/admin/stats-suspects` : tableau de bord des **suspicions et blocages** — total suspectes + répartition par statut, par motif (vitesse / GPS / autres), contestations (total, en attente, accordées, refusées), **employés actuellement bloqués**, **détail par employé** (total + statuts) et évolution mensuelle sur 6 mois. **Export PDF** du tableau de bord. Lien dans la sidebar. |
| **Déblocage manuel** | Depuis la timeline d'un employé (admin), bouton **« Débloquer l'employé »** si celui-ci est bloqué : toutes ses présences suspectes non justifiées passent en `justifié` (commentaire de déblocage), levée immédiate du blocage de pointage. Chaque présence est journalisée dans l'historique (`presence_traitements`). |

### 🧑‍💼 Superviseur (`/superviseur/*`)
| Fonctionnalité | Détail |
|---|---|
| **Dashboard d'équipe** | Statistiques de son équipe (membres, présents, absents, retards du jour). |
| **Suivre les présences** | Liste des membres de son équipe + détail par utilisateur (frise graphique Chart.js). |
| **Ajouter / retirer un membre** | Gestion de l'équipe (rattache `Sup_id` + `equipe` de façon cohérente). |
| **Rapport d'équipe** | Rapport mensuel de son équipe + **export PDF**. |
| **Changer de rôle** | Bascule Employé ↔ Superviseur. |
| **Présences suspectes** | Vue en lecture seule des pointages suspects des membres de son équipe (motif, distance, vitesse) + filtres. Le traitement reste réservé à l'admin. |
| **Statistiques des suspicions** | Page `/superviseur/stats-suspects` : mêmes statistiques que l'admin mais **limitées à son équipe** — total suspectes par statut et motif, contestations, membres bloqués, **détail par membre** et évolution mensuelle. **Export PDF**. Lien dans la sidebar. |
| **Notifications** | Reçoit les retards/absences des membres de son équipe. |

### 🧑‍🔧 Employé (`/user/*`)
| Fonctionnalité | Détail |
|---|---|
| **Dashboard** | Présences du mois, dernière arrivée / dernier départ. |
| **Pointer** | Marquage d'**arrivée** (7h–10h) et de **départ** (17h–18h30), validé par géolocalisation. |
| **Bilan de présence** | Historique du mois + total présences/absences. **Contestation** : l'employé peut contester une de ses présences marquées suspecte (commentaire + date), ce qui notifie l'admin. |
| **Profil** | Modification du profil et de l'avatar. |

### ⚙️ Toutes les personnes connectées
- **Notifications** : centre de notifications (marquage lu / tout marquer lu).
- **Pointage partagé** : les routes `/presence`, `/mark-arrival`, `/mark-departure` sont communes aux Employés et aux Superviseurs en mode Employé.

---

## 6. Flux métier détaillés

### 6.1 Pointage avec géofencing **anti-triche**
1. L'employé ouvre la page « Présence », le navigateur récupère sa position GPS (précision `accuracy` incluse).
2. `POST /check-location` vérifie (formule de **Haversine**) que la position est dans le rayon d'un lieu actif → réponse JSON `valid: true/false` + **signature HMAC** si valide.
3. Arrivée (`POST /mark-arrival`) : le serveur vérifie la signature, l'écart d'horloge client/serveur, puis créé une `presence` avec statut `en attente`, heure d'arrivée, coordonnées et métadonnées de vérification.
   - **Fenêtre** : 7h00 → 10h00, jours ouvrés uniquement (week-end bloqué).
   - **Retard** : arrivée après 8h00 → notifications automatiques.
   - **Anti double-pointage** : une seule arrivée par jour.
4. Départ (`POST /mark-departure`) : vérifie la signature puis met à jour la présence avec statut `présent`, coordonnées, distance/vitesse de déplacement.
   - **Fenêtre** : 17h00 → 18h30.

### 6.1bis Protection anti-triche de la géolocalisation
Un service dédié (`App\Services\GeolocationVerificationService`) protège le pointage :

| Protection | Mécanisme |
|---|---|
| **Signature à usage unique** | `check-location` signe la position validée (HMAC-SHA256 + nonce aléatoire + horodatage). `mark-arrival`/`mark-departure` refusent toute requête sans signature valide, expirée, rejouée ou altérée. |
| **Vérification d'horloge** | Le navigateur envoie son horodatage ; un écart > 5 min avec le serveur rejette la requête (anti-falsification d'horloge). |
| **Vérification croisée de vitesse** | Distance Haversine arrivée↔départ ÷ temps écoulé. Une vitesse > 40 km/h (ex. pointage à Paris le matin et Lyon le soir) marque la présence **suspecte**. |
| **Précision GPS** | L'`accuracy` du navigateur est contrôlée (> 300 m → suspect). |
| **Traçabilité** | IP, user-agent, horodatages, précision et coordonnées sont enregistrés sur chaque présence. |
| **Alerte automatique** | Dès qu'une présence est marquée suspecte (arrivée ou départ), l'administrateur principal reçoit une **notification email + interne** (`SuspectPresenceNotification`) avec un lien direct vers la page d'examen. |
| **Visibilité** | Les présences suspectes sont marquées dans la liste des employés et les rapports (HTML + PDF) ; une page admin `/admin/suspect-presences` les liste avec filtres. Le superviseur voit celles de son équipe (`/superviseur/suspect-presences`). |

Toute présence douteuse est marquée `suspect = true` avec un motif (colonne `motif_suspicion`). Les seuils sont configurables dans `config/geolocation.php` et via variables d'environnement (`GEOLOC_*`).

### 6.1ter Workflow de traitement des présences suspectes
1. **Détection** : l'anti-triche marque une présence `suspect = true` + `motif_suspicion` ; l'admin est notifié automatiquement.
2. **Examen** : l'admin consulte `/admin/suspect-presences` (motif, distance, vitesse, employé, période) avec filtres nom + période + **statut de traitement** (nouveau/examiné/justifié/rejeté) — le superviseur a le même filtre sur sa vue d'équipe.
3. **Traitement** : l'admin choisit un statut — `nouveau`, `examiné`, `justifié` ou `rejeté` — et ajoute un commentaire. La présence enregistre `statut_traitement`, `commentaire_traitement`, `traite_par` et `traite_le`.
4. **Historique** : chaque changement de statut est journalisé dans la table `presence_traitements` (`statut_avant` → `statut_apres`, commentaire, auteur, date) — traçabilité complète.
5. **Visibilité superviseur** : le superviseur voit les pointages suspects de son équipe en lecture seule (pas de droit de traitement).
6. **Notifications au traitement** : quand l'admin traite une présence, le **superviseur de l'équipe** ET **l'employé concerné** reçoivent un **email + notification interne** (`PresenceTraiteeNotification`, adaptée au rôle du destinataire) avec le nouveau statut, le commentaire de l'admin et un lien pertinent.
7. **Badge sidebar** : la sidebar admin affiche un badge rouge avec le nombre de présences suspectes **non traitées** (statut `nouveau`) — il disparaît dès qu'elles sont traitées.
8. **Export d'audit** : la page admin permet d'exporter la liste filtrée en **CSV** (BOM UTF-8, séparateur `;`, compatible Excel FR) ou en **PDF** stylé (même gabarit que les rapports).
9. **Contestation employé** : l'employé voit ses présences suspectes dans son bilan (`/user/presence-report`) et peut les **contester** (commentaire requis). La présence enregistre `commentaire_contestation` + `conteste_le`, l'admin est notifié (`PresenceContesteeNotification`) et la contestation s'affiche avec badge « Contesté » sur les pages admin, superviseur et l'export PDF.
10. **Réponse de l'admin à la contestation** : boutons **Accorder / Refuser** sur la page suspectes (uniquement quand une contestation est en attente). Accord → statut `justifié` (suspicion levée) ; refus → statut `rejeté` (avec motif du refus). Chaque réponse journalise l'historique (`presence_traitements`) et **notifie l'employé** (`ContestationReponseNotification` — email + interne).
11. **Blocage des récidivistes** : si un employé cumule ≥ `GEOLOC_BLOCAGE_SUSPECTS_MAX` (défaut 3) présences suspectes **non justifiées** sur les `GEOLOC_BLOCAGE_PERIODE_JOURS` derniers jours (défaut 30), son **arrivée est refusée** avec un message invitant à contacter l'admin — jusqu'à examen des présences. À chaque blocage, le **superviseur de l'équipe reçoit un email + notification interne** (`MembresBloquesNotification`) listant tous les membres bloqués de son équipe avec leur nombre de suspectes non justifiées.
12. **Timeline de présence** : l'**historique complet** d'une présence (arrivée, départ, suspicion, contestation, réponse de l'admin, changements de statut) est consultable en chronologie : par l'**employé** depuis son bilan (`/user/presence-history/{id}`), par l'**admin** et le **superviseur** depuis la page suspectes (lien « Voir l'historique »). L'accès est contrôlé par rôle (admin : tout ; superviseur : son équipe ; employé : ses propres présences).
13. **Export CSV enrichi** : l'export CSV des suspectes inclut désormais les colonnes **Contestation** (date + commentaire) et **Réponse admin** (accordé/refusé + commentaire), avec échappement CSV correct des `;` et `"`.
14. **Export PDF de la timeline** : chaque timeline (employé, admin ou superviseur) peut être **exportée en PDF** (bouton « Export PDF ») pour l'archivage — récapitulatif complet, suspicion, contestation, réponse et historique des statuts.
15. **Statistiques globales (admin)** : le tableau de bord `/admin/stats-suspects` agrège les suspicions (total, statuts, motifs, contestations, employés bloqués, évolution mensuelle sur 6 mois) pour piloter l'activité de contrôle.
16. **Déblocage manuel (admin)** : quand un employé est bloqué au pointage, l'admin peut le **débloquer** depuis sa timeline — les suspectes non justifiées passent en `justifié` avec traçabilité (`presence_traitements`) et la levée du blocage est immédiate.
17. **Statistiques superviseur** : `/superviseur/stats-suspects` fournit au superviseur le même tableau de bord que l'admin mais **restreint à son équipe** (les suspectes des autres équipes n'y figurent jamais).
18. **Détail par employé** : les deux tableaux de bord (admin + superviseur) affichent un tableau **par employé/membre** — total de suspectes et répartition par statut de traitement (en attente / examinées / justifiées / rejetées).
19. **Export PDF des statistiques** : boutons « Exporter PDF » sur les deux tableaux de bord (`/admin/stats-suspects/pdf` et `/superviseur/stats-suspects/pdf`) — même gabarit que les rapports (en-tête 3HCIG, cartes récapitulatives, tableaux par statut/motif/contestation/détail par employé).

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
| `presence:rappel-suspectes` | **Tous les lundis à 9h00** | Notifie l'admin principal des présences suspectes restées **non traitées** (statut `nouveau`) depuis plus de `GEOLOC_RAPPEL_SUSPECTES_JOURS` jours (défaut 7). Option `--days=N` pour forcer le seuil. |
| `presence:rappel-blocages` | **Tous les jours à 8h30** | Tant que des membres d'une équipe restent **bloqués au pointage** (seuil de suspectes non justifiées atteint), le superviseur de l'équipe reçoit un rappel quotidien (`MembresBloquesNotification`) avec la liste des membres bloqués. |
| `presence:bilan-hebdo` | **Tous les lundis à 9h30** | Envoie à l'admin un **bilan email + interne** (`BilanHebdoNotification`) des présences suspectes de la semaine précédente : total, en attente, examinées, justifiées, rejetées, **avec un PDF joint** (détail par employé/date/statut/motif). |

La **levée du blocage** se fait par traitement des présences (statut `justifié`) ou par **déblocage manuel** de l'admin (voir §6.1ter).

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
| **Géolocalisation** | Signature HMAC serveur à usage unique + vérification d'horloge + vitesse + précision | Reste contournable par un utilisateur déterminé (devtools) — une app native reste la solution ultime si exigence forte. Les présences suspectes sont marquées (`suspect`, `motif_suspicion`). |
| **`APP_DEBUG`** | `true` en dev | Passer à `false` en production. |
