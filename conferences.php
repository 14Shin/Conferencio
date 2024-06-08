<?php
session_start();

// Connexion à la base de données
$user = "raphael.daviot";
$pass = "1234567890";
try {
    $cnx = new PDO('pgsql:host=sqletud.u-pem.fr; dbname=raphael.daviot_db', $user, $pass);
} catch (PDOException $e) {
    echo "ERREUR : La connexion a échouée";
    exit;
}

try {
    // Récupération de trois conférences à découvrir de manière aléatoire
    $cnx->beginTransaction(); 
    $query = "SELECT * FROM sae.conference ORDER BY RANDOM() LIMIT 3 FOR UPDATE";
    $stmt = $cnx->query($query);
    $conferences = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $cnx->commit(); 
} catch (PDOException $e) {
    $cnx->rollBack(); 
    echo "Erreur lors de la récupération des conférences : " . $e->getMessage();
}

try {
    // Récupération des trois conférences dont la date est la plus proche
    $cnx->beginTransaction(); 
    $query2 = "SELECT * FROM sae.conference ORDER BY date_horaire ASC LIMIT 3 FOR UPDATE";
    $stmt = $cnx->query($query2);
    $upcoming = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $cnx->commit(); 
} catch (PDOException $e) {
    $cnx->rollBack();
    echo "Erreur lors de la récupération des conférences à venir : " . $e->getMessage();
}

try {
    // Récupération de trois conférenciers de manière aléatoire
    $cnx->beginTransaction(); 
    $query3 = "SELECT * FROM sae.conferencier ORDER BY RANDOM() LIMIT 3 FOR UPDATE";
    $stmt = $cnx->query($query3);
    $speakers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $cnx->commit(); 
} catch (PDOException $e) {
    $cnx->rollBack(); 
    echo "Erreur lors de la récupération des conférenciers : " . $e->getMessage();
}
?>


<!DOCTYPE html>
<hmtl lang="fr">
<HEAD>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Conferencio</title>
</HEAD>
<BODY>
    <header class="top-banner">
        <div class="search-bar">
            <p>RECHERCHEZ UNE CONFÉRENCE, UN CONFÉRENCIER OU UNE DATE:</p>
            <form name="recherche" method="get" action="recherche.php">
                <input type="text" name="keywords" value="Entrez votre recherche." placeholder="Entrez votre recherche." />
                <input type="submit" name="valider" value="Rechercher" />
            </form>
        </div>
        <div class="title">
            <h1>CATALOGUE DES CONFERENCES</h1>
        </div>
    </header>

    <section class="upcoming">
        <h2>Conférences à venir -----------------------------------------------------</h2>
        <?php foreach ($upcoming as $next) : ?>
            <div class="conference-block">
                <h3><?php echo $next['theme']; ?></h3>
                <p><strong>Date et Horaire:</strong> <?php echo $next['date_horaire']; ?></p>
                <p><strong>Langue:</strong> <?php echo $next['langue']; ?></p>
                <p><strong>Durée:</strong> <?php echo $next['duree']; ?> minutes</p>
                <p><strong>Résumé court:</strong> <?php echo $next['resume_court']; ?></p>
                <p><strong>Résumé long:</strong> <?php echo $next['resume_long']; ?></p>
                <?php 
            $user_type = $_SESSION['user_type'];
            if($user_type=='participant'){
                echo '<form action="participer.php" method="post">
                        <input type="hidden" name="conference_id" value="' . $next["num_conference"] . '">
                        <p><button type="submit">Participer</button> </p>
                    </form>';
            }
            ?>
            </div>
            <?php endforeach; ?>
    </section>

    <section class="discover">
        <h2>Conférences à découvrir ------------------------------------------------</h2>
        <?php foreach ($conferences as $conference) : ?>
            <div class="conference-block">
                <h3><?php echo $conference['theme']; ?></h3>
                <p><strong>Date et Horaire:</strong> <?php echo $conference['date_horaire']; ?></p>
                <p><strong>Langue:</strong> <?php echo $conference['langue']; ?></p>
                <p><strong>Durée:</strong> <?php echo $conference['duree']; ?> minutes</p>
                <p><strong>Résumé court:</strong> <?php echo $conference['resume_court']; ?></p>
                <p><strong>Résumé long:</strong> <?php echo $conference['resume_long']; ?></p>
            <?php 
            $user_type = $_SESSION['user_type'];
            if($user_type=='participant'){
                echo '<form action="participer.php" method="post">
                        <input type="hidden" name="conference_id" value="' . $conference["num_conference"] . '">
                        <p><button type="submit">Participer</button> </p>
                    </form>';
            }
            ?>
            </div>
            <?php endforeach; ?>
    </section>

    <section class="recommended">
        <h2>Conférenciers qui pourraient vous intéresser -----------------------</h2>
        <?php foreach ($speakers as $speaker) : ?>
            <div class="conference-block">
                <h3><?php echo $speaker['num_conferencier']; ?></h3>
                <p><strong><?php echo $speaker['nom_conferencier']; ?><?php echo ' '.$speaker['prenom_conferencier']; ?></strong></p>
                <p><strong>Organisme :</strong> <?php echo $speaker['organisme']; ?> minutes</p>
                <p><strong>Fonction:</strong> <?php echo $speaker['fonction_conferencier']; ?></p>
            </div>
            <?php endforeach; ?>
    </section>
    <aside>
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>CONFERENCIO</h2>
                <p>LANGUES ET CULTURES</p>
            </div>
            <nav>
                <ul>
                    <li><a href="accueil.php">ACCUEIL</a></li>
                    <li><a href="conferences.php" class="active">CATALOGUE DES CONFERENCES</a></li>
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
</BODY>
</HTML>