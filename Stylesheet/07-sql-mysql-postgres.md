# SQL — MySQL vs PostgreSQL : Script complet avec exemples

> Ce fichier couvre le SQL "de base à avancé" avec, à chaque fois qu'il y a une différence, la syntaxe **MySQL** et la syntaxe **PostgreSQL** côte à côte.

---

## 1. Créer / supprimer une base de données

```sql
-- MySQL
CREATE DATABASE ecopanier;
DROP DATABASE ecopanier;
USE ecopanier;

-- PostgreSQL
CREATE DATABASE ecopanier;
DROP DATABASE ecopanier;
-- Pas de USE : on se connecte à la base directement (\c ecopanier dans psql)
```

---

## 2. Types de données — équivalences courantes

| Besoin                     | MySQL                     | PostgreSQL                |
|------------------------------|----------------------------|------------------------------|
| Entier auto-incrémenté          | `INT AUTO_INCREMENT`         | `SERIAL` ou `INT GENERATED ALWAYS AS IDENTITY` |
| Grand entier auto-incrémenté      | `BIGINT AUTO_INCREMENT`        | `BIGSERIAL`                    |
| Texte court                          | `VARCHAR(100)`                    | `VARCHAR(100)`                     |
| Texte long                              | `TEXT`                               | `TEXT`                                |
| Nombre décimal exact                       | `DECIMAL(10,2)`                         | `NUMERIC(10,2)`                          |
| Booléen                                       | `TINYINT(1)` ou `BOOLEAN`                  | `BOOLEAN`                                   |
| Date seule                                       | `DATE`                                        | `DATE`                                         |
| Date + heure                                        | `DATETIME`                                       | `TIMESTAMP`                                       |
| Horodatage auto (création)                             | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`               | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP` / `NOW()`      |
| JSON                                                       | `JSON`                                                 | `JSON` ou `JSONB` (recommandé, indexable)               |
| Énumération                                                    | `ENUM('actif','inactif')`                                  | Pas de ENUM natif simple → `VARCHAR` + `CHECK`, ou type `CREATE TYPE` |

---

## 3. CREATE TABLE — avec toutes les contraintes

```sql
-- MySQL
CREATE TABLE produits (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nom           VARCHAR(100) NOT NULL,
    prix          DECIMAL(10,2) NOT NULL DEFAULT 0,
    stock         INT NOT NULL DEFAULT 0,
    categorie_id  INT,
    actif         BOOLEAN NOT NULL DEFAULT TRUE,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_categorie
        FOREIGN KEY (categorie_id) REFERENCES categories(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT chk_prix CHECK (prix >= 0)
);

-- PostgreSQL
CREATE TABLE produits (
    id            SERIAL PRIMARY KEY,
    nom           VARCHAR(100) NOT NULL,
    prix          NUMERIC(10,2) NOT NULL DEFAULT 0,
    stock         INT NOT NULL DEFAULT 0,
    categorie_id  INT,
    actif         BOOLEAN NOT NULL DEFAULT TRUE,
    created_at    TIMESTAMP DEFAULT NOW(),
    updated_at    TIMESTAMP DEFAULT NOW(),

    CONSTRAINT fk_categorie
        FOREIGN KEY (categorie_id) REFERENCES categories(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT chk_prix CHECK (prix >= 0)
);
```

> **Piège** : PostgreSQL n'a pas de `ON UPDATE CURRENT_TIMESTAMP` natif. Il faut un **trigger** (voir section 12) pour mettre à jour `updated_at` automatiquement.

---

## 4. INSERT

```sql
-- Un seul enregistrement (MySQL et PostgreSQL identiques)
INSERT INTO produits (nom, prix, stock, categorie_id)
VALUES ('Riz local', 3500, 100, 1);

-- Plusieurs enregistrements d'un coup
INSERT INTO produits (nom, prix, stock, categorie_id) VALUES
('Riz local', 3500, 100, 1),
('Huile', 8000, 50, 2),
('Sucre', 4000, 80, 2);
```

### Récupérer l'ID généré après un INSERT

```sql
-- MySQL
SELECT LAST_INSERT_ID();

-- PostgreSQL
INSERT INTO produits (nom, prix) VALUES ('Riz local', 3500)
RETURNING id;
```

---

## 5. SELECT — toutes les variantes utiles

```sql
-- Sélection simple
SELECT * FROM produits;
SELECT nom, prix FROM produits;

-- Avec condition
SELECT * FROM produits WHERE prix > 3000;

-- Plusieurs conditions
SELECT * FROM produits WHERE prix > 3000 AND stock > 0;
SELECT * FROM produits WHERE categorie_id = 1 OR categorie_id = 2;

-- IN / NOT IN
SELECT * FROM produits WHERE categorie_id IN (1, 2, 3);

-- BETWEEN
SELECT * FROM produits WHERE prix BETWEEN 1000 AND 5000;

-- LIKE (recherche texte)
SELECT * FROM produits WHERE nom LIKE '%riz%';   -- contient "riz"
SELECT * FROM produits WHERE nom LIKE 'riz%';    -- commence par "riz"

-- IS NULL / IS NOT NULL
SELECT * FROM produits WHERE categorie_id IS NULL;

-- Tri
SELECT * FROM produits ORDER BY prix DESC;
SELECT * FROM produits ORDER BY categorie_id ASC, prix DESC;

-- Limiter le nombre de résultats
-- MySQL et PostgreSQL : syntaxe identique
SELECT * FROM produits LIMIT 10;
SELECT * FROM produits LIMIT 10 OFFSET 20; -- pagination (page 3 si 10/page)

-- Éliminer les doublons
SELECT DISTINCT categorie_id FROM produits;

-- Alias
SELECT nom AS nom_produit, prix AS prix_unitaire FROM produits;
```

---

## 6. Fonctions d'agrégation & GROUP BY / HAVING

```sql
SELECT COUNT(*) FROM produits;
SELECT SUM(stock) FROM produits;
SELECT AVG(prix) FROM produits;
SELECT MIN(prix), MAX(prix) FROM produits;

-- Grouper par catégorie
SELECT categorie_id, COUNT(*) AS total, SUM(stock) AS stock_total
FROM produits
GROUP BY categorie_id;

-- HAVING : filtrer APRÈS le regroupement (WHERE ne peut pas filtrer sur une agrégation)
SELECT categorie_id, COUNT(*) AS total
FROM produits
GROUP BY categorie_id
HAVING COUNT(*) > 5;
```

> **Piège classique d'examen** : `WHERE COUNT(*) > 5` est **interdit**. Il faut `HAVING`.

---

## 7. JOIN — toutes les variantes

```sql
-- INNER JOIN : uniquement les lignes qui correspondent des deux côtés
SELECT p.nom, c.nom AS categorie
FROM produits p
INNER JOIN categories c ON p.categorie_id = c.id;

-- LEFT JOIN : toutes les lignes de "produits", même sans catégorie correspondante
SELECT p.nom, c.nom AS categorie
FROM produits p
LEFT JOIN categories c ON p.categorie_id = c.id;

-- RIGHT JOIN : toutes les lignes de "categories", même sans produit
SELECT p.nom, c.nom AS categorie
FROM produits p
RIGHT JOIN categories c ON p.categorie_id = c.id;

-- FULL OUTER JOIN
-- MySQL : PAS supporté nativement (il faut simuler avec UNION de LEFT et RIGHT JOIN)
-- PostgreSQL : supporté directement
SELECT p.nom, c.nom AS categorie
FROM produits p
FULL OUTER JOIN categories c ON p.categorie_id = c.id;

-- Simulation FULL OUTER JOIN en MySQL
SELECT p.nom, c.nom AS categorie FROM produits p LEFT JOIN categories c ON p.categorie_id = c.id
UNION
SELECT p.nom, c.nom AS categorie FROM produits p RIGHT JOIN categories c ON p.categorie_id = c.id;
```

---

## 8. Sous-requêtes (subqueries)

```sql
-- Sous-requête dans WHERE
SELECT * FROM produits
WHERE categorie_id IN (SELECT id FROM categories WHERE nom = 'Alimentation');

-- Sous-requête dans FROM
SELECT categorie_id, moyenne
FROM (
    SELECT categorie_id, AVG(prix) AS moyenne
    FROM produits
    GROUP BY categorie_id
) AS sous_requete
WHERE moyenne > 2000;

-- EXISTS
SELECT nom FROM categories c
WHERE EXISTS (SELECT 1 FROM produits p WHERE p.categorie_id = c.id);
```

---

## 9. UPDATE

```sql
UPDATE produits
SET prix = 4000
WHERE id = 5;

-- Plusieurs colonnes
UPDATE produits
SET prix = prix * 1.1, updated_at = NOW()
WHERE categorie_id = 2;

-- UPDATE avec une jointure
-- MySQL
UPDATE produits p
INNER JOIN categories c ON p.categorie_id = c.id
SET p.prix = p.prix * 0.9
WHERE c.nom = 'Promotion';

-- PostgreSQL
UPDATE produits p
SET prix = p.prix * 0.9
FROM categories c
WHERE p.categorie_id = c.id AND c.nom = 'Promotion';
```

---

## 10. DELETE

```sql
DELETE FROM produits WHERE id = 5;
DELETE FROM produits WHERE stock = 0;

-- Vider complètement une table (plus rapide qu'un DELETE sans condition,
-- réinitialise aussi l'auto-incrément)
TRUNCATE TABLE produits;
```

> **Piège** : `DELETE FROM produits;` **sans WHERE** supprime TOUTES les lignes une par une (déclenche les triggers). `TRUNCATE` est plus rapide mais ne peut pas être filtré et réinitialise l'ID auto-incrémenté.

---

## 11. ALTER TABLE / DROP

```sql
-- Ajouter une colonne
ALTER TABLE produits ADD COLUMN description TEXT;

-- MySQL : positionner la colonne
ALTER TABLE produits ADD COLUMN description TEXT AFTER nom;
-- PostgreSQL : pas d'AFTER, l'ordre des colonnes n'est pas modifiable directement

-- Modifier une colonne
-- MySQL
ALTER TABLE produits MODIFY COLUMN prix DECIMAL(12,2) NOT NULL;
-- PostgreSQL
ALTER TABLE produits ALTER COLUMN prix TYPE NUMERIC(12,2);
ALTER TABLE produits ALTER COLUMN prix SET NOT NULL;

-- Renommer une colonne (identique dans les deux)
ALTER TABLE produits RENAME COLUMN nom TO nom_produit;

-- Supprimer une colonne
ALTER TABLE produits DROP COLUMN description;

-- Renommer une table
-- MySQL
RENAME TABLE produits TO produits_archives;
-- PostgreSQL
ALTER TABLE produits RENAME TO produits_archives;

-- Supprimer une table
DROP TABLE produits;
DROP TABLE IF EXISTS produits; -- évite l'erreur si la table n'existe pas
```

---

## 12. Index, contraintes UNIQUE, triggers

```sql
-- Créer un index (accélère les recherches sur une colonne)
CREATE INDEX idx_produits_nom ON produits(nom);

-- Contrainte UNIQUE
ALTER TABLE produits ADD CONSTRAINT uq_nom UNIQUE (nom);

-- Trigger pour mettre à jour updated_at automatiquement en PostgreSQL
CREATE OR REPLACE FUNCTION maj_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_produits_updated_at
BEFORE UPDATE ON produits
FOR EACH ROW
EXECUTE FUNCTION maj_updated_at();
```

---

## 13. UPSERT (insérer ou mettre à jour si le doublon existe)

```sql
-- MySQL
INSERT INTO produits (id, nom, prix) VALUES (5, 'Riz local', 3800)
ON DUPLICATE KEY UPDATE prix = 3800;

-- PostgreSQL
INSERT INTO produits (id, nom, prix) VALUES (5, 'Riz local', 3800)
ON CONFLICT (id) DO UPDATE SET prix = EXCLUDED.prix;
```

---

## 14. Transactions (identiques dans les deux SGBD)

```sql
START TRANSACTION; -- ou BEGIN;

UPDATE comptes SET solde = solde - 1000 WHERE id = 1;
UPDATE comptes SET solde = solde + 1000 WHERE id = 2;

COMMIT;
-- En cas de problème : ROLLBACK;
```

---

## 15. Vues (VIEW)

```sql
CREATE VIEW vue_produits_disponibles AS
SELECT * FROM produits WHERE stock > 0;

SELECT * FROM vue_produits_disponibles;

DROP VIEW vue_produits_disponibles;
```

---

## 16. Fonctions utiles fréquemment demandées en examen

| Besoin                          | MySQL                          | PostgreSQL                     |
|-----------------------------------|-----------------------------------|-----------------------------------|
| Concaténer des chaînes               | `CONCAT(nom, ' - ', prix)`           | `nom \|\| ' - ' \|\| prix`             |
| Date/heure actuelle                     | `NOW()`                                 | `NOW()`                                 |
| Extraire l'année                            | `YEAR(created_at)`                          | `EXTRACT(YEAR FROM created_at)`             |
| Longueur d'une chaîne                            | `LENGTH(nom)`                                    | `LENGTH(nom)`                                    |
| Valeur si NULL                                       | `IFNULL(stock, 0)`                                    | `COALESCE(stock, 0)`                                  |
| Condition en ligne                                        | `IF(stock > 0, 'dispo', 'rupture')`                       | `CASE WHEN stock > 0 THEN 'dispo' ELSE 'rupture' END`     |
| Auto-incrément                                                | `AUTO_INCREMENT`                                                 | `SERIAL` / séquence                                                |

`COALESCE` fonctionne aussi en MySQL (norme SQL standard) — à privilégier si tu veux un code portable.

---

## 17. Erreurs fréquentes à l'examen

1. **`WHERE` avec une fonction d'agrégation** au lieu de `HAVING`.
2. **Oublier `IF EXISTS`** avant un `DROP` → erreur si l'objet n'existe pas déjà.
3. **`DELETE` sans `WHERE`** → suppression totale accidentelle.
4. **Confondre `DECIMAL`/`NUMERIC`** (équivalents) avec `FLOAT`/`DOUBLE` (imprécis, à éviter pour de l'argent).
5. **Oublier `ON DELETE`/`ON UPDATE`** sur une clé étrangère → comportement par défaut (`RESTRICT`) qui bloque les suppressions en cascade attendues.
6. **PostgreSQL : oublier les guillemets doubles** pour les noms de colonnes/tables en majuscules ou avec espaces (`"Nom"` ≠ `nom`), alors que MySQL est plus tolérant par défaut.
7. **Utiliser `AUTO_INCREMENT` en PostgreSQL** → erreur de syntaxe, il faut `SERIAL`/`BIGSERIAL`/`GENERATED ALWAYS AS IDENTITY`.
