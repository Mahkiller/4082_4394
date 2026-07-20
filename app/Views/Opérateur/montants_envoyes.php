<?= $this->extend('Opérateur/layout') ?>

<?= $this->section('title') ?>Montants à envoyer par opérateur<?= $this->endSection() ?>

<?= $this->section('content') ?>
<p class="text-muted">Situation des montants à envoyer à chaque opérateur pour règlement.</p>

<div class="card">
    <div class="card-header">Récapitulatif par opérateur</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr><th>Opérateur</th><th class="text-end">Envoyé par YAS (Ar)</th><th class="text-end">Reçu par YAS (Ar)</th><th class="text-end">Net à régler (Ar)</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($resultats as $r): ?>
                        <tr>
                            <td><span class="badge badge-prefix"><?= esc($r['operateur']->nom) ?></span></td>
                            <td class="text-end"><?= number_format($r['total_envoye'], 0, ',', ' ') ?></td>
                            <td class="text-end"><?= number_format($r['total_recu'], 0, ',', ' ') ?></td>
                            <td class="text-end fw-bold <?= $r['net'] > 0 ? 'text-danger' : ($r['net'] < 0 ? 'text-success' : '') ?>">
                                <?= number_format($r['net'], 0, ',', ' ') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
