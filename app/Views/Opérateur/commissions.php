<?= $this->extend('Opérateur/layout') ?>

<?= $this->section('title') ?>Commissions<?= $this->endSection() ?>

<?= $this->section('content') ?>
<p class="text-muted">Configuration des commissions inter-opérateurs pour les transferts (% du montant transféré).</p>

<div class="row mb-3">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">Ajouter une commission</div>
            <div class="card-body">
                <form method="post" action="<?= base_url('operateur/commission/ajouter') ?>">
                    <input type="hidden" name="operateur_source_id" value="1">
                    <div class="mb-2">
                        <label class="form-label">Opérateur source</label>
                        <input type="text" class="form-control" value="YAS" disabled>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Opérateur destinataire</label>
                        <select name="operateur_destinataire_id" class="form-select" required>
                            <?php foreach ($operateurs as $op): ?>
                                <?php if ($op->nom !== 'YAS'): ?>
                                    <option value="<?= $op->id ?>"><?= esc($op->nom) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Pourcentage (%)</label>
                        <input type="number" step="0.01" name="pourcentage" class="form-control" value="10.00" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" placeholder="Ex : Commission YAS vers AIRTEL">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Ajouter</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">Commissions configurées (<?= count($commissions) ?>)</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Source</th>
                                <th>Destinataire</th>
                                <th class="text-end">%</th>
                                <th>Description</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($commissions)): ?>
                                <tr><td colspan="5" class="text-center text-muted">Aucune commission configurée.</td></tr>
                            <?php else: ?>
                                <?php foreach ($commissions as $com): ?>
                                    <tr>
                                        <td><?= esc($com->sourceNom) ?></td>
                                        <td><?= esc($com->destNom) ?></td>
                                        <td class="text-end"><?= number_format($com->pourcentage, 2) ?> %</td>
                                        <td><?= esc($com->description) ?></td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#editModal<?= $com->id ?>" title="Modifier"><i class="bi bi-pencil"></i> <span class="d-none d-sm-inline">Modifier</span></button>
                                            <a href="<?= base_url('operateur/commission/supprimer/' . $com->id) ?>" class="btn btn-sm btn-action btn-delete ms-1" onclick="return confirm('Supprimer cette commission ?')" title="Supprimer"><i class="bi bi-trash"></i> <span class="d-none d-sm-inline">Supprimer</span></a>
                                        </td>
                                    </tr>
                                    <!-- Modal édition -->
                                    <div class="modal fade" id="editModal<?= $com->id ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="post" action="<?= base_url('operateur/commission/modifier/' . $com->id) ?>">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Modifier la commission</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-2">
                                                            <label class="form-label">Source → Destinataire</label>
                                                            <input type="text" class="form-control" value="<?= esc($com->sourceNom) ?> → <?= esc($com->destNom) ?>" disabled>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label">Pourcentage (%)</label>
                                                            <input type="number" step="0.01" name="pourcentage" class="form-control" value="<?= $com->pourcentage ?>" required>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label">Description</label>
                                                            <input type="text" name="description" class="form-control" value="<?= esc($com->description) ?>">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
