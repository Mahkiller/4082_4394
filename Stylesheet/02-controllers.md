# CodeIgniter 4 — Les Contrôleurs

## 1. Où ça se passe

```
app/Controllers/
```

Chaque contrôleur **étend** `CodeIgniter\Controller` (ou `BaseController` fourni par défaut, qui étend lui-même `Controller`).

---

## 2. Structure de base

```php
<?php

namespace App\Controllers;

class ProduitController extends BaseController
{
    public function index()
    {
        return view('produits/index');
    }

    public function show($id)
    {
        return view('produits/show', ['id' => $id]);
    }
}
```

> Le `namespace App\Controllers;` doit correspondre exactement au dossier du fichier.
> Si le fichier est dans `app/Controllers/Admin/DashboardController.php`, le namespace doit être `App\Controllers\Admin;`.

---

## 3. Récupérer les données d'une requête

```php
public function create()
{
    // Récupérer un champ POST précis
    $nom = $this->request->getPost('nom');

    // Récupérer TOUS les champs POST sous forme de tableau
    $data = $this->request->getPost();

    // Récupérer un paramètre GET (?search=xyz)
    $search = $this->request->getGet('search');

    // Récupérer du JSON envoyé dans le corps de la requête (API)
    $json = $this->request->getJSON(); // objet
    $json = $this->request->getJSON(true); // tableau associatif

    // Récupérer TOUTES les données quelle que soit la méthode
    $data = $this->request->getVar('nom');
}
```

---

## 4. Retourner une réponse

### Une vue (HTML classique)

```php
public function index()
{
    $data['produits'] = $this->produitModel->findAll();
    return view('produits/index', $data);
}
```

### Une réponse JSON (API)

```php
public function apiIndex()
{
    $produits = $this->produitModel->findAll();

    return $this->response->setJSON($produits);
}

public function apiShow($id)
{
    $produit = $this->produitModel->find($id);

    if ($produit === null) {
        return $this->response->setStatusCode(404)->setJSON([
            'error' => 'Produit introuvable',
        ]);
    }

    return $this->response->setJSON($produit);
}
```

### Redirection

```php
public function delete($id)
{
    $this->produitModel->delete($id);

    return redirect()->to('/produits')->with('success', 'Produit supprimé');
}

// Retour à la page précédente
return redirect()->back()->with('error', 'Formulaire invalide');
```

---

## 5. Validation dans un contrôleur (pattern très demandé en examen)

```php
public function create()
{
    $rules = [
        'nom'   => 'required|min_length[3]|max_length[100]',
        'prix'  => 'required|numeric|greater_than[0]',
        'email' => 'required|valid_email',
    ];

    if (! $this->validate($rules)) {
        // Renvoie les erreurs vers la vue précédente
        return redirect()->back()
            ->withInput()
            ->with('errors', $this->validator->getErrors());
    }

    $this->produitModel->save([
        'nom'  => $this->request->getPost('nom'),
        'prix' => $this->request->getPost('prix'),
    ]);

    return redirect()->to('/produits')->with('success', 'Produit créé avec succès');
}
```

---

## 6. Charger un modèle dans un contrôleur

**Méthode 1 — dans le constructeur (recommandé)**

```php
<?php

namespace App\Controllers;

use App\Models\ProduitModel;

class ProduitController extends BaseController
{
    protected $produitModel;

    public function __construct()
    {
        $this->produitModel = new ProduitModel();
    }

    public function index()
    {
        $data['produits'] = $this->produitModel->findAll();
        return view('produits/index', $data);
    }
}
```

**Méthode 2 — via le helper `model()`**

```php
public function index()
{
    $produitModel = model('ProduitModel'); // ou model(ProduitModel::class)
    $data['produits'] = $produitModel->findAll();
    return view('produits/index', $data);
}
```

---

## 7. BaseController et méthodes communes

`app/Controllers/BaseController.php` est le parent de tous tes contrôleurs. On y met souvent :
- les helpers à charger automatiquement (`$helpers = ['form', 'url'];`)
- une méthode `initController()` (appelée avant chaque action)

```php
class BaseController extends Controller
{
    protected $helpers = ['form', 'url', 'text'];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        // Code exécuté avant CHAQUE méthode de CHAQUE contrôleur enfant
    }
}
```

---

## 8. Erreurs fréquentes à l'examen

1. **Oublier `extends BaseController`** → les helpers et raccourcis (`$this->request`, `view()`, etc.) restent disponibles, mais on perd les initialisations custom du projet.
2. **Confondre `getPost()` et `getVar()`** : `getPost()` ne lit QUE les données POST ; `getVar()` lit POST + GET.
3. **Oublier `withInput()`** lors d'une redirection après erreur de validation → le formulaire est vidé côté vue.
4. **Ne pas vérifier `$produit === null`** après un `find()` → erreur fatale si l'ID n'existe pas.
5. **Namespace incorrect** dans un sous-dossier de contrôleurs (`Admin/`, `Api/`, etc.).
