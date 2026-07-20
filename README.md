# 4082_4394 — Système Mobile Money (S4 Info & Design)

Projet d'examen final : un simulateur d'opérateur de mobile money développé avec **CodeIgniter 4**, **SQLite** embarqué, et **Bootstrap 5**. Deux espaces sont disponibles : l'espace **Opérateur** (gestion) et l'espace **Client** (opérations).

## Lancer le projet

```bash
# Installer les dépendances (si besoin)
composer install

# Démarrer le serveur de développement
php spark serve
```

Le site est accessible sur **http://localhost:8080**.

> La base de données (`exam_S4_design_4082_4394.db`) se trouve dans le dossier `writable/` et est déjà remplie avec des données de démo (opérateurs, préfixes, types d'opérations, barèmes, clients et transactions).

## Accès aux espaces

| Espace | URL | Description |
|--------|-----|-------------|
| **Opérateur** | http://localhost:8080/operateur | Tableau de bord, préfixes, types & barèmes, gains, comptes clients |
| **Connexion Client** | http://localhost:8080/login | Connexion automatique par numéro de téléphone |
| **Espace Client** | http://localhost:8080/solde | Solde, dépôt, retrait, transfert, historique |

### Espace Opérateur
- Tableau de bord avec indicateurs clés (préfixes, types, clients, gains, dernières transactions)
- Configuration des **préfixes** valables par opérateur (ex : 033, 037)
- Création des **types d'opérations** (dépôt, retrait, transfert) avec **barèmes de frais** par tranche de montant (modifiables)
- **Situation des gains** générés par les frais (retrait et transfert)
- **Situation des comptes clients** (solde, opérateur, ajout / modification / suppression)
- Sélecteur d'opérateur (YAS / ORANGE / AIRTEL / **Tous**) : toutes les pages se filtrent selon l'opérateur choisi

### Espace Client
Connexion automatique avec le numéro de téléphone (aucune inscription préalable) :
- Consulter le **solde**
- Faire un **dépôt** (automatique)
- Faire un **retrait** (automatique)
- Faire un **transfert**
- Consulter l'**historique** des opérations

Numéros de démo disponibles : `0341000001`, `0321000001`, `0331000001`, etc.

## Structure du projet

```
app/
├── Controllers/
│   ├── OperateurController.php   # Espace opérateur
│   ├── Client.php                # Pages client (solde, dépôt, retrait, transfert, historique)
│   ├── Auth.php                  # Connexion client
│   └── Transaction.php           # Traitement dépôt / retrait / transfert
├── Models/                       # Operateur, OperateurPrefixe, TransactionType, Montant, Client, Transaction
└── Views/
    ├── Opérateur/                # layout, dashboard, prefixe, types, montants, gains, comptes, client_*
    └── Client/                   # layout, login, Solde, Depot, Retrait, Transfert, Historique
public/css/client.css             # Feuille de style de l'espace client
```

## Technologies
- PHP 8.2 + CodeIgniter 4
- SQLite3
- HTML / CSS / JavaScript
- Bootstrap 5 + Bootstrap Icons
