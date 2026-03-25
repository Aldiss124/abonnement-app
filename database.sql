-- ============================================================
-- SCRIPT SQL COMPLET - Application de Gestion d'Abonnements
-- Base de données : abonnement_db
-- ============================================================

-- Création et sélection de la base de données
CREATE DATABASE IF NOT EXISTS abonnement_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE abonnement_db;

-- ============================================================
-- TABLE : admins
-- Stocke les comptes administrateurs
-- ============================================================
CREATE TABLE IF NOT EXISTS admins (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,       -- Mot de passe hashé (bcrypt)
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE : clients
-- Stocke les clients et leurs abonnements
-- ============================================================
CREATE TABLE IF NOT EXISTS clients (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(150) NOT NULL,
    telephone   VARCHAR(30)  NOT NULL,
    service     VARCHAR(100) NOT NULL,       -- Ex: Netflix, Spotify, Canal+
    offre       VARCHAR(100) NOT NULL,       -- Ex: Premium, Standard, Basic
    prix        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    date_debut  DATE NOT NULL,
    date_fin    DATE NOT NULL,               -- Calculé automatiquement (+30 jours)
    statut      ENUM('actif','expire','expire_bientot') NOT NULL DEFAULT 'actif',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DONNÉES DE TEST : Admin
-- Mot de passe : admin123  (hashé avec password_hash PHP)
-- ============================================================
INSERT INTO admins (username, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ============================================================
-- DONNÉES DE TEST : Clients
-- ============================================================
INSERT INTO clients (nom, telephone, service, offre, prix, date_debut, date_fin, statut) VALUES
('Jean Dupont',       '+237 6XX XXX 001', 'Netflix',   'Premium',  15000, CURDATE() - INTERVAL 5  DAY, CURDATE() + INTERVAL 25 DAY, 'actif'),
('Marie Nguema',      '+237 6XX XXX 002', 'Spotify',   'Family',    8000, CURDATE() - INTERVAL 10 DAY, CURDATE() + INTERVAL 20 DAY, 'actif'),
('Paul Biya Jr',      '+237 6XX XXX 003', 'Canal+',    'Essentiel',12000, CURDATE() - INTERVAL 28 DAY, CURDATE() + INTERVAL 2  DAY, 'expire_bientot'),
('Sandrine Mballa',   '+237 6XX XXX 004', 'Amazon',    'Prime',     9500, CURDATE() - INTERVAL 35 DAY, CURDATE() - INTERVAL 5  DAY, 'expire'),
('Alain Fotso',       '+237 6XX XXX 005', 'Netflix',   'Standard', 10000, CURDATE() - INTERVAL 3  DAY, CURDATE() + INTERVAL 27 DAY, 'actif'),
('Claire Eto',        '+237 6XX XXX 006', 'Disney+',   'Basic',     7500, CURDATE() - INTERVAL 27 DAY, CURDATE() + INTERVAL 3  DAY, 'expire_bientot'),
('Robert Kamga',      '+237 6XX XXX 007', 'Spotify',   'Premium',   6000, CURDATE() - INTERVAL 40 DAY, CURDATE() - INTERVAL 10 DAY, 'expire'),
('Fatima Moussa',     '+237 6XX XXX 008', 'Canal+',    'Integral', 18000, CURDATE() - INTERVAL 2  DAY, CURDATE() + INTERVAL 28 DAY, 'actif'),
('Eric Nkoulou',      '+237 6XX XXX 009', 'Apple TV+', 'Standard',  5000, CURDATE() - INTERVAL 15 DAY, CURDATE() + INTERVAL 15 DAY, 'actif'),
('Brigitte Abanda',   '+237 6XX XXX 010', 'Netflix',   'Premium',  15000, CURDATE() - INTERVAL 29 DAY, CURDATE() + INTERVAL 1  DAY, 'expire_bientot');

-- ============================================================
-- VUE : Mise à jour automatique des statuts
-- Utilisée pour rafraîchir les statuts à chaque connexion
-- ============================================================
CREATE OR REPLACE VIEW v_statuts AS
SELECT
    id,
    nom,
    CASE
        WHEN date_fin < CURDATE()                        THEN 'expire'
        WHEN date_fin <= CURDATE() + INTERVAL 3 DAY     THEN 'expire_bientot'
        ELSE                                                  'actif'
    END AS statut_calcule
FROM clients;
