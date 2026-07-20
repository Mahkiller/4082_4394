# CodeIgniter 4 — Les Vues

## 1. Où ça se passe

```
app/Views/
```

---

## 2. Charger une vue depuis un contrôleur

```php
public function index()
{
    $data = [
        'titre'    => 'Liste des produits',
        'produits' => $this->produitModel->findAll(),
    ];

    return view('produits/index', $data);
    // charge app/Views/produits/index.php
}
```

Dans la vue, les clés du tableau `$data` deviennent directement des **variables PHP** :

```php
<!-- app/Views/produits/index.php -->
<h1><?= esc($titre) ?></h1>

<ul>
<?php foreach ($produits as $produit): ?>
    <li><?= esc($produit['nom']) ?> — <?= number_format($produit['prix']) ?> Ar</li>
<?php endforeach; ?>
</ul>
```

> **Toujours utiliser `esc()`** pour afficher une donnée venant de l'utilisateur → protège contre les failles XSS.

---

## 3. Layouts avec les templates (extend / section)

### Le layout parent

```php
<!-- app/Views/templates/main.php -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <title><?= $titre ?? 'Mon site' ?></title>
</head>
<body>
    <?= $this->renderSection('contenu') ?>
</body>
</html>
```

### La vue enfant

```php
<!-- app/Views/produits/index.php -->
<?= $this->extend('templates/main') ?>

<?= $this->section('contenu') ?>
    <h1>Liste des produits</h1>
    <?php foreach ($produits as $produit): ?>
        <p><?= esc($produit['nom']) ?></p>
    <?php endforeach; ?>
<?= $this->endSection() ?>
```

---

## 4. Inclure une sous-vue (partial)

```php
<?= $this->include('partials/navbar') ?>
```

---

## 5. Formulaires (avec le helper `form`)

```php
<?= form_open('produits/create') ?>
    <label>Nom</label>
    <?= form_input('nom', old('nom')) ?>

    <label>Prix</label>
    <?= form_input('prix', old('prix')) ?>

    <?= form_submit('submit', 'Enregistrer') ?>
<?= form_close() ?>
```

Équivalent en HTML pur (souvent utilisé en pratique) :

```php
<form action="<?= site_url('produits/create') ?>" method="post">
    <?= csrf_field() ?>

    <input type="text" name="nom" value="<?= old('nom') ?>">
    <input type="number" name="prix" value="<?= old('prix') ?>">

    <button type="submit">Enregistrer</button>
</form>
```

> `csrf_field()` est **obligatoire** dès que la protection CSRF est activée (`app/Config/Filters.php`).
> `old('nom')` récupère la valeur précédemment saisie après une erreur de validation (avec `withInput()`).

---

## 6. Afficher les erreurs de validation

```php
<?php if (session('errors')): ?>
    <ul class="erreurs">
        <?php foreach (session('errors') as $erreur): ?>
            <li><?= esc($erreur) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
```

Ou, si tu utilises `$validator` directement dans la vue :

```php
<?= $validation->listErrors() ?>
```

---

## 7. Messages flash (succès / erreur après redirection)

Dans le contrôleur :
```php
return redirect()->to('/produits')->with('success', 'Produit ajouté !');
```

Dans la vue :
```php
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>
```

---

## 8. Vues d'API : pas de vue HTML, juste du JSON

Pour une API, on ne passe généralement PAS par `view()` — la réponse vient directement du contrôleur (voir `02-controllers.md`, section réponse JSON).

---

## 9. Erreurs fréquentes à l'examen

1. **Oublier `esc()`** sur une donnée affichée → faille XSS potentielle (souvent noté en examen sécurité).
2. **Oublier `<?= $this->endSection() ?>`** dans une vue enfant → erreur ou section vide.
3. **Mauvais chemin de vue** : `view('produits/index')` charge `app/Views/produits/index.php`, pas un fichier à la racine de `Views/`.
4. **Oublier `csrf_field()`** dans un formulaire alors que le filtre CSRF est actif → erreur 403.
5. **Utiliser `$_POST` directement dans la vue** au lieu de passer les données depuis le contrôleur (mauvaise pratique MVC).
