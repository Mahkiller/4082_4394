# Documentation des Modèles

## Vue d'ensemble

Les modèles constituent la couche d'accès aux données dans l'architecture MVC de CodeIgniter 4. Cette application compte **8 modèles** qui correspondent aux 7 tables principales de la base de données SQLite, plus un modèle `UserModel` héritage.

## Structure des fichiers

```
app/Models/
├── BaseController.php          # (non applicable, contrôleur)
├── ClientModel.php             # Table clients
├── OperateurModel.php          # Table operateur (+ méthode custom)
├── OperateurPrefixeModel.php   # Table operateur_prefixe (+ méthode custom)
├── TransactionModel.php        # Table transactions
├── TransactionTypeModel.php    # Table transaction_type
├── MontantModel.php            # Table montant (+ 2 méthodes custom)
├── CommissionModel.php         # Table commission (+ méthode custom)
└── UserModel.php               # Table users (non utilisée)
```

## Architecture des modèles

Tous les modèles étendent `CodeIgniter\Model` et bénéficient automatiquement de :

- **CRUD complet** : `find()`, `findAll()`, `insert()`, `update()`, `delete()`
- **Query Builder** : Accès à `$this->builder()` pour les requêtes complexes
- **Validation** : Définie par `$allowedFields`
- **Timestamps** : Gestion automatique de `created_at` / `updated_at`
- **Soft Deletes** : Suppression logique via `deleted_at`

## Modèles standards

### 1. ClientModel

**Fichier:** `app/Models/ClientModel.php`

```php
class ClientModel extends Model
{
    protected $table            = 'clients';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = ['numero', 'solde'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';
}
```

**Table:** `clients`

#### Propriétés

| Propriété | Valeur | Description |
|-----------|--------|-------------|
| `$table` | `'clients'` | Nom de la table |
| `$primaryKey` | `'id'` | Colonne de clé primaire |
| `$useAutoIncrement` | `true` | ID auto-incrémenté |
| `$returnType` | `'object'` | Retourne des objets stdClass |
| `$useSoftDeletes` | `true` | Active les suppressions logiques |
| `$allowedFields` | `['numero', 'solde']` | Champs modifiables |
| `$useTimestamps` | `true` | Gère created_at / updated_at |
| `$createdField` | `'created_at'` | Colonne de création |
| `$updatedField` | `'updated_at'` | Colonne de modification |
| `$deletedField` | `'deleted_at'` | Colonne de suppression |

#### Méthodes personnalisées

Aucune. Ce modèle utilise uniquement les méthodes CRUD héritées.

#### Opérations disponibles

```php
// Lecture
$client = $clientModel->find($id);           // Par ID
$clients = $clientModel->findAll();          // Tous les clients (hors soft deleted)
$client = $clientModel->where('numero', $n)->first(); // Par numéro

// Écriture
$clientModel->insert(['numero' => '034...', 'solde' => 100000]);
$clientModel->update($id, ['solde' => 200000]);
$clientModel->delete($id);                   // Soft delete
$clientModel->delete($id, true);             // Hard delete (permanent)

// Requêtes avancées
$clientModel->where('solde >', 100000)->findAll();
$clientModel->builder()->join(...)->get()->getResult();
```

---

### 2. OperateurModel

**Fichier:** `app/Models/OperateurModel.php`

```php
class OperateurModel extends Model
{
    protected $table            = 'operateur';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = ['nom'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';
}
```

**Table:** `operateur`

#### Méthode personnalisée

##### `getPrefixes(?int $operateurId)`

```php
public function getPrefixes(?int $operateurId)
{
    $builder = $this->db->table('operateur_prefixe');
    if ($operateurId !== null) {
        $builder->where('operateur_id', $operateurId);
    }
    return $builder->orderBy('prefixe', 'ASC')->get()->getResult();
}
```

**Description:** Récupère les préfixes d'un opérateur, ou tous les préfixes si `$operateurId` est `null`.

**Utilisation:**
```php
$prefixes = $operateurModel->getPrefixes(1); // Préfixes de YAS
$allPrefixes = $operateurModel->getPrefixes(null); // Tous les préfixes
```

---

### 3. OperateurPrefixeModel

**Fichier:** `app/Models/OperateurPrefixeModel.php`

```php
class OperateurPrefixeModel extends Model
{
    protected $table            = 'operateur_prefixe';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['operateur_id', 'prefixe'];
    protected $useTimestamps    = false;
}
```

**Table:** `operateur_prefixe`

#### Méthode personnalisée

##### `getOperateurFromPrefixe(string $prefixe)`

```php
public function getOperateurFromPrefixe(string $prefixe)
{
    return $this->builder()
        ->select('operateur.nom')
        ->join('operateur', 'operateur.id = operateur_prefixe.operateur_id', 'left')
        ->where('operateur_prefixe.prefixe', $prefixe)
        ->get()->getRow();
}
```

**Description:** Retourne le nom de l'opérateur correspondant à un préfixe donné.

**Retour:** Objet avec propriété `nom`, ou `null` si non trouvé.

**Utilisation:**
```php
$operateur = $prefixeModel->getOperateurFromPrefixe('034');
// $operateur->nom == 'YAS'
```

---

### 4. TransactionModel

**Fichier:** `app/Models/TransactionModel.php`

```php
class TransactionModel extends Model
{
    protected $table            = 'transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'client_id',
        'transaction_type_id',
        'montant',
        'frais_applique',
        'montant_net',
        'reference',
        'status',
    ];
    protected $useTimestamps    = false;
}
```

**Table:** `transactions`

#### Propriétés

| Propriété | Valeur | Description |
|-----------|--------|-------------|
| `$useSoftDeletes` | `false` | Pas de soft delete |
| `$useTimestamps` | `false` | Pas de created_at/updated_at automatiques |
| `$allowedFields` | 7 champs | Tous les champs transactionnels |

#### Méthodes personnalisées

Aucune. Les requêtes complexes sont faites via `$this->builder()` dans les contrôleurs.

#### Notes

- Le champ `commission` et `destinataire_numero` ne sont pas dans `$allowedFields` car ils sont optionnels et gérés directement via `$this->builder()->insert()`
- Les transactions sont insérées avec `status = 'Reussi'` (pas de statut `En attente` dans les opérations client)

---

### 5. TransactionTypeModel

**Fichier:** `app/Models/TransactionTypeModel.php`

```php
class TransactionTypeModel extends Model
{
    protected $table            = 'transaction_type';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['code', 'label'];
    protected $useTimestamps    = false;
}
```

**Table:** `transaction_type`

#### Méthodes personnalisées

Aucune.

#### Utilisation typique

```php
// Récupérer tous les types
$types = $typeModel->findAll();

// Récupérer par code
$type = $typeModel->where('code', 'DEPOSIT')->first();

// Vérifier l'existence
$exists = $typeModel->where('code', $code)->first();
```

---

### 6. MontantModel

**Fichier:** `app/Models/MontantModel.php`

```php
class MontantModel extends Model
{
    protected $table            = 'montant';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['transaction_type_id', 'min_montant', 'max_montant', 'frais_montant'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
}
```

**Table:** `montant`

#### Méthodes personnalisées

##### `getByType(int $typeId)`

```php
public function getByType(int $typeId)
{
    return $this->where('transaction_type_id', $typeId)
        ->orderBy('min_montant', 'ASC')
        ->findAll();
}
```

**Description:** Récupère toutes les tranches de frais pour un type de transaction, triées par montant minimum croissant.

**Retour:** Tableau d'objets Montant

**Utilisation:**
```php
$tranches = $montantModel->getByType(1); // Tranches pour DEPOSIT
```

##### `getFrais(int $typeId, float $montant)`

```php
public function getFrais(int $typeId, float $montant)
{
    return $this->where('transaction_type_id', $typeId)
        ->where('min_montant <=', $montant)
        ->where('max_montant >=', $montant)
        ->first();
}
```

**Description:** Retourne la tranche de frais applicable pour un type et un montant donnés.

**Retour:** Objet Montant avec `frais_montant`, ou `null` si aucune tranche trouvée.

**Utilisation:**
```php
$tranche = $montantModel->getFrais(3, 25000); // Frais pour transfert de 25 000 Ar
$frais = $tranche ? $tranche->frais_montant : 0;
```

---

### 7. CommissionModel

**Fichier:** `app/Models/CommissionModel.php`

```php
class CommissionModel extends Model
{
    protected $table            = 'commission';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'operateur_source_id',
        'operateur_destinataire_id',
        'pourcentage',
        'description',
        'est_actif',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
}
```

**Table:** `commission`

#### Méthode personnalisée

##### `getPourcentage(int $sourceId, int $destId): float`

```php
public function getPourcentage(int $sourceId, int $destId): float
{
    $row = $this->where('operateur_source_id', $sourceId)
        ->where('operateur_destinataire_id', $destId)
        ->where('est_actif', 1)
        ->first();

    return $row ? (float) $row->pourcentage : 0.0;
}
```

**Description:** Retourne le pourcentage de commission entre deux opérateurs, ou 0.0 si aucune commission active n'est trouvée.

**Retour:** Float (pourcentage)

**Utilisation:**
```php
$pct = $commissionModel->getPourcentage(1, 2); // Commission YAS → ORANGE
// $pct == 10.0
```

---

### 8. UserModel

**Fichier:** `app/Models/UserModel.php`

```php
class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['numero', 'solde'];
}
```

**Table:** `users`

#### Remarques

- Ce modèle **n'est pas utilisé** dans l'application actuelle
- Il semble être un reliquat ou une structure prévue pour un système d'authentification utilisateur
- La table `users` n'existe pas dans `base.sql`

---

## Comparaison des modèles

| Modèle | Table | Soft Delete | Timestamps | Méthodes custom | Retour Type |
|--------|-------|-------------|------------|-----------------|-------------|
| ClientModel | clients | ✓ | ✓ | 0 | object |
| OperateurModel | operateur | ✓ | ✓ | 1 | object |
| OperateurPrefixeModel | operateur_prefixe | ✗ | ✗ | 1 | object |
| TransactionModel | transactions | ✗ | ✗ | 0 | object |
| TransactionTypeModel | transaction_type | ✗ | ✗ | 0 | object |
| MontantModel | montant | ✗ | ✓ | 2 | object |
| CommissionModel | commission | ✗ | ✓ | 1 | object |
| UserModel | users | ✗ | ✗ | 0 | - |

## Relations entre modèles

```
Operateur (1)
    └── OperateurPrefixe (N)
            └── Client (N) [via prefixe du numero]
                    └── Transaction (N)
                            ├── TransactionType (1)
                            └── Montant (1) [pour calcul des frais]
                    └── Commission (N) [via operateur_source_id]
                            └── Operateur (N) [via operateur_destinataire_id]
TransactionType (1)
    └── Montant (N)
```

## Utilisation des modèles

### Pattern d'accès aux données

```php
// 1. Instanciation
$model = new ClientModel();

// 2. Requête simple
$client = $model->find($id);

// 3. Requête avec conditions
$client = $model->where('numero', $numero)->first();

// 4. Requête complexe avec Query Builder
$builder = $model->builder()
    ->select('clients.*, operateur.nom')
    ->join('operateur_prefixe', '...')
    ->join('operateur', '...')
    ->where('operateur.nom', 'YAS')
    ->get()
    ->getResult();

// 5. Insertion
$model->insert(['numero' => '034...', 'solde' => 100000]);

// 6. Mise à jour
$model->update($id, ['solde' => 200000]);

// 7. Suppression
$model->delete($id); // Soft delete
$model->delete($id, true); // Hard delete
```

### Accès direct à la base de données

Certains contrôleurs utilisent `\Config\Database::connect()` directement au lieu des modèles :

```php
$db = \Config\Database::connect();
$client = $db->table('clients')->where('id', $id)->get()->getRow();
```

**Raisons possibles:**
- Requêtes trop spécifiques pour le modèle
- Performance (éviter l'instanciation multiple)
- Accès à des tables sans modèle dédié

**Inconvénients:**
- Pas de réutilisation
- Pas de validation centralisée
- Duplication de logique

---

## Extensions futures possibles

### Ajout de méthodes personnalisées

```php
// ClientModel
public function findByNumero(string $numero): ?object
{
    return $this->where('numero', $numero)->first();
}

public function getSoldeTotal(): float
{
    $row = $this->selectSum('solde')->first();
    return (float) $row->solde;
}

// OperateurModel
public function findByNom(string $nom): ?object
{
    return $this->where('nom', $nom)->first();
}

// TransactionModel
public function getByClient(int $clientId, int $limit = 50)
{
    return $this->where('client_id', $clientId)
        ->orderBy('created_at', 'DESC')
        ->limit($limit)
        ->findAll();
}
```

### Relations Eloquent-style

Bien que CodeIgniter 4 n'ait pas de relations Eloquent natives, des packages ou une implémentation custom pourraient ajouter :

```php
class ClientModel extends Model
{
    public function transactions()
    {
        return $this->hasMany(TransactionModel::class, 'client_id');
    }
    
    public function operateur()
    {
        return $this->belongsTo(OperateurModel::class, 'prefixe', 'numero', 'operateur_prefixe', 'operateur_id');
    }
}
```

### Validation centralisée

Ajout de règles de validation dans les modèles :

```php
class ClientModel extends Model
{
    protected $rules = [
        'numero' => 'required|min_length[9]|max_length[10]|is_unique[clients.numero,{id}]',
        'solde' => 'required|decimal',
    ];
}
```
