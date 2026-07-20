# CodeIgniter 4 — Migrations & Base de données

## 1. Configuration de la connexion

```php
// .env
database.default.hostname = localhost
database.default.database = mabase
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
```

---

## 2. Créer une migration

```bash
php spark make:migration CreateProduitsTable
```

Fichier généré dans `app/Database/Migrations/`, nommé avec un timestamp, exemple :
`2026-07-19-100000_CreateProduitsTable.php`

```php
<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProduitsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nom' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'prix' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'categorie_id' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null'     => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true); // clé primaire
        $this->forge->addForeignKey('categorie_id', 'categories', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('produits');
    }

    public function down()
    {
        $this->forge->dropTable('produits');
    }
}
```

### Modifier une table existante

```php
public function up()
{
    $this->forge->addColumn('produits', [
        'stock' => [
            'type'       => 'INT',
            'constraint' => 11,
            'default'    => 0,
            'after'      => 'prix',
        ],
    ]);
}

public function down()
{
    $this->forge->dropColumn('produits', 'stock');
}
```

---

## 3. Exécuter les migrations

```bash
php spark migrate              # exécute toutes les migrations en attente
php spark migrate:rollback     # annule le dernier batch
php spark migrate:refresh      # rollback complet puis re-migrate
php spark migrate:status       # voir l'état des migrations
```

---

## 4. Les Seeders (données de test)

```bash
php spark make:seeder ProduitSeeder
```

```php
<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProduitSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['nom' => 'Riz local', 'prix' => 3500, 'stock' => 100],
            ['nom' => 'Huile', 'prix' => 8000, 'stock' => 50],
        ];

        $this->db->table('produits')->insertBatch($data);
    }
}
```

```bash
php spark db:seed ProduitSeeder
```

---

## 5. Query Builder — récapitulatif rapide (hors modèle)

```php
$db = \Config\Database::connect();
$builder = $db->table('produits');

$builder->select('*');
$builder->where('prix >', 1000);
$builder->orderBy('nom', 'ASC');
$resultats = $builder->get()->getResultArray();

// Insertion
$builder->insert(['nom' => 'Sucre', 'prix' => 4000]);

// Mise à jour
$builder->where('id', 5)->update(['prix' => 4200]);

// Suppression
$builder->where('id', 5)->delete();
```

---

## 6. Transactions (important pour les examens sur l'intégrité des données)

```php
$db = \Config\Database::connect();
$db->transStart();

$produitModel->insert($produitData);
$stockModel->update($stockId, ['quantite' => $nouvelleQuantite]);

$db->transComplete();

if ($db->transStatus() === false) {
    // une erreur est survenue, tout est annulé automatiquement (rollback)
}
```

---

## 7. Erreurs fréquentes à l'examen

1. **Oublier `down()`** dans une migration → impossible de faire un rollback proprement.
2. **Oublier `addForeignKey`** alors que l'énoncé décrit une relation → perte de points sur l'intégrité référentielle.
3. **Ordre des migrations** : une table avec clé étrangère doit être migrée APRÈS la table référencée (le timestamp du nom de fichier détermine l'ordre).
4. **Confondre `addColumn` et `modifyColumn`** : `addColumn` ajoute un nouveau champ, `modifyColumn` change un champ existant.
5. **Ne pas fermer les transactions correctement** en cas d'exception non gérée.
