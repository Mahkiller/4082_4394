<?= $this->extend('Opérateur/layout') ?>

<?= $this->section('title') ?>Comptes clients<?= $this->endSection() ?>

<?= $this->section('content') ?>
<p class="text-muted">Situation des comptes clients et de leurs soldes.</p>

<div class="card mb-3">
    <div class="card-body text-center">
        <div class="text-muted">Solde total des clients</div>
        <div class="display-5 fw-bold"><?= number_format($totalSolde, 0, ',', ' ') ?> Ar</div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Liste des clients (<?= count($clients) ?>)</span>
        <a href="<?= base_url('operateur/client/ajouter') ?>" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Ajouter un client</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr><th>#</th><th>Numéro</th><th class="text-end">Solde (Ar)</th><th class="text-end">Transactions</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($clients)): ?>
                        <tr><td colspan="5" class="text-center text-muted">Aucun client</td></tr>
                    <?php else: ?>
                        <?php foreach ($clients as $c): ?>
                            <tr>
                                <td><?= $c->id ?></td>
                                <td><code><?= esc($c->numero) ?></code></td>
                                <td class="text-end"><?= number_format($c->solde, 0, ',', ' ') ?></td>
                                <td class="text-end"><span class="badge bg-light text-dark"><?= $c->nbTransac ?></span></td>
                                <td class="text-end">
                                    <a href="<?= base_url('operateur/client/modifier/' . $c->id) ?>" class="btn btn-sm btn-outline-primary" title="Modifier"><i class="bi bi-pencil"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
