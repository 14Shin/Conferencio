<?php
session_start();

$user = "raphael.daviot";
$pass = "1234567890";
try {
    $cnx = new PDO('pgsql:host=sqletud.u-pem.fr; dbname=raphael.daviot_db', $user, $pass);
    $cnx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
} catch (PDOException $e) {
    echo "ERREUR : La connexion a échouée";
    exit;
}

// Récupération des paramètres de recherche
@$keywords = $_GET["keywords"];
@$valider = $_GET["valider"];

if (isset($valider) && !empty(trim($keywords))) {
    try {
        $cnx->beginTransaction(); 

        $words = explode(" ", trim($keywords));
        $conditions = [];
        $params = [];

        foreach ($words as $word) {
            $conditions[] = "(theme LIKE ? OR resume_long LIKE ?)";
            $params[] = "%$word%";
            $params[] = "%$word%";
        }

        foreach ($words as $word) {
            $conditions[] = "(nom_conferencier LIKE ? OR prenom_conferencier LIKE ?)";
            $params[] = "%$word%";
            $params[] = "%$word%";
        }

        $sql = "SELECT DISTINCT sae.conference.num_conference, theme, resume_long, nom_conferencier, prenom_conferencier 
                FROM sae.conference 
                INNER JOIN sae.presenter ON sae.conference.num_conference = sae.presenter.num_conference 
                INNER JOIN sae.conferencier ON sae.presenter.num_conferencier = sae.conferencier.num_conferencier 
                WHERE " . implode(" OR ", $conditions);

        $res = $cnx->prepare($sql);
        $res->execute($params);
        $results = $res->fetchAll(PDO::FETCH_ASSOC);
        $afficher = "oui";

        $cnx->commit(); 
    } catch (PDOException $e) {
        $cnx->rollBack(); 
        echo "Erreur lors de l'exécution de la requête : " . $e->getMessage(); 
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Résultats de la Recherche</title>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="recherche.css?t=<?php echo time()?>" />
    <meta name="viewport" content="width=device-width" />
    <header>
        <div class="title">
            <h2>RECHERCHE</h2>
        </div>
    </header>
</head>
<body>
    <?php if (@$afficher == "oui") { ?>
        <div id="resultats">
            <?php if (count($results) > 0) { ?>
                <div id="nbr"><?=count($results)." ".(count($results)>1?"résultats trouvés":"résultat trouvé") ?></div>
                <ol>
                    <?php foreach ($results as $result) { ?>
                        <li>
                            <div class="conference-block">
                                <h3><?php echo $result['theme']; ?></h3>
                                <p><strong>Résumé long:</strong> <?php echo $result['resume_long']; ?></p>
                                <p><strong>Conférencier:</strong> <?php echo $result['nom_conferencier'] . " " . $result['prenom_conferencier']; ?></p>
                                <form action="details_conference.php" method="post">
                                    <input type="hidden" name="conference_id" value="<?php echo $result['num_conference']; ?>">
                                    <button type="submit">Voir les détails de la conférence</button>
                                </form>
                            </div>
                        </li>
                    <?php } ?>
                </ol>
            <?php } else { ?>
                <p>Aucun résultat trouvé pour la recherche.</p>
            <?php } ?>
        </div>  
    <?php } ?>
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
</body>
</html>
