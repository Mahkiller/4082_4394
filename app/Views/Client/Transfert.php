<?= $this->extend('Client/layout') ?>

<?= $this->section('title') ?>Transfert<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="client-card">
    <div class="client-card-header">
        <i class="bi bi-send"></i>
        <h1>Faire un transfert</h1>
        <p>Envoyez de l'argent à un autre numéro</p>
    </div>
    <div class="client-card-body">
        <form action="<?= site_url('transfert') ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="destinataire" class="form-label">Numéro du destinataire</label>
                <input type="text" class="form-control form-control-lg" id="destinataire"
                       name="destinataire" placeholder="Ex : 0320000000" required>
            </div>
            <div class="mb-3">
                <label for="montant" class="form-label">Montant (Ar)</label>
                <input type="number" min="100" step="100" class="form-control form-control-lg" id="montant"
                       name="montant" placeholder="Ex : 5000" required>
            </div>
            <button type="submit" class="btn btn-mm w-100 mt-2"><i class="bi bi-check-lg"></i> Transférer</button>
        </form>
        <a href="<?= base_url('solde') ?>" class="back-link"><i class="bi bi-arrow-left"></i> Retour au solde</a>
    </div>
</div>
<?= $this->endSection() ?>
