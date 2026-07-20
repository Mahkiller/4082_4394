<?= $this->extend('Opérateur/layout') ?>

<?= $this->section('title') ?>Types d'opérations<?= $this->endSection() ?>

<?= $this->section('content') ?>
<p class="text-muted">Création des types d'opérations (dépôt, retrait, transfert) et de leurs barèmes de frais.</p>

<div class="card mb-3">
    <div class="card-header">Ajouter un type d'opération</div>
    <div class="card-body">
        <form method="post" action="<?= base_url('operateur/type/ajouter') ?>" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Code</label>
                <input type="text" name="code" class="form-control" placeholder="Ex: DEPOSIT" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Libellé</label>
                <input type="text" name="label" class="form-control" placeholder="Ex: Dépôt" required>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" type="submit"><i class="bi bi-plus-lg"></i> Ajouter</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($types as $type): ?>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><strong><?= esc($type->label) ?></strong> <span class="badge bg-secondary"><?= esc($type->code) ?></span></span>
            <div>
                <a href="<?= base_url('operateur/montants/' . $type->id) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-sliders"></i> Barèmes</a>
                <a href="<?= base_url('operateur/type/supprimer/' . $type->id) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ce type et ses barèmes ?')"><i class="bi bi-trash"></i></a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead>
                        <tr><th>Tranche min (Ar)</th><th>Tranche max (Ar)</th><th class="text-end">Frais (Ar)</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($type->tranches)): ?>
                            <tr><td colspan="3" class="text-center text-muted">Aucun barème</td></tr>
                        <?php else: ?>
                            <?php foreach ($type->tranches as $tr): ?>
                                <tr>
                                    <td><?= number_format($tr->min_montant, 0, ',', ' ') ?></td>
                                    <td><?= number_format($tr->max_montant, 0, ',', ' ') ?></td>
                                    <td class="text-end"><?= number_format($tr->frais_montant, 0, ',', ' ') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endforeach; ?>
<?= $this->endSection() ?>
