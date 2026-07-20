<?= $this->extend('Opérateur/layout') ?>

<?= $this->section('title') ?>Ajouter un client<?= $this->endSection() ?>

<?= $this->section('content') ?>
<p class="text-muted">Création d'un compte client (numéro et solde initial).</p>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Nouveau client</div>
            <div class="card-body">
                <?= form_open('operateur/client/store') ?>
                    <div class="mb-3">
                        <label class="form-label">Numéro de téléphone</label>
                        <div class="input-group">
                            <select name="prefixe" class="form-select" style="max-width:110px;">
                                <?php foreach ($prefixes as $p): ?>
                                    <option value="<?= esc($p->prefixe) ?>"><?= esc($p->prefixe) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="reste" class="form-control" placeholder="1000001" maxlength="7" required>
                        </div>
                        <input type="hidden" name="numero" id="numero">
                        <div class="form-text">Préfixe de l'opérateur + 7 chiffres restants.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Solde initial (Ar)</label>
                        <input type="number" step="0.01" name="solde" class="form-control" value="0" min="0">
                    </div>
                    <button type="submit" class="btn btn-primary" onclick="document.getElementById('numero').value = document.querySelector('[name=prefixe]').value + document.querySelector('[name=reste]').value"><i class="bi bi-check-lg"></i> Enregistrer</button>
                    <a href="<?= base_url('operateur/comptes') ?>" class="btn btn-outline-secondary">Annuler</a>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
