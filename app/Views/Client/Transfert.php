<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfert</title>
</head>
<body>
<h1>Faire un transfert</h1>
    <form action="/transfert" method="post">
        <label for="destinataire">Numéro du destinataire :</label>
        <input type="text" id="destinataire" name="destinataire" required>
        <br>
        <label for="montant">Montant :</label>
        <input type="number" id="montant" name="montant" required>
        <br>
        <button type="submit">Transférer</button>
    </form>
    <br>
    <a href="/solde">Consulter le solde</a>
    <br>
    <a href="/depot">Faire un dépôt</a>
    <br>
    <a href="/retrait">Faire un retrait</a>
</body>
</html>