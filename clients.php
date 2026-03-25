<?php
// ============================================================
// clients.php
// Liste des clients avec actions CRUD et filtres
// ============================================================

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();
mettreAJourStatuts();

// ── Action : Suppression ────────────────────────────────────
$message = '';
$msg_type = 'success';

if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    if ($id > 0 && supprimerClient($id)) {
        $message  = 'Client supprimé avec succès.';
    } else {
        $message  = 'Erreur lors de la suppression.';
        $msg_type = 'error';
    }
}

// ── Message de succès depuis ajouter/modifier ───────────────
if (isset($_GET['success'])) {
    $message = match($_GET['success']) {
        'ajoute'   => 'Client ajouté avec succès ! ✓',
        'modifie'  => 'Client modifié avec succès ! ✓',
        default    => 'Opération réussie.',
    };
}

// ── Récupération avec filtres ────────────────────────────────
$search = trim($_GET['q']      ?? '');
$filtre = trim($_GET['filtre'] ?? '');

$clients = getClients($search, $filtre);
$stats   = getStatistiques();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clients — AbonManager</title>
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
                <i class="fas fa-users"></i>
                Clients
                <span style="font-size:0.8rem;font-weight:500;color:var(--text2);
                    background:var(--bg3);border:1px solid var(--border);
                    padding:2px 10px;border-radius:99px">
                    <?= count($clients) ?>
                </span>
            </div>
            <div class="topbar-actions">
                <!-- Barre de recherche -->
                <form method="GET" action="" style="display:flex;gap:8px;align-items:center">
                    <?php if ($filtre): ?>
                        <input type="hidden" name="filtre" value="<?= htmlspecialchars($filtre) ?>">
                    <?php endif; ?>
                    <div class="search-bar">
                        <i class="fas fa-magnifying-glass"></i>
                        <input
                            type="text"
                            name="q"
                            id="searchInput"
                            class="form-control"
                            placeholder="Rechercher..."
                            value="<?= htmlspecialchars($search) ?>"
                        >
                    </div>
                </form>
                <a href="/abonnement-app/ajouter_client.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span>Ajouter</span>
                </a>
            </div>
        </div>

        <div class="page-body">

            <!-- Message de retour -->
            <?php if ($message): ?>
                <div class="alert alert-<?= $msg_type ?> fade-in" data-autohide>
                    <i class="fas fa-<?= $msg_type === 'success' ? 'circle-check' : 'circle-xmark' ?>"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Filtres rapides -->
            <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
                <a href="/abonnement-app/clients.php<?= $search ? '?q='.urlencode($search) : '' ?>"
                   class="btn btn-sm <?= !$filtre ? 'btn-primary' : 'btn-secondary' ?>">
                    Tous <span style="opacity:0.7">(<?= $stats['total'] ?>)</span>
                </a>
                <a href="/abonnement-app/clients.php?filtre=actif<?= $search ? '&q='.urlencode($search) : '' ?>"
                   class="btn btn-sm <?= $filtre==='actif' ? 'btn-primary' : 'btn-secondary' ?>"
                   style="<?= $filtre==='actif' ? '' : 'color:var(--green)' ?>">
                    <i class="fas fa-circle" style="font-size:0.5rem"></i>
                    Actifs (<?= $stats['actifs'] ?>)
                </a>
                <a href="/abonnement-app/clients.php?filtre=expire_bientot<?= $search ? '&q='.urlencode($search) : '' ?>"
                   class="btn btn-sm <?= $filtre==='expire_bientot' ? 'btn-primary' : 'btn-secondary' ?>"
                   style="<?= $filtre==='expire_bientot' ? '' : 'color:var(--orange)' ?>">
                    <i class="fas fa-triangle-exclamation" style="font-size:0.7rem"></i>
                    Bientôt (<?= $stats['expire_bientot'] ?>)
                </a>
                <a href="/abonnement-app/clients.php?filtre=expire<?= $search ? '&q='.urlencode($search) : '' ?>"
                   class="btn btn-sm <?= $filtre==='expire' ? 'btn-primary' : 'btn-secondary' ?>"
                   style="<?= $filtre==='expire' ? '' : 'color:var(--red)' ?>">
                    <i class="fas fa-circle-xmark" style="font-size:0.7rem"></i>
                    Expirés (<?= $stats['expires'] ?>)
                </a>
            </div>

            <!-- Tableau des clients -->
            <div class="card fade-in">
                <div class="table-wrapper">
                    <?php if (count($clients) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Client</th>
                                    <th>Service / Offre</th>
                                    <th>Prix</th>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                    <th>Jours restants</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clients as $index => $client): ?>
                                    <?php
                                    // Calcul jours restants pour colorisation
                                    $now   = new DateTime();
                                    $fin   = new DateTime($client['date_fin']);
                                    $diff  = (int) $now->diff($fin)->format('%r%a');
                                    $jCls  = $diff < 0 ? 'jours-expire' : ($diff <= 3 ? 'jours-alerte' : 'jours-ok');
                                    ?>
                                    <tr data-searchable="<?= htmlspecialchars(strtolower($client['nom'].' '.$client['telephone'].' '.$client['service'])) ?>">
                                        <td style="color:var(--text3);font-size:0.78rem">
                                            <?= $index + 1 ?>
                                        </td>
                                        <td class="td-nom">
                                            <strong><?= htmlspecialchars($client['nom']) ?></strong>
                                            <span><?= htmlspecialchars($client['telephone']) ?></span>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($client['service']) ?></strong>
                                            <span style="display:block;font-size:0.78rem;color:var(--text2)">
                                                <?= htmlspecialchars($client['offre']) ?>
                                            </span>
                                        </td>
                                        <td style="font-variant-numeric:tabular-nums;white-space:nowrap">
                                            <?= formatPrix((float)$client['prix']) ?>
                                        </td>
                                        <td style="color:var(--text2);white-space:nowrap">
                                            <?= date('d/m/Y', strtotime($client['date_debut'])) ?>
                                        </td>
                                        <td style="white-space:nowrap">
                                            <?= date('d/m/Y', strtotime($client['date_fin'])) ?>
                                        </td>
                                        <td>
                                            <span class="<?= $jCls ?>">
                                                <?= joursRestants($client['date_fin']) ?>
                                            </span>
                                        </td>
                                        <td><?= badgeStatut($client['statut']) ?></td>
                                        <td>
                                            <div style="display:flex;gap:6px">
                                                <!-- Modifier -->
                                                <a href="/abonnement-app/modifier_client.php?id=<?= $client['id'] ?>"
                                                   class="btn btn-secondary btn-icon btn-sm"
                                                   title="Modifier">
                                                    <i class="fas fa-pen-to-square"></i>
                                                </a>
                                                <!-- Supprimer -->
                                                <button
                                                    class="btn btn-danger btn-icon btn-sm"
                                                    data-delete-id="<?= $client['id'] ?>"
                                                    data-delete-name="<?= htmlspecialchars($client['nom']) ?>"
                                                    title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <!-- État vide si recherche sans résultat -->
                        <div id="emptySearch" style="display:none">
                            <div class="empty-state"><i class="fas fa-magnifying-glass"></i><p>Aucun résultat trouvé.</p></div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-user-slash"></i>
                            <p>Aucun client trouvé.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>

</div>

<!-- ── Modal de confirmation de suppression ─── -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal">
        <div class="modal-title">
            <i class="fas fa-trash" style="color:var(--red);margin-right:8px"></i>
            Supprimer ce client ?
        </div>
        <p class="modal-text" id="deleteModalText">Cette action est irréversible.</p>
        <div class="modal-actions">
            <button class="btn btn-secondary" id="cancelDelete">Annuler</button>
            <button class="btn btn-danger" id="confirmDelete">
                <i class="fas fa-trash"></i> Supprimer
            </button>
        </div>
    </div>
</div>

<script src="/abonnement-app/js/app.js"></script>
</body>
</html>
