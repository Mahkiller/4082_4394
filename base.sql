-- Création de la base
CREATE DATABASE IF NOT EXISTS exam_S4_design_4082_4394;
USE exam_S4_design_4082_4394;

-- Table operateur
CREATE TABLE IF NOT EXISTS operateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP DEFAULT NULL
);

-- Table prefixe numero
CREATE TABLE IF NOT EXISTS operateur_prefixe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    operateur_id INT NOT NULL,
    prefixe VARCHAR(10) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (operateur_id) REFERENCES operateur(id)
);

-- Table type de transaction
CREATE TABLE IF NOT EXISTS transaction_type (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    label VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table montant (barème des frais)
CREATE TABLE IF NOT EXISTS montant (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_type_id INT NOT NULL,
    min_montant DECIMAL(15,2) NOT NULL,
    max_montant DECIMAL(15,2) NOT NULL,
    frais_montant DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_type_id) REFERENCES transaction_type(id),
    CONSTRAINT chk_min_max CHECK (min_montant <= max_montant)
);

-- Table Client
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(20) UNIQUE NOT NULL,
    solde DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    deleted_at TIMESTAMP DEFAULT NULL
);

-- Table transaction
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    transaction_type_id INT NOT NULL,
    montant DECIMAL(15,2) NOT NULL,
    frais_applique DECIMAL(15,2) NOT NULL,
    montant_net DECIMAL(15,2) NOT NULL,
    reference VARCHAR(50) UNIQUE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'En attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (transaction_type_id) REFERENCES transaction_type(id),
    CONSTRAINT chk_status CHECK (status IN ('En attente', 'Reussi', 'Echoue'))
);

-- Index pour optimiser les recherches
CREATE INDEX idx_transaction_client ON transactions(client_id);
CREATE INDEX idx_transaction_created ON transactions(created_at);
CREATE INDEX idx_montant_type ON montant(transaction_type_id);