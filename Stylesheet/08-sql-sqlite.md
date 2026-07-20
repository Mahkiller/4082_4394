# SQL — SQLite : Script complet avec exemples

> SQLite a son propre fonctionnement, assez différent de MySQL/PostgreSQL sur plusieurs points (typage, ALTER TABLE limité, clés étrangères désactivées par défaut...). Ce fichier suit le même plan que la fiche MySQL/PostgreSQL pour que tu puisses comparer facilement.

---

## 1. Base de données

SQLite n'a **pas de serveur** : une base = **un simple fichier** (souvent `.sqlite` ou `.db`).

```bash
sqlite3 ecopanier.db
```

Pas de `CREATE DATABASE` ni `USE` : le fichier EST la base. Se connecter au fichier suffit.

```sql
.open ecopanier.db     -- dans le shell sqlite3
.tables                -- lister les tables
.schema produits        -- voir la structure d'une table
```

---

## 2. Typage — particularité importante de SQLite

SQLite utilise un système de **typage dynamique** ("type affinity"). Contrairement à MySQL/PostgreSQL, il accepte presque n'importe quelle valeur dans n'importe quelle colonne. Il n'y a que **5 classes de stockage** :

| Classe SQLite | Équivalent utilisé habituellement       |
|-----------------|---------------------------------------------|
| `NULL`             | valeur nulle                                   |
| `INTEGER`             | entiers                                          |
| `REAL`                  | nombres à virgule flottante                        |
| `TEXT`                    | chaînes de caractères                                |
| `BLOB`                      | données binaires                                        |

> **Piège d'examen** : écrire `VARCHAR(100)`, `DECIMAL(10,2)`, `BOOLEAN` fonctionne en SQLite (il les accepte par tolérance), mais SQLite les convertit en interne vers une de ses 5 classes. `BOOLEAN` devient en réalité `INTEGER` (0 ou 1), `DECIMAL` devient `NUMERIC`/`REAL`.

---

## 3. CREATE TABLE

```sql
CREATE TABLE produits (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    nom           TEXT NOT NULL,
    prix          NUMERIC NOT NULL DEFAULT 0,
    stock         INTEGER NOT NULL DEFAULT 0,
    categorie_id  INTEGER,
    actif         INTEGER NOT NULL DEFAULT 1,       -- 0 = faux, 1 = vrai (pas de vrai BOOLEAN)
    created_at    TEXT DEFAULT CURRENT_TIMESTAMP,   -- pas de type DATETIME natif, stocké en TEXT

    FOREIGN KEY (categorie_id) REFERENCES categories(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CHECK (prix >= 0)
);
```

> **Important** : les clés étrangères sont **désactivées par défaut** en SQLite ! Il faut les activer à chaque connexion :
```sql
PRAGMA foreign_keys = ON;
```

### `INTEGER PRIMARY KEY AUTOINCREMENT`

- `INTEGER PRIMARY KEY` seul suffit déjà à créer un ID auto-incrémenté (alias du `ROWID` interne de SQLite).
- `AUTOINCREMENT` (en un seul mot, sans underscore) garantit en plus que les ID supprimés **ne sont jamais réutilisés**. Sans lui, SQLite peut réutiliser un ID libéré.

```sql
-- Suffisant dans la majorité des cas
id INTEGER PRIMARY KEY

-- Garantit des ID toujours croissants, jamais réutilisés
id INTEGER PRIMARY KEY AUTOINCREMENT
```

---

## 4. INSERT

```sql
INSERT INTO produits (nom, prix, stock, categorie_id)
VALUES ('Riz local', 3500, 100, 1);

-- Plusieurs lignes
INSERT INTO produits (nom, prix, stock) VALUES
('Riz local', 3500, 100),
('Huile', 8000, 50),
('Sucre', 4000, 80);

-- Récupérer le dernier ID inséré
SELECT last_insert_rowid();
```

---

## 5. SELECT — mêmes bases que MySQL/PostgreSQL

```sql
SELECT * FROM produits;
SELECT nom, prix FROM produits WHERE prix > 3000;
SELECT * FROM produits WHERE categorie_id IN (1, 2, 3);
SELECT * FROM produits WHERE nom LIKE '%riz%';
SELECT * FROM produits ORDER BY prix DESC;
SELECT * FROM produits LIMIT 10 OFFSET 20;
SELECT DISTINCT categorie_id FROM produits;
```

---

## 6. GROUP BY / HAVING / agrégations

```sql
SELECT COUNT(*) FROM produits;
SELECT AVG(prix) FROM produits;

SELECT categorie_id, COUNT(*) AS total
FROM produits
GROUP BY categorie_id
HAVING COUNT(*) > 5;
```

---

## 7. JOIN

```sql
-- INNER JOIN
SELECT p.nom, c.nom AS categorie
FROM produits p
INNER JOIN categories c ON p.categorie_id = c.id;

-- LEFT JOIN
SELECT p.nom, c.nom AS categorie
FROM produits p
LEFT JOIN categories c ON p.categorie_id = c.id;
```

> **Piège** : SQLite ne supporte **ni `RIGHT JOIN` ni `FULL OUTER JOIN`** (avant la version 3.39 — et même après, la prise en charge dépend de la compilation). En pratique pour un examen, considère qu'ils **n'existent pas** : il faut inverser les tables pour simuler un `RIGHT JOIN` avec un `LEFT JOIN`.

```sql
-- Simuler un RIGHT JOIN (produits ↔ categories) en inversant l'ordre
SELECT p.nom, c.nom AS categorie
FROM categories c
LEFT JOIN produits p ON p.categorie_id = c.id;
```

---

## 8. UPDATE

```sql
UPDATE produits SET prix = 4000 WHERE id = 5;

UPDATE produits
SET prix = prix * 1.1
WHERE categorie_id = 2;
```

> SQLite ne supporte pas `UPDATE ... JOIN` comme MySQL. Il faut passer par une sous-requête :

```sql
UPDATE produits
SET prix = prix * 0.9
WHERE categorie_id IN (
    SELECT id FROM categories WHERE nom = 'Promotion'
);
```

---

## 9. DELETE

```sql
DELETE FROM produits WHERE id = 5;
DELETE FROM produits WHERE stock = 0;

-- Vider une table (réinitialise aussi le compteur AUTOINCREMENT si utilisé)
DELETE FROM produits; -- SQLite n'a pas de TRUNCATE, DELETE sans WHERE fait le même effet
```

> SQLite n'a **pas de `TRUNCATE TABLE`**. `DELETE FROM table;` (sans `WHERE`) est l'équivalent, et SQLite l'optimise automatiquement en interne (mode "truncate optimization").

---

## 10. ALTER TABLE — très limité en SQLite

C'est le point le plus important à retenir pour un examen : **SQLite ne permet quasiment pas de modifier une table existante** comme MySQL/PostgreSQL.

```sql
-- Fonctionne : ajouter une colonne
ALTER TABLE produits ADD COLUMN description TEXT;

-- Fonctionne (depuis SQLite 3.25+) : renommer une colonne
ALTER TABLE produits RENAME COLUMN nom TO nom_produit;

-- Fonctionne : renommer une table
ALTER TABLE produits RENAME TO produits_archives;

-- NE FONCTIONNE PAS : modifier le type d'une colonne, supprimer une colonne (avant 3.35),
-- ajouter une contrainte FOREIGN KEY après coup, changer une PRIMARY KEY
```

> **La méthode standard SQLite pour "modifier" une colonne** (changer son type, retirer une contrainte, etc.) consiste à :
> 1. Créer une nouvelle table avec la bonne structure
> 2. Copier les données de l'ancienne vers la nouvelle
> 3. Supprimer l'ancienne table
> 4. Renommer la nouvelle table

```sql
-- Exemple : changer le type de "prix" en INTEGER (technique du "12 étapes" simplifiée)
PRAGMA foreign_keys = OFF;

CREATE TABLE produits_new (
    id     INTEGER PRIMARY KEY AUTOINCREMENT,
    nom    TEXT NOT NULL,
    prix   INTEGER NOT NULL DEFAULT 0
);

INSERT INTO produits_new (id, nom, prix)
SELECT id, nom, prix FROM produits;

DROP TABLE produits;

ALTER TABLE produits_new RENAME TO produits;

PRAGMA foreign_keys = ON;
```

Depuis SQLite 3.35+, `DROP COLUMN` fonctionne directement dans les cas simples :
```sql
ALTER TABLE produits DROP COLUMN description;
```

---

## 11. DROP

```sql
DROP TABLE produits;
DROP TABLE IF EXISTS produits;
DROP VIEW IF EXISTS vue_produits_disponibles;
DROP INDEX IF EXISTS idx_produits_nom;
```

---

## 12. Index, contraintes UNIQUE

```sql
CREATE INDEX idx_produits_nom ON produits(nom);

CREATE UNIQUE INDEX uq_produits_nom ON produits(nom);
-- équivalent à une contrainte UNIQUE, car SQLite gère les UNIQUE via des index automatiquement
```

---

## 13. UPSERT

```sql
-- Syntaxe identique à PostgreSQL (SQLite s'en est inspiré depuis la version 3.24)
INSERT INTO produits (id, nom, prix) VALUES (5, 'Riz local', 3800)
ON CONFLICT (id) DO UPDATE SET prix = excluded.prix;
```

---

## 14. Transactions

```sql
BEGIN TRANSACTION;

UPDATE comptes SET solde = solde - 1000 WHERE id = 1;
UPDATE comptes SET solde = solde + 1000 WHERE id = 2;

COMMIT;
-- En cas de problème : ROLLBACK;
```

---

## 15. Vues

```sql
CREATE VIEW vue_produits_disponibles AS
SELECT * FROM produits WHERE stock > 0;

SELECT * FROM vue_produits_disponibles;

DROP VIEW vue_produits_disponibles;
```

---

## 16. Fonctions utiles

| Besoin                       | SQLite                                              |
|---------------------------------|----------------------------------------------------|
| Concaténer                          | `nom \|\| ' - ' \|\| prix`                          |
| Date/heure actuelle                    | `CURRENT_TIMESTAMP` ou `datetime('now')`                |
| Extraire l'année                          | `strftime('%Y', created_at)`                             |
| Longueur d'une chaîne                        | `LENGTH(nom)`                                              |
| Valeur si NULL                                  | `COALESCE(stock, 0)` ou `IFNULL(stock, 0)`                    |
| Condition en ligne                                 | `CASE WHEN stock > 0 THEN 'dispo' ELSE 'rupture' END`             |
| ID de la dernière ligne insérée                       | `last_insert_rowid()`                                                |

---

## 17. Activer les clés étrangères (à ne jamais oublier)

```sql
PRAGMA foreign_keys = ON;
```

> En SQLite, **les FOREIGN KEY sont ignorées silencieusement par défaut**. Sans cette ligne exécutée sur chaque connexion, tu peux insérer un `categorie_id` qui n'existe pas dans `categories` sans aucune erreur. C'est LE piège n°1 des examens sur SQLite.

---

## 18. Erreurs fréquentes à l'examen

1. **Oublier `PRAGMA foreign_keys = ON;`** → les clés étrangères ne sont jamais vérifiées.
2. **Essayer de faire `ALTER TABLE ... MODIFY COLUMN` ou `ALTER COLUMN ... TYPE`** → syntaxe inexistante en SQLite, il faut recréer la table.
3. **Utiliser `RIGHT JOIN` ou `FULL OUTER JOIN`** en pensant que ça marche comme MySQL/PostgreSQL → non supporté.
4. **Chercher `TRUNCATE TABLE`** → n'existe pas, utiliser `DELETE FROM table;`.
5. **Croire que `VARCHAR(50)` limite réellement la longueur du texte** → SQLite ignore la limite indiquée entre parenthèses (typage dynamique), une chaîne de 500 caractères sera acceptée quand même.
6. **Utiliser `AUTO_INCREMENT` (MySQL) au lieu de `AUTOINCREMENT`** (un seul mot, sans underscore) en SQLite.
