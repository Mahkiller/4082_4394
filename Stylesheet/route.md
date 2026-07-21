# Documentation des Routes

## Vue d'ensemble

Ce document référence toutes les routes de l'application **CodeIgniter 4** de transfert d'argent mobile. Les routes sont définies dans `app/Config/Routes.php` et organisées en deux groupes principaux : l'espace opérateur (administration) et l'espace client (utilisateurs finaux).

## Structure du fichier

**Fichier:** `app/Config/Routes.php`

```php
$routes->get('/', 'OperateurController::index');

// Groupe opérateur
$routes->group('operateur', function ($routes) {
    // ... routes opérateur
});

// Authentification
$routes->get('/login', 'Client::login');
$routes->post('/login', 'Auth::loginAuth');

// Routes client
$routes->get('/solde', 'Client::solde');
// ... autres routes client
```

## Routes par catégorie

### 1. Page d'accueil et Dashboard Opérateur

| Méthode | URL | Contrôleur::Méthode | Description |
|---------|-----|---------------------|-------------|
| GET | `/` | `OperateurController::index` | Dashboard principal (redirection par défaut) |
| GET | `/operateur/` | `OperateurController::index` | Dashboard opérateur YAS |

### 2. Authentification Client

| Méthode | URL | Contrôleur::Méthode | Description |
|---------|-----|---------------------|-------------|
| GET | `/login` | `Client::login` | Affichage formulaire de connexion |
| POST | `/login` | `Auth::loginAuth` | Traitement de la connexion |

**Paramètres POST pour `/login`:**
- `numero` : Numéro de téléphone du client

### 3. Espace Client

#### Consultation et Navigation

| Méthode | URL | Contrôleur::Méthode | Description |
|---------|-----|---------------------|-------------|
| GET | `/solde` | `Client::solde` | Affichage du solde client |
| GET | `/historique` | `Client::historique` | Historique des transactions |
| GET | `/logout` | `Client::logout` | Déconnexion |

#### Transactions Financières

| Méthode | URL | Contrôleur::Méthode | Description |
|---------|-----|---------------------|-------------|
| GET | `/depot` | `Client::depot` | Formulaire de dépôt |
| POST | `/depot` | `Transaction::faireDepot` | Traitement du dépôt |
| GET | `/retrait` | `Client::retrait` | Formulaire de retrait |
| POST | `/retrait` | `Transaction::faireRetrait` | Traitement du retrait |
| GET | `/transfert` | `Client::transfert` | Formulaire de transfert |
| POST | `/transfert` | `Transaction::faireTransfert` | Traitement du transfert simple |
| POST | `/transfert-multiple` | `Transaction::faireTransfertMultiple` | Traitement du transfert multiple |

**Paramètres POST pour `/depot`:**
- `montant` : Montant à déposer

**Paramètres POST pour `/retrait`:**
- `montant` : Montant à retirer

**Paramètres POST pour `/transfert`:**
- `montant` : Montant à transférer
- `destinataire` : Numéro de téléphone du destinataire
- `frais_mode` : Mode de frais (`deductible` ou `inclus`)

**Paramètres POST pour `/transfert-multiple`:**
- `montant_total` : Montant total à répartir
- `destinataires` : Liste des numéros séparés par des virgules
- `frais_mode` : Mode de frais (`deductible` ou `inclus`)

### 4. Gestion des Préfixes (Opérateur)

| Méthode | URL | Contrôleur::Méthode | Description |
|---------|-----|---------------------|-------------|
| GET | `/operateur/prefixe` | `OperateurController::prefixe` | Liste des préfixes |
| POST | `/operateur/prefixe/ajouter` | `OperateurController::prefixeAjouter` | Ajout d'un préfixe |
| GET | `/operateur/prefixe/supprimer/:num` | `OperateurController::prefixeSupprimer` | Suppression d'un préfixe |

**Paramètres POST pour `/operateur/prefixe/ajouter`:**
- `prefixe` : Préfixe téléphonique à ajouter

### 5. Gestion des Types de Transactions (Opérateur)

| Méthode | URL | Contrôleur::Méthode | Description |
|---------|-----|---------------------|-------------|
| GET | `/operateur/types` | `OperateurController::types` | Liste des types avec tranches |
| POST | `/operateur/type/ajouter` | `OperateurController::typeAjouter` | Ajout d'un type |
| GET | `/operateur/type/supprimer/:num` | `OperateurController::typeSupprimer` | Suppression d'un type |

**Paramètres POST pour `/operateur/type/ajouter`:**
- `code` : Code du type (ex: DEPOSIT)
- `label` : Libellé affiché (ex: Dépôt)

### 6. Gestion des Barèmes de Frais (Opérateur)

| Méthode | URL | Contrôleur::Méthode | Description |
|---------|-----|---------------------|-------------|
| GET | `/operateur/montants/:num` | `OperateurController::montants` | Tranches d'un type |
| POST | `/operateur/montant/ajouter` | `OperateurController::montantAjouter` | Ajout d'une tranche |
| POST | `/operateur/montant/modifier/:num` | `OperateurController::montantModifier` | Modification d'une tranche |
| GET | `/operateur/montant/supprimer/:num` | `OperateurController::montantSupprimer` | Suppression d'une tranche |

**Paramètres POST pour `/operateur/montant/ajouter`:**
- `transaction_type_id` : ID du type de transaction
- `min_montant` : Montant minimum de la tranche
- `max_montant` : Montant maximum de la tranche
- `frais_montant` : Frais applicables

### 7. Gestion des Clients (Opérateur)

| Méthode | URL | Contrôleur::Méthode | Description |
|---------|-----|---------------------|-------------|
| GET | `/operateur/comptes` | `OperateurController::comptes` | Liste des comptes clients YAS |
| GET | `/operateur/client/ajouter` | `OperateurController::clientAjouter` | Formulaire d'ajout |
| POST | `/operateur/client/store` | `OperateurController::clientStore` | Enregistrement d'un client |
| GET | `/operateur/client/modifier/:num` | `OperateurController::clientModifier` | Formulaire de modification |
| POST | `/operateur/client/update/:num` | `OperateurController::clientUpdate` | Mise à jour d'un client |
| GET | `/operateur/client/supprimer/:num` | `OperateurController::clientSupprimer` | Suppression d'un client |
| GET | `/operateur/client/detail/:num` | `OperateurController::clientDetail` | Détail + historique |

**Paramètres POST pour `/operateur/client/store` et `/operateur/client/update/:num`:**
- `numero` : Numéro de téléphone (9-10 chiffres)
- `solde` : Solde initial

### 8. Gestion des Commissions (Opérateur)

| Méthode | URL | Contrôleur::Méthode | Description |
|---------|-----|---------------------|-------------|
| GET | `/operateur/commissions` | `OperateurController::commissions` | Liste des commissions |
| POST | `/operateur/commission/ajouter` | `OperateurController::commissionAjouter` | Ajout d'une commission |
| POST | `/operateur/commission/modifier/:num` | `OperateurController::commissionModifier` | Modification d'une commission |
| GET | `/operateur/commission/supprimer/:num` | `OperateurController::commissionSupprimer` | Suppression d'une commission |

**Paramètres POST pour `/operateur/commission/ajouter`:**
- `operateur_destinataire_id` : ID de l'opérateur destinataire
- `pourcentage` : Pourcentage de commission
- `description` : Description de la commission

### 9. Analyses et Statistiques (Opérateur)

| Méthode | URL | Contrôleur::Méthode | Description |
|---------|-----|---------------------|-------------|
| GET | `/operateur/gains` | `OperateurController::gains` | Analyse des gains par opérateur |
| GET | `/operateur/montants-envoyes` | `OperateurController::montantsEnvoyes` | Analyse des flux inter-opérateurs |

---

## Paramètres de route

### Paramètres dynamiques

| Pattern | Type | Description | Exemple |
|---------|------|-------------|---------|
| `(:num)` | integer | ID ou numéro numérique | `/operateur/montants/1` |
| `(:num)` | integer | ID de commission | `/operateur/commission/modifier/5` |

### Contraintes implicites

- Tous les paramètres `(:num)` sont convertis en integer par CodeIgniter
- Aucune contrainte regex supplémentaire n'est définie dans Routes.php

## Groupes de routes

### Groupe `operateur`

Toutes les routes du panel d'administration sont préfixées par `/operateur` :

```php
$routes->group('operateur', function ($routes) {
    // Routes définies ci-dessus
});
```

Cela signifie que :
- `/operateur/` -> Dashboard
- `/operateur/prefixe` -> Gestion des préfixes
- etc.

## Gestion des erreurs et redirections

### Redirections automatiques

| Situation | Redirection |
|-----------|-------------|
| Client non connecté accède à `/solde` | `/login` |
| Client non connecté accède à `/transfert` | `/login` |
| Client non connecté accède à `/historique` | `/login` |
| Transaction réussie (dépôt/retrait/transfert) | `/solde` avec message flash |
| Transaction échouée | Page source avec message d'erreur |

### Messages flash

L'application utilise les messages flash de CodeIgniter pour les retours utilisateur :

| Type | Clé | Utilisation |
|------|-----|-------------|
| Succès | `success` | Opération réussie |
| Erreur | `error` | Erreur de validation ou métier |

Exemple d'utilisation dans les contrôleurs :
```php
return redirect()->back()->with('success', 'Client ajouté.');
return redirect()->to('/login')->with('error', 'Numéro invalide.');
```

## Sécurité

### Authentification

- **Espace client** : Protégé par session (`user_id` doit exister)
- **Espace opérateur** : Pas d'authentification dans la version actuelle (accès libre)

### Validation des entrées

- Numéros de téléphone : regex `/^[0-9]{9,10}$/`
- Montants : validation de cohérence (`min <= max`)
- Préfixes : vérification d'unicité avant insertion
- Commissions : vérification source != destination

## Ordre de priorité des routes

Les routes sont évaluées dans l'ordre de définition :

1. Routes spécifiques avec paramètres (ex: `/operateur/montant/modifier/:num`)
2. Routes spécifiques sans paramètres (ex: `/operateur/gains`)
3. Routes génériques (ex: `/operateur/montants/:num`)
4. Routes par défaut

**Note:** Dans CodeIgniter 4, la première route correspondante est utilisée. Il est donc important de définir les routes les plus spécifiques en premier.

## Extensions futures possibles

- Ajout d'un middleware d'authentification pour l'espace opérateur
- Ajout de routes API RESTful pour les applications mobiles
- Ajout de paramètres de filtrage (dates, opérateurs) dans les routes d'analyse
- Ajout de routes pour l'impression de reçus PDF
