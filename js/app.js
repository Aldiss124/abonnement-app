/* ============================================================
   js/app.js
   JavaScript principal — Interactions et comportements
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

    // ── 1. Toggle Sidebar (Mobile) ──────────────────────────
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar       = document.getElementById('sidebar');
    const overlay       = document.getElementById('sidebarOverlay');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            if (overlay) overlay.classList.toggle('active');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        });
    }

    // ── 2. Modal de confirmation de suppression ─────────────
    const deleteButtons = document.querySelectorAll('[data-delete-id]');
    const deleteModal   = document.getElementById('deleteModal');
    const confirmDelete = document.getElementById('confirmDelete');
    const cancelDelete  = document.getElementById('cancelDelete');

    let deleteTargetId = null;

    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            deleteTargetId = this.dataset.deleteId;
            const nom = this.dataset.deleteName || 'ce client';
            const modalText = document.getElementById('deleteModalText');
            if (modalText) {
                modalText.textContent = `Êtes-vous sûr de vouloir supprimer "${nom}" ? Cette action est irréversible.`;
            }
            if (deleteModal) deleteModal.classList.add('active');
        });
    });

    if (confirmDelete) {
        confirmDelete.addEventListener('click', function () {
            if (deleteTargetId) {
                window.location.href = `clients.php?action=supprimer&id=${deleteTargetId}`;
            }
        });
    }

    if (cancelDelete) {
        cancelDelete.addEventListener('click', () => {
            if (deleteModal) deleteModal.classList.remove('active');
            deleteTargetId = null;
        });
    }

    // Fermer modal sur clic overlay
    if (deleteModal) {
        deleteModal.addEventListener('click', function (e) {
            if (e.target === this) {
                this.classList.remove('active');
                deleteTargetId = null;
            }
        });
    }

    // ── 3. Fermeture automatique des alertes ────────────────
    const alerts = document.querySelectorAll('.alert[data-autohide]');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.4s, transform 0.4s';
            alert.style.opacity    = '0';
            alert.style.transform  = 'translateY(-8px)';
            setTimeout(() => alert.remove(), 400);
        }, 4000); // Disparaît après 4 secondes
    });

    // ── 4. Filtre de recherche en temps réel ────────────────
    const searchInput = document.getElementById('searchInput');
    const tableRows   = document.querySelectorAll('tbody tr[data-searchable]');

    if (searchInput && tableRows.length > 0) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            let visibleCount = 0;

            tableRows.forEach(row => {
                const text = row.dataset.searchable.toLowerCase();
                const match = text.includes(query);
                row.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });

            // Afficher état vide si aucun résultat
            const emptyState = document.getElementById('emptySearch');
            if (emptyState) {
                emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        });
    }

    // ── 5. Calcul automatique date de fin (+30j) ────────────
    const dateDebut = document.getElementById('date_debut');
    const dateFin   = document.getElementById('date_fin_preview');

    if (dateDebut && dateFin) {
        const updateDateFin = () => {
            if (dateDebut.value) {
                const debut    = new Date(dateDebut.value);
                const fin      = new Date(debut);
                fin.setDate(fin.getDate() + 30);
                const formatted = fin.toLocaleDateString('fr-FR', {
                    day: '2-digit', month: 'long', year: 'numeric'
                });
                dateFin.textContent = formatted;
                dateFin.classList.add('jours-ok');
            }
        };
        dateDebut.addEventListener('change', updateDateFin);
        updateDateFin(); // Initialisation
    }

    // ── 6. Validation formulaire client ─────────────────────
    const clientForm = document.getElementById('clientForm');
    if (clientForm) {
        clientForm.addEventListener('submit', function (e) {
            const required = this.querySelectorAll('[required]');
            let valid = true;

            required.forEach(field => {
                field.style.borderColor = '';
                if (!field.value.trim()) {
                    field.style.borderColor = 'var(--red)';
                    valid = false;
                }
            });

            if (!valid) {
                e.preventDefault();
                // Afficher message d'erreur
                let errorAlert = document.getElementById('formError');
                if (!errorAlert) {
                    errorAlert = document.createElement('div');
                    errorAlert.id        = 'formError';
                    errorAlert.className = 'alert alert-error';
                    errorAlert.innerHTML = '<i class="fas fa-exclamation-circle"></i> Veuillez remplir tous les champs obligatoires.';
                    clientForm.insertBefore(errorAlert, clientForm.firstChild);
                }
                errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }

    // ── 7. Tooltip sur les badges de statut ─────────────────
    const badges = document.querySelectorAll('.badge');
    badges.forEach(badge => {
        badge.style.cursor = 'default';
    });

    // ── 8. Compteur animé pour les statistiques ─────────────
    const animateCount = (el, target) => {
        const duration  = 800;
        const start     = performance.now();
        const startVal  = 0;

        const update = (time) => {
            const elapsed  = time - start;
            const progress = Math.min(elapsed / duration, 1);
            const eased    = 1 - Math.pow(1 - progress, 3); // ease-out cubic
            const current  = Math.round(startVal + (target - startVal) * eased);

            el.textContent = current.toLocaleString('fr-FR');

            if (progress < 1) requestAnimationFrame(update);
        };

        requestAnimationFrame(update);
    };

    // Lancer les animations des stats
    document.querySelectorAll('.stat-value[data-count]').forEach(el => {
        const target = parseInt(el.dataset.count, 10);
        if (!isNaN(target)) animateCount(el, target);
    });

    // ── 9. Highlight ligne au clic ──────────────────────────
    document.querySelectorAll('tbody tr').forEach(row => {
        row.style.cursor = 'default';
    });

});
