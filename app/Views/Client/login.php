<?= $this->extend('Client/layout') ?>

<?= $this->section('title') ?>Connexion<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="client-main client-auth">
    <div class="client-card">
        <div class="client-card-header">
            <i class="bi bi-phone-vibrate"></i>
            <h1>Mobile Money</h1>
            <p>Votre espace mobile money</p>
        </div>
        <div class="client-card-body">
            <p class="text-center text-muted mb-4">Entrez votre numéro de téléphone pour accéder à votre compte.</p>
            <form action="<?= site_url('login') ?>" method="post" id="loginForm">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="numero" class="form-label">Numéro de téléphone</label>
                    <input type="tel" class="form-control form-control-lg" id="numero" name="numero"
                           placeholder="Ex : 0340000000" value="<?= old('numero') ?>" required>
                </div>
                <button type="submit" class="btn btn-mm w-100 mt-2">Se connecter</button>
                <small id="erreurNumero" class="text-danger"></small>
            </form>
            <div class="text-center mt-3">
                <small class="text-muted">Connexion automatique — aucune inscription requise.</small>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById("loginForm").addEventListener("submit", function (e) {

    let numero = document.getElementById("numero").value.trim();
    let erreur = document.getElementById("erreurNumero");

    // Efface l'ancien message
    erreur.textContent = "";

    // Le numéro doit commencer par 034 ou 038 et contenir 10 chiffres
    let regex = /^(034|038)\d{7}$/;

    if (!regex.test(numero)) {
        e.preventDefault();
        erreur.textContent = "Seuls les numéros commençant par 034 ou 038 sont autorisés.";
    }

});
</script>
<?= $this->endSection() ?>
