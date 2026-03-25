<?php
// ============================================================
// includes/functions.php
// Fonctions utilitaires partagées
// ============================================================

require_once __DIR__ . '/../config/database.php';

// ============================================================
// Mise à jour automatique des statuts selon les dates
// À appeler au chargement de chaque page protégée
// ============================================================
function mettreAJourStatuts(): void {
    $db = getDB();
    $db->exec("
        UPDATE clients SET statut =
            CASE
                WHEN date_fin < CURDATE()                       THEN 'expire'
                WHEN date_fin <= CURDATE() + INTERVAL 3 DAY    THEN 'expire_bientot'
                ELSE 'actif'
            END
    ");
}

// ============================================================
// Récupère les statistiques pour le tableau de bord
// ============================================================
function getStatistiques(): array {
    $db = getDB();

    $stats = [];

    // Total clients
    $stats['total']           = $db->query("SELECT COUNT(*) FROM clients")->fetchColumn();

    // Abonnements actifs
    $stats['actifs']          = $db->query("SELECT COUNT(*) FROM clients WHERE statut = 'actif'")->fetchColumn();

    // Expirés
    $stats['expires']         = $db->query("SELECT COUNT(*) FROM clients WHERE statut = 'expire'")->fetchColumn();

    // Expire bientôt
    $stats['expire_bientot']  = $db->query("SELECT COUNT(*) FROM clients WHERE statut = 'expire_bientot'")->fetchColumn();

    // Revenus estimés (clients actifs + expire bientôt)
    $stats['revenus']         = $db->query("SELECT COALESCE(SUM(prix), 0) FROM clients WHERE statut IN ('actif','expire_bientot')")->fetchColumn();

    // Nouveaux ce mois
    $stats['nouveaux_mois']   = $db->query("SELECT COUNT(*) FROM clients WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetchColumn();

    return $stats;
}

// ============================================================
// Récupère les clients qui expirent bientôt (alertes)
// ============================================================
function getAlertes(): array {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT * FROM clients
        WHERE statut = 'expire_bientot'
        ORDER BY date_fin ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

// ============================================================
// Récupère tous les clients avec pagination optionnelle
// ============================================================
function getClients(string $search = '', string $filtre = ''): array {
    $db = getDB();

    $sql    = "SELECT * FROM clients WHERE 1=1";
    $params = [];

    // Recherche par nom ou téléphone
    if ($search) {
        $sql     .= " AND (nom LIKE ? OR telephone LIKE ? OR service LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    // Filtre par statut
    if ($filtre && in_array($filtre, ['actif','expire','expire_bientot'])) {
        $sql     .= " AND statut = ?";
        $params[] = $filtre;
    }

    $sql .= " ORDER BY date_fin ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// ============================================================
// Récupère un client par son ID
// ============================================================
function getClientById(int $id): array|false {
    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// ============================================================
// Ajoute un nouveau client
// ============================================================
function ajouterClient(array $data): bool {
    $db = getDB();

    // Calcul automatique de la date de fin (+30 jours)
    $date_fin = date('Y-m-d', strtotime($data['date_debut'] . ' +30 days'));

    // Calcul du statut initial
    $statut = calculerStatut($data['date_debut'], $date_fin);

    $stmt = $db->prepare("
        INSERT INTO clients (nom, telephone, service, offre, prix, date_debut, date_fin, statut)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    return $stmt->execute([
        nettoyerChaine($data['nom']),
        nettoyerChaine($data['telephone']),
        nettoyerChaine($data['service']),
        nettoyerChaine($data['offre']),
        (float) $data['prix'],
        $data['date_debut'],
        $date_fin,
        $statut,
    ]);
}

// ============================================================
// Modifie un client existant
// ============================================================
function modifierClient(int $id, array $data): bool {
    $db = getDB();

    $date_fin = date('Y-m-d', strtotime($data['date_debut'] . ' +30 days'));
    $statut   = calculerStatut($data['date_debut'], $date_fin);

    $stmt = $db->prepare("
        UPDATE clients
        SET nom=?, telephone=?, service=?, offre=?, prix=?, date_debut=?, date_fin=?, statut=?
        WHERE id=?
    ");

    return $stmt->execute([
        nettoyerChaine($data['nom']),
        nettoyerChaine($data['telephone']),
        nettoyerChaine($data['service']),
        nettoyerChaine($data['offre']),
        (float) $data['prix'],
        $data['date_debut'],
        $date_fin,
        $statut,
        $id,
    ]);
}

// ============================================================
// Supprime un client par ID
// ============================================================
function supprimerClient(int $id): bool {
    $db   = getDB();
    $stmt = $db->prepare("DELETE FROM clients WHERE id = ?");
    return $stmt->execute([$id]);
}

// ============================================================
// Calcule le statut selon les dates
// ============================================================
function calculerStatut(string $date_debut, string $date_fin): string {
    $aujourd_hui = new DateTime();
    $fin         = new DateTime($date_fin);
    $diff        = (int) $aujourd_hui->diff($fin)->format('%r%a'); // Négatif si passé

    if ($diff < 0)              return 'expire';
    if ($diff <= DELAI_ALERTE)  return 'expire_bientot';
    return 'actif';
}

// ============================================================
// Nettoie une chaîne (XSS basique)
// ============================================================
function nettoyerChaine(string $str): string {
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}

// ============================================================
// Formate un prix en FCFA
// ============================================================
function formatPrix(float $prix): string {
    return number_format($prix, 0, ',', ' ') . ' FCFA';
}

// ============================================================
// Retourne le badge HTML selon le statut
// ============================================================
function badgeStatut(string $statut): string {
    return match($statut) {
        'actif'           => '<span class="badge badge-actif">✓ Actif</span>',
        'expire'          => '<span class="badge badge-expire">✗ Expiré</span>',
        'expire_bientot'  => '<span class="badge badge-alerte">⚡ Bientôt</span>',
        default           => '<span class="badge">Inconnu</span>',
    };
}

// ============================================================
// Retourne le nombre de jours restants formaté
// ============================================================
function joursRestants(string $date_fin): string {
    $aujourd_hui = new DateTime();
    $fin         = new DateTime($date_fin);
    $diff        = (int) $aujourd_hui->diff($fin)->format('%r%a');

    if ($diff < 0)  return abs($diff) . 'j expiré';
    if ($diff == 0) return "Aujourd'hui";
    return $diff . ' jours';
}
