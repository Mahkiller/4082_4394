# Répartition des Tâches - Projet Mobile Money

**Date :** 20/07/2024 (Date de l'examen)
**Heure de début :** 08h00
**Heure de fin :** 12h50

---

## 👨‍💻 Mahery (ID: 4082)

Mahery s'est concentré sur la structure de base du projet, la base de données, et toute la partie administrative de l'**Espace Opérateur**.

### Tâches réalisées :

1.  **Base de Données (SQLite)**
    -   Conception et création du schéma complet (`base.sql`).
    -   Intégration des tables, triggers, vues et données de test.
    -   Rédaction de la documentation pour l'installation et l'utilisation de la base (`base.md`).

2.  **Espace Opérateur**
    -   Mise en place des routes pour l'ensemble de la section `/operateur`.
    -   Création du `OperateurController` pour gérer toute la logique métier.
    -   Développement des modèles (`OperateurModel`, `ClientModel`, `TransactionModel`, etc.).
    -   Création du layout et du design de l'interface opérateur (`Opérateur/layout.php`).
    -   Développement des vues pour le CRUD complet (Dashboard, Préfixes, Types, Barèmes, Gains, Comptes clients).

3.  **Documentation & Structure**
    -   Création du `README.md` principal du projet.
    -   Mise en place de la structure initiale de CodeIgniter 4.

---

## 👨‍💻 Lucas (ID: 4394)

Lucas a pris en charge l'intégralité de l'**Espace Client**, de l'authentification aux fonctionnalités de transaction.

### Tâches réalisées :

1.  **Espace Client**
    -   Mise en place des routes pour la partie client (`/login`, `/solde`, `/depot`, etc.).
    -   Création des contrôleurs `Client`, `Auth` et `Transaction` pour gérer la logique client.
    -   Développement du système d'authentification par numéro de téléphone.
    -   Création d'un layout et d'un design soigné et accueillant pour l'interface client (`Client/layout.php`, `public/css/client.css`).
    -   Développement des vues pour :
        -   La connexion (`login.php`).
        -   L'affichage du solde et de l'historique (`solde.php`).
        -   Les formulaires de dépôt, retrait et transfert.