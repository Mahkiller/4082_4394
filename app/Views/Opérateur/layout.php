<?php
$uri = uri_string();
$segs = explode('/', trim($uri, '/'));
$section = $segs[1] ?? '';

$activeLinks = [
    ''         => $section === '',
    'prefixe'  => $section === 'prefixe',
    'types'    => $section === 'types' || $section === 'montants',
    'gains'    => $section === 'gains',
    'comptes'  => $section === 'comptes',
    'client'   => $section === 'client' && ($segs[2] ?? '') === 'ajouter',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->renderSection('title') ?> | Espace Opérateur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --sidebar-bg: #0f172a; --sidebar-bg-2: #1e293b; --accent: #6366f1; }
        body { background: #eef2f7; font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, var(--sidebar-bg) 0%, var(--sidebar-bg-2) 100%);
            color: #e2e8f0;
            position: sticky; top: 0;
        }
        .sidebar .brand { color: #fff; font-weight: 700; font-size: 1.15rem; letter-spacing: .5px; }
        .sidebar .brand i { color: var(--accent); }
        .sidebar .nav-link {
            color: #94a3b8;
            border-radius: 10px;
            margin-bottom: 6px;
            padding: 10px 14px;
            transition: all .15s ease;
            border-left: 3px solid transparent;
        }
        .sidebar .nav-link i { width: 20px; }
        .sidebar .nav-link:hover { background: #1e293b; color: #fff; }
        .sidebar .nav-link.active {
            background: rgba(99,102,241,.15);
            color: #fff;
            border-left: 3px solid var(--accent);
            font-weight: 600;
        }
        .stat-card {
            border: none;
            border-left: 4px solid var(--accent);
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(15,23,42,.06);
            background: #fff;
        }
        .stat-label { color: #64748b; font-size: .85rem; }
        .stat-value { font-size: 1.6rem; font-weight: 700; color: #0f172a; }
        .content-wrap { padding: 28px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(15,23,42,.06); }
        .card-header { background: #fff; border-bottom: 1px solid #eef2f7; font-weight: 600; }
        .table thead th { background: #f8fafc; color: #475569; font-weight: 600; border-bottom-width: 1px; }
        .badge-prefix { background: #e0e7ff; color: #4338ca; font-weight: 600; }
        .btn-action { min-width: 36px; font-weight: 600; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; font-size: .875rem; }
        .btn-edit { background: #4f46e5; border-color: #4f46e5; color: #fff; }
        .btn-edit:hover { background: #4338ca; border-color: #4338ca; color: #fff; }
        .btn-delete { background: #ef4444; border-color: #ef4444; color: #fff; }
        .btn-delete:hover { background: #dc2626; border-color: #dc2626; color: #fff; }
        .btn-sm-action { padding: 4px 10px; font-size: .8rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <aside class="col-lg-2 sidebar p-3">
            <div class="brand mb-3"><i class="bi bi-phone"></i> MobileMoney</div>
            <div class="mb-4 text-center">
                <div class="badge bg-light text-dark fs-6">Opérateur YAS</div>
            </div>
            <nav class="nav flex-column">
                <a class="nav-link <?= $activeLinks[''] ? 'active' : '' ?>" href="<?= base_url('operateur') ?>"><i class="bi bi-grid-1x2"></i> Tableau de bord</a>
                <a class="nav-link <?= $activeLinks['prefixe'] ? 'active' : '' ?>" href="<?= base_url('operateur/prefixe') ?>"><i class="bi bi-signpost"></i> Préfixes</a>
                <a class="nav-link <?= $activeLinks['types'] ? 'active' : '' ?>" href="<?= base_url('operateur/types') ?>"><i class="bi bi-list-check"></i> Types & barèmes</a>
                <a class="nav-link <?= $activeLinks['gains'] ? 'active' : '' ?>" href="<?= base_url('operateur/gains') ?>"><i class="bi bi-graph-up-arrow"></i> Gains</a>
                <a class="nav-link <?= $activeLinks['comptes'] ? 'active' : '' ?>" href="<?= base_url('operateur/comptes') ?>"><i class="bi bi-wallet2"></i> Comptes clients</a>
                <a class="nav-link <?= $section === 'commissions' ? 'active' : '' ?>" href="<?= base_url('operateur/commissions') ?>"><i class="bi bi-percent"></i> Commissions</a>
                <a class="nav-link <?= $activeLinks['client'] ? 'active' : '' ?>" href="<?= base_url('operateur/client/ajouter') ?>"><i class="bi bi-person-plus"></i> Ajouter client</a>
                <a class="nav-link" href="<?= base_url('login') ?>" style="color:#fbbf24;"><i class="bi bi-phone"></i> Mode client</a>
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
