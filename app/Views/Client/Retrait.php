<?= $this->extend('Client/layout') ?>

<?= $this->section('title') ?>Retrait<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="client-card">
    <div class="client-card-header">
        <i class="bi bi-arrow-up-circle"></i>
        <h1>Faire un retrait</h1>
        <p>Retirez de l'argent de votre compte</p>
    </div>
    <div class="client-card-body">
        <form id="form_retrait" action="<?= site_url('retrait') ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="montant" class="form-label">Montant (Ar)</label>
                <input type="number" min="100" step="100" class="form-control form-control-lg" id="montant_retrait"
                       name="montant" placeholder="Ex : 5000" required>
            </div>
            <button type="submit" class="btn btn-mm w-100 mt-2"><i class="bi bi-check-lg"></i> Retirer</button>
        </form>
        <a href="<?= base_url('solde') ?>" class="back-link"><i class="bi bi-arrow-left"></i> Retour au solde</a>
    </div>
</div>

<script>
document.getElementById('form_retrait').addEventListener('submit', function(e) {
    e.preventDefault();
    const montant = parseFloat(document.getElementById('montant_retrait').value) || 0;
    if (montant <= 0) return;

    fetch('<?= site_url('api/calcul-frais') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: 'type=2&montant=' + montant + '&<?= csrf_token() ?>=<?= csrf_hash() ?>'
    })
    .then(r => r.json())
    .then(data => {
        const frais = data.frais || 0;
        const totalDebit = montant + frais;
        document.getElementById('confirm_message').textContent = 'Vous allez effectuer un retrait de :';
        document.getElementById('confirm_details').innerHTML = `
            <tr><td>Montant demandé</td><td class="text-end">${montant.toLocaleString('fr-FR')} Ar</td></tr>
            <tr><td>Frais</td><td class="text-end text-danger">- ${frais.toLocaleString('fr-FR')} Ar</td></tr>
            <tr class="table-light"><td><strong>Total débité</strong></td><td class="text-end fw-bold text-danger"><strong>${totalDebit.toLocaleString('fr-FR')} Ar</strong></td></tr>
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
