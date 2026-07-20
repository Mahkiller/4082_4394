# CodeIgniter 4 — Les Routes

## 1. Où ça se passe

Le fichier de routage se trouve dans :
```
app/Config/Routes.php
```

C'est ici qu'on définit **quelle URL déclenche quelle méthode de quel contrôleur**.

---

## 2. Syntaxe de base

```php
<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Route simple : GET
$routes->get('/', 'Home::index');

// Route vers un autre contrôleur
$routes->get('produits', 'ProduitController::index');

// Avec un paramètre dynamique (segment d'URL)
$routes->get('produits/(:num)', 'ProduitController::show/$1');
$routes->get('produits/(:segment)', 'ProduitController::show/$1');
```

### Les placeholders les plus utilisés

| Placeholder   | Signification                          | Exemple d'URL matchée |
|---------------|------------------------------------------|------------------------|
| `(:num)`      | Chiffres uniquement                      | `produits/12`          |
| `(:alpha)`    | Lettres uniquement                       | `produits/abc`         |
| `(:segment)`  | N'importe quel segment (sans `/`)        | `produits/abc-12`      |
| `(:any)`      | N'importe quoi (peut contenir des `/`)   | `fichiers/a/b/c`       |

Le `$1` dans la méthode correspond à la 1ère parenthèse capturée, `$2` à la 2ème, etc.

```php
$routes->get('produits/(:num)/avis/(:num)', 'ProduitController::avis/$1/$2');
```

---

## 3. Les verbes HTTP

```php
$routes->get('produits', 'ProduitController::index');       // Lister / afficher
$routes->post('produits', 'ProduitController::create');      // Créer
$routes->put('produits/(:num)', 'ProduitController::update/$1');    // Modifier (remplace tout)
$routes->patch('produits/(:num)', 'ProduitController::update/$1');  // Modifier (partiel)
$routes->delete('produits/(:num)', 'ProduitController::delete/$1'); // Supprimer

// Accepter plusieurs verbes pour la même route
$routes->match(['get', 'post'], 'contact', 'ContactController::index');

// Répondre à TOUS les verbes
$routes->add('produits/(:num)', 'ProduitController::show/$1');
```

> **Piège classique d'examen** : `add()` ne filtre PAS par méthode HTTP, contrairement à `get()`, `post()`, etc.

---

## 4. Les groupes de routes (`group`)

Très utile pour préfixer plusieurs routes (ex : un espace admin) :

```php
$routes->group('admin', function ($routes) {
    $routes->get('dashboard', 'Admin\DashboardController::index');
    $routes->get('produits', 'Admin\ProduitController::index');
    $routes->get('produits/(:num)/edit', 'Admin\ProduitController::edit/$1');
});
// -> /admin/dashboard, /admin/produits, /admin/produits/3/edit
```

On peut aussi appliquer un **filtre** (middleware) directement sur le groupe :

```php
$routes->group('admin', ['filter' => 'auth'], function ($routes) {
    $routes->get('dashboard', 'Admin\DashboardController::index');
});
```

---

## 5. Routes nommées (`as`)

Permet de générer une URL depuis un nom plutôt qu'un chemin en dur (pratique si l'URL change plus tard) :

```php
$routes->get('produits/(:num)', 'ProduitController::show/$1', ['as' => 'produit.show']);
```

Dans une vue :
```php
<a href="<?= route_to('produit.show', 12) ?>">Voir le produit</a>
```

---

## 6. Le contrôleur par défaut

```php
$routes->get('/', 'Home::index');
```

Ou via la config générale :
```php
// app/Config/Routes.php
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
```

---

## 7. Resource routes (CRUD automatique)

CI4 peut générer automatiquement toutes les routes CRUD d'un contrôleur :

```php
$routes->resource('produits', ['controller' => 'ProduitController']);
```

Cela crée automatiquement :

| Verbe  | URL                       | Méthode du contrôleur |
|--------|---------------------------|------------------------|
| GET    | produits                  | index()                |
| GET    | produits/new              | new()                  |
| POST   | produits                  | create()                |
| GET    | produits/(:num)           | show($id)               |
| GET    | produits/(:num)/edit      | edit($id)                |
| PUT    | produits/(:num)           | update($id)              |
| DELETE | produits/(:num)           | delete($id)               |

---

## 8. Erreurs fréquentes à l'examen

1. **Oublier `/$1`** → le paramètre d'URL n'est jamais transmis au contrôleur.
2. **Confondre `(:num)` et `(:any)`** → `(:num)` ne matche pas les lettres, donc `produits/abc` renverra une erreur 404 si la route attend `(:num)`.
3. **Mauvais namespace dans les groupes** : si le contrôleur est dans `app/Controllers/Admin/DashboardController.php`, il faut écrire `Admin\DashboardController`, pas juste `DashboardController`.
4. **Ordre des routes** : CI4 s'arrête à la **première route qui correspond**. Une route générique placée trop tôt peut "avaler" une route plus spécifique définie après.

```php
// ❌ Mauvais ordre : la 2e route ne sera jamais atteinte
$routes->get('produits/(:any)', 'ProduitController::show/$1');
$routes->get('produits/nouveau', 'ProduitController::new'); // jamais atteinte !

// ✅ Bon ordre
$routes->get('produits/nouveau', 'ProduitController::new');
$routes->get('produits/(:any)', 'ProduitController::show/$1');
```
