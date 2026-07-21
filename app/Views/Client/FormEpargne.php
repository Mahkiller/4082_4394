<?= $this->extend('Client/layout') ?>

<?= $this->section('title') ?>Epargne<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="client-card">
    <div class="client-card-header">
        <i class="bi bi-arrow-up-circle"></i>
        <h1>Epargne</h1>
        <p>Choisir votre pourcentage d'epargne</p>
    </div>
    <div class="client-card-body">
        <form id="form_epargne" action="<?= site_url('epargne') ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="pourcentageEpargne" class="form-label">Pourcentage de l'Epargne (%)</label>
                <input type="number" min="0" step="100" class="form-control form-control-lg" id="pourcentageEpargne"
                       name="pourcentageEpargne" placeholder="Ex : 10" required>
            </div>
            <button type="submit" class="btn btn-mm w-100 mt-2"><i class="bi bi-check-lg"></i> Valider</button>
        </form>
        <a href="<?= base_url('solde') ?>" class="back-link"><i class="bi bi-arrow-left"></i> Retour au solde</a>
    </div>
</div>
