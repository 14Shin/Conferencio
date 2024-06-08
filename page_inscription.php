<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="connexion_inscription.css">
    <title>Inscription</title>
</head>
<body>
<div class="container">
    <h1>CONFERENCIO</h1>
    <h2>LANGUES ET CULTURE</h2>
    <div class="square">
        <h2>Inscription en tant que participant</h2>
        <form action="register.php" method="post">
            <label for="firstname">Prénom :</label><br>
            <input type="text" id="firstname" name="firstname" required><br><br>
            <label for="lastname">Nom :</label><br>
            <input type="text" id="lastname" name="lastname" required><br><br>
            <label for="mail">Mail :</label><br>
            <input type="text" id="mail" name="mail" required><br><br>
            <label for="birthdate">Date de Naissance :</label><br>
            <input type="date" id="birthdate" name="birthdate" required><br><br>
            <label for="phone">Numéro de Téléphone :</label><br>
            <input type="tel" id="phone" name="phone" required><br><br>
            <label for="profession">Profession :</label><br>
            <input type="text" id="profession" name="profession" required><br><br>
            <label for="password">Mot de passe :</label><br>
            <input type="password" id="password" name="password" required><br><br>
            <button type="submit">S'inscrire</button>
        </form>
    </div>
</div>

<footer>
        <a href="accueil.php">Accueil</a>
        <a href="conferences.php">Catalogue des conférences</a>
        <a href="informations_personnelles.php">Informations personnelles</a>
        <a href="#">Paramètres</a>
        <a href="deconnexion.php">Se déconnecter</a>
        <hr>
        <a href="#">À propos de nous</a>
        <a href="#">Mentions légales</a>
        <a href="#">Politique de confidentialité</a>
    </footer>
</body>
</html>
