<?php
// ============================================================
// login.php
// Page de connexion administrateur
// ============================================================

require_once __DIR__ . '/includes/auth.php';

// Si déjà connecté → rediriger vers le dashboard
if (isLoggedIn()) {
    header('Location: /abonnement-app/dashboard.php');
    exit;
}

$error   = '';
$success = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Protection CSRF basique
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (login($username, $password)) {
        // Connexion réussie → dashboard
        header('Location: /abonnement-app/dashboard.php');
        exit;
    } else {
        $error = 'Identifiant ou mot de passe incorrect.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — AbonManager</title>

    <!-- Fonts Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- CSS Principal -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="login-page">

    <div class="login-card fade-in">

        <!-- En-tête -->
        <div class="login-header">
            <div class="login-logo">📡</div>
            <h1>AbonManager</h1>
            <p>Connectez-vous à votre espace admin</p>
        </div>

        <!-- Affichage des erreurs -->
        <?php if ($error): ?>
            <div class="alert alert-error" data-autohide>
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire de connexion -->
        <form class="login-form" method="POST" action="">

            <!-- Champ : Identifiant -->
            <div class="form-group">
                <label for="username">Identifiant</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-user"></i>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-control"
                        placeholder="admin"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        required
                        autocomplete="username"
                    >
                </div>
            </div>

            <!-- Champ : Mot de passe -->
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-lock"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >
                </div>
            </div>

            <!-- Bouton de connexion -->
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px">
                <i class="fas fa-arrow-right-to-bracket"></i>
                Se connecter
            </button>

        </form>

        <!-- Indication compte de test -->
        <div style="text-align:center;margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
            <p style="font-size:0.78rem;color:var(--text3)">
                Compte de test : <strong style="color:var(--text2)">admin</strong> /
                <strong style="color:var(--text2)">admin123</strong>
            </p>
        </div>

    </div>

</div>

<script src="/abonnement-app/js/app.js"></script>
</body>
</html>
