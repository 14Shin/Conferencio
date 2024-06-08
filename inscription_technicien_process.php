<?php
session_start();

$user = "raphael.daviot";
$pass = "1234567890";
try {
    $cnx = new PDO('pgsql:host=sqletud.u-pem.fr; dbname=raphael.daviot_db', $user, $pass);
    $cnx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "ERREUR : La connexion a échoué";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $cnx->beginTransaction(); 

        $query_max_id = "SELECT MAX(num_technicien) AS max_id FROM sae.technicien FOR UPDATE";
        $stmt_max_id = $cnx->query($query_max_id);
        
        if ($stmt_max_id) { 
            $max_id_row = $stmt_max_id->fetch(PDO::FETCH_ASSOC);
            $max_id = $max_id_row['max_id'];
            $technicien_id = $max_id + 1;
        }

        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $birthdate = $_POST['birthdate'];
        $phone = $_POST['phone'];
        $profession = $_POST['profession'];
        $password = $_POST['password'];
        $mail = $_POST['mail'];

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO sae.technicien (num_technicien, nom_technicien, prenom_technicien, date_naissance_technicien, telephone_technicien, fonction_technicien, password_technicien, mail_technicien) VALUES (:technicien_id, :lastname, :firstname, :birthdate, :phone, :profession, :password, :mail)";
        
        $stmt = $cnx->prepare($query);

        $stmt->bindParam(':technicien_id', $technicien_id);
        $stmt->bindParam(':firstname', $firstname);
        $stmt->bindParam(':lastname', $lastname);
        $stmt->bindParam(':birthdate', $birthdate);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':profession', $profession);
        $stmt->bindParam(':password', $passwordHash);
        $stmt->bindParam(':mail', $mail);

        if ($stmt->execute()) {
            $cnx->commit(); 
            header("Location: tableau_bord.php");
            exit;
        } else {
            echo "Erreur lors de l'inscription du technicien : " . $stmt->errorInfo()[2];
        }
    } catch (PDOException $e) {
        $cnx->rollBack(); 
        echo "Erreur lors de l'inscription du technicien : " . $e->getMessage();
    }
}
?>
