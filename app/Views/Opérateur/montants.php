<?= $this->extend('Opérateur/layout') ?>

<?= $this->section('title') ?>Barèmes - <?= esc($type->label) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<p class="text-muted">Barèmes de frais par tranche de montant pour le type <strong><?= esc($type->label) ?></strong> (modifiable).</p>

<div class="card mb-3">
    <div class="card-header">Ajouter une tranche</div>
    <div class="card-body">
        <form method="post" action="<?= base_url('operateur/montant/ajouter') ?>" class="row g-2 align-items-end">
            <input type="hidden" name="transaction_type_id" value="<?= $type->id ?>">
            <div class="col-md-3">
                <label class="form-label">Min (Ar)</label>
                <input type="number" step="0.01" name="min_montant" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Max (Ar)</label>
                <input type="number" step="0.01" name="max_montant" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Frais (Ar)</label>
                <input type="number" step="0.01" name="frais_montant" class="form-control" required>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100" type="submit"><i class="bi bi-plus-lg"></i> Ajouter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Tranches configurées (<?= count($tranches) ?>)</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead>
                    <tr><th>Tranche min (Ar)</th><th>Tranche max (Ar)</th><th class="text-end">Frais (Ar)</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($tranches)): ?>
                        <tr><td colspan="4" class="text-center text-muted">Aucune tranche</td></tr>
                    <?php else: ?>
                        <?php foreach ($tranches as $tr): ?>
                            <tr>
                                <form method="post" action="<?= base_url('operateur/montant/modifier/' . $tr->id) ?>">
                                    <td><input type="number" step="0.01" name="min_montant" class="form-control form-control-sm" value="<?= $tr->min_montant ?>" required></td>
                                    <td><input type="number" step="0.01" name="max_montant" class="form-control form-control-sm" value="<?= $tr->max_montant ?>" required></td>
                                    <td class="text-end"><input type="number" step="0.01" name="frais_montant" class="form-control form-control-sm text-end" value="<?= $tr->frais_montant ?>" required></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-success" type="submit" title="Enregistrer"><i class="bi bi-check-lg"></i></button>
                                        <a href="<?= base_url('operateur/montant/supprimer/' . $tr->id) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ?')"><i class="bi bi-trash"></i></a>
                                    </td>
                                </form>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
