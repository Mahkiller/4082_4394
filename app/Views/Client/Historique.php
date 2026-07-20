<?= $this->extend('Client/layout') ?>

<?= $this->section('title') ?>Historique<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="client-card" style="max-width:560px;">
    <div class="client-card-header">
        <i class="bi bi-clock-history"></i>
        <h1>Mon historique</h1>
        <p>Vos dernières opérations</p>
    </div>
    <div class="client-card-body">
        <?php if (empty($transactions)): ?>
            <p class="hist-empty"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Aucune transaction pour le moment.</p>
        <?php else: ?>
            <?php foreach ($transactions as $t): ?>
                <?php
                $icone = 'depot';
                if ($t->code === 'WITHDRAWAL') $icone = 'retrait';
                elseif ($t->code === 'TRANSFER') $icone = 'transfert';
                $signe = ($t->code === 'DEPOSIT') ? '+' : '-';
                ?>
                <div class="hist-item">
                    <div class="hist-icon <?= $icone ?>">
                        <i class="bi bi-<?= $icone === 'depot' ? 'arrow-down' : ($icone === 'retrait' ? 'arrow-up' : 'send') ?>"></i>
                    </div>
                    <div>
                        <div class="hist-label"><?= esc($t->label) ?></div>
                        <div class="hist-meta"><?= esc($t->reference) ?> · <?= date('d/m/Y H:i', strtotime($t->created_at)) ?></div>
                    </div>
                    <div class="hist-montant"><?= $signe ?><?= number_format($t->montant, 0, ',', ' ') ?> Ar</div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <a href="<?= base_url('solde') ?>" class="back-link"><i class="bi bi-arrow-left"></i> Retour au solde</a>
    </div>
</div>
<?= $this->endSection() ?>
