<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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

        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $birthdate = $_POST['birthdate'];
        $phone = $_POST['phone'];
        $profession = $_POST['profession'];
        $password = $_POST['password'];
        $mail = $_POST['mail'];

        $query_max_id = "SELECT MAX(num_participant) AS max_id FROM sae.participant";
        $stmt_max_id = $cnx->query($query_max_id);

        if ($stmt_max_id) {
            $max_id_row = $stmt_max_id->fetch(PDO::FETCH_ASSOC);
            $max_id = $max_id_row['max_id'];
            $participant_id = $max_id + 1;
        } else {
            echo "ERREUR : Impossible de récupérer le maximum de num_participant.";
            exit;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $req= "INSERT INTO sae.participant (num_participant, prenom_participant, nom_participant, date_naissance_participant, num_tel_participant, profession_participant, password, mail) 
            VALUES ('$participant_id', '$firstname', '$lastname', '$birthdate', '$phone', '$profession', '$passwordHash', '$mail');";
        echo $req;

        $test = $cnx->prepare("SELECT COUNT(*) FROM sae.participant WHERE mail = '$mail'");
        $test->execute();
        $count = $test->fetchColumn();
        if ($count > 0){
            echo "Erreur : L'adresse e-mail existe déjà dans la base de données.";
            exit;
        } else {
            $result = $cnx->exec($req);

            if ($result){
                echo "Votre compte a été créé avec succès !";
                $cnx->commit(); 
                header('location: page_connexion.php');
                exit;
            } else {
                echo "Erreur lors de la création de votre compte.";
                exit;
            }
        }
    } catch (PDOException $e) {
        $cnx->rollBack(); 
        echo "ERREUR : " . $e->getMessage();
        exit;
    }
}
?>
