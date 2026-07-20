<?= $this->extend('Client/layout') ?>

<?= $this->section('title') ?>Dépôt<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="client-card">
    <div class="client-card-header">
        <i class="bi bi-arrow-down-circle"></i>
        <h1>Faire un dépôt</h1>
        <p>Créditez votre compte en toute simplicité</p>
    </div>
    <div class="client-card-body">
        <form action="<?= site_url('depot') ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="montant" class="form-label">Montant (Ar)</label>
                <input type="number" min="100" step="100" class="form-control form-control-lg" id="montant"
                       name="montant" placeholder="Ex : 10000" required>
            </div>
            <button type="submit" class="btn btn-mm w-100 mt-2"><i class="bi bi-check-lg"></i> Déposer</button>
        </form>
        <a href="<?= base_url('solde') ?>" class="back-link"><i class="bi bi-arrow-left"></i> Retour au solde</a>
    </div>
</div>
<?= $this->endSection() ?>
