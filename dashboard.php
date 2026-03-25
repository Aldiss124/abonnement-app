<?php
// ============================================================
// dashboard.php
// Tableau de bord principal avec statistiques
// ============================================================

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Vérification de connexion obligatoire
requireLogin();

// Mise à jour automatique des statuts
mettreAJourStatuts();

// Récupération des données
$stats   = getStatistiques();
$alertes = getAlertes();

// 5 derniers clients ajoutés
$db     = getDB();
$recents = $db->query("SELECT * FROM clients ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — AbonManager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-wrapper">

    <!-- ── Sidebar ─────────────────────────────── -->
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <!-- ── Contenu principal ──────────────────── -->
    <main class="main-content">

        <!-- Topbar -->
        <div class="topbar">
            <div class="page-title">
                <i class="fas fa-chart-line"></i>
                Tableau de bord
            </div>
            <div class="topbar-actions">
                <!-- Bouton menu mobile -->
                <button class="btn btn-secondary btn-icon" id="sidebarToggle" style="display:none">
                    <i class="fas fa-bars"></i>
                </button>
                <a href="/abonnement-app/ajouter_client.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span>Nouveau client</span>
                </a>
            </div>
        </div>

        <div class="page-body">

            <!-- ── Alerte globale si expirations proches ── -->
            <?php if (count($alertes) > 0): ?>
                <div class="alert alert-warning fade-in" data-autohide>
                    <i class="fas fa-triangle-exclamation"></i>
                    <div>
                        <strong><?= count($alertes) ?> abonnement(s)</strong> expire(nt) dans moins de 3 jours.
                        <a href="/abonnement-app/clients.php?filtre=expire_bientot" style="margin-left:8px;text-decoration:underline">
                            Voir les détails →
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ── Cartes statistiques ───────────────── -->
            <div class="stats-grid">

                <div class="stat-card accent fade-in">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-value" data-count="<?= $stats['total'] ?>"><?= $stats['total'] ?></div>
                    <div class="stat-label">Total clients</div>
                </div>

                <div class="stat-card green fade-in">
                    <div class="stat-icon"><i class="fas fa-circle-check"></i></div>
                    <div class="stat-value" data-count="<?= $stats['actifs'] ?>"><?= $stats['actifs'] ?></div>
                    <div class="stat-label">Abonnements actifs</div>
                </div>

                <div class="stat-card red fade-in">
                    <div class="stat-icon"><i class="fas fa-circle-xmark"></i></div>
                    <div class="stat-value" data-count="<?= $stats['expires'] ?>"><?= $stats['expires'] ?></div>
                    <div class="stat-label">Expirés</div>
                </div>

                <div class="stat-card orange fade-in">
                    <div class="stat-icon"><i class="fas fa-triangle-exclamation"></i></div>
                    <div class="stat-value" data-count="<?= $stats['expire_bientot'] ?>"><?= $stats['expire_bientot'] ?></div>
                    <div class="stat-label">Expirent bientôt</div>
                </div>

                <div class="stat-card blue fade-in">
                    <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                    <div class="stat-value" style="font-size:1.3rem">
                        <?= number_format($stats['revenus'], 0, ',', ' ') ?>
                    </div>
                    <div class="stat-label">Revenus estimés (FCFA)</div>
                </div>

            </div>

            <!-- ── Grille : Alertes + Récents ──────────── -->
            <div style="display:grid;grid-template-columns:1fr 1.4fr;gap:20px;margin-top:8px">

                <!-- Alertes d'expiration -->
                <div class="card fade-in">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-bell"></i>
                            Alertes d'expiration
                        </div>
                        <?php if (count($alertes) > 0): ?>
                            <span class="badge badge-alerte"><?= count($alertes) ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if (count($alertes) > 0): ?>
                        <?php foreach ($alertes as $alerte): ?>
                            <div class="alert-row">
                                <div class="alert-row-info">
                                    <strong><?= htmlspecialchars($alerte['nom']) ?></strong>
                                    <span><?= htmlspecialchars($alerte['service']) ?> — <?= htmlspecialchars($alerte['offre']) ?></span>
                                </div>
                                <span class="badge badge-alerte">
                                    <?= joursRestants($alerte['date_fin']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                        <a href="/abonnement-app/clients.php?filtre=expire_bientot"
                           class="btn btn-secondary btn-sm"
                           style="margin-top:14px;width:100%;justify-content:center">
                            Voir tous les détails
                        </a>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-bell-slash"></i>
                            <p>Aucune expiration imminente 🎉</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Clients récents -->
                <div class="card fade-in">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-clock-rotate-left"></i>
                            Derniers clients
                        </div>
                        <a href="/abonnement-app/clients.php" class="btn btn-secondary btn-sm">Voir tout</a>
                    </div>

                    <?php if (count($recents) > 0): ?>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Service</th>
                                        <th>Expiration</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recents as $c): ?>
                                        <tr>
                                            <td class="td-nom">
                                                <strong><?= htmlspecialchars($c['nom']) ?></strong>
                                                <span><?= htmlspecialchars($c['telephone']) ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($c['service']) ?></td>
                                            <td>
                                                <?php
                                                $diff = (new DateTime())->diff(new DateTime($c['date_fin']))->format('%r%a');
                                                $diff = (int)$diff;
                                                $cls  = $diff < 0 ? 'jours-expire' : ($diff <= 3 ? 'jours-alerte' : 'jours-ok');
                                                ?>
                                                <span class="<?= $cls ?>">
                                                    <?= joursRestants($c['date_fin']) ?>
                                                </span>
                                            </td>
                                            <td><?= badgeStatut($c['statut']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-user-slash"></i>
                            <p>Aucun client enregistré</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

        </div><!-- /page-body -->
    </main>

</div>

<script src="/abonnement-app/js/app.js"></script>
<script>
    // Afficher le bouton menu sur mobile
    if (window.innerWidth <= 900) {
        document.getElementById('sidebarToggle').style.display = 'flex';
    }
</script>

</body>
</html>
