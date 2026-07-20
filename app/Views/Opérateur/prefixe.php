<?= $this->extend('Opérateur/layout') ?>

<?= $this->section('title') ?>Préfixes<?= $this->endSection() ?>

<?= $this->section('content') ?>
<p class="text-muted">Configuration des préfixes valables de l'opérateur (ex : 033, 037).</p>

<div class="row">
    <div class="col-lg-5 mb-3">
        <div class="card">
            <div class="card-header">Ajouter un préfixe</div>
            <div class="card-body">
                <form method="post" action="<?= base_url('operateur/prefixe/ajouter') ?>">
                    <div class="input-group">
                        <input type="text" name="prefixe" class="form-control" placeholder="Ex: 033" maxlength="10" required>
                        <button class="btn btn-primary" type="submit"><i class="bi bi-plus-lg"></i> Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">Préfixes configurés (<?= count($prefixes) ?>)</div>
            <div class="card-body">
                <?php if (empty($prefixes)): ?>
                    <p class="text-muted mb-0">Aucun préfixe configuré.</p>
                <?php else: ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($prefixes as $p): ?>
                            <span class="badge bg-primary fs-6 d-flex align-items-center">
                                <?= esc($p->prefixe) ?>
                                <a href="<?= base_url('operateur/prefixe/supprimer/' . $p->id) ?>" class="ms-2 text-white" onclick="return confirm('Supprimer ce préfixe ?')"><i class="bi bi-x-circle"></i></a>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
