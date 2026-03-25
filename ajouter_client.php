<?php
// ============================================================
// ajouter_client.php
// Formulaire d'ajout d'un nouveau client
// ============================================================

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$errors = [];
$data   = [
    'nom'        => '',
    'telephone'  => '',
    'service'    => '',
    'offre'      => '',
    'prix'       => '',
    'date_debut' => date('Y-m-d'), // Date du jour par défaut
];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération et validation des champs
    $data['nom']       = trim($_POST['nom']       ?? '');
    $data['telephone'] = trim($_POST['telephone'] ?? '');
    $data['service']   = trim($_POST['service']   ?? '');
    $data['offre']     = trim($_POST['offre']      ?? '');
    $data['prix']      = trim($_POST['prix']       ?? '');
    $data['date_debut']= trim($_POST['date_debut'] ?? '');

    // Validations
    if (empty($data['nom']))        $errors[] = 'Le nom est obligatoire.';
    if (empty($data['telephone']))  $errors[] = 'Le téléphone est obligatoire.';
    if (empty($data['service']))    $errors[] = 'Le service est obligatoire.';
    if (empty($data['offre']))      $errors[] = "L'offre est obligatoire.";
    if (!is_numeric($data['prix'])) $errors[] = 'Le prix doit être un nombre.';
    if (empty($data['date_debut'])) $errors[] = 'La date de début est obligatoire.';

    // Si pas d'erreurs → enregistrement
    if (empty($errors)) {
        if (ajouterClient($data)) {
            header('Location: /abonnement-app/clients.php?success=ajoute');
            exit;
        } else {
            $errors[] = 'Erreur lors de l\'enregistrement. Veuillez réessayer.';
        }
    }
}

// Services prédéfinis
$services = ['Netflix', 'Spotify', 'Canal+', 'Disney+', 'Amazon Prime', 'Apple TV+', 'YouTube Premium', 'Deezer', 'Autre'];
$offres   = ['Basic', 'Standard', 'Premium', 'Family', 'Duo', 'Essentiel', 'Intégral', 'Autre'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un client — AbonManager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/abonnement-app/css/style.css">
</head>
<body>

<div class="app-wrapper">

    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">

        <!-- Topbar -->
        <div class="topbar">
            <div class="page-title">
                <i class="fas fa-user-plus"></i>
                Ajouter un client
            </div>
            <div class="topbar-actions">
                <a href="/abonnement-app/clients.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Retour
                </a>
            </div>
        </div>

        <div class="page-body">

            <!-- Affichage des erreurs -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error fade-in">
                    <i class="fas fa-circle-xmark"></i>
                    <div>
                        <strong>Corrigez les erreurs suivantes :</strong>
                        <ul style="margin-top:6px;padding-left:18px">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Formulaire d'ajout -->
            <div class="card fade-in" style="max-width:760px">

                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-user"></i>
                        Informations du client
                    </div>
                    <span style="font-size:0.78rem;color:var(--text3)">
                        * Champs obligatoires
                    </span>
                </div>

                <form id="clientForm" method="POST" action="">

                    <div class="form-grid">

                        <!-- Nom complet -->
                        <div class="form-group">
                            <label for="nom">Nom complet *</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-user"></i>
                                <input type="text" id="nom" name="nom"
                                    class="form-control"
                                    placeholder="Ex: Jean Dupont"
                                    value="<?= htmlspecialchars($data['nom']) ?>"
                                    required>
                            </div>
                        </div>

                        <!-- Téléphone -->
                        <div class="form-group">
                            <label for="telephone">Téléphone *</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-phone"></i>
                                <input type="text" id="telephone" name="telephone"
                                    class="form-control"
                                    placeholder="+237 6XX XXX XXX"
                                    value="<?= htmlspecialchars($data['telephone']) ?>"
                                    required>
                            </div>
                        </div>

                        <!-- Service -->
                        <div class="form-group">
                            <label for="service">Service *</label>
                            <select id="service" name="service" class="form-control" required>
                                <option value="">-- Choisir un service --</option>
                                <?php foreach ($services as $svc): ?>
                                    <option value="<?= htmlspecialchars($svc) ?>"
                                        <?= $data['service'] === $svc ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($svc) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Offre -->
                        <div class="form-group">
                            <label for="offre">Offre / Plan *</label>
                            <select id="offre" name="offre" class="form-control" required>
                                <option value="">-- Choisir une offre --</option>
                                <?php foreach ($offres as $off): ?>
                                    <option value="<?= htmlspecialchars($off) ?>"
                                        <?= $data['offre'] === $off ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($off) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Prix -->
                        <div class="form-group">
                            <label for="prix">Prix mensuel (FCFA) *</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-coins"></i>
                                <input type="number" id="prix" name="prix"
                                    class="form-control"
                                    placeholder="Ex: 15000"
                                    min="0" step="100"
                                    value="<?= htmlspecialchars($data['prix']) ?>"
                                    required>
                            </div>
                        </div>

                        <!-- Date de début -->
                        <div class="form-group">
                            <label for="date_debut">Date de début *</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-calendar-day"></i>
                                <input type="date" id="date_debut" name="date_debut"
                                    class="form-control"
                                    value="<?= htmlspecialchars($data['date_debut']) ?>"
                                    required>
                            </div>
                        </div>

                    </div><!-- /form-grid -->

                    <!-- Aperçu date de fin calculée -->
                    <div style="margin-top:16px;padding:14px 18px;background:var(--bg3);
                        border-radius:var(--radius-sm);border:1px solid var(--border);
                        display:flex;align-items:center;gap:12px">
                        <i class="fas fa-calendar-check" style="color:var(--accent2)"></i>
                        <span style="font-size:0.9rem;color:var(--text2)">
                            Date de fin calculée automatiquement (+30 jours) :
                        </span>
                        <strong id="date_fin_preview" class="jours-ok">—</strong>
                    </div>

                    <!-- Boutons d'action -->
                    <div style="display:flex;gap:10px;margin-top:24px;justify-content:flex-end">
                        <a href="/abonnement-app/clients.php" class="btn btn-secondary">
                            <i class="fas fa-xmark"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Ajouter le client
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </main>

</div>

<script src="/abonnement-app/js/app.js"></script>
</body>
</html>
