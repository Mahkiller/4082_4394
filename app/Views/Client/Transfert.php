<?= $this->extend('Client/layout') ?>

<?= $this->section('title') ?>Transfert<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="client-card">
    <div class="client-card-header">
        <i class="bi bi-send"></i>
        <h1>Faire un transfert</h1>
        <p>Envoyez de l'argent à un autre numéro</p>
    </div>
    <div class="client-card-body">
        <!-- Toggle Simple / Multiple -->
        <div class="btn-group w-100 mb-3" role="group">
            <input type="radio" class="btn-check" name="transfert_mode" id="mode_simple" value="simple" checked>
            <label class="btn btn-outline-mm" for="mode_simple"><i class="bi bi-person"></i> Simple</label>

            <input type="radio" class="btn-check" name="transfert_mode" id="mode_multiple" value="multiple">
            <label class="btn btn-outline-mm" for="mode_multiple"><i class="bi bi-people"></i> Multiple</label>
        </div>

        <!-- Formulaire Transfert Simple -->
        <form id="form_simple" action="<?= site_url('transfert') ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="destinataire" class="form-label">Numéro du destinataire</label>
                <input type="text" class="form-control form-control-lg" id="destinataire"
                       name="destinataire" placeholder="Ex : 0320000000" required>
            </div>
            <div class="mb-3">
                <label for="montant_simple" class="form-label">Montant (Ar)</label>
                <input type="number" min="100" step="100" class="form-control form-control-lg" id="montant_simple"
                       name="montant" placeholder="Ex : 5000" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mode de frais</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="frais_mode" id="frais_deductible_simple" value="deductible" checked>
                    <label class="btn btn-outline-mm" for="frais_deductible_simple">Frais déductibles de l'expéditeur</label>

                    <input type="radio" class="btn-check" name="frais_mode" id="frais_inclus_simple" value="inclus">
                    <label class="btn btn-outline-mm" for="frais_inclus_simple">Frais inclus dans l'envoi</label>
                </div>
                <div class="form-text" id="frais_help_simple">Les frais seront déduits de votre solde en plus du montant envoyé.</div>
            </div>
            <button type="submit" class="btn btn-mm w-100 mt-2"><i class="bi bi-check-lg"></i> Transférer</button>
        </form>

        <!-- Formulaire Transfert Multiple -->
        <form id="form_multiple" action="<?= site_url('transfert-multiple') ?>" method="post" style="display:none;">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="destinataires" class="form-label">Numéros des destinataires</label>
                <textarea class="form-control" id="destinataires" name="destinataires" rows="3"
                          placeholder="Ex : 0340000001, 0340000002, 0380000003" required></textarea>
                <div class="form-text">Séparez les numéros par des virgules</div>
            </div>
            <div class="mb-3">
                <label for="montant_total" class="form-label">Montant total à envoyer (Ar)</label>
                <input type="number" min="100" step="100" class="form-control form-control-lg" id="montant_total"
                       name="montant_total" placeholder="Ex : 9000" required>
            </div>
            <div class="alert alert-info" id="preview_multiple" style="display:none;">
                <strong>Aperçu :</strong> <span id="nb_destinataires">0</span> destinataire(s) &rarr;
                <strong><span id="montant_par_destinataire">0</span> Ar</strong> par personne
                <br><small id="preview_frais_info"></small>
            </div>
            <div class="mb-3">
                <label class="form-label">Mode de frais</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="frais_mode" id="frais_deductible_multiple" value="deductible" checked>
                    <label class="btn btn-outline-mm" for="frais_deductible_multiple">Frais déductibles de l'expéditeur</label>

                    <input type="radio" class="btn-check" name="frais_mode" id="frais_inclus_multiple" value="inclus">
                    <label class="btn btn-outline-mm" for="frais_inclus_multiple">Frais inclus dans l'envoi</label>
                </div>
            </div>
            <button type="submit" class="btn btn-mm w-100 mt-2"><i class="bi bi-check-lg"></i> Transférer à tous</button>
        </form>

        <a href="<?= base_url('solde') ?>" class="back-link"><i class="bi bi-arrow-left"></i> Retour au solde</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modeSimple = document.getElementById('mode_simple');
    const modeMultiple = document.getElementById('mode_multiple');
    const formSimple = document.getElementById('form_simple');
    const formMultiple = document.getElementById('form_multiple');
    const destinatairesInput = document.getElementById('destinataire');
    const montantTotalInput = document.getElementById('montant_total');
    const previewDiv = document.getElementById('preview_multiple');
    const nbDestinatairesSpan = document.getElementById('nb_destinataires');
    const montantParDestSpan = document.getElementById('montant_par_destinataire');
    const fraisInclusRadio = document.getElementById('frais_inclus_simple');
    const fraisDeductibleRadio = document.getElementById('frais_deductible_simple');
    const fraisHelpSimple = document.getElementById('frais_help_simple');

    const prefixesAutorises = <?= json_encode($prefixesAutorises ?? []) ?>;

    function switchMode() {
        if (modeMultiple.checked) {
            formSimple.style.display = 'none';
            formMultiple.style.display = 'block';
        } else {
            formSimple.style.display = 'block';
            formMultiple.style.display = 'none';
            previewDiv.style.display = 'none';
        }
    }

    function updateFraisHelpSimple() {
        if (fraisInclusRadio.checked) {
            fraisHelpSimple.textContent = 'Les frais seront déduits du montant envoyé (le destinataire reçoit moins).';
        } else {
            fraisHelpSimple.textContent = 'Les frais seront déduits de votre solde en plus du montant envoyé.';
        }
    }

    function verifierOperateurDestinataire() {
        const numero = destinatairesInput.value.trim();
        const prefixe = numero.substring(0, 3);
        const memeOperateur = prefixesAutorises.includes(prefixe);

        if (!memeOperateur && fraisInclusRadio.checked) {
            fraisDeductibleRadio.checked = true;
        }

        fraisInclusRadio.disabled = !memeOperateur;
        fraisDeductibleRadio.disabled = false;

        if (!memeOperateur) {
            fraisHelpSimple.textContent = 'Les frais sont déductibles de votre solde (opérateur différent).';
        } else {
            updateFraisHelpSimple();
        }
    }

    function showConfirmTransfert(e) {
        e.preventDefault();
        const montant = parseFloat(document.getElementById('montant_simple').value) || 0;
        const destinataire = document.getElementById('destinataire').value.trim();
        const fraisMode = document.querySelector('input[name="frais_mode"]:checked').value;
        if (montant <= 0 || !destinataire) return;

        fetch('<?= site_url('api/calcul-frais') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: 'type=3&montant=' + montant + '&destinataire=' + encodeURIComponent(destinataire) + '&frais_mode=' + fraisMode + '&<?= csrf_token() ?>=<?= csrf_hash() ?>'
        })
        .then(r => r.json())
        .then(data => {
            const frais = data.frais || 0;
            const commission = data.commission || 0;
            const montantRecu = data.montant_recu || montant;
            const totalDebit = data.total_debit || montant;
            let html = `
                <tr><td>Montant envoyé</td><td class="text-end">${montant.toLocaleString('fr-FR')} Ar</td></tr>
                <tr><td>Frais</td><td class="text-end text-danger">- ${frais.toLocaleString('fr-FR')} Ar</td></tr>
            `;
            if (commission > 0) {
                html += `<tr><td>Commission inter-opérateur</td><td class="text-end text-warning">- ${commission.toLocaleString('fr-FR')} Ar</td></tr>`;
            }
            html += `<tr class="table-light"><td><strong>Total débité</strong></td><td class="text-end fw-bold text-danger"><strong>${totalDebit.toLocaleString('fr-FR')} Ar</strong></td></tr>`;
            html += `<tr><td>Destinataire reçoit</td><td class="text-end text-success">${montantRecu.toLocaleString('fr-FR')} Ar</td></tr>`;
            document.getElementById('confirm_message').textContent = 'Vous allez effectuer un transfert de :';
            document.getElementById('confirm_details').innerHTML = html;
            const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
            modal.show();
            document.getElementById('confirm_btn').onclick = function() {
                e.target.submit();
            };
        });
    }

    function updatePreview() {
        const montantTotal = parseInt(montantTotalInput.value) || 0;
        const numeros = document.getElementById('destinataires').value.split(',').map(n => n.trim()).filter(n => n.length > 0);
        const nb = numeros.length;
        const fraisInclus = document.getElementById('frais_inclus_multiple').checked;
        const previewFraisInfo = document.getElementById('preview_frais_info');

        if (nb > 0 && montantTotal > 0) {
            const parPersonne = Math.floor(montantTotal / nb);
            nbDestinatairesSpan.textContent = nb;
            montantParDestSpan.textContent = parPersonne.toLocaleString('fr-FR');
            
            if (fraisInclus) {
                previewFraisInfo.textContent = 'Frais déduits du montant total (chaque destinataire reçoit moins).';
            } else {
                previewFraisInfo.textContent = 'Frais déductibles de votre solde en plus du montant envoyé.';
            }
            previewDiv.style.display = 'block';
        } else {
            previewDiv.style.display = 'none';
        }
    }

    function showConfirmTransfertMultiple(e) {
        e.preventDefault();
        const montantTotal = parseInt(montantTotalInput.value) || 0;
        const destinataires = document.getElementById('destinataires').value.split(',').map(n => n.trim()).filter(n => n.length > 0);
        const fraisMode = document.querySelector('input[name="frais_mode"]:checked').value;
        if (montantTotal <= 0 || destinataires.length === 0) return;

        fetch('<?= site_url('api/calcul-frais') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: 'type=3&montant=' + montantTotal + '&frais_mode=' + fraisMode + '&<?= csrf_token() ?>=<?= csrf_hash() ?>'
        })
        .then(r => r.json())
        .then(data => {
            const frais = data.frais || 0;
            const commission = data.commission || 0;
            const totalDebit = data.total_debit || montantTotal;
            const parPersonne = Math.floor(montantTotal / destinataires.length);
            let html = `
                <tr><td>Montant total</td><td class="text-end">${montantTotal.toLocaleString('fr-FR')} Ar</td></tr>
                <tr><td>Nb destinataires</td><td class="text-end">${destinataires.length}</td></tr>
                <tr><td>Par personne</td><td class="text-end">${parPersonne.toLocaleString('fr-FR')} Ar</td></tr>
                <tr><td>Frais</td><td class="text-end text-danger">- ${frais.toLocaleString('fr-FR')} Ar</td></tr>
            `;
            if (commission > 0) {
                html += `<tr><td>Commission inter-opérateur</td><td class="text-end text-warning">- ${commission.toLocaleString('fr-FR')} Ar</td></tr>`;
            }
            html += `<tr class="table-light"><td><strong>Total débité</strong></td><td class="text-end fw-bold text-danger"><strong>${totalDebit.toLocaleString('fr-FR')} Ar</strong></td></tr>`;
            document.getElementById('confirm_message').textContent = 'Vous allez effectuer un transfert multiple de :';
            document.getElementById('confirm_details').innerHTML = html;
            const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
            modal.show();
            document.getElementById('confirm_btn').onclick = function() {
                e.target.submit();
            };
        });
    }

    modeSimple.addEventListener('change', switchMode);
    modeMultiple.addEventListener('change', switchMode);
    destinatairesInput.addEventListener('input', verifierOperateurDestinataire);
    document.getElementById('destinataires').addEventListener('input', updatePreview);
    montantTotalInput.addEventListener('input', updatePreview);
    document.getElementById('frais_deductible_multiple').addEventListener('change', updatePreview);
    document.getElementById('frais_inclus_multiple').addEventListener('change', updatePreview);
    fraisDeductibleRadio.addEventListener('change', updateFraisHelpSimple);
    fraisInclusRadio.addEventListener('change', updateFraisHelpSimple);

    document.getElementById('form_simple').addEventListener('submit', showConfirmTransfert);
    document.getElementById('form_multiple').addEventListener('submit', showConfirmTransfertMultiple);
});
</script>
<?= $this->endSection() ?>
