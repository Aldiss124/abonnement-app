<?php
// ============================================================
// modifier_client.php
// Formulaire de modification d'un client existant
// ============================================================

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

// Validation de l'ID passé en paramètre
$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: /abonnement-app/clients.php');
    exit;
}

// Récupération du client à modifier
$client = getClientById($id);

if (!$client) {
    header('Location: /abonnement-app/clients.php');
    exit;
}

$errors = [];
$data   = $client; // Pré-remplissage avec données existantes

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data['nom']        = trim($_POST['nom']        ?? '');
    $data['telephone']  = trim($_POST['telephone']  ?? '');
    $data['service']    = trim($_POST['service']    ?? '');
    $data['offre']      = trim($_POST['offre']      ?? '');
    $data['prix']       = trim($_POST['prix']       ?? '');
    $data['date_debut'] = trim($_POST['date_debut'] ?? '');

    // Validations
    if (empty($data['nom']))        $errors[] = 'Le nom est obligatoire.';
    if (empty($data['telephone']))  $errors[] = 'Le téléphone est obligatoire.';
    if (empty($data['service']))    $errors[] = 'Le service est obligatoire.';
    if (empty($data['offre']))      $errors[] = "L'offre est obligatoire.";
    if (!is_numeric($data['prix'])) $errors[] = 'Le prix doit être un nombre.';
    if (empty($data['date_debut'])) $errors[] = 'La date de début est obligatoire.';

    if (empty($errors)) {
        if (modifierClient($id, $data)) {
            header('Location: /abonnement-app/clients.php?success=modifie');
            exit;
        } else {
            $errors[] = 'Erreur lors de la modification. Veuillez réessayer.';
        }
    }
}

$services = ['Netflix', 'Spotify', 'Canal+', 'Disney+', 'Amazon Prime', 'Apple TV+', 'YouTube Premium', 'Deezer', 'Autre'];
$offres   = ['Basic', 'Standard', 'Premium', 'Family', 'Duo', 'Essentiel', 'Intégral', 'Autre'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier — <?= htmlspecialchars($client['nom']) ?> — AbonManager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-wrapper">

    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">

        <!-- Topbar -->
        <div class="topbar">
            <div class="page-title">
                <i class="fas fa-pen-to-square"></i>
                Modifier : <?= htmlspecialchars($client['nom']) ?>
            </div>
            <div class="topbar-actions">
                <a href="/abonnement-app/clients.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Retour
                </a>
            </div>
        </div>

        <div class="page-body">

            <!-- Infos actuelles -->
            <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap">
                <?= badgeStatut($client['statut']) ?>
                <span style="font-size:0.85rem;color:var(--text2)">
                    <i class="fas fa-calendar" style="color:var(--accent2);margin-right:4px"></i>
                    Expire le <?= date('d/m/Y', strtotime($client['date_fin'])) ?>
                </span>
                <span style="font-size:0.85rem;color:var(--text2)">
                    <i class="fas fa-clock" style="color:var(--accent2);margin-right:4px"></i>
                    <?= joursRestants($client['date_fin']) ?>
                </span>
            </div>

            <!-- Erreurs -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error fade-in">
                    <i class="fas fa-circle-xmark"></i>
                    <div>
                        <strong>Erreurs :</strong>
                        <ul style="margin-top:6px;padding-left:18px">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Formulaire -->
            <div class="card fade-in" style="max-width:760px">

                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-user-pen"></i>
                        Modifier les informations
                    </div>
                </div>

                <form id="clientForm" method="POST" action="">

                    <div class="form-grid">

                        <div class="form-group">
                            <label for="nom">Nom complet *</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-user"></i>
                                <input type="text" id="nom" name="nom"
                                    class="form-control"
                                    value="<?= htmlspecialchars($data['nom']) ?>"
                                    required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="telephone">Téléphone *</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-phone"></i>
                                <input type="text" id="telephone" name="telephone"
                                    class="form-control"
                                    value="<?= htmlspecialchars($data['telephone']) ?>"
                                    required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="service">Service *</label>
                            <select id="service" name="service" class="form-control" required>
                                <?php foreach ($services as $svc): ?>
                                    <option value="<?= htmlspecialchars($svc) ?>"
                                        <?= $data['service'] === $svc ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($svc) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="offre">Offre / Plan *</label>
                            <select id="offre" name="offre" class="form-control" required>
                                <?php foreach ($offres as $off): ?>
                                    <option value="<?= htmlspecialchars($off) ?>"
                                        <?= $data['offre'] === $off ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($off) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="prix">Prix mensuel (FCFA) *</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-coins"></i>
                                <input type="number" id="prix" name="prix"
                                    class="form-control"
                                    min="0" step="100"
                                    value="<?= htmlspecialchars($data['prix']) ?>"
                                    required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="date_debut">Date de début (renouvellement) *</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-calendar-day"></i>
                                <input type="date" id="date_debut" name="date_debut"
                                    class="form-control"
                                    value="<?= htmlspecialchars($data['date_debut']) ?>"
                                    required>
                            </div>
                        </div>

                    </div>

                    <!-- Aperçu nouvelle date de fin -->
                    <div style="margin-top:16px;padding:14px 18px;background:var(--bg3);
                        border-radius:var(--radius-sm);border:1px solid var(--border);
                        display:flex;align-items:center;gap:12px">
                        <i class="fas fa-calendar-check" style="color:var(--accent2)"></i>
                        <span style="font-size:0.9rem;color:var(--text2)">
                            Nouvelle date de fin (+30 jours) :
                        </span>
                        <strong id="date_fin_preview" class="jours-ok">—</strong>
                    </div>

                    <!-- Boutons -->
                    <div style="display:flex;gap:10px;margin-top:24px;justify-content:flex-end">
                        <a href="/abonnement-app/clients.php" class="btn btn-secondary">
                            <i class="fas fa-xmark"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-floppy-disk"></i> Enregistrer les modifications
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
