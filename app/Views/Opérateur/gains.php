<?= $this->extend('Opérateur/layout') ?>

<?= $this->section('title') ?>Situation des gains<?= $this->endSection() ?>

<?= $this->section('content') ?>
<p class="text-muted">Gains générés par les frais des opérations réussies (retrait et transfert).</p>

<div class="card mb-3">
    <div class="card-body text-center">
        <div class="text-muted">Gain total</div>
        <div class="display-5 fw-bold text-success"><?= number_format($total, 0, ',', ' ') ?> Ar</div>
    </div>
</div>

<div class="card">
    <div class="card-header">Détail par type d'opération</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr><th>Type</th><th>Code</th><th class="text-end">Volume (Ar)</th><th class="text-end">Gain (Ar)</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($details as $d): ?>
                        <tr>
                            <td><?= esc($d['type']->label) ?></td>
                            <td><span class="badge bg-secondary"><?= esc($d['type']->code) ?></span></td>
                            <td class="text-end"><?= number_format($d['volume'], 0, ',', ' ') ?></td>
                            <td class="text-end fw-bold text-success"><?= number_format($d['gain'], 0, ',', ' ') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-light">
                        <td colspan="3" class="text-end fw-bold">Total</td>
                        <td class="text-end fw-bold text-success"><?= number_format($total, 0, ',', ' ') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
