<?php
session_start();

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

    // Récupérer toutes les conférences
    $query_conferences = "SELECT * FROM sae.conference FOR UPDATE";
    $stmt_conferences = $cnx->query($query_conferences);
    $conferences = $stmt_conferences->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer toutes les salles
    $query_salles = "SELECT * FROM sae.salle FOR UPDATE";
    $stmt_salles = $cnx->query($query_salles);
    $salles = $stmt_salles->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer tout les conférenciers
    $query_conferencier = "SELECT * FROM sae.conferencier FOR UPDATE";
    $stmt_conferenciers= $cnx->query($query_conferencier);
    $conferenciers = $stmt_conferenciers->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer toutes les affectations
    $query_se_deroule = "SELECT * FROM sae.se_deroule FOR UPDATE";
    $stmt_se_deroule = $cnx->query($query_se_deroule);
    $se_deroule = $stmt_se_deroule->fetchAll(PDO::FETCH_ASSOC);

    // Récupération des associations de la table superviser
    $query = "SELECT * FROM sae.superviser FOR UPDATE";
    $stmt = $cnx->query($query);
    $associations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['conference']) && isset($_POST['salle'])) {
        $id_conference = $_POST['conference'];
        $id_salle = $_POST['salle'];

        $query_associer_salle = "INSERT INTO sae.se_deroule (num_salle, num_conference) VALUES (:id_salle, :id_conference)";
        $stmt_associer_salle = $cnx->prepare($query_associer_salle);
        $stmt_associer_salle->bindParam(':id_conference', $id_conference);
        $stmt_associer_salle->bindParam(':id_salle', $id_salle);
        
        if ($stmt_associer_salle->execute()) {
            echo "Conférence associée avec succès.";
            $cnx->commit(); 
            header("Refresh:0"); 
            exit;
        } else {
            echo "Erreur lors de l'association : " . $stmt_associer_salle->errorInfo()[2];
        }
    }

    $cnx->commit(); 
} catch (PDOException $e) {
    $cnx->rollBack(); 
    echo "ERREUR : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="accueil.css">
    <title>Tableau de Bord</title>
</head>
<body>


<main> 
    <section id="conferences">
        <h2>Conferences</h2>
        <h3>Liste de toutes les conférences</h3>
        <ul>
            <?php foreach ($conferences as $conference) : ?>
                <li><?php echo $conference['theme'].': '.$conference['resume_court']; ?></li>
            <?php endforeach; ?>
        </ul>
        <a href="creation_conference.php"><button>Nouvelle Conférence</button></a>

        <h3>Liste de toutes les salles</h3>
        <ul>
            <?php foreach ($salles as $salle) : ?>
                <li>
                    <?php echo $salle['num_salle']; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <form action="" method="post">
            <label for="select_conference">Conférence:</label>
            <select name="conference" id="select_conference">
                <?php foreach ($conferences as $conference) : ?>
                    <option value="<?php echo $conference['num_conference']; ?>"><?php echo $conference['resume_court']; ?></option>
                <?php endforeach; ?>
            </select><br>
            <label for="select_salle">Salle:</label>
            <select name="salle" id="select_salle">
                <?php foreach ($salles as $salle) : ?>
                    <option value="<?php echo $salle['num_salle']; ?>"><?php echo $salle['num_salle']; ?></option>
                <?php endforeach; ?>
            </select><br>
            <button type="submit">Associer</button><br>

            <h3>Liste de toutes les affectations</h3>
                <?php foreach ($se_deroule as $deroule) : ?>
                    <?php echo $deroule['num_conference'].': '.$deroule['num_salle']; ?><br>
                <?php endforeach; ?>
            
        </form>


        <h3>Liste de tous les conférenciers</h3>
        <ul>
            <?php foreach ($conferenciers as $conferencier) : ?>
                <li><?php echo $conferencier['nom_conferencier'] . ' ' . $conferencier['prenom_conferencier']; ?></li>
            <?php endforeach; ?>
        </ul>

        <a href="inscription_conferencier.php"><button>Nouveau Conferencier</button></a>

    </section>







</main>

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
    <aside>
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>CONFERENCIO</h2>
                <p>LANGUES ET CULTURES</p>
            </div>
            <nav>
                <ul>
                    <li><a href="accueil.php">ACCUEIL</a></li>
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
                                echo '<li><a href="gestion_conferences.php" class="active">GESTION DES CONFERENCES</a></li>';
                                echo '<li><a href="gestion_equipes.php">GESTION DES EQUIPES TECHNIQUES</a></li>';
                                echo '<li><a href="tableau_bord.php">TABLEAU DE BORD ADMINISTRATEUR</a></li>';
                                break;
                            case 2:
                                echo '<li><a href="gestion_equipes.php" class="active">GESTION DES EQUIPES TECHNIQUES</a></li>';
                                break;
                            case 3:
                                echo '<li><a href="gestion_conferences.php" class="active">GESTION DES CONFERENCES</a></li>';
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
