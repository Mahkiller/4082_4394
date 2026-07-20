<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solde</title>
</head>
<body>
<h1>Faire un retrait</h1>
    <form action="/retrait" method="post">
        <label for="montant">Montant :</label>
        <input type="number" id="montant" name="montant" required>
        <button type="submit">Retirer</button>
    </form>
    <br>
    <a href="/solde">Consulter le solde</a>
    <br>
    <a href="/depot">Faire un dépôt</a>
    <br>
    <a href="/transfert">Faire un transfert</a>
</body>
</html>