<?= $this->extend('Opérateur/layout') ?>

<?= $this->section('title') ?>Tableau de bord<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-label">Préfixes</div>
                <div class="stat-value"><?= $nbPrefixes ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-label">Types d'opération</div>
                <div class="stat-value"><?= $nbTypes ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-label">Clients</div>
                <div class="stat-value"><?= $nbClients ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-label">Gains (Ar)</div>
                <div class="stat-value"><?= number_format($gains, 0, ',', ' ') ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history"></i> Dernières transactions</span>
        <span class="badge bg-secondary"><?= $nbTransac ?> au total</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th class="text-end">Montant</th>
                        <th class="text-end">Frais</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dernieres)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-3">Aucune transaction</td></tr>
                    <?php else: ?>
                        <?php foreach ($dernieres as $t): ?>
                            <?php
                            $badge = $t->status === 'Reussi' ? 'success' : ($t->status === 'Echoue' ? 'danger' : 'warning');
                            ?>
                            <tr>
                                <td><code><?= esc($t->reference) ?></code></td>
                                <td><?= esc($t->client_numero ?? ('#' . $t->client_id)) ?></td>
                                <td><?= esc($t->type_label ?? ('#' . $t->transaction_type_id)) ?></td>
                                <td class="text-end"><?= number_format($t->montant, 0, ',', ' ') ?> Ar</td>
                                <td class="text-end"><?= number_format($t->frais_applique, 0, ',', ' ') ?> Ar</td>
                                <td><span class="badge bg-<?= $badge ?>"><?= esc($t->status) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
