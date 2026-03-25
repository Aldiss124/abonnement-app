<?php
// ============================================================
// config/database.php
// Configuration de la connexion à la base de données MySQL
// ============================================================

// Paramètres de connexion — à adapter selon votre environnement
define('DB_HOST',     'db-abonnement-gastonmani066-2fa7.l.aivencloud.com');
define('DB_NAME',     'defaultdb');
define('DB_USER',     'avnadmin');        // Changer en production
define('DB_PASS',     'AVNS_7KxkKuK2VMIP_I9SIAs');            // Changer en production
define('DB_CHARSET',  'utf8mb4');

// ============================================================
// Connexion PDO sécurisée avec gestion d'erreur
// ============================================================
function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        try {$dsn = "mysql:host=" . DB_HOST . ";port=16947;dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $dsn = 

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Lève des exceptions
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Retourne des tableaux associatifs
                PDO::ATTR_EMULATE_PREPARES   => false,                   // Requêtes préparées réelles
            ];

            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

        } catch (PDOException $e) {
            // En production, ne pas afficher les détails de l'erreur
            die(json_encode(['error' => 'Connexion à la base de données impossible.']));
        }
    }

    return $pdo;
}

// ============================================================
// Constantes de l'application
// ============================================================
define('APP_NAME',    'AbonManager');
define('APP_VERSION', '1.0.0');
define('DELAI_ALERTE', 3); // Nombre de jours avant expiration pour l'alerte
