<?php
// ============================================================
// includes/auth.php
// Gestion de l'authentification et des sessions
// ============================================================

// Démarrage sécurisé de la session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 3600,         // 1 heure
        'path'     => '/',
        'secure'   => false,        // Passer à true en HTTPS
        'httponly' => true,         // Protection contre XSS
        'samesite' => 'Strict',
    ]);
    session_start();
}

// ============================================================
// Vérifie si l'utilisateur est connecté
// Redirige vers login.php si non authentifié
// ============================================================
function requireLogin(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: /abonnement-app/login.php');
        exit;
    }
}

// ============================================================
// Retourne vrai si l'admin est connecté
// ============================================================
function isLoggedIn(): bool {
    return !empty($_SESSION['admin_id']);
}

// ============================================================
// Connexion : vérifie identifiants et crée la session
// ============================================================
function login(string $username, string $password): bool {
    require_once __DIR__ . '/../config/database.php';
    $db = getDB();

    $stmt = $db->prepare("SELECT id, username, password FROM admins WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        // Régénérer l'ID de session pour prévenir le fixation de session
        session_regenerate_id(true);

        $_SESSION['admin_id']       = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['login_time']     = time();
        return true;
    }

    return false;
}

// ============================================================
// Déconnexion : détruit la session
// ============================================================
function logout(): void {
    $_SESSION = [];
    session_destroy();
    header('Location: /abonnement-app/login.php');
    exit;
}
