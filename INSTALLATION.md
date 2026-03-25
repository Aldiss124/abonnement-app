# 📦 AbonManager — Guide d'installation Ubuntu (LAMP)

## ✅ Prérequis
- Ubuntu 20.04 / 22.04
- Apache2, MySQL 8+, PHP 8.1+

---

## 1️⃣ Installer LAMP

```bash
sudo apt update && sudo apt upgrade -y

# Apache
sudo apt install apache2 -y

# MySQL
sudo apt install mysql-server -y
sudo mysql_secure_installation

# PHP + extensions nécessaires
sudo apt install php php-mysql php-mbstring php-xml php-curl libapache2-mod-php -y

# Vérifier les versions
php -v && mysql --version && apache2 -v
```

---

## 2️⃣ Déployer l'application

```bash
# Copier le projet dans le répertoire web
sudo cp -r abonnement-app /var/www/html/

# Donner les bonnes permissions
sudo chown -R www-data:www-data /var/www/html/abonnement-app
sudo chmod -R 755 /var/www/html/abonnement-app
```

---

## 3️⃣ Configurer la base de données

```bash
# Connexion à MySQL
sudo mysql -u root -p

# Exécuter le script SQL
SOURCE /var/www/html/abonnement-app/database.sql;

# Vérifier
USE abonnement_db;
SHOW TABLES;
SELECT * FROM admins;
EXIT;
```

Ou depuis le terminal :
```bash
sudo mysql -u root -p < /var/www/html/abonnement-app/database.sql
```

---

## 4️⃣ Configurer Apache (mod_rewrite)

```bash
sudo a2enmod rewrite

# Créer un Virtual Host (optionnel)
sudo nano /etc/apache2/sites-available/abonmanager.conf
```

Contenu du VHost :
```apache
<VirtualHost *:80>
    DocumentRoot /var/www/html
    <Directory /var/www/html/abonnement-app>
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

```bash
sudo a2ensite abonmanager.conf
sudo systemctl restart apache2
```

---

## 5️⃣ Adapter la configuration

Éditer `config/database.php` :
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'abonnement_db');
define('DB_USER', 'root');      // ou votre utilisateur MySQL
define('DB_PASS', 'votre_mdp'); // mot de passe MySQL
```

---

## 6️⃣ Accéder à l'application

Ouvrir dans le navigateur :
```
http://localhost/abonnement-app/
```

**Identifiants de test :**
- Utilisateur : `admin`
- Mot de passe : `admin123`

---

## 🔐 Créer un nouvel admin en PHP

```php
<?php
echo password_hash('votre_nouveau_mdp', PASSWORD_DEFAULT);
// Copier le hash et l'insérer dans la table admins
```

---

## 🗂️ Structure du projet

```
/abonnement-app/
├── config/
│   └── database.php       ← Config BDD
├── css/
│   └── style.css          ← Styles globaux
├── js/
│   └── app.js             ← JavaScript
├── includes/
│   ├── auth.php           ← Auth & sessions
│   ├── functions.php      ← Fonctions CRUD
│   └── sidebar.php        ← Navigation
├── index.php              ← Redirection
├── login.php              ← Connexion admin
├── logout.php             ← Déconnexion
├── dashboard.php          ← Tableau de bord
├── clients.php            ← Liste clients
├── ajouter_client.php     ← Ajout client
├── modifier_client.php    ← Modification
└── database.sql           ← Script SQL complet
```

---

## 🔄 Mise à jour automatique des statuts

Les statuts sont recalculés automatiquement à chaque chargement de page :
- **Actif** : date de fin > aujourd'hui + 3 jours
- **Expire bientôt** : date de fin dans ≤ 3 jours
- **Expiré** : date de fin < aujourd'hui

---

## 🛡️ Sécurité implémentée

- ✅ Requêtes préparées PDO (anti-injection SQL)
- ✅ `password_hash()` / `password_verify()` pour les mots de passe
- ✅ `htmlspecialchars()` sur tous les affichages (anti-XSS)
- ✅ Sessions PHP sécurisées (httponly, samesite)
- ✅ Régénération de l'ID de session à la connexion
- ✅ Validation des données côté serveur
- ✅ Cast en `(int)` pour les IDs
