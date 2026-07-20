

# Guide d'installation et de configuration - Application Mobile Money

## 📋 Informations générales

- **Projet** : Application Mobile Money
- **Équipe** : 4082_4394
- **Année** : 2026
- **Technologies** : PHP 8.x, CodeIgniter 4, SQLite 3, HTML5, CSS3, JavaScript

---

## 🚀 Prérequis

- PHP 8.0 ou supérieur
- Composer
- SQLite 3
- Git
- Visual Studio Code (ou tout autre éditeur)

---

## 📂 Structure du projet

4082_4394/
├── app/
│ ├── Config/
│ ├── Controllers/
│ ├── Database/
│ ├── Models/
│ └── Views/
├── public/
├── writable/
├── .env # Configuration (ignoré par Git)
├── .env.example # Template de configuration
├── .gitignore # Fichiers ignorés par Git
├── base.sql # Script de création de la base
├── donne.sql # Script d'insertion des données
├── base.md # Ce fichier
└── spark


---

## 🗄️ Installation de la base de données

### 1. Création de la base

```powershell
# Ouvrir SQLite avec la base
sqlite3 exam_S4_design_4082_4394.db

# Exécuter le script de création
sqlite> .read base.sql

# Vérifier les tables
sqlite> .tables

# Quitter
sqlite> .exit
```


# Ouvrir SQLite avec la base

sqlite3 exam_S4_design_4082_4394.db

# Exécuter le script d'insertion

sqlite> .read donne.sql

# Vérifier les données

sqlite> SELECT COUNT(*) FROM clients;        -- 18 clients
sqlite> SELECT COUNT(*) FROM transactions;   -- 17 transactions
sqlite> SELECT COUNT(*) FROM operateur;      -- 3 opérateurs

# Quitter

sqlite> .exit



-- Vérifier toutes les tables
SELECT name FROM sqlite_master WHERE type='table';

-- Compter les lignes par table
SELECT
    (SELECT COUNT(*) FROM operateur) AS operateurs,
    (SELECT COUNT(*) FROM operateur_prefixe) AS prefixes,
    (SELECT COUNT(*) FROM transaction_type) AS types_transaction,
    (SELECT COUNT(*) FROM montant) AS baremes,
    (SELECT COUNT(*) FROM clients) AS clients,
    (SELECT COUNT(*) FROM transactions) AS transactions;

-- Afficher les 5 plus gros soldes
SELECT numero, solde FROM clients ORDER BY solde DESC LIMIT 5;

-- Statistiques des transactions par type
SELECT
    tt.label,
    COUNT(*) AS nb_transactions,
    SUM(montant) AS total_montant,
    SUM(frais_applique) AS total_frais
FROM transactions t
JOIN transaction_type tt ON t.transaction_type_id = tt.id
WHERE t.status = 'Reussi'
GROUP BY tt.label;
