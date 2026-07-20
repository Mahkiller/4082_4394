<?= $this->extend('Opérateur/layout') ?>

<?= $this->section('title') ?>Situation des gains<?= $this->endSection() ?>

<?= $this->section('content') ?>
<p class="text-muted">Gains générés par les frais des opérations réussies.</p>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-label">Gains YAS (Ar)</div>
                <div class="stat-value text-success"><?= number_format($totalYas, 0, ',', ' ') ?></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-label">Gains autres opérateurs (Ar)</div>
                <div class="stat-value text-warning"><?= number_format($totalAutres, 0, ',', ' ') ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">Détail par type d'opération — YAS</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Type</th><th>Code</th><th class="text-end">Volume (Ar)</th><th class="text-end">Gain (Ar)</th></tr></thead>
                <tbody>
                    <?php foreach ($detailsYas as $d): ?>
                        <tr><td><?= esc($d['type']->label) ?></td><td><span class="badge bg-secondary"><?= esc($d['type']->code) ?></span></td><td class="text-end"><?= number_format($d['volume'], 0, ',', ' ') ?></td><td class="text-end fw-bold text-success"><?= number_format($d['gain'], 0, ',', ' ') ?></td></tr>
                    <?php endforeach; ?>
                    <tr class="table-light"><td colspan="3" class="text-end fw-bold">Total</td><td class="text-end fw-bold text-success"><?= number_format($totalYas, 0, ',', ' ') ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Détail par type d'opération — Autres opérateurs</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Type</th><th>Code</th><th class="text-end">Volume (Ar)</th><th class="text-end">Frais (Ar)</th><th class="text-end">Commission (Ar)</th></tr></thead>
                <tbody>
                    <?php foreach ($detailsAutres as $d): ?>
                        <tr>
                            <td><?= esc($d['type']->label) ?></td>
                            <td><span class="badge bg-secondary"><?= esc($d['type']->code) ?></span></td>
                            <td class="text-end"><?= number_format($d['volume'], 0, ',', ' ') ?></td>
                            <td class="text-end fw-bold text-warning"><?= number_format($d['gain'], 0, ',', ' ') ?></td>
                            <?php if (($d['type']->code ?? '') === 'TRANSFER' && !empty($d['commission'])): ?>
                                <td class="text-end text-info">+ <?= number_format($d['commission'], 0, ',', ' ') ?></td>
                            <?php else: ?>
                                <td class="text-end text-muted">-</td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-light">
                        <td colspan="4" class="text-end fw-bold">Total (frais + commissions)</td>
                        <td class="text-end fw-bold text-warning"><?= number_format($totalAutres, 0, ',', ' ') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
