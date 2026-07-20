<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Depot</title>
</head>
<body>
    <h1>Faire un dépôt</h1>
    <form action="/depot" method="post">
        <label for="montant">Montant :</label>
        <input type="number" id="montant" name="montant" required>Ar
        <br>
        <button type="submit">Déposer</button>
    </form>
    <a href="/solde">Consulter le solde</a>
    <br>
    <a href="/retrait">Faire un retrait</a>
    <br>
    <a href="/transfert">Faire un transfert</a>
</body>
</html>