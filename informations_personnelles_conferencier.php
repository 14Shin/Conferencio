<?php
session_start();

// Informations de connexion à la base de données
$user = "raphael.daviot";
$pass = "1234567890";
try {
    $cnx = new PDO('pgsql:host=sqletud.u-pem.fr; dbname=raphael.daviot_db', $user, $pass);
    $cnx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "ERREUR : La connexion a échouée";
    exit;
}

try {
    $cnx->beginTransaction(); 

    // Récupérer l'identifiant du conférencier connecté
    $user_id = $_SESSION['id_utilisateur'];

    // Requête pour récupérer les informations du conférencier
    $query_conferencier = "SELECT c.*, p.num_conference
                           FROM sae.conferencier c
                           LEFT JOIN sae.presenter p ON c.num_conferencier = p.num_conferencier
                           WHERE c.num_conferencier = :user_id FOR UPDATE";
    $stmt_conferencier = $cnx->prepare($query_conferencier);
    $stmt_conferencier->bindParam(':user_id', $user_id);
    $stmt_conferencier->execute();
    $conferencier_info = $stmt_conferencier->fetch(PDO::FETCH_ASSOC);

    // Requête pour récupérer les conférences associées au conférencier
    $query_conferences = "SELECT c.*
                          FROM sae.conference c
                          INNER JOIN sae.presenter p ON c.num_conference = p.num_conference
                          WHERE p.num_conferencier = :conferencier_id FOR UPDATE";
    $stmt_conferences = $cnx->prepare($query_conferences);
    $stmt_conferences->bindParam(':conferencier_id', $user_id);
    $stmt_conferences->execute();
    $conferences = $stmt_conferences->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Informations Personnelles Conferencier</title>
</head>
<body>
    <main>
        <h1>Informations Personnelles</h1>
        <?php if ($conferencier_info): ?>
            <p>Nom : <?php echo $conferencier_info['nom_conferencier']; ?></p>
            <p>Prénom : <?php echo $conferencier_info['prenom_conferencier']; ?></p>
            <p>Organisme : <?php echo $conferencier_info['organisme']; ?></p>
            <p>Fonction : <?php echo $conferencier_info['fonction_conferencier']; ?></p>
            <p>Adresse e-mail : <?php echo $conferencier_info['mail_conferencier']; ?></p>
            <h2>Conférences associées</h2>
            <ul>
                <?php foreach ($conferences as $conference) : ?>
                    <li>
                        <?php echo $conference['theme']; ?>
                        
                        <form action="details_conference.php" method="post" style="display: inline;">
                            <input type="hidden" name="conference_id" value="<?php echo $conference['num_conference']; ?>">
                            <button type="submit">Détails</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
            <!-- Formulaire de création de nouvelle conférence -->
            <h2>Créer une nouvelle conférence</h2>
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
        <?php else: ?>
            <p>Aucune information disponible pour ce conférencier.</p>
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
                    <li><a href="accueil.php" >ACCUEIL</a></li>
                    <li><a href="conferences.php">CATALOGUE DES CONFERENCES</a></li>
                    <?php 
                    $user_type = $_SESSION['user_type'];
                    if($user_type=='participant'){
                        echo '<li><a href="informations_personnelles.php">INFORMATIONS PERSONNELLES</a></li>'; 
                    } else if ($user_type=='technician'){
                        echo '<li><a href="informations_personnelles_technicien.php">INFORMATIONS PERSONNELLES</a></li>';
                    } else if ($user_type=='conference-speaker'){
                        echo '<li><a href="informations_personnelles_conferencier.php" class="active">INFORMATIONS PERSONNELLES</a></li>';
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
