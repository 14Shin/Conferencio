<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Informations de connexion à la base de données
    $user = "raphael.daviot";
    $pass = "1234567890";
    
    try {
        $cnx = new PDO('pgsql:host=sqletud.u-pem.fr; dbname=raphael.daviot_db', $user, $pass);
    } catch (PDOException $e) {
        echo "ERREUR : La connexion a échouée";
        exit; // Arrêter le script en cas d'échec de connexion
    }

    $id_participant = $_SESSION['id_utilisateur'];

    $id_conference = $_POST['conference_id'];

    // Requête pour insérer la participation à la conférence
    $req = "INSERT INTO sae.participer (num_participant, num_conference) VALUES (:id_participant, :id_conference)";
    $stmt = $cnx->prepare($req);
    $stmt->bindParam(':id_participant', $id_participant);
    $stmt->bindParam(':id_conference', $id_conference);

    // Requête pour vérifier si la conférence est complète
    $req_places = "SELECT COUNT(*) FROM sae.participer WHERE num_conference = :id_conference";
    $stmt_places = $cnx->prepare($req_places);
    $stmt_places->bindParam(':id_conference', $id_conference);
    $stmt_places->execute();
    $count = $stmt_places->fetchColumn();

    // Requête pour obtenir le nombre de places disponibles pour la conférence
    $req_nb_places = "SELECT (SELECT SUM(nb_place) FROM sae.salle TS WHERE TS.num_salle IN (SELECT num_salle FROM sae.se_deroule WHERE num_conference = :id_conference)) AS nombre_de_places";
    $stmt_nb_places = $cnx->prepare($req_nb_places);
    $stmt_nb_places->bindParam(':id_conference', $id_conference);
    $stmt_nb_places->execute();
    $nb_place = $stmt_nb_places->fetchColumn();

    // Requête pour vérifier si l'utilisateur est déjà inscrit à la conférence
    $req_check_participation = "SELECT COUNT(*) FROM sae.participer WHERE num_participant = :id_participant AND num_conference = :id_conference";
    $stmt_check_participation = $cnx->prepare($req_check_participation);
    $stmt_check_participation->bindParam(':id_participant', $id_participant);
    $stmt_check_participation->bindParam(':id_conference', $id_conference);
    $stmt_check_participation->execute();
    $count_participation = $stmt_check_participation->fetchColumn();

    // Vérifier si l'utilisateur est déjà inscrit à la conférence
    $user_type = $_SESSION['user_type'];
    if($user_type!='participant') {
        echo "Seul les participants peuvent s'inscrire à une conférence";
        exit;
    }
    if ($count_participation > 0) {
        header('location: accueil.php?error=Vous êtes déjà inscrit à cette conférence');
        exit;
    } else {
        // L'utilisateur n'est pas déjà inscrit, insérer la participation
        try {
            if ($count > $nb_place) {
                echo "Erreur : Cette conférence est complète";
                exit;
            } else {
                $stmt->execute();
                header('location: accueil.php');
                exit;
            }
        } catch (PDOException $e) {
            echo "ERREUR : Impossible d'insérer la participation à la conférence";
            exit;
        }
    }
}
?>
