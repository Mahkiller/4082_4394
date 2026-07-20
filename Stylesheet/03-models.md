# CodeIgniter 4 — Les Modèles

## 1. Où ça se passe

```
app/Models/
```

Chaque modèle étend `CodeIgniter\Model`.

---

## 2. Structure de base

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class ProduitModel extends Model
{
    protected $table            = 'produits';       // nom de la table
    protected $primaryKey       = 'id';              // clé primaire

    protected $allowedFields    = ['nom', 'prix', 'stock', 'categorie_id'];

    protected $useTimestamps    = true;   // gère created_at / updated_at automatiquement
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $returnType       = 'array'; // ou 'object', ou une classe Entity

    protected $useSoftDeletes   = false;  // true = delete() ne fait qu'un "soft delete"
}
```

> **Piège d'examen n°1** : si un champ n'est pas dans `$allowedFields`, il est **silencieusement ignoré** lors d'un `save()` ou `insert()` — pas d'erreur, mais la donnée n'est jamais enregistrée.

---

## 3. Les méthodes CRUD principales

```php
$model = new ProduitModel();

// CREATE
$model->insert([
    'nom'   => 'Riz local',
    'prix'  => 3500,
    'stock' => 100,
]);

// READ - tout
$produits = $model->findAll();

// READ - un seul par ID
$produit = $model->find(5);

// READ - avec conditions (Query Builder hérité)
$produits = $model->where('stock >', 0)
                   ->orderBy('nom', 'ASC')
                   ->findAll();

// UPDATE
$model->update(5, ['prix' => 4000]);

// DELETE
$model->delete(5);

// save() : insert OU update automatique selon la présence de la clé primaire
$model->save(['id' => 5, 'prix' => 4200]); // update
$model->save(['nom' => 'Nouveau produit', 'prix' => 1000]); // insert
```

---

## 4. Query Builder (hérité automatiquement dans un Model)

```php
// WHERE simple
$model->where('categorie_id', 2)->findAll();

// WHERE avec opérateur
$model->where('prix >=', 1000)->findAll();

// LIKE
$model->like('nom', 'riz')->findAll();

// JOIN
$model->select('produits.*, categories.nom as categorie_nom')
      ->join('categories', 'categories.id = produits.categorie_id')
      ->findAll();

// Compter
$total = $model->countAllResults();

// Pagination
$produits = $model->paginate(10); // 10 par page
$pager    = $model->pager;
```

---

## 5. Validation intégrée au modèle (souvent demandée en examen)

```php
class ProduitModel extends Model
{
    protected $validationRules = [
        'nom'  => 'required|min_length[3]',
        'prix' => 'required|numeric|greater_than[0]',
    ];

    protected $validationMessages = [
        'nom' => [
            'required'   => 'Le nom du produit est obligatoire.',
            'min_length' => 'Le nom doit contenir au moins 3 caractères.',
        ],
    ];

    protected $skipValidation = false;
}
```

```php
if (! $model->save($data)) {
    $errors = $model->errors(); // tableau des erreurs de validation
}
```

---

## 6. Callbacks (hooks avant/après une action)

```php
class ProduitModel extends Model
{
    protected $beforeInsert = ['genererSlug'];
    protected $afterInsert  = ['logCreation'];

    protected function genererSlug(array $data)
    {
        if (isset($data['data']['nom'])) {
            $data['data']['slug'] = url_title($data['data']['nom'], '-', true);
        }
        return $data;
    }

    protected function logCreation(array $data)
    {
        log_message('info', 'Produit créé, id = ' . $data['id']);
        return $data;
    }
}
```

---

## 7. Requêtes SQL brutes (quand le Query Builder ne suffit pas)

```php
$db = \Config\Database::connect();
$query = $db->query('SELECT * FROM produits WHERE stock < ?', [10]);
$resultats = $query->getResultArray();
```

---

## 8. Entities (alternative aux tableaux/objets simples)

```php
// app/Entities/Produit.php
namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Produit extends Entity
{
    protected $attributes = [
        'id' => null, 'nom' => null, 'prix' => null,
    ];
}
```

```php
class ProduitModel extends Model
{
    protected $returnType = \App\Entities\Produit::class;
}

$produit = $model->find(5);
echo $produit->nom; // accès comme propriété d'objet
```

---

## 9. Erreurs fréquentes à l'examen

1. **Oublier un champ dans `$allowedFields`** → aucune erreur visible, mais la donnée n'est jamais insérée/mise à jour. C'est LE piège classique.
2. **Confondre `save()` et `insert()`** : `save()` fait un update automatique si la clé primaire est présente dans le tableau.
3. **`$table` mal orthographié** (au singulier alors que la table SQL est au pluriel, ou l'inverse).
4. **Oublier `use CodeIgniter\Model;`**.
5. **Utiliser `$this->produitModel` sans l'avoir instancié dans le constructeur du contrôleur.**
