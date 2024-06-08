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
    $cnx->beginTransaction();
    
    // Récupération de trois conférences à découvrir de manière aléatoire
    $query = "SELECT * FROM sae.conference ORDER BY RANDOM() LIMIT 3 FOR UPDATE";
    $stmt = $cnx->query($query);
    $conferences = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupération de toutes les participations de l'utilisateur
    $user_id = $_SESSION['id_utilisateur'];
    $query = "SELECT * FROM sae.participer WHERE num_participant = :user_id FOR UPDATE";
    $stmt = $cnx->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $participations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $cnx->commit();
} catch (PDOException $e) {
    $cnx->rollback(); 
    echo "Erreur : " . $e->getMessage();
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
        <button class="join-button" href="conferences.php">Découvrir des conférences</button>
    </header>
    <main>
        <section class="intro">
            <h1>Conferencio</h1>
            <p>Explorez le monde des langues et des cultures à travers notre colloque passionnant où chaque mot est une porte vers la découverte !</p>
            <p>Bonjour, utilisateur <?php echo $_SESSION['id_utilisateur']; ?></p>
        </section>
        <section class="cloud">
        <h2>Vos participations</h2>
        <?php foreach ($participations as $participation) : ?>
        <form action="details_conference.php" method="post">
            <input type="hidden" name="conference_id" value="<?php echo $participation['num_conference']; ?>">
            <p>Conférence : <?php echo $participation['num_conference']; ?> <button type="submit">Voir les détails de la conférence</button> </p>
        </form>
        <?php endforeach; ?>
        <?php
        if(isset($_GET['error'])) {
            echo '<div style="color: red; font-size: 18px;">' . $_GET['error'] . '</div>';
        }
        if(isset($_GET['desinscription'])) {
            echo '<div style="color: green; font-size: 18px;">' . $_GET['desinscription'] . '</div>';
        }
        ?>
    </section>

        <section class="discover">
            <h2>Conférences à découvrir</h2>
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

                <form action="details_conference.php" method="post">
                    <input type="hidden" name="conference_id" value="<?php echo $conference['num_conference']; ?>">
                    <p><button type="submit">Voir les détails de la conférence</button> </p>
                </form>
            </div>
            <?php endforeach; ?>
        </section>
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
