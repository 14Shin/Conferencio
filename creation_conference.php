<<?php
session_start();

$user = "raphael.daviot";
$pass = "1234567890";
try {
    $cnx = new PDO('pgsql:host=sqletud.u-pem.fr; dbname=raphael.daviot_db', $user, $pass);
} catch (PDOException $e) {
    echo "ERREUR : La connexion a échouée";
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="accueil.css">
    <title>Nouvelle Conférence</title>
</head>
<body>
    <main>
        <h2>Nouvelle Conférence</h2>
        <form action="creation_conference_process.php" method="post">
            <label for="type_intervention">Type d'intervention :</label><br>
            <input type="text" id="type_intervention" name="type_intervention" required><br><br>
            
            <label for="theme">Thème :</label><br>
            <input type="text" id="theme" name="theme" required><br><br>
            
            <label for="langue">Langue :</label><br>
            <input type="text" id="langue" name="langue" required><br><br>
            
            <label for="date_horaire">Date et Horaire :</label><br>
            <input type="datetime-local" id="date_horaire" name="date_horaire" required><br><br>
            
            <label for="duree">Durée (en minutes) :</label><br>
            <input type="number" id="duree" name="duree" required><br><br>
            
            <label for="resume_court">Résumé court :</label><br>
            <textarea id="resume_court" name="resume_court" required></textarea><br><br>
            
            <label for="resume_long">Résumé long :</label><br>
            <textarea id="resume_long" name="resume_long" required></textarea><br><br>
            
            <button type="submit">Créer Conférence</button>
        </form>
    </main>
    <footer>
        <a href="accueil.php">Accueil</a>
        <a href="#">Catalogue des conférences</a>
        <a href="informations_personnelles.php">Informations personnelles</a>
        <a href="#">Paramètres</a>
        <a href="deconnexion.php">Se déconnecter</a>
        <hr>
        <a href="#">À propos de nous</a>
        <a href="#">Mentions légales</a>
        <a href="#">Politique de confidentialité</a>
    </footer>
    <aside>
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>CONFERENCIO</h2>
                <p>LANGUES ET CULTURES</p>
            </div>
            <nav>
                <ul>
                    <li><a href="accueil.php" class="active">ACCUEIL</a></li>
                    <li><a href="conferences.php">CATALOGUE DES CONFERENCES</a></li>
                    <?php 
                    $user_type = $_SESSION['user_type'];
                    if($user_type=='participant'){
                        echo '<li><a href="informations_personnelles.php">INFORMATIONS PERSONNELLES</a></li>'; 
                    } else if ($user_type=='technician'){
                        echo '<li><a href="informations_personnelles_technicien.php">INFORMATIONS PERSONNELLES</a></li>';
                    } else if ($user_type=='conference-speaker'){
                        echo '<li><a href="informations_personnelles_conferencier.php">INFORMATIONS PERSONNELLES</a></li>';
                    }



                    $user_id = $_SESSION['id_utilisateur'];
                    $query = "SELECT rank FROM sae.admin WHERE num_admin = :user_id";
                    $stmt = $cnx->prepare($query);
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->execute();
                    $admin_type = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if($user_type=='autres'){
                        echo'<br><br>';
                        switch ($user_id) {
                            case 1:
                                echo '<li><a href="gestion_conferences.php">GESTION DES CONFERENCES</a></li>';
                                echo '<li><a href="gestion_equipes.php">GESTION DES EQUIPES TECHNIQUES</a></li>';
                                echo '<li><a href="tableau_bord.php">TABLEAU DE BORD ADMINISTRATEUR</a></li>';
                                break;
                            case 2:
                                echo '<li><a href="gestion_equipes.php">GESTION DES EQUIPES TECHNIQUES</a></li>';
                                break;
                            case 3:
                                echo '<li><a href="gestion_conferences.php">GESTION DES CONFERENCES</a></li>';
                                break;

                            default:

                                exit;
                        }
                    }    ?>
                </ul>
            </nav>
            <footer>
                <ul>
                    <li><a href="#">PARAMETRES</a></li>
                    <li><a href="deconnexion.php">SE DECONNECTER</a></li>
                    
                </ul>
            </footer>
        </div>
    </aside>
</body>
</html>
