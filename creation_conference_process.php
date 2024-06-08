<?php
session_start();

$user = "raphael.daviot";
$pass = "1234567890";
try {
    $cnx = new PDO('pgsql:host=sqletud.u-pem.fr; dbname=raphael.daviot_db', $user, $pass);
} catch (PDOException $e) {
    echo "ERREUR : La connexion a échoué";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $cnx->beginTransaction(); 

        if (isset($_POST['type_intervention'], $_POST['theme'], $_POST['langue'], $_POST['date_horaire'], $_POST['duree'], $_POST['resume_court'], $_POST['resume_long'])) {
            $query_max_id = "SELECT MAX(num_conference) AS max_id FROM sae.conference FOR UPDATE";
            $stmt_max_id = $cnx->query($query_max_id);
            
            if ($stmt_max_id) {
                $max_id_row = $stmt_max_id->fetch(PDO::FETCH_ASSOC);
                $max_id = $max_id_row['max_id'];
                $id_conference = $max_id + 1;
            }

            $type_intervention = $_POST['type_intervention'];
            $theme = $_POST['theme'];
            $langue = $_POST['langue'];
            $date_horaire = $_POST['date_horaire'];
            $duree = $_POST['duree'];
            $resume_court = $_POST['resume_court'];
            $resume_long = $_POST['resume_long'];

            $query = "INSERT INTO sae.conference (num_conference, type_intervention, theme, langue, date_horaire, duree, resume_court, resume_long) VALUES (:id_conference, :type_intervention, :theme, :langue, :date_horaire, :duree, :resume_court, :resume_long)";
            $stmt = $cnx->prepare($query);

            $stmt->bindParam(':id_conference', $id_conference);
            $stmt->bindParam(':type_intervention', $type_intervention);
            $stmt->bindParam(':theme', $theme);
            $stmt->bindParam(':langue', $langue);
            $stmt->bindParam(':date_horaire', $date_horaire);
            $stmt->bindParam(':duree', $duree);
            $stmt->bindParam(':resume_court', $resume_court);
            $stmt->bindParam(':resume_long', $resume_long);

            if ($stmt->execute()) {
                $user_type = $_SESSION['user_type'];
                if($user_type=='conference-speaker'){

                    $query_associate_presenter = "INSERT INTO sae.presenter (num_conferencier, num_conference) VALUES (:user_id, :id_conference)";
                    $stmt_associate_presenter = $cnx->prepare($query_associate_presenter);
                    $stmt_associate_presenter->bindParam(':user_id', $_SESSION['id_utilisateur']);
                    $stmt_associate_presenter->bindParam(':id_conference', $id_conference);
                    $stmt_associate_presenter->execute();
                    header('location: informations_personnelles_conferencier.php');
                }else{
                    header('location: tableau_bord.php');
                }
                
            } else {
                echo "Erreur lors de la création de la conférence.";
            }
        } else {
            echo "Tous les champs sont obligatoires.";
        }

        $cnx->commit(); 
    } catch (PDOException $e) {
        $cnx->rollBack(); 
        echo "Erreur lors de la création de la conférence : " ;
    }
}
?>
