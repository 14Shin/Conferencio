
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="connexion_inscription.css">
    <title>Connexion</title>
</head>
<body>
<div class="container">
    <h1>CONFERENCIO</h1>
    <h2>LANGUES ET CULTURE</h2>
    <div class="square">
        <h1>Connexion</h1>

            <?php if (isset($error)) : ?>
                <p><?php echo $error; ?></p>
            <?php endif; ?>
            
            <?php
            if (isset($_SESSION['confirmation_message'])) {
                echo "<p>{$_SESSION['confirmation_message']}</p>";
                unset($_SESSION['confirmation_message']);
            }
            ?>

            <form action="connexion.php" method="post">
                <label for="user-type">Je suis :</label><br>
                <input type="radio" id="participant" name="user-type" value="participant" class="active">
                <label for="participant">Participant</label><br>
                <input type="radio" id="conference-speaker" name="user-type" value="conference-speaker">
                <label for="conference-speaker">Conférencier</label><br>
                <input type="radio" id="technician" name="user-type" value="technician">
                <label for="technician">Technicien</label><br>
                <input type="radio" id="autres" name="user-type" value="autres">
                <label for="autres">Autres</label><br><br>
                <label for="mail">Mail :</label><br>
                <input type="text" id="mail" name="mail" required><br><br>
                <label for="password">Mot de passe :</label><br>
                <input type="password" id="password" name="password" required><br><br> 
                <button type="submit">Se connecter</button>
            </form>

            <p>Vous n'avez pas de compte ? <a href="page_inscription.php">Inscrivez-vous ici</a></p>
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
