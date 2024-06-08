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

    // Récupérer l'identifiant du technicien connecté
    $user_id = $_SESSION['id_utilisateur'];

    // Requête pour récupérer les informations du technicien
    $query_technicien = "SELECT t.*,
                        et.responsable_technique,
                        et.responsable_organisationnel
                        FROM sae.technicien t
                        LEFT JOIN sae.appartenir a ON t.num_technicien = a.num_technicien
                        LEFT JOIN sae.equipe_technique et ON a.num_equipe = et.num_equipe
                        WHERE t.num_technicien = :user_id FOR UPDATE";
    $stmt_technicien = $cnx->prepare($query_technicien);
    $stmt_technicien->bindParam(':user_id', $user_id);
    $stmt_technicien->execute();
    $technicien_info = $stmt_technicien->fetch(PDO::FETCH_ASSOC);

    // Récupérer les conférences associées à l'équipe technique du technicien
    $query_conferences = "SELECT c.*
                        FROM sae.conference c
                        INNER JOIN sae.superviser s ON c.num_conference = s.num_conference
                        WHERE s.num_equipe = :equipe_id FOR UPDATE";
    $stmt_conferences = $cnx->prepare($query_conferences);
    $stmt_conferences->bindParam(':equipe_id', $technicien_info['num_equipe']);
    $stmt_conferences->execute();
    $conferences = $stmt_conferences->fetchAll(PDO::FETCH_ASSOC);

    // Requête pour récupérer l'identifiant de l'équipe
    $query_equipe_id = "SELECT num_equipe
                        FROM sae.equipe_technique
                        WHERE responsable_technique = :responsable_technique FOR UPDATE";
    $stmt_equipe_id = $cnx->prepare($query_equipe_id);
    $stmt_equipe_id->bindParam(':responsable_technique', $technicien_info['num_technicien']);
    $stmt_equipe_id->execute();
    $equipe_id = $stmt_equipe_id->fetchColumn(); 

    // Requête pour récupérer les membres de l'équipe
    $query_membres_equipe = "SELECT DISTINCT t.nom_technicien, t.prenom_technicien
                             FROM sae.technicien t
                             JOIN sae.appartenir a ON t.num_technicien = a.num_technicien
                             WHERE a.num_equipe = :equipe_id FOR UPDATE";
    $stmt_membres_equipe = $cnx->prepare($query_membres_equipe);
    $stmt_membres_equipe->bindParam(':equipe_id', $equipe_id);
    $stmt_membres_equipe->execute();
    $membres_equipe = $stmt_membres_equipe->fetchAll(PDO::FETCH_ASSOC);

    // Requête pour récupérer les conférences associées à l'équipe technique
    $query_conferences_equipe = "SELECT DISTINCT c.*
                                 FROM sae.conference c
                                 INNER JOIN sae.superviser s ON c.num_conference = s.num_conference
                                 WHERE s.num_equipe = :equipe_id FOR UPDATE";
    $stmt_conferences_equipe = $cnx->prepare($query_conferences_equipe);
    $stmt_conferences_equipe->bindParam(':equipe_id', $equipe_id);
    $stmt_conferences_equipe->execute();
    $conferences_equipe = $stmt_conferences_equipe->fetchAll(PDO::FETCH_ASSOC);

    $cnx->commit(); 
} catch (PDOException $e) {
    $cnx->rollBack(); 
    echo "ERREUR : " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="accueil.css">
    <title>Informations Personnelles</title>
</head>
<body>
    <main>
        <h1>Informations Personnelles</h1>
        <?php if ($technicien_info): ?>
            <p>Nom : <?php echo $technicien_info['nom_technicien']; ?></p>
            <p>Prénom : <?php echo $technicien_info['prenom_technicien']; ?></p>
            <p>Date de Naissance : <?php echo $technicien_info['date_naissance_technicien']; ?></p>
            <p>Numéro de Téléphone : <?php echo $technicien_info['telephone_technicien']; ?></p>
            <p>Fonction : <?php echo $technicien_info['fonction_technicien']; ?></p>
            <p>Adresse e-mail : <?php echo $technicien_info['mail_technicien']; ?></p>
            <h2>Équipe Technique</h2>
            <p>Responsable Technique : <?php echo $technicien_info['responsable_technique']; ?></p>
            <p>Responsable Organisationnel : <?php echo $technicien_info['responsable_organisationnel']; ?></p>
            <p>Membres de l'équipe :</p>
            <ul>
            <?php if ($membres_equipe): ?>
                <?php foreach ($membres_equipe as $membre) : ?>
                    <li><?php echo $membre['nom_technicien'] . ' ' . $membre['prenom_technicien']; ?></li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>Aucun membre dans l'équipe</li>
            <?php endif; ?>


            </ul>
            <h2>Conférences associées à l'équipe technique</h2>
            <ul>
                <?php foreach ($conferences_equipe as $conference) : ?>
                    <li>
                        <?php echo $conference['theme']; ?>
                        <form action="details_conference.php" method="post" style="display: inline;">
                            <input type="hidden" name="conference_id" value="<?php echo $conference['num_conference']; ?>">
                            <button type="submit">Détails</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>


        <?php else: ?>
            <p>Aucune information disponible pour ce technicien.</p>
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
