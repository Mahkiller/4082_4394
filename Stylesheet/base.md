
-- Ouvrir SQLite avec la base
sqlite3 exam_S4_design_4082_4394.db

-- Exécuter le script complet
sqlite> .read base.sql

-- Vérifier les tables
sqlite> .tables

-- Quitter
sqlite> .exit


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
├── base.sql # Script complet (création + données)
├── base.md # Ce fichier
└── spark


---

## 🗄️ Installation de la base de données

### 1. Création de la base et insertion des données

Le fichier `base.sql` contient **tout en un seul fichier** :

- ✅ Création des tables
- ✅ Création des triggers
- ✅ Création des index
- ✅ Insertion des données de test
- ✅ Vues SQL

#### Méthode 1 : Depuis PowerShell

```powershell
# Créer la base et exécuter le script complet
sqlite3 exam_S4_design_4082_4394.db < base.sql

-- Ouvrir SQLite avec la base
sqlite3 exam_S4_design_4082_4394.db

-- Exécuter le script complet
sqlite> .read base.sql

-- Vérifier les tables
sqlite> .tables

-- Quitter
sqlite> .exit

# PowerShell
Get-Content base.sql | sqlite3 exam_S4_design_4082_4394.db

-- Ouvrir la base
sqlite3 exam_S4_design_4082_4394.db

-- Activer l'affichage
sqlite> .headers on
sqlite> .mode column

-- Vérifier toutes les tables
sqlite> SELECT name FROM sqlite_master WHERE type='table';

-- Compter les lignes par table
sqlite> SELECT
    (SELECT COUNT(*) FROM operateur) AS operateurs,
    (SELECT COUNT(*) FROM operateur_prefixe) AS prefixes,
    (SELECT COUNT(*) FROM transaction_type) AS types_transaction,
    (SELECT COUNT(*) FROM montant) AS baremes,
    (SELECT COUNT(*) FROM clients) AS clients,
    (SELECT COUNT(*) FROM transactions) AS transactions;

-- Afficher les 5 plus gros soldes
sqlite> SELECT numero, solde FROM clients ORDER BY solde DESC LIMIT 5;

-- Afficher tous les clients
sqlite> SELECT * FROM clients;

-- Statistiques des transactions par type
sqlite> SELECT
    tt.label,
    COUNT(*) AS nb_transactions,
    SUM(montant) AS total_montant,
    SUM(frais_applique) AS total_frais
FROM transactions t
JOIN transaction_type tt ON t.transaction_type_id = tt.id
WHERE t.status = 'Reussi'
GROUP BY tt.label;

-- Voir les clients avec leur opérateur
sqlite> SELECT 
    c.id,
    c.numero,
    c.solde,
    o.nom AS operateur,
    op.prefixe
FROM clients c
JOIN operateur_prefixe op ON c.numero LIKE op.prefixe || '%'
JOIN operateur o ON op.operateur_id = o.id
ORDER BY o.nom, c.numero;
```
