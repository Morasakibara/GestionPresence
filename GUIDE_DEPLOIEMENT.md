# Guide de déploiement — Gestion de présence « Le Pharaon »

Ce guide décrit, étape par étape, comment installer et mettre en production l'application **Gestion de présence, rendement et évaluation** pour l'entreprise **Le Pharaon**.

---

## 1. Architecture de l'application

| Élément | Technologie |
|---|---|
| **Backend** | Laravel 12 (PHP 8.4) |
| **Base de données** | MySQL 8 (MariaDB compatible) |
| **Frontend** | Blade + Tailwind CSS 3 + Vite + Chart.js |
| **PDF** | DomPDF (rapports & bulletins) |
| **Exports Excel** | PhpSpreadsheet (logo + charte Pharaon) |
| **Emails** | SMTP (recommandé : Resend) |
| **Scheduler** | Laravel Task Scheduler (rappels hebdomadaires) |

**Rôles** : Administrateur (fondateurs) · Superviseur (directeur / directeur adjoint) · Employé.
**Fonctionnalités clés** : pointage géolocalisé (arrivée/départ), fiche de rendement obligatoire au départ, évaluations colorées /20 (vert/orange/rouge), alertes email (retards, absences, évaluations rouges), rapports hebdomadaires/mensuels (HTML, PDF, Excel), bulletins individuels.

---

## 2. Prérequis serveur

- **PHP ≥ 8.2** (recommandé 8.3/8.4) avec les extensions : `pdo_mysql`, `mbstring`, `xml`, `gd` (images), `zip` (exports Excel), `intl` (dates françaises), `curl`, `openssl`.
- **MySQL ≥ 8.0** (ou MariaDB ≥ 10.6).
- **Composer** (gestion des dépendances PHP).
- **Node.js ≥ 18** + npm (pour compiler les assets avec Vite).
- Un **domaine** et un **certificat SSL** (HTTPS obligatoire pour la géolocalisation du navigateur).
- **Cron** actif (Linux) ou Planificateur de tâches (Windows) pour le scheduler.

> 💡 **Conseil hébergement** : un VPS Linux (Ubuntu 22.04/24.04) ou un hébergement mutualisé compatible Laravel suffisent. Pour simplifier, on peut aussi utiliser Laravel Forge ou une offre PaaS (Heroku, Railway…).

---

## 3. Installation (nouveau serveur)

### 3.1 Récupérer le code

```bash
cd /var/www
git clone <url-du-depot> gestion-presence
cd gestion-presence
```

### 3.2 Installer les dépendances

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build   # compile les assets CSS/JS
```

### 3.3 Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Puis éditer `.env` :

```env
APP_NAME="Le Pharaon — Gestion de présence"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestionpresence
DB_USERNAME=lepharaon
DB_PASSWORD=UN_MOT_DE_PASSE_FORT

MAIL_MAILER=resend
MAIL_FROM_ADDRESS="no-reply@votre-domaine.com"
MAIL_FROM_NAME="Le Pharaon — Gestion de présence"
RESEND_KEY=re_VOTRE_CLE_API
```

> ⚠️ **En production** : `APP_DEBUG=false` (sinon les erreurs et mots de passe hashés sont exposés). Le mot de passe de la base doit être unique et fort.

### 3.4 Base de données

```bash
php artisan migrate --force
php artisan db:seed --force    # crée les comptes par défaut (admin, superviseurs, employés)
```

> Les **comptes de démonstration** créés par les seeds doivent être **modifiés ou supprimés** avant la mise en service (voir § 6).

### 3.5 Stockage & liens

```bash
php artisan storage:link        # expose public/storage (avatars, logo, PDF)
chmod -R 775 storage bootstrap/cache
```

### 3.6 Droits de fichiers

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

---

## 4. Configuration du serveur web

### 4.1 Nginx (recommandé)

```nginx
server {
    listen 443 ssl http2;
    server_name votre-domaine.com;

    ssl_certificate     /etc/letsencrypt/live/votre-domaine.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/votre-domaine.com/privkey.pem;

    root /var/www/gestion-presence/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 4.2 Apache

Activer le module `rewrite`, puis dans le vhost :

```apache
<VirtualHost *:443>
    ServerName votre-domaine.com
    DocumentRoot /var/www/gestion-presence/public
    <Directory /var/www/gestion-presence/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 4.3 HTTPS (gratuit)

```bash
apt install certbot python3-certbot-nginx
certbot --nginx -d votre-domaine.com
```

> 🔒 **Le HTTPS est indispensable** : la géolocalisation du navigateur (pointage) est refusée sur les connexions non sécurisées.

---

## 5. Scheduler (tâches planifiées)

L'application envoie des **rappels automatiques** (fiches de rendement manquantes, évaluations). Ajouter au crontab de l'utilisateur serveur :

```bash
crontab -e
```

```cron
* * * * * cd /var/www/gestion-presence && php artisan schedule:run >> /dev/null 2>&1
```

Vérifier les tâches enregistrées :

```bash
php artisan schedule:list
```

---

## 6. Comptes utilisateurs initiaux (à sécuriser)

Après le seed, les comptes de démonstration existent. **Avant la mise en production réelle** :

1. Se connecter avec le compte admin principal.
2. **Changer immédiatement le mot de passe** du compte admin (via le profil).
3. **Supprimer les employés/superviseurs de démonstration** ou changer leurs mots de passe.
4. **Révoquer le code d'accès d'enregistrement** public (variable / paramètre du formulaire d'ajout d'admin) et le communiquer uniquement aux fondateurs.

---

## 7. Emails (Resend)

L'application utilise le **transport natif Laravel → Resend** (`MAIL_MAILER=resend`, `RESEND_KEY`). Avec un compte gratuit, Resend n'envoie **que vers l'adresse du propriétaire du compte** ; pour envoyer aux destinataires réels (administrateurs, superviseurs, employés), il faut **ajouter et vérifier un domaine**.

### 7.1 Ajouter le domaine (une seule fois)

1. Créer un compte sur [resend.com](https://resend.com).
2. Aller dans **Domains → Add Domain**, saisir le domaine de l'entreprise (ex. `lepharaon.com` — ou un sous-domaine dédié `mail.lepharaon.com`).
3. Resend génère des **enregistrements DNS uniques** à copier chez votre registrar/hébergeur DNS (Cloudflare, OVH, Namecheap, GoDaddy…).

### 7.2 Enregistrements DNS à créer

Les 3 enregistrements ci-dessous sont générés par Resend (les **valeurs exactes** s'affichent dans le dashboard ; le tableau donne le format) :

| Type | Hôte / Nom | Valeur | Remarque |
|------|-----------|--------|----------|
| **MX** | `send` | `feedback-smtp.us-east-1.amazonses.com` | Priorité `10` — acheminement des retours |
| **TXT** | `send` | `v=spf1 include:amazonses.com ~all` | SPF — autorise les serveurs Resend |
| **TXT** | `resend._domainkey` | `p=MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC...` (clé publique générée par Resend) | **DKIM** — signature des emails |

> ⚠️ Copier chaque valeur **exactement** (sans guillemets supplémentaires, sans espaces en trop, sans tronquer la longue clé DKIM) — c'est la cause n°1 d'échec de vérification. Attention aussi : saisir `resend._domainkey` (pas `resend._domainkey.votre-domaine.com`).

**Recommandé** — ajouter aussi un enregistrement DMARC :
| Type | Hôte / Nom | Valeur |
|------|-----------|--------|
| TXT | `_dmarc` | `v=DMARC1; p=none; rua=mailto:dmarc@votre-domaine.com` |

4. Retourner sur Resend → **Verify DNS Records**. La vérification prend de quelques minutes à 24 h (propagation DNS).
5. Une fois le domaine **vérifié**, mettre à jour `.env` :
   ```
   MAIL_FROM_ADDRESS="no-reply@lepharaon.com"
   ```
   puis `php artisan config:clear` et re-tester.

### 7.3 Clé API et envoi

1. Générer une **clé API** dans [resend.com/api-keys](https://resend.com/api-keys) (domaine restreint au domaine vérifié pour la sécurité).
2. Renseigner dans `.env` :
   ```
   MAIL_MAILER=resend
   RESEND_KEY=re_...
   MAIL_FROM_ADDRESS="no-reply@votre-domaine.com"
   MAIL_FROM_NAME="Le Pharaon — Gestion de présence"
   ```
3. Tester avec une vraie notification (marquer un retard) ou :
   ```bash
   php artisan tinker
   Mail::raw('Test', fn ($m) => $m->to('destinataire@domaine.com')->subject('Test'));
   ```

> 📬 Les emails envoyés : alertes de **retard**, alertes d'**absence**, alertes d'**évaluation rouge**, **rappels de fiches de rendement** manquantes.

---

## 8. Géolocalisation des points de présence

- L'application compare la position GPS du navigateur à celle des **lieux de travail enregistrés** (lat/long + rayon).
- **Configurer les lieux de travail** dans `Admin → Lieux de travail` (nom, latitude, longitude, rayon en mètres, actif).
- Le pointage n'est **autorisé que dans un rayon** défini autour d'un lieu actif.
- Renforcer l'anti-triche (si exigence forte) : application mobile native ou signature serveur (voir `DOCUMENTATION.md`).

---

## 9. Sauvegardes (obligatoire)

Planifier des sauvegardes automatiques **quotidiennes** :

```bash
# Sauvegarde MySQL
mysqldump -u lepharaon -p gestionpresence > /backups/gp_$(date +%F).sql

# Sauvegarde des fichiers (PDF générés, avatars)
rsync -av /var/www/gestion-presence/storage/app/public/ /backups/storage/
```

Idéalement, envoyer les sauvegardes vers un **stockage distant** (object storage S3, etc.).

---

## 10. Mise à jour de l'application

```bash
cd /var/www/gestion-presence
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan view:cache
php artisan config:cache
php artisan route:cache
```

> Toujours **sauvegarder avant** et tester sur un environnement de staging si possible.

---

## 11. Sécurité — checklist finale

| Contrôle | État attendu |
|---|---|
| `APP_DEBUG=false` | ✅ |
| HTTPS actif (certificat valide) | ✅ |
| `APP_KEY` générée et secrète | ✅ |
| Mots de passe admin changés (comptes seed supprimés) | ✅ |
| Code d'accès d'ajout d'admin révoqué/changé | ✅ |
| Emails Resend vérifiés (DKIM/SPF) | ✅ |
| Sauvegardes planifiées + testées | ✅ |
| Droits `storage` restreints (`www-data`) | ✅ |
| Geofencing configuré (lieux de travail + rayons) | ✅ |

---

## 12. Dépannage rapide

| Problème | Solution |
|---|---|
| Page blanche / erreur 500 | `php artisan config:clear && php artisan view:clear`, vérifier `storage/logs/laravel.log` |
| Pointage refusé | Vérifier que le lieu de travail est **actif** et que la position est dans le rayon |
| Emails non reçus | Vérifier les DNS (DKIM/SPF), le port SMTP 587, les logs `storage/logs/laravel.log` |
| Export Excel corrompu | Vérifier l'extension PHP `zip` |
| Date/heure erronée | Vérifier le fuseau horaire du serveur (`.env` → `APP_TIMEZONE`) |
| Assets non chargés | Relancer `npm run build` puis `php artisan view:cache` |
