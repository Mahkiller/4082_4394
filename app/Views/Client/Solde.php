<?= $this->extend('Client/layout') ?>

<?= $this->section('title') ?>Mon solde<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$numero = session()->get('user_numero');
$solde = session()->get('user_solde');
?>
<div class="client-card solde-card">
    <div class="client-card-header">
        <i class="bi bi-wallet2"></i>
        <h1>Mon compte</h1>
        <p>Bonjour <?= esc($numero) ?></p>
    </div>
    <div class="client-card-body">
        <span class="solde-badge"><i class="bi bi-cash-coin"></i> Solde disponible</span>
        <div class="solde-amount"><?= number_format((float) $solde, 0, ',', ' ') ?> <small>Ar</small></div>

        <div class="action-grid">
            <a href="<?= base_url('depot') ?>" class="action-tile depot">
                <i class="bi bi-arrow-down-circle"></i> Dépôt
            </a>
            <a href="<?= base_url('retrait') ?>" class="action-tile retrait">
                <i class="bi bi-arrow-up-circle"></i> Retrait
            </a>
            <a href="<?= base_url('transfert') ?>" class="action-tile transfert">
                <i class="bi bi-send"></i> Transfert
            </a>
            <a href="/epargneform" class="action-tile transfert">
                <i class="bi bi-send"></i> Configurer votre pourcentage d'epargne
            </a>
            <a href="/epargnesolde" class="action-tile transfert">
                <i class="bi bi-send"></i> Voir votre solde dans l'epargne
            </a>
        </div>

        <a href="<?= base_url('historique') ?>" class="back-link"><i class="bi bi-clock-history"></i> Voir mon historique</a>
    </div>
</div>
<?= $this->endSection() ?>
