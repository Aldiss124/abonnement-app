<?php
// index.php — Redirection vers login ou dashboard
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: /abonnement-app/dashboard.php');
} else {
    header('Location: /abonnement-app/login.php');
}
exit;
