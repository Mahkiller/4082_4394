<?php
$current = uri_string();
function clientActive($current, $seg): string
{
    $premier = explode('/', trim($current, '/'))[0] ?? '';
    return $premier === $seg ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->renderSection('title') ?> | MobileMoney</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/client.css') ?>">
</head>
<body>

<?php if (session()->get('user_numero')): ?>
<nav class="navbar client-navbar">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url('solde') ?>">
            <i class="bi bi-phone-vibrate"></i> MobileMoney
        </a>
        <div class="d-flex align-items-center gap-3">
            <span class="client-user"><i class="bi bi-person-circle"></i> <?= esc(session()->get('user_numero')) ?></span>
            <a href="<?= base_url('logout') ?>" class="btn btn-sm btn-light"><i class="bi bi-box-arrow-right"></i> Quitter</a>
        </div>
    </div>
</nav>
<?php endif; ?>

<main class="client-main">
    <?php if (session()->get('success')): ?>
        <div class="container"><div class="alert alert-success"><?= esc(session()->get('success')) ?></div></div>
    <?php endif; ?>
    <?php if (session()->get('error')): ?>
        <div class="container"><div class="alert alert-danger"><?= esc(session()->get('error')) ?></div></div>
    <?php endif; ?>
    <?= $this->renderSection('content') ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?= $this->renderSection('scripts') ?>

<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer l'opération</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="confirm_message"></p>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <tbody id="confirm_details"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-mm" id="confirm_btn">Confirmer</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>
