<?= $this->extend('Client/layout') ?>

<?= $this->section('title') ?>Dépôt<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="client-card">
    <div class="client-card-header">
        <i class="bi bi-arrow-down-circle"></i>
        <h1>Faire un dépôt</h1>
        <p>Créditez votre compte en toute simplicité</p>
    </div>
    <div class="client-card-body">
        <form id="form_depot" action="<?= site_url('depot') ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="montant" class="form-label">Montant (Ar)</label>
                <input type="number" min="100" step="100" class="form-control form-control-lg" id="montant_depot"
                       name="montant" placeholder="Ex : 10000" required>
            </div>
            <button type="submit" class="btn btn-mm w-100 mt-2"><i class="bi bi-check-lg"></i> Déposer</button>
        </form>
        <a href="<?= base_url('solde') ?>" class="back-link"><i class="bi bi-arrow-left"></i> Retour au solde</a>
    </div>
</div>

<script>
document.getElementById('form_depot').addEventListener('submit', function(e) {
    e.preventDefault();
    const montant = parseFloat(document.getElementById('montant_depot').value) || 0;
    if (montant <= 0) return;

    fetch('<?= site_url('api/calcul-frais') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: 'type=1&montant=' + montant + '&<?= csrf_token() ?>=<?= csrf_hash() ?>'
    })
    .then(r => r.json())
    .then(data => {
        const frais = data.frais || 0;
        const net = montant - frais;
        document.getElementById('confirm_message').textContent = 'Vous allez effectuer un dépôt de :';
        document.getElementById('confirm_details').innerHTML = `
            <tr><td>Montant demandé</td><td class="text-end">${montant.toLocaleString('fr-FR')} Ar</td></tr>
            <tr><td>Frais</td><td class="text-end text-danger">- ${frais.toLocaleString('fr-FR')} Ar</td></tr>
            <tr class="table-light"><td><strong>Net crédité</strong></td><td class="text-end fw-bold text-success"><strong>${net.toLocaleString('fr-FR')} Ar</strong></td></tr>
        `;
        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        modal.show();
        document.getElementById('confirm_btn').onclick = function() {
            e.target.submit();
        };
    });
});
</script>
<?= $this->endSection() ?>
