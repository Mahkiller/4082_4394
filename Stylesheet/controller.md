# Documentation des Contrôleurs

## Vue d'ensemble

Les contrôleurs constituent la couche intermédiaire entre les requêtes HTTP et les modèles/vues dans l'architecture MVC de CodeIgniter 4. Cette application compte **6 contrôleurs** organisés en deux espaces fonctionnels distincts :

1. **Espace Client** : Authentification et opérations financières
2. **Espace Opérateur** : Administration et gestion du système

## Structure générale

```
app/Controllers/
├── BaseController.php      # Classe abstraite de base
├── Home.php                # Page d'accueil
├── Auth.php                # Authentification client
├── Client.php              # Interface client
├── Transaction.php         # Opérations financières
└── OperateurController.php # Administration opérateur
```

## Hiérarchie des contrôleurs

```
Controller (CodeIgniter\HTTP\Controller)
    └── BaseController (abstrait)
            ├── Home
            ├── Auth
            ├── Client
            ├── Transaction
            └── OperateurController
```

## BaseController

**Fichier:** `app/Controllers/BaseController.php`

```php
abstract class BaseController extends Controller
{
    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);
        // Preload models, libraries, etc.
    }
}
```

### Rôle

- **Classe abstraite** de base pour tous les contrôleurs de l'application
- Fournit un point d'entrée commun pour l'initialisation
- Permet le chargement préventif de modèles et helpers
- Sert de documention pour les nouveaux développeurs

### Méthodes

| Méthode | Visibilité | Description |
|---------|-----------|-------------|
| `initController()` | public | Initialisation du contrôleur (appelé automatiquement) |

---

## Contrôleurs de l'Espace Client

### 1. Auth

**Fichier:** `app/Controllers/Auth.php`

**Rôle:** Authentification des clients par numéro de téléphone.

**Modèle utilisé:** Aucun (requête SQL directe)

#### Méthode : `loginAuth()`

**Route:** `POST /login`

**Fonctionnalité:**
- Recherche un client par son numéro de téléphone dans la table `clients`
- Si trouvé : initialise la session avec `user_id`, `user_numero`, `user_solde`
- Redirige vers `/solde` en cas de succès
- Redirige vers `/login` avec message d'erreur en cas d'échec

**Note:** Aucun mot de passe n'est requis. L'authentification repose uniquement sur le numéro de téléphone.

**Flux:**
```
POST /login (numero)
    ↓
SELECT * FROM clients WHERE numero = '$numero'
    ↓
[Si trouvé]
    session()->set([user_id, user_numero, user_solde])
    redirect('/solde')
[Si non trouvé]
    redirect('/login')->with('error', 'Numéro invalide')
```

---

### 2. Client

**Fichier:** `app/Controllers/Client.php`

**Rôle:** Affichage des pages de l'interface client.

**Modèles utilisés:** Aucun (accès direct à la base de données)

#### Méthodes

| Méthode | Route | Description | Accès |
|---------|-------|-------------|-------|
| `login()` | `GET /login` | Affiche le formulaire de connexion | Public |
| `solde()` | `GET /solde` | Affiche le solde client | Connecté |
| `depot()` | `GET /depot` | Affiche le formulaire de dépôt | Connecté |
| `retrait()` | `GET /retrait` | Affiche le formulaire de retrait | Connecté |
| `transfert()` | `GET /transfert` | Affiche le formulaire de transfert | Connecté |
| `historique()` | `GET /historique` | Affiche l'historique des transactions | Connecté |
| `logout()` | `GET /logout` | Déconnecte le client | Connecté |

#### Détail des méthodes

##### `login(): string`
- Retourne la vue `Client/login`
- Aucune vérification de session (page publique)

##### `solde()`
- Vérifie la présence de `user_id` en session
- Retourne la vue `Client/Solde`
- Le solde est lu depuis la session (`user_solde`)

##### `depot()`
- Retourne la vue `Client/Depot`
- Aucune logique métier (affichage uniquement)

##### `retrait()`
- Retourne la vue `Client/Retrait`
- Aucune logique métier (affichage uniquement)

##### `transfert()`
- Vérifie la connexion via `user_id`
- Récupère le client et son opérateur (via préfixe du numéro)
- Récupère tous les préfixes du même opérateur
- Passe `prefixesAutorises` à la vue pour validation front-end
- Retourne la vue `Client/Transfert`

##### `historique()`
- Vérifie la connexion via `user_id`
- Joint `transactions` avec `transaction_type`
- Filtre par `client_id`
- Trie par `created_at` DESC
- Passe `transactions` à la vue

##### `logout()`
- Détruit la session complète
- Redirige vers `/login`

---

### 3. Transaction

**Fichier:** `app/Controllers/Transaction.php`

**Rôle:** Traitement des transactions financières (dépôts, retraits, transferts).

**Modèles utilisés:** Aucun (accès direct à la base de données)

**Transaction atomique:** Ce contrôleur utilise `transStart()` et `transComplete()` pour garantir l'intégrité des données lors des transferts.

#### Méthodes

| Méthode | Route | Description | Type |
|---------|-------|-------------|------|
| `faireDepot()` | `POST /depot` | Traite un dépôt | Transaction simple |
| `faireRetrait()` | `POST /retrait` | Traite un retrait | Transaction simple |
| `faireTransfert()` | `POST /transfert` | Traite un transfert simple | Transaction atomique |
| `faireTransfertMultiple()` | `POST /transfert-multiple` | Traite un transfert multiple | Transaction atomique |

#### Détail des méthodes

##### `faireDepot()`

**Paramètres POST:** `montant`

**Logique:**
1. Recherche des frais dans `montant` pour `transaction_type_id = 1` (DEPOSIT)
2. Calcul du nouveau solde : `solde + montant - frais`
3. Mise à jour de la session et de la base
4. Génération de référence : `TXN-YYYYMMDD-XXXX`
5. Insertion dans `transactions` avec `status = 'Reussi'`

**Code de transaction:** Aucune transaction atomique (opération simple)

##### `faireRetrait()`

**Paramètres POST:** `montant`

**Logique:**
1. Recherche des frais dans `montant` pour `transaction_type_id = 2` (WITHDRAWAL)
2. Vérification : `montant + frais <= solde`
3. Calcul du nouveau solde : `solde - montant - frais`
4. Mise à jour de la session et de la base
5. Génération de référence
6. Insertion dans `transactions`

**Code de transaction:** Aucune transaction atomique

##### `faireTransfert()`

**Paramètres POST:**
- `montant` : Montant à transférer
- `destinataire` : Numéro de téléphone du destinataire
- `frais_mode` : `deductible` ou `inclus`

**Logique détaillée:**

1. **Récupération des frais de transaction**
   - Recherche dans `montant` pour `transaction_type_id = 3` (TRANSFER)

2. **Vérification du solde**
   - `montant + frais <= solde`

3. **Récupération des acteurs**
   - Émetteur : client connecté
   - Destinataire : client avec le numéro fourni

4. **Validations**
   - Destinataire existe
   - Destinataire ≠ émetteur

5. **Détection des opérateurs**
   - Source : préfixe de l'émetteur (3 premiers chiffres)
   - Destination : préfixe du destinataire

6. **Calcul de la commission**
   - Si opérateurs différents : recherche dans `commission`
   - `commission = montant * pourcentage / 100`

7. **Calcul des montants**
   - Mode `deductible` :
     - `montant_recu = montant`
     - `total_debit = montant + frais + commission`
   - Mode `inclus` :
     - `montant_recu = montant - frais`
     - `total_debit = montant + commission`

8. **Transaction atomique**
   ```php
   $db->transStart();
   // Mise à jour solde émetteur
   // Mise à jour solde destinataire
   // Insertion transaction
   $db->transComplete();
   ```

##### `faireTransfertMultiple()`

**Paramètres POST:**
- `montant_total` : Montant total à répartir
- `destinataires` : Liste séparée par des virgules
- `frais_mode` : `deductible` ou `inclus`

**Logique détaillée:**

1. **Parsing des destinataires**
   - Séparation par `,`
   - Filtrage des valeurs vides

2. **Calcul du montant par destinataire**
   - `montant_par_destinataire = floor(montant_total / nombre_destinataires)`
   - Validation : `>= 100 Ar`

3. **Frais de transaction**
   - Basés sur `montant_total` (pas sur le montant par destinataire)

4. **Vérification de l'opérateur**
   - L'émetteur doit être YAS, ORANGE ou AIRTEL
   - Récupération de tous les préfixes de l'opérateur

5. **Validation des destinataires**
   - Chaque destinataire doit exister
   - Chaque destinataire ne doit pas être l'émetteur
   - Chaque destinataire doit avoir le même opérateur (même préfixe autorisé)

6. **Transaction atomique**
   - Débit du montant total de l'émetteur
   - Pour chaque destinataire :
     - Crédit du montant reçu
     - Insertion d'une transaction individuelle

---

## Contrôleurs de l'Espace Opérateur

### 4. OperateurController

**Fichier:** `app/Controllers/OperateurController.php`

**Rôle:** Panel d'administration complet pour la gestion des opérateurs, préfixes, types de transactions, barèmes, clients et commissions.

**Modèles utilisés:**
- `OperateurModel`
- `OperateurPrefixeModel`
- `TransactionTypeModel`
- `MontantModel`
- `ClientModel`
- `TransactionModel`
- `CommissionModel`

**Helpers activés:** `form`, `url`

#### Propriétés

```php
protected OperateurModel $operateurModel;
protected OperateurPrefixeModel $prefixeModel;
protected TransactionTypeModel $typeModel;
protected MontantModel $montantModel;
protected ClientModel $clientModel;
protected TransactionModel $transactionModel;
protected CommissionModel $commissionModel;
```

#### Initialisation

`initController()` charge tous les modèles et helpers au démarrage.

#### Méthodes par catégorie

### Dashboard

| Méthode | Route | Description |
|---------|-------|-------------|
| `index()` | `GET /operateur/` | Tableau de bord avec statistiques |
| `totalGains()` (privé) | - | Calcule les gains YAS (retraits + transferts) |

### Gestion des préfixes

| Méthode | Route | Description |
|---------|-------|-------------|
| `prefixe()` | `GET /operateur/prefixe` | Liste des préfixes YAS |
| `prefixeAjouter()` | `POST /operateur/prefixe/ajouter` | Ajoute un préfixe à YAS |
| `prefixeSupprimer($id)` | `GET /operateur/prefixe/supprimer/:id` | Supprime un préfixe |

### Gestion des types

| Méthode | Route | Description |
|---------|-------|-------------|
| `types()` | `GET /operateur/types` | Liste des types avec tranches |
| `typeAjouter()` | `POST /operateur/type/ajouter` | Ajoute un type |
| `typeSupprimer($id)` | `GET /operateur/type/supprimer/:id` | Supprime un type et ses tranches |

### Gestion des barèmes

| Méthode | Route | Description |
|---------|-------|-------------|
| `montants($typeId)` | `GET /operateur/montants/:typeId` | Tranches d'un type |
| `montantAjouter()` | `POST /operateur/montant/ajouter` | Ajoute une tranche |
| `montantModifier($id)` | `POST /operateur/montant/modifier/:id` | Modifie une tranche |
| `montantSupprimer($id)` | `GET /operateur/montant/supprimer/:id` | Supprime une tranche |

### Gestion des clients

| Méthode | Route | Description |
|---------|-------|-------------|
| `comptes()` | `GET /operateur/comptes` | Liste des clients YAS |
| `clientAjouter()` | `GET /operateur/client/ajouter` | Formulaire d'ajout |
| `clientStore()` | `POST /operateur/client/store` | Enregistre un client |
| `clientModifier($id)` | `GET /operateur/client/modifier/:id` | Formulaire de modification |
| `clientUpdate($id)` | `POST /operateur/client/update/:id` | Met à jour un client |
| `clientSupprimer($id)` | `GET /operateur/client/supprimer/:id` | Supprime un client (soft delete) |
| `clientDetail($id)` | `GET /operateur/client/detail/:id` | Détail + historique |

### Gestion des commissions

| Méthode | Route | Description |
|---------|-------|-------------|
| `commissions()` | `GET /operateur/commissions` | Liste des commissions |
| `commissionAjouter()` | `POST /operateur/commission/ajouter` | Ajoute une commission |
| `commissionModifier($id)` | `POST /operateur/commission/modifier/:id` | Modifie une commission |
| `commissionSupprimer($id)` | `GET /operateur/commission/supprimer/:id` | Supprime une commission |

### Analyses

| Méthode | Route | Description |
|---------|-------|-------------|
| `gains()` | `GET /operateur/gains` | Gains par opérateur et type |
| `montantsEnvoyes()` | `GET /operateur/montants-envoyes` | Flux inter-opérateurs |

---

## Flux de données

### Flux d'une transaction financière

```
Client (Navigateur)
    ↓ POST /depot
Transaction::faireDepot()
    ↓
[1] Vérification montant
[2] Recherche frais dans table `montant`
[3] Calcul nouveau solde
[4] UPDATE clients SET solde = nouveau_solde
[5] INSERT INTO transactions
[6] Mise à jour session
    ↓
redirect('/solde')->with('success', '...')
```

### Flux d'un transfert inter-opérateurs

```
Client A (YAS) → Client B (ORANGE)
    ↓ POST /transfert
Transaction::faireTransfert()
    ↓
[1] Vérification solde
[2] Détection opérateurs (préfixes)
[3] Calcul commission (table `commission`)
[4] Calcul montants (selon mode frais)
[5] transStart()
    [5a] UPDATE clients SET solde = nouveau_solde WHERE id = A
    [5b] UPDATE clients SET solde = nouveau_solde WHERE id = B
    [5c] INSERT INTO transactions
[6] transComplete()
    ↓
redirect('/solde')->with('success', '...')
```

### Flux d'ajout de client (Opérateur)

```
Opérateur (Admin)
    ↓ POST /operateur/client/store
OperateurController::clientStore()
    ↓
[1] Validation numero (regex, unique)
[2] INSERT INTO clients (numero, solde)
    ↓
redirect('/operateur/comptes')->with('success', '...')
```

---

## Bonnes pratiques appliquées

### Séparation des responsabilités

- **Client** : Affichage des vues (pas de logique métier)
- **Auth** : Authentification uniquement
- **Transaction** : Logique métier financière
- **OperateurController** : Administration complète

### Transactions atomiques

Les transferts utilisent `transStart()` / `transComplete()` pour garantir l'intégrité :
- Si une erreur survient, toutes les modifications sont annulées
- Cela préserve la cohérence des soldes

### Validation des entrées

- Numéros de téléphone : regex `/^[0-9]{9,10}$/`
- Montants : comparaisons cohérentes
- Unicité : vérifications avant insertion
- Doublons : exclusion de l'ID courant lors des modifications

### Gestion des erreurs

- Redirections avec messages flash (`success` / `error`)
- Pas de try/catch explicite (gestion par CodeIgniter)
- Retour à la page précédente en cas d'erreur de formulaire

### Performances

- Lecture du solde depuis la session (pas de requête DB)
- Jointures optimisées avec index
- Limitation des résultats (`limit(5)` pour le dashboard)

---

## Points d'attention

### Authentification faible

L'authentification client repose uniquement sur le numéro de téléphone, sans mot de passe. Cela convient à un environnement de démonstration mais nécessite renforcement pour la production.

### Pas de validation CSRF sur toutes les routes

Les formulaires devraient utiliser la protection CSRF de CodeIgniter pour plus de sécurité.

### Accès libre à l'espace opérateur

Aucune authentification n'est requise pour accéder au panel opérateur. Cela devrait être ajouté en production.

### Injection SQL potentielle dans Auth

```php
$user = $db->query("SELECT * FROM clients WHERE numero = '$numero'")->getRowArray();
```

Cette requête utilise une interpolation directe. Elle devrait utiliser des requêtes préparées :
```php
$user = $db->query("SELECT * FROM clients WHERE numero = ?", [$numero])->getRowArray();
```
