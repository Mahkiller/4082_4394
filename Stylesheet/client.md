# Documentation Client

## Vue d'ensemble

Le module **Client** gère l'interface utilisateur finale de l'application de transfert d'argent mobile. Les clients peuvent se connecter, consulter leur solde, effectuer des dépôts, des retraits et des transferts (simples ou multiples), ainsi que consulter leur historique de transactions.

## Structure des fichiers

```
app/
├── Controllers/
│   ├── Client.php          # Contrôleur des vues client
│   └── Auth.php            # Authentification client
├── Models/
│   └── ClientModel.php     # Modèle de données clients
└── Views/
    └── Client/
        ├── layout.php      # Template principal
        ├── login.php       # Page de connexion
        ├── Solde.php       # Consultation du solde
        ├── Depot.php       # Formulaire de dépôt
        ├── Retrait.php     # Formulaire de retrait
        ├── Transfert.php   # Formulaire de transfert
        └── Historique.php  # Historique des transactions
```

## Base de données

### Table `clients`

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | INTEGER | Clé primaire auto-incrémentée |
| `numero` | VARCHAR(20) | Numéro de téléphone (unique, 9-10 chiffres) |
| `solde` | DECIMAL(15,2) | Solde du compte (défaut: 0) |
| `created_at` | DATETIME | Date de création |
| `updated_at` | DATETIME | Date de dernière modification |
| `deleted_at` | DATETIME | Date de suppression (soft delete) |

### Règles métier

- Le numéro de téléphone est l'identifiant unique du client (pas de mot de passe)
- L'authentification se fait uniquement par numéro de téléphone
- Le solde est stocké en session pour une performance optimale
- Les transferts multiples sont autorisés uniquement vers des clients du même opérateur

## Modèle : ClientModel

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

### Caractéristiques

- **Soft Deletes** : Les clients ne sont jamais physiquement supprimés
- **Timestamps automatiques** : `created_at` et `updated_at` gérés par le framework
- **Champs autorisés** : Seul `numero` et `solde` peuvent être modifiés massivement

## Contrôleur : Client

**Fichier:** `app/Controllers/Client.php`

### Méthodes

#### `login(): string`
Affiche la page de connexion du client.

- **Route:** `GET /login`
- **Retourne:** Vue `Client/login`

#### `solde()`
Affiche le solde et le numéro du client connecté.

- **Route:** `GET /solde`
- **Données session utilisées:**
  - `user_id` : ID du client
  - `user_numero` : Numéro de téléphone
  - `user_solde` : Solde actuel
- **Retourne:** Vue `Client/Solde`

#### `depot()`
Affiche le formulaire de dépôt d'argent.

- **Route:** `GET /depot`
- **Retourne:** Vue `Client/Depot`

#### `retrait()`
Affiche le formulaire de retrait d'argent.

- **Route:** `GET /retrait`
- **Retourne:** Vue `Client/Retrait`

#### `transfert()`
Affiche le formulaire de transfert d'argent.

- **Route:** `GET /transfert`
- **Vérifications:**
  - Client connecté (session `user_id`)
  - Récupération de l'opérateur du client via son préfixe (3 premiers chiffres du numéro)
  - Récupération des préfixes autorisés (même opérateur)
- **Données passées à la vue:**
  - `prefixesAutorises` : Tableau des préfixes autorisés pour le transfert
- **Retourne:** Vue `Client/Transfert`

#### `historique()`
Affiche l'historique des transactions du client.

- **Route:** `GET /historique`
- **Requête SQL:**
  - Jointure entre `transactions` et `transaction_type`
  - Filtrage par `client_id`
  - Tri par `created_at` DESC
- **Données passées à la vue:**
  - `transactions` : Liste des transactions avec leur type (label et code)
- **Retourne:** Vue `Client/Historique`

#### `logout()`
Déconnecte le client.

- **Route:** `GET /logout`
- **Actions:**
  - Destruction de la session
  - Redirection vers `/login`

## Contrôleur : Auth

**Fichier:** `app/Controllers/Auth.php`

### Méthodes

#### `loginAuth()`
Authentifie un client par son numéro de téléphone.

- **Route:** `POST /login`
- **Paramètre POST:** `numero` : Numéro de téléphone du client
- **Logique:**
  1. Recherche du client dans la table `clients` par numéro
  2. Si trouvé : création de la session avec `user_id`, `user_numero`, `user_solde`
  3. Redirection vers `/solde`
  4. Si non trouvé : redirection vers `/login` avec message d'erreur
- **Note:** Aucun mot de passe n'est requis (authentification par numéro seul)

## Contrôleur : Transaction

**Fichier:** `app/Controllers/Transaction.php`

Ce contrôleur gère le traitement des transactions financières (dépôts, retraits, transferts).

### Méthodes

#### `faireDepot()`
Traite un dépôt d'argent sur le compte client.

- **Route:** `POST /depot`
- **Paramètre POST:** `montant` : Montant à déposer
- **Logique:**
  1. Récupération du montant
  2. Recherche des frais applicables dans la table `montant` (type = 1=DEPOSIT)
  3. Calcul du nouveau solde : `solde + montant - frais`
  4. Mise à jour du solde en session et en base de données
  5. Génération d'une référence unique : `TXN-YYYYMMDD-XXXX`
  6. Insertion de la transaction avec statut `Reussi`
- **Redirection:** `/solde` avec message de succès

#### `faireRetrait()`
Traite un retrait d'argent du compte client.

- **Route:** `POST /retrait`
- **Paramètre POST:** `montant` : Montant à retirer
- **Logique:**
  1. Récupération du montant
  2. Recherche des frais applicables dans la table `montant` (type = 2=WITHDRAWAL)
  3. Vérification du solde suffisant : `montant + frais <= solde`
  4. Calcul du nouveau solde : `solde - montant - frais`
  5. Mise à jour du solde en session et en base de données
  6. Génération d'une référence unique
  7. Insertion de la transaction avec statut `Reussi`
- **Redirection:** `/solde` avec message de succès ou `/retrait` avec erreur

#### `faireTransfert()`
Traite un transfert simple d'argent vers un destinataire.

- **Route:** `POST /transfert`
- **Paramètres POST:**
  - `montant` : Montant à transférer
  - `destinataire` : Numéro de téléphone du destinataire
  - `frais_mode` : Mode de frais (`deductible` ou `inclus`)
- **Logique:**
  1. Récupération des paramètres
  2. Recherche des frais applicables (type = 3=TRANSFER)
  3. Vérification du solde suffisant
  4. Récupération de l'émetteur et du destinataire
  5. Vérifications :
     - Destinataire existe
     - Destinataire != émetteur
  6. Détection des opérateurs source et destination via les préfixes
  7. Calcul de la commission inter-opérateurs si applicable
  8. Calcul du montant reçu selon le mode de frais :
     - `deductible` : `montant_recu = montant`, `total_debit = montant + frais + commission`
     - `inclus` : `montant_recu = montant - frais`, `total_debit = montant + commission`
  9. Transaction atomique (`transStart()` / `transComplete()`)
  10. Mise à jour des soldes émetteur et destinataire
  11. Insertion de la transaction avec référence unique et statut `Reussi`
- **Redirection:** `/solde` avec message de succès ou `/transfert` avec erreur

#### `faireTransfertMultiple()`
Traite un transfert multiple vers plusieurs destinataires du même opérateur.

- **Route:** `POST /transfert-multiple`
- **Paramètres POST:**
  - `montant_total` : Montant total à répartir
  - `destinataires` : Liste des numéros séparés par des virgules
  - `frais_mode` : Mode de frais (`deductible` ou `inclus`)
- **Logique:**
  1. Parsing de la liste des destinataires (séparateur: `,`)
  2. Calcul du montant par destinataire : `floor(montant_total / nombre_destinataires)`
  3. Vérification : montant par destinataire >= 100 Ar
  4. Recherche des frais applicables (basé sur `montant_total`, type = 3)
  5. Vérification du solde suffisant
  6. Récupération de l'opérateur de l'émetteur
  7. Vérification de chaque destinataire :
     - Existe dans la base
     - N'est pas l'émetteur
     - Appartient au même opérateur (même préfixe autorisé)
  8. Transaction atomique
  9. Pour chaque destinataire :
     - Calcul du montant reçu selon le mode de frais
     - Mise à jour du solde destinataire
     - Insertion d'une transaction individuelle
- **Redirection:** `/solde` avec message de succès ou `/transfert` avec erreur

## Sessions

### Variables de session utilisateur

| Clé | Description | Exemple |
|-----|-------------|---------|
| `user_id` | ID du client connecté | `1` |
| `user_numero` | Numéro de téléphone du client | `0341000001` |
| `user_solde` | Solde actuel du client | `250000` |

### Gestion de la session

- **Connexion:** `loginAuth()` initialise les 3 variables de session
- **Mise à jour:** Après chaque transaction, `user_solde` est mis à jour en session ET en base
- **Déconnexion:** `logout()` détruit la session complète

## Vues Client

### Layout (`Client/layout.php`)
Template principal avec :
- Bootstrap 5.3.5
- Sidebar de navigation (Solde, Dépôt, Retrait, Transfert, Historique, Déconnexion)
- Affichage du solde et du numéro client
- Gestion des messages flash (succès/erreur)

### Pages

| Vue | URL | Description |
|-----|-----|-------------|
| `login.php` | `/login` | Formulaire de connexion par numéro |
| `Solde.php` | `/solde` | Affichage du solde et informations client |
| `Depot.php` | `/depot` | Formulaire de dépôt avec calcul des frais |
| `Retrait.php` | `/retrait` | Formulaire de retrait avec calcul des frais |
| `Transfert.php` | `/transfert` | Formulaire de transfert (simple/multiple) |
| `Historique.php` | `/historique` | Liste des transactions avec détails |

## Règles métier spécifiques

### Frais de transaction

Les frais sont déterminés par des tranches de montant (`table montant`) :

| Type | Plage (Ar) | Frais (Ar) |
|------|-----------|------------|
| Dépôt | 100 - 1 000 | 50 |
| Dépôt | 1 001 - 5 000 | 50 |
| Dépôt | 5 001 - 10 000 | 100 |
| ... | ... | ... |
| Retrait | Mêmes tranches que Dépôt | Mêmes frais |
| Transfert | 100 - 1 000 | 100 |
| Transfert | 1 001 - 5 000 | 150 |
| ... | ... | ... |

### Transferts inter-opérateurs

- Les transferts vers un autre opérateur (basé sur le préfixe) génèrent une commission
- La commission est calculée : `montant * pourcentage / 100`
- Les commissions sont définies dans la table `commission`

### Transferts multiples

- Réservés aux clients du même opérateur
- Le montant total est divisé équitablement entre les destinataires
- Chaque destinataire reçoit une transaction individuelle
- Le montant par destinataire doit être >= 100 Ar
