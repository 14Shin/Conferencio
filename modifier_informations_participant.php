<?php
session_start();

if (!isset($_SESSION['id_utilisateur'])) {
    header('location: page_connexion.php');
    exit;
}

$user = "raphael.daviot";
$pass = "1234567890";
try {
    $cnx = new PDO('pgsql:host=sqletud.u-pem.fr; dbname=raphael.daviot_db', $user, $pass);
    $cnx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "ERREUR : La connexion a échouée";
    exit;
}

if (!isset($_GET['participant_id'])) {
    echo "ID du participant non spécifié.";
    exit;
}
$participant_id = $_GET['participant_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $cnx->beginTransaction(); 

        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $date_naissance = $_POST['date_naissance'];
        $tel = $_POST['tel'];
        $profession = $_POST['profession'];

        $query = "UPDATE sae.participant SET 
                    nom_participant = :nom,
                    prenom_participant = :prenom,
                    date_naissance_participant = :date_naissance,
                    num_tel_participant = :tel,
                    profession_participant = :profession
                  WHERE num_participant = :participant_id";
        $stmt = $cnx->prepare($query);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':date_naissance', $date_naissance);
        $stmt->bindParam(':tel', $tel);
        $stmt->bindParam(':profession', $profession);
        $stmt->bindParam(':participant_id', $participant_id);

        $stmt->execute();
        
        $cnx->commit(); 
        echo '<div style="color: green; font-size: 18px;margin-left: 300px;"><p>Les informations du participant ont été mises à jour avec succès.</p></div>';
    } catch (PDOException $e) {
        $cnx->rollBack();
        echo "<p>Erreur lors de la mise à jour des informations du participant.</p>";
    }
}

$query = "SELECT * FROM sae.participant WHERE num_participant = :participant_id";
$stmt = $cnx->prepare($query);
$stmt->bindParam(':participant_id', $participant_id);
$stmt->execute();
$participant_info = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="accueil.css">
    <title>Modifier les Informations du Participant</title>
</head>
<body>
    <main>
        <h1>Modifier les Informations du Participant</h1>
        <form action="" method="post">
            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom" value="<?php echo $participant_info['nom_participant']; ?>"><br><br>
            
            <label for="prenom">Prénom :</label>
            <input type="text" id="prenom" name="prenom" value="<?php echo $participant_info['prenom_participant']; ?>"><br><br>
            
            <label for="date_naissance">Date de Naissance :</label>
            <input type="date" id="date_naissance" name="date_naissance" value="<?php echo $participant_info['date_naissance_participant']; ?>"><br><br>
            
            <label for="tel">Numéro de Téléphone :</label>
            <input type="tel" id="tel" name="tel" value="<?php echo $participant_info['num_tel_participant']; ?>"><br><br>
            
            <label for="profession">Profession :</label>
            <input type="text" id="profession" name="profession" value="<?php echo $participant_info['profession_participant']; ?>"><br><br>
            
            <input type="submit" value="Modifier">
        </form>
    </main>
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
                        echo '<li><a href="informations_personnelles.php" class="active">INFORMATIONS PERSONNELLES</a></li>'; 
                    } else if ($user_type=='technician'){
                        echo '<li><a href="informations_personnelles_technicien.php" class="active">INFORMATIONS PERSONNELLES</a></li>';
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
