<?= $this->extend('Opérateur/layout') ?>

<?= $this->section('title') ?>Modifier un client<?= $this->endSection() ?>

<?= $this->section('content') ?>
<p class="text-muted">Modification du compte client (les changements sont enregistrés en base).</p>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Client #<?= $client->id ?></div>
            <div class="card-body">
                <?= form_open('operateur/client/update/' . $client->id) ?>
                    <div class="mb-3">
                        <label class="form-label">Numéro de téléphone</label>
                        <input type="text" name="numero" class="form-control" value="<?= esc($client->numero) ?>" maxlength="10" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Solde (Ar)</label>
                        <input type="number" step="0.01" name="solde" class="form-control" value="<?= $client->solde ?>" min="0">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Enregistrer</button>
                    <a href="<?= base_url('operateur/comptes') ?>" class="btn btn-outline-secondary">Annuler</a>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
