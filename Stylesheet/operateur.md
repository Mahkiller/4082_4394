# Documentation Opérateur

## Vue d'ensemble

Le module **Opérateur** constitue le panel d'administration de l'application, dédié à la gestion des opérateurs de téléphonie mobile, des préfixes téléphoniques, des types de transactions, des barèmes de frais, des comptes clients et des commissions inter-opérateurs.

Le panel est conçu spécifiquement pour l'opérateur **YAS** (opérateur principal), mais permet de gérer des clients et des transactions de tous les opérateurs.

## Structure des fichiers

```
app/
├── Controllers/
│   └── OperateurController.php   # Contrôleur principal de l'admin
├── Models/
│   ├── OperateurModel.php         # Modèle opérateurs
│   ├── OperateurPrefixeModel.php  # Modèle préfixes opérateurs
│   ├── TransactionTypeModel.php   # Modèle types de transactions
│   ├── MontantModel.php           # Modèle barèmes de frais
│   ├── ClientModel.php            # Modèle clients
│   ├── TransactionModel.php       # Modèle transactions
│   └── CommissionModel.php        # Modèle commissions
└── Views/
    └── Opérateur/
        ├── layout.php             # Template admin
        ├── dashboard.php          # Tableau de bord
        ├── prefixe.php            # Gestion des préfixes
        ├── types.php              # Gestion des types de transactions
        ├── montants.php           # Gestion des barèmes de frais
        ├── gains.php              # Analyse des gains par opérateur
        ├── montants_envoyes.php   # Analyse des flux par opérateur
        ├── comptes.php            # Liste des comptes clients
        ├── client_ajouter.php     # Ajout d'un client
        ├── client_modifier.php    # Modification d'un client
        ├── client_detail.php      # Détail d'un client
        └── commissions.php        # Gestion des commissions
```

## Base de données

### Tables concernées

#### Table `operateur`

Représente les opérateurs de téléphonie mobile.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | INTEGER | Clé primaire auto-incrémentée |
| `nom` | VARCHAR(50) | Nom de l'opérateur |
| `created_at` | DATETIME | Date de création |
| `updated_at` | DATETIME | Date de modification |
| `deleted_at` | DATETIME | Date de suppression (soft delete) |

**Données initiales:**
- YAS (id=1)
- ORANGE (id=2)
- AIRTEL (id=3)

#### Table `operateur_prefixe`

Associe des préfixes téléphoniques à des opérateurs.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | INTEGER | Clé primaire |
| `operateur_id` | INTEGER | ID de l'opérateur (FK vers `operateur.id`) |
| `prefixe` | VARCHAR(10) | Préfixe téléphonique (unique) |

**Données initiales:**
- YAS : 034, 038
- ORANGE : 032, 037
- AIRTEL : 033, 036

#### Table `transaction_type`

Types de transactions possibles.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | INTEGER | Clé primaire |
| `code` | VARCHAR(20) | Code unique (DEPOSIT, WITHDRAWAL, TRANSFER) |
| `label` | VARCHAR(50) | Libellé affiché |

**Données initiales:**
- 1 : DEPOSIT - Dépôt
- 2 : WITHDRAWAL - Retrait
- 3 : TRANSFER - Transfert

#### Table `montant`

Barèmes de frais par type de transaction et par tranche de montant.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | INTEGER | Clé primaire |
| `transaction_type_id` | INTEGER | ID du type de transaction (FK) |
| `min_montant` | DECIMAL(15,2) | Montant minimum de la tranche |
| `max_montant` | DECIMAL(15,2) | Montant maximum de la tranche |
| `frais_montant` | DECIMAL(15,2) | Frais applicables pour cette tranche |
| `created_at` | DATETIME | Date de création |
| `updated_at` | DATETIME | Date de modification |

**Contrainte:** `min_montant <= max_montant`

#### Table `commission`

Commissions inter-opérateurs pour les transferts.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | INTEGER | Clé primaire |
| `operateur_source_id` | INTEGER | Opérateur source (FK) |
| `operateur_destinataire_id` | INTEGER | Opérateur destinataire (FK) |
| `pourcentage` | DECIMAL(5,2) | Pourcentage de commission (défaut: 10%) |
| `description` | TEXT | Description de la commission |
| `est_actif` | BOOLEAN | Statut actif/inactif (défaut: 1) |
| `created_at` | DATETIME | Date de création |
| `updated_at` | DATETIME | Date de modification |

**Contraintes:**
- `operateur_source_id != operateur_destinataire_id`
- Unique: `(operateur_source_id, operateur_destinataire_id)`

## Contrôleur : OperateurController

**Fichier:** `app/Controllers/OperateurController.php`

### Propriétés

```php
protected OperateurModel $operateurModel;
protected OperateurPrefixeModel $prefixeModel;
protected TransactionTypeModel $typeModel;
protected MontantModel $montantModel;
protected ClientModel $clientModel;
protected TransactionModel $transactionModel;
protected CommissionModel $commissionModel;
```

### Méthodes

#### `initController()`
Initialisation du contrôleur avec tous les modèles et helpers.

- Charge tous les modèles nécessaires
- Active les helpers `form` et `url`

---

## Dashboard

### `index()`
Affiche le tableau de bord principal.

- **Route:** `GET /operateur/`
- **Données récupérées:**
  - `nbPrefixes` : Nombre total de préfixes
  - `nbTypes` : Nombre de types de transactions
  - `nbClients` : Nombre de clients YAS (jointure par préfixe)
  - `nbTransac` : Nombre de transactions YAS
  - `gains` : Total des gains YAS (via `totalGains()`)
  - `dernieres` : 5 dernières transactions YAS
  - `types` : Liste de tous les types de transactions
  - `operateurs` : Liste de tous les opérateurs
- **Retourne:** Vue `Opérateur/dashboard`

### `totalGains(): float` (privé)
Calcule le total des gains YAS sur les transferts et retraits réussis.

- **Requête:**
  - Jointure : `transactions` -> `clients` -> `operateur_prefixe` -> `operateur`
  - Filtres : `status = 'Reussi'`, `operateur.nom = 'YAS'`
  - Types : transaction_type_id IN [2, 3] (WITHDRAWAL, TRANSFER)
  - Somme : `frais_applique`

---

## Gestion des préfixes

### `prefixe()`
Affiche la page de gestion des préfixes.

- **Route:** `GET /operateur/prefixe`
- **Données:**
  - `prefixes` : Préfixes de l'opérateur YAS
  - `operateurs` : Tous les opérateurs
- **Retourne:** Vue `Opérateur/prefixe`

### `prefixeAjouter()`
Ajoute un nouveau préfixe pour YAS.

- **Route:** `POST /operateur/prefixe/ajouter`
- **Paramètre POST:** `prefixe` : Le préfixe à ajouter
- **Validations:**
  - Préfixe non vide
  - Préfixe n'existe pas déjà
- **Action:** Insertion dans `operateur_prefixe` lié à YAS
- **Redirection:** `/operateur/prefixe`

### `prefixeSupprimer($id)`
Supprime un préfixe.

- **Route:** `GET /operateur/prefixe/supprimer/:id`
- **Action:** Suppression (soft delete si activé) du préfixe
- **Redirection:** `/operateur/prefixe`

---

## Gestion des types de transactions

### `types()`
Affiche la liste des types de transactions avec leurs tranches de frais.

- **Route:** `GET /operateur/types`
- **Données:**
  - `types` : Liste des types avec `tranches` (via `MontantModel::getByType()`)
  - `operateurs` : Tous les opérateurs
- **Retourne:** Vue `Opérateur/types`

### `typeAjouter()`
Ajoute un nouveau type de transaction.

- **Route:** `POST /operateur/type/ajouter`
- **Paramètres POST:** `code`, `label`
- **Validations:**
  - Code et label non vides
  - Code unique (transformé en majuscules)
- **Action:** Insertion dans `transaction_type`
- **Redirection:** `/operateur/types`

### `typeSupprimer($id)`
Supprime un type de transaction et ses tranches associées.

- **Route:** `GET /operateur/type/supprimer/:id`
- **Actions:**
  1. Suppression de toutes les tranches dans `montant`
  2. Suppression du type dans `transaction_type`
- **Redirection:** `/operateur/types`

---

## Gestion des barèmes de frais (Montants)

### `montants($typeId)`
Affiche les tranches de frais pour un type de transaction.

- **Route:** `GET /operateur/montants/:typeId`
- **Paramètre:** `typeId` : ID du type de transaction
- **Données:**
  - `type` : Le type de transaction
  - `tranches` : Liste des tranches (triées par `min_montant` ASC)
  - `operateurs` : Tous les opérateurs
- **Retourne:** Vue `Opérateur/montants`

### `montantAjouter()`
Ajoute une nouvelle tranche de frais.

- **Route:** `POST /operateur/montant/ajouter`
- **Paramètres POST:**
  - `transaction_type_id` : ID du type
  - `min_montant` : Montant minimum
  - `max_montant` : Montant maximum
  - `frais_montant` : Frais applicables
- **Validation:** `min_montant <= max_montant`
- **Redirection:** `/operateur/montants/{typeId}`

### `montantModifier($id)`
Modifie une tranche de frais existante.

- **Route:** `POST /operateur/montant/modifier/:id`
- **Paramètres POST:** `min_montant`, `max_montant`, `frais_montant`
- **Validation:** `min_montant <= max_montant`
- **Redirection:** `/operateur/montants/{typeId}`

### `montantSupprimer($id)`
Supprime une tranche de frais.

- **Route:** `GET /operateur/montant/supprimer/:id`
- **Redirection:** `/operateur/montants/{typeId}`

---

## Gestion des clients

### `comptes()`
Affiche la liste des comptes clients YAS.

- **Route:** `GET /operateur/comptes`
- **Requête:**
  - Jointure : `clients` -> `operateur_prefixe` -> `operateur`
  - Filtre : `operateur.nom = 'YAS'`
  - Groupement : `clients.id, operateur.nom`
  - Comptage des transactions par client
- **Données:**
  - `clients` : Liste des clients YAS avec opérateur et nombre de transactions
  - `totalSolde` : Somme de tous les soldes
- **Retourne:** Vue `Opérateur/comptes`

### `clientAjouter()`
Affiche le formulaire d'ajout de client.

- **Route:** `GET /operateur/client/ajouter`
- **Données:**
  - `prefixes` : Préfixes YAS disponibles
- **Retourne:** Vue `Opérateur/client_ajouter`

### `clientStore()`
Enregistre un nouveau client.

- **Route:** `POST /operateur/client/store`
- **Paramètres POST:** `numero`, `solde`
- **Validations:**
  - Numéro non vide
  - Format : 9 à 10 chiffres (`/^[0-9]{9,10}$/`)
  - Numéro unique
- **Redirection:** `/operateur/comptes`

### `clientModifier($id)`
Affiche le formulaire de modification d'un client.

- **Route:** `GET /operateur/client/modifier/:id`
- **Retourne:** Vue `Opérateur/client_modifier`

### `clientUpdate($id)`
Met à jour un client existant.

- **Route:** `POST /operateur/client/update/:id`
- **Validations:** Mêmes que pour l'ajout + vérification de non-doublon (excluant l'ID actuel)
- **Redirection:** `/operateur/comptes`

### `clientSupprimer($id)`
Supprime un client (soft delete).

- **Route:** `GET /operateur/client/supprimer/:id`
- **Redirection:** `/operateur/comptes`

### `clientDetail($id)`
Affiche le détail d'un client avec son historique.

- **Route:** `GET /operateur/client/detail/:id`
- **Données:**
  - `client` : Informations du client
  - `transactions` : Historique des transactions avec type (jointure `transaction_type`)
- **Retourne:** Vue `Opérateur/client_detail`

### `operateurDuClient(string $numero): string` (privé)
Retourne le nom de l'opérateur d'un client à partir de son numéro.

- Extraction du préfixe (3 premiers caractères)
- Recherche de l'opérateur correspondant

---

## Analyse des gains

### `gains()`
Affiche l'analyse détaillée des gains par opérateur et par type de transaction.

- **Route:** `GET /operateur/gains`
- **Logique:**
  - Pour chaque type de transaction :
    - Calcul des gains YAS (`frais_applique` des transactions réussies YAS)
    - Calcul des gains autres opérateurs
    - Pour les transferts (type=3) : calcul des commissions vers autres opérateurs
- **Données:**
  - `detailsYas` : Détails par type pour YAS (gain, volume)
  - `detailsAutres` : Détails par type pour autres opérateurs (gain, volume, commission, gain_total)
  - `totalYas` : Total des gains YAS
  - `totalAutres` : Total des gains autres opérateurs (incluant commissions)
  - `totalCommissions` : Total des commissions versées
- **Retourne:** Vue `Opérateur/gains`

---

## Analyse des montants envoyés

### `montantsEnvoyes()`
Affiche les flux d'argent par opérateur (envoyés et reçus par YAS).

- **Route:** `GET /operateur/montants-envoyes`
- **Logique:**
  - Pour chaque opérateur (sauf YAS) :
    - **Envoyé :** Montant total envoyé PAR YAS VERS cet opérateur
      (destinataire avec préfixe de l'opérateur, type TRANSFER, statut Réussi)
    - **Reçu :** Montant total reçu PAR YAS DEPUIS cet opérateur
      (émetteur avec préfixe de l'opérateur, type TRANSFER, statut Réussi)
    - **Net :** `envoyé - reçu`
- **Données:**
  - `resultats` : Tableau par opérateur avec `operateur`, `total_envoye`, `total_recu`, `net`
- **Retourne:** Vue `Opérateur/montants_envoyes`

---

## Gestion des commissions

### `commissions()`
Affiche la liste des commissions inter-opérateurs.

- **Route:** `GET /operateur/commissions`
- **Données:**
  - `commissions` : Liste des commissions avec noms source et destination
  - `operateurs` : Tous les opérateurs
- **Retourne:** Vue `Opérateur/commissions`

### `commissionAjouter()`
Ajoute une commission inter-opérateurs.

- **Route:** `POST /operateur/commission/ajouter`
- **Paramètres POST:**
  - `operateur_destinataire_id` : ID de l'opérateur destinataire
  - `pourcentage` : Pourcentage de commission
  - `description` : Description
- **Contraintes:**
  - Source = YAS uniquement (id=1)
  - Source != Destination
- **Redirection:** `/operateur/commissions`

### `commissionModifier($id)`
Modifie une commission existante.

- **Route:** `POST /operateur/commission/modifier/:id`
- **Paramètres POST:** `pourcentage`, `description`
- **Redirection:** `/operateur/commissions`

### `commissionSupprimer($id)`
Supprime une commission.

- **Route:** `GET /operateur/commission/supprimer/:id`
- **Redirection:** `/operateur/commissions`

---

## Modèles utilisés

### OperateurModel

```php
class OperateurModel extends Model
{
    protected $table = 'operateur';
    protected $useSoftDeletes = true;
    protected $allowedFields = ['nom'];
    
    public function getPrefixes(?int $operateurId)
    // Récupère les préfixes d'un opérateur, triés par ordre alphabétique
}
```

### OperateurPrefixeModel

```php
class OperateurPrefixeModel extends Model
{
    protected $table = 'operateur_prefixe';
    protected $allowedFields = ['operateur_id', 'prefixe'];
    
    public function getOperateurFromPrefixe(string $prefixe)
    // Retourne le nom de l'opérateur correspondant à un préfixe
}
```

### TransactionTypeModel

```php
class TransactionTypeModel extends Model
{
    protected $table = 'transaction_type';
    protected $allowedFields = ['code', 'label'];
}
```

### MontantModel

```php
class MontantModel extends Model
{
    protected $table = 'montant';
    protected $allowedFields = ['transaction_type_id', 'min_montant', 'max_montant', 'frais_montant'];
    
    public function getByType(int $typeId)
    // Récupère toutes les tranches pour un type, triées par min_montant ASC
    
    public function getFrais(int $typeId, float $montant)
    // Retourne la tranche applicable pour un montant donné
}
```

### CommissionModel

```php
class CommissionModel extends Model
{
    protected $table = 'commission';
    protected $allowedFields = [
        'operateur_source_id',
        'operateur_destinataire_id',
        'pourcentage',
        'description',
        'est_actif'
    ];
    
    public function getPourcentage(int $sourceId, int $destId): float
    // Retourne le pourcentage de commission entre deux opérateurs, 0.0 si non trouvé
}
```

---

## Vues Opérateur

### Layout (`Opérateur/layout.php`)
Template principal avec :
- Bootstrap 5.3.5
- Sidebar de navigation (Dashboard, Préfixes, Types, Montants, Gains, Comptes, Clients, Commissions)
- Affichage de l'opérateur connecté
- Gestion des messages flash

### Pages

| Vue | Route | Description |
|-----|-------|-------------|
| `dashboard.php` | `/operateur/` | Tableau de bord avec statistiques |
| `prefixe.php` | `/operateur/prefixe` | Gestion des préfixes YAS |
| `types.php` | `/operateur/types` | Gestion des types de transactions |
| `montants.php` | `/operateur/montants/:id` | Gestion des barèmes par type |
| `gains.php` | `/operateur/gains` | Analyse des gains par opérateur |
| `montants_envoyes.php` | `/operateur/montants-envoyes` | Analyse des flux inter-opérateurs |
| `comptes.php` | `/operateur/comptes` | Liste des comptes clients YAS |
| `client_ajouter.php` | `/operateur/client/ajouter` | Formulaire d'ajout client |
| `client_modifier.php` | `/operateur/client/modifier/:id` | Formulaire de modification client |
| `client_detail.php` | `/operateur/client/detail/:id` | Détail client + historique |
| `commissions.php` | `/operateur/commissions` | Gestion des commissions |

---

## Règles métier spécifiques

### Détection de l'opérateur

L'opérateur d'un client est déterminé par les 3 premiers chiffres de son numéro de téléphone (préfixe). Cette détection est utilisée pour :
- Filtrer les transactions YAS dans le dashboard
- Calculer les gains par opérateur
- Déterminer les commissions inter-opérateurs
- Afficher les flux envoyés/reçus

### Filtre YAS

La majorité des vues et requêtes filtrent sur l'opérateur YAS (id=1) car c'est l'opérateur principal géré par ce panel.

### Commissions inter-opérateurs

Les commissions sont calculées uniquement pour les transferts (type=3) entre opérateurs différents :
- Source et destination identifiées par les préfixes
- Pourcentage défini dans la table `commission`
- Commission = `montant * pourcentage / 100`

### Gains

Les gains sont calculés sur la somme des `frais_applique` des transactions réussies :
- Pour YAS : inclut dépôts, retraits et transferts
- Pour autres opérateurs : inclut uniquement les frais des transactions de leurs clients
- Pour les transferts inter-opérateurs : inclut également les commissions
