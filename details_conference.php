<?php
session_start();

$user = "raphael.daviot";
$pass = "1234567890";
try {
    $cnx = new PDO('pgsql:host=sqletud.u-pem.fr; dbname=raphael.daviot_db', $user, $pass);
    $cnx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "ERREUR : La connexion a échoué";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['conference_id']) && isset($_POST['unsubscribe'])) {
    try {
        $cnx->beginTransaction(); 

        $conference_id = $_POST['conference_id'];
        $user_id = $_SESSION['id_utilisateur'];

        $query = "DELETE FROM sae.participer WHERE num_conference = :conference_id AND num_participant = :user_id";
        $stmt = $cnx->prepare($query);
        $stmt->bindParam(':conference_id', $conference_id);
        $stmt->bindParam(':user_id', $user_id);
        if ($stmt->execute()) {
            echo "Désinscription réussie.";
            header('location: accueil.php?desinscription=Vous vous êtes désinscrit avec succès');
        } else {
            echo "Erreur lors de la désinscription.";
        }

        $cnx->commit(); 
    } catch (PDOException $e) {
        $cnx->rollBack();
        echo "ERREUR : " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['conference_id'])) {
    $conference_id = $_POST['conference_id'];
    try {
        $query = "SELECT c.*, s.nb_place, s.lettre_aile, co.nom_conferencier, co.prenom_conferencier, sd.num_salle
            FROM sae.conference c
            LEFT JOIN sae.se_deroule sd ON c.num_conference = sd.num_conference
            LEFT JOIN sae.salle s ON sd.num_salle = s.num_salle
            INNER JOIN sae.presenter p ON c.num_conference = p.num_conference
            INNER JOIN sae.conferencier co ON p.num_conferencier = co.num_conferencier
            WHERE c.num_conference = :conference_id
            ";
                  
        $stmt = $cnx->prepare($query);
        $stmt->bindParam(':conference_id', $conference_id);
        $stmt->execute();
        $conference_details = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "ERREUR : " . $e->getMessage();
    }
} else {
    $conference_details = false;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="accueil.css">
    <title>Conferencio - Conférences à découvrir</title>
</head>
<body>
    <header>
        <h1>Détails de la conférence</h1>
    </header>
    <main>
        <?php if ($conference_details) : ?>
        <section>
            <h2>Thème : <?php echo $conference_details['theme']; ?></h2>
            <p>Date et Horaire : <?php echo $conference_details['date_horaire']; ?></p>
            <p>Langue : <?php echo $conference_details['langue']; ?></p>
            <p>Durée : <?php echo $conference_details['duree']; ?> minutes</p>
            <p>Résumé court : <?php echo $conference_details['resume_court']; ?></p>
            <p>Résumé long : <?php echo $conference_details['resume_long']; ?></p>
            <p>Salle : <?php echo $conference_details['num_salle']; ?></p>
            <p>Nombre de places : <?php echo $conference_details['nb_place']; ?></p>
            <p>Conférencier : <?php echo $conference_details['prenom_conferencier'] . ' ' . $conference_details['nom_conferencier']; ?></p>
            <?php 
            $user_type = $_SESSION['user_type'];
            if ($user_type == 'participant') {
                echo '<form action="" method="post">
                        <input type="hidden" name="conference_id" value="' . $conference_details["num_conference"] . '">
                        <input type="hidden" name="unsubscribe" value="1">
                        <button type="submit">Se désinscrire</button>
                    </form>';
            }
            ?>

            
        </section>
        <?php else : ?>
        <section>
            <p>Conférence non trouvée.</p>
        </section>
        <?php endif; ?>
    </main>
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
</body>
</html>
