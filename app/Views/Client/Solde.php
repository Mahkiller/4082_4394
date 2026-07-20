<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solde</title>
</head>
<body>
<h2>Votre numero est : <?= session()->get('user_numero') ?></h2>
    <h1>Votre solde est : <?= session()->get('user_solde') ?> Ar</h1>
    <br>
    <a href="/depot">Faire un dépôt</a>
    <br>
    <a href="/retrait">Faire un retrait</a>
    <br>
    <a href="/transfert">Faire un transfert</a>
</body>
</html>