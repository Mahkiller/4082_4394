<?= $this->extend('Opérateur/layout') ?>

<?= $this->section('title') ?>Détail client <?= esc($client->numero) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1"><i class="bi bi-person"></i> Client #<?= $client->id ?></h5>
            <div class="text-muted">Numéro : <code><?= esc($client->numero) ?></code></div>
        </div>
        <a href="<?= base_url('operateur/comptes') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Retour</a>
    </div>
</div>

<div class="card">
    <div class="card-header">Historique des transactions (<?= count($transactions) ?>)</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Type</th>
                        <th class="text-end">Montant</th>
                        <th class="text-end">Frais</th>
                        <th class="text-end">Commission</th>
                        <th class="text-end">Destinataire</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-3">Aucune transaction</td></tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $t): ?>
                            <?php
                            $badge = $t->status === 'Reussi' ? 'success' : ($t->status === 'Echoue' ? 'danger' : 'warning');
                            $destOperateur = '';
                            if ($t->code === 'TRANSFER' && !empty($t->destinataire_numero)) {
                                $prefixeDest = substr($t->destinataire_numero, 0, 3);
                                if ($prefixeDest === '032' || $prefixeDest === '037') {
                                    $destOperateur = 'ORANGE';
                                } elseif ($prefixeDest === '033' || $prefixeDest === '036') {
                                    $destOperateur = 'AIRTEL';
                                } elseif ($prefixeDest === '034' || $prefixeDest === '038') {
                                    $destOperateur = 'YAS';
                                } else {
                                    $destOperateur = 'Inconnu';
                                }
                            }
                            ?>
                            <tr>
                                <td><code><?= esc($t->reference) ?></code></td>
                                <td><?= esc($t->label) ?></td>
                                <td class="text-end"><?= number_format($t->montant, 0, ',', ' ') ?> Ar</td>
                                <td class="text-end"><?= number_format($t->frais_applique, 0, ',', ' ') ?> Ar</td>
                                <td class="text-end"><?= $t->commission > 0 ? number_format($t->commission, 0, ',', ' ') . ' Ar' : '-' ?></td>
                                <td class="text-end"><?= $destOperateur ? '<span class="badge bg-info text-dark">' . esc($destOperateur) . '</span>' : '-' ?></td>
                                <td><span class="badge bg-<?= $badge ?>"><?= esc($t->status) ?></span></td>
                                <td><?= date('d/m/Y H:i', strtotime($t->created_at)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
