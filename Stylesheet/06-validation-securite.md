# CodeIgniter 4 — Validation, Filtres & Sécurité

## 1. Règles de validation les plus courantes

| Règle                  | Effet                                       |
|-------------------------|----------------------------------------------|
| `required`               | champ obligatoire                             |
| `min_length[3]`           | longueur minimale                              |
| `max_length[100]`          | longueur maximale                               |
| `numeric`                   | doit être un nombre                              |
| `integer`                    | doit être un entier                               |
| `greater_than[0]`             | strictement supérieur à                            |
| `valid_email`                  | format email valide                                 |
| `is_unique[produits.email]`     | doit être unique dans la table/colonne donnée        |
| `matches[password]`              | doit être identique à un autre champ                  |
| `permit_empty`                    | autorise vide (à combiner avec d'autres règles)        |

```php
$rules = [
    'nom'      => 'required|min_length[3]|max_length[100]',
    'email'    => 'required|valid_email|is_unique[utilisateurs.email]',
    'password' => 'required|min_length[8]',
    'confirm'  => 'required|matches[password]',
];
```

---

## 2. Valider dans un contrôleur

```php
if (! $this->validate($rules)) {
    return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
}
```

Ou avec des messages personnalisés :

```php
$rules = [
    'nom' => [
        'rules'  => 'required|min_length[3]',
        'errors' => [
            'required'   => 'Le nom est obligatoire.',
            'min_length' => 'Le nom doit faire au moins 3 caractères.',
        ],
    ],
];
```

---

## 3. CSRF (Cross-Site Request Forgery)

Activer globalement dans `app/Config/Filters.php` :

```php
public array $globals = [
    'before' => [
        'csrf',
        // ...
    ],
];
```

Dans chaque formulaire :
```php
<form method="post" action="...">
    <?= csrf_field() ?>
    ...
</form>
```

> Sans `csrf_field()`, la requête POST renvoie une erreur **403 Forbidden**.

---

## 4. Les Filtres (équivalent middleware)

### Créer un filtre

```bash
php spark make:filter AuthFilter
```

```php
<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // code exécuté après la réponse (souvent vide)
    }
}
```

### Enregistrer et appliquer le filtre

```php
// app/Config/Filters.php
public array $aliases = [
    'auth' => \App\Filters\AuthFilter::class,
];
```

```php
// app/Config/Routes.php
$routes->group('admin', ['filter' => 'auth'], function ($routes) {
    $routes->get('dashboard', 'Admin\DashboardController::index');
});
```

---

## 5. Rôles / Contrôle d'accès (RBAC simple, pattern typique d'examen)

```php
class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $roleAutorise = $arguments[0] ?? null; // ex: 'admin'
        $roleUtilisateur = session()->get('role');

        if ($roleUtilisateur !== $roleAutorise) {
            return redirect()->to('/')->with('error', 'Accès refusé.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
```

```php
$routes->group('admin', ['filter' => 'role:admin'], function ($routes) {
    $routes->get('dashboard', 'Admin\DashboardController::index');
});
```

---

## 6. Mots de passe : hachage sécurisé

```php
// À l'inscription
$hash = password_hash($motDePasse, PASSWORD_DEFAULT);
$this->userModel->insert(['email' => $email, 'password' => $hash]);

// À la connexion
$user = $this->userModel->where('email', $email)->first();

if ($user && password_verify($motDePasse, $user['password'])) {
    session()->set(['logged_in' => true, 'user_id' => $user['id']]);
} else {
    // échec
}
```

> **Ne jamais stocker un mot de passe en clair ou avec `md5()`/`sha1()`** — c'est un piège classique d'examen sécurité.

---

## 7. Sessions

```php
// Définir des données de session
session()->set([
    'user_id'    => $user['id'],
    'logged_in'  => true,
]);

// Lire
$id = session()->get('user_id');

// Détruire (déconnexion)
session()->destroy();
```

---

## 8. Erreurs fréquentes à l'examen

1. **Oublier `is_unique`** sur un champ email lors de l'inscription → doublons possibles.
2. **Utiliser `md5()` pour un mot de passe** au lieu de `password_hash()`.
3. **Filtre déclaré dans `$aliases` mais jamais appliqué** dans les routes.
4. **Oublier `csrf_field()`** → 403 Forbidden en soumission de formulaire.
5. **Oublier de détruire la session** (`session()->destroy()`) à la déconnexion.
