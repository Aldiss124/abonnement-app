<?php
// ============================================================
// includes/sidebar.php
// Barre de navigation latérale commune à toutes les pages
// ============================================================
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$alertes_count = count(getAlertes()); // Nombre d'alertes actives
?>
<!-- Overlay mobile -->
<div id="sidebarOverlay" class="sidebar-overlay" style="
    display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);
    z-index:99;opacity:0;transition:opacity 0.2s;
" onclick="this.style.opacity='0';document.getElementById('sidebar').classList.remove('open')"></div>

<style>
.sidebar-overlay.active { display:block !important; opacity:1 !important; }
</style>

<aside class="sidebar" id="sidebar">

    <!-- Logo -->
    <div class="sidebar-logo">
        <div class="logo-icon">📡</div>
        <div class="logo-text">Abon<span>Manager</span></div>
    </div>

    <!-- Navigation principale -->
    <div class="sidebar-section">
        <div class="sidebar-section-label">Navigation</div>

        <a href="/abonnement-app/dashboard.php"
           class="nav-item <?= $current_page === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-chart-line"></i>
            Tableau de bord
        </a>

        <a href="/abonnement-app/clients.php"
           class="nav-item <?= $current_page === 'clients' ? 'active' : '' ?>">
            <i class="fas fa-users"></i>
            Clients
            <?php if ($alertes_count > 0): ?>
                <span class="nav-badge"><?= $alertes_count ?></span>
            <?php endif; ?>
        </a>

        <a href="/abonnement-app/ajouter_client.php"
           class="nav-item <?= $current_page === 'ajouter_client' ? 'active' : '' ?>">
            <i class="fas fa-user-plus"></i>
            Ajouter un client
        </a>
    </div>

    <!-- Filtres rapides -->
    <div class="sidebar-section">
        <div class="sidebar-section-label">Filtres rapides</div>

        <a href="/abonnement-app/clients.php?filtre=actif" class="nav-item">
            <i class="fas fa-circle" style="color:var(--green);font-size:0.6rem"></i>
            Actifs
        </a>

        <a href="/abonnement-app/clients.php?filtre=expire_bientot" class="nav-item">
            <i class="fas fa-circle" style="color:var(--orange);font-size:0.6rem"></i>
            Expirent bientôt
        </a>

        <a href="/abonnement-app/clients.php?filtre=expire" class="nav-item">
            <i class="fas fa-circle" style="color:var(--red);font-size:0.6rem"></i>
            Expirés
        </a>
    </div>

    <!-- Footer sidebar : info admin + déconnexion -->
    <div class="sidebar-footer">
        <div class="admin-info">
            <div class="admin-avatar">
                <?= strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)) ?>
            </div>
            <div>
                <div class="admin-name"><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></div>
                <div class="admin-role">Administrateur</div>
            </div>
            <a href="/abonnement-app/logout.php" class="btn-logout" title="Déconnexion">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>

</aside>
