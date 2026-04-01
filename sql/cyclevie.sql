-- Base de données CYCLEVIE
CREATE DATABASE IF NOT EXISTS cyclevie CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cyclevie;

-- Table utilisateurs
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('etudiant', 'admin') DEFAULT 'etudiant',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table équipements
CREATE TABLE equipements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    type ENUM('switch', 'routeur', 'serveur', 'firewall', 'autre') NOT NULL,
    numero_serie VARCHAR(100),
    date_achat DATE,
    etat ENUM('neuf', 'en_service', 'en_maintenance', 'hors_service', 'mis_au_rebut') DEFAULT 'neuf',
    utilisateur_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- Table historique cycle de vie
CREATE TABLE historique (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipement_id INT NOT NULL,
    ancien_etat VARCHAR(50),
    nouvel_etat VARCHAR(50) NOT NULL,
    commentaire TEXT,
    date_changement DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (equipement_id) REFERENCES equipements(id) ON DELETE CASCADE
);

-- Table notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    message TEXT NOT NULL,
    lu TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- Compte admin par défaut (mot de passe: admin1234)
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role)
VALUES ('Admin', 'System', 'admin@cyclevie.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
