<?php
$current = basename($_SERVER['REQUEST_URI'] ?? '');
$segments = explode('/', trim($current, '/'));
$active = $segments[1] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->renderSection('title') ?> | Espace Opérateur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6fb; }
        .sidebar { min-height: 100vh; background: #1e293b; }
        .sidebar .nav-link { color: #cbd5e1; border-radius: 8px; margin-bottom: 4px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #334155; color: #fff; }
        .sidebar .brand { color: #fff; font-weight: 700; }
        .stat-card { border: none; border-left: 4px solid #4f46e5; box-shadow: 0 2px 8px rgba(0,0,0,.05); }
        .stat-label { color: #64748b; font-size: .85rem; }
        .stat-value { font-size: 1.6rem; font-weight: 700; }
        .content-wrap { padding: 24px; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <aside class="col-lg-2 sidebar p-3">
            <div class="brand mb-3"><i class="bi bi-phone"></i> MobileMoney</div>
            <form method="get" action="<?= base_url('operateur/selectionner') ?>" class="mb-3">
                <label class="text-white-50 small d-block mb-1">Opérateur</label>
                <div class="input-group input-group-sm">
                    <select name="operateur_id" class="form-select" onchange="this.form.submit()">
                        <option value="all" <?= (session('operateur_id') ? '' : 'selected') ?>>Tous les opérateurs</option>
                        <?php foreach (($operateurs ?? []) as $op): ?>
                            <option value="<?= $op->id ?>" <?= (session('operateur_id') == $op->id ? 'selected' : '') ?>><?= esc($op->nom) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <input type="hidden" name="redirect" value="<?= '/' . esc(uri_string()) ?>">
            </form>
            <nav class="nav flex-column">
                <a class="nav-link <?= $active === '' ? 'active' : '' ?>" href="<?= base_url('operateur') ?>"><i class="bi bi-grid-1x2"></i> Tableau de bord</a>
                <a class="nav-link <?= $active === 'prefixe' ? 'active' : '' ?>" href="<?= base_url('operateur/prefixe') ?>"><i class="bi bi-signpost"></i> Préfixes</a>
                <a class="nav-link <?= $active === 'types' ? 'active' : '' ?>" href="<?= base_url('operateur/types') ?>"><i class="bi bi-list-check"></i> Types & barèmes</a>
                <a class="nav-link <?= $active === 'gains' ? 'active' : '' ?>" href="<?= base_url('operateur/gains') ?>"><i class="bi bi-graph-up-arrow"></i> Gains</a>
                <a class="nav-link <?= $active === 'comptes' ? 'active' : '' ?>" href="<?= base_url('operateur/comptes') ?>"><i class="bi bi-wallet2"></i> Comptes clients</a>
                <a class="nav-link <?= $active === 'client' ? 'active' : '' ?>" href="<?= base_url('operateur/client/ajouter') ?>"><i class="bi bi-person-plus"></i> Ajouter client</a>
            </nav>
        </aside>
        <main class="col-lg-10 content-wrap">
            <?php if (session()->get('success')): ?>
                <div class="alert alert-success"><?= esc(session()->get('success')) ?></div>
            <?php endif; ?>
            <?php if (session()->get('error')): ?>
                <div class="alert alert-danger"><?= esc(session()->get('error')) ?></div>
            <?php endif; ?>
            <h4 class="mb-3"><?= $this->renderSection('title') ?></h4>
            <?= $this->renderSection('content') ?>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
