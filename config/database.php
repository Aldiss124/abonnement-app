<?php
// ============================================================
// config/database.php
// Configuration de la connexion à la base de données MySQL
// ============================================================

define('DB_HOST', 'crossover.proxy.rlwy.net');
define('DB_NAME', 'railway');
define('DB_USER', 'root');
define('DB_PASS', 'LJRxFMDWrEaIpSbXELPHOsEMqtODFoCA');
define('DB_CHARSET', 'utf8mb4');

// ============================================================
// Connexion PDO sécurisée avec gestion d'erreur
// ============================================================
function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        try {
            // Utilisation du port public Railway 43376
            $dsn = "mysql:host=" . DB_HOST . ";port=43376;dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

        } catch (PDOException $e) {
            // Affiche l'erreur réelle pour le débogage (à retirer après succès)
            die(json_encode(['error' => 'Erreur : ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

// ============================================================
// Constantes de l'application
// ============================================================
define('APP_NAME', 'AbonManager');
define('APP_VERSION', '1.0.0');
define('DELAI_ALERTE', 3);