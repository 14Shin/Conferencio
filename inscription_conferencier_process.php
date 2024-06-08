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

        if (isset($_POST['lastname'], $_POST['firstname'], $_POST['organisme'], $_POST['profession'], $_POST['password'], $_POST['mail'])) {
            $query_max_id = "SELECT COUNT(num_conferencier) AS nombre_conferenciers FROM sae.conferencier FOR UPDATE";
            $stmt_max_id = $cnx->query($query_max_id);
            
            if ($stmt_max_id) { 
                $max_id_row = $stmt_max_id->fetch(PDO::FETCH_ASSOC);
                $max_id = $max_id_row['nombre_conferenciers'];
                $id_conferencier = 'c0'.($max_id + 1);
            }

            $nom = $_POST['lastname'];
            $prenom = $_POST['firstname'];
            $organisme = $_POST['organisme'];
            $fonction = $_POST['profession'];
            $password = $_POST['password'];
            $mail = $_POST['mail'];

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $query = "INSERT INTO sae.conferencier (num_conferencier, nom_conferencier, prenom_conferencier, organisme, fonction_conferencier, password_conferencier, mail_conferencier) VALUES (:id_conferencier, :nom, :prenom, :organisme, :fonction, :password, :mail)";
            $stmt = $cnx->prepare($query);

            $stmt->bindParam(':id_conferencier', $id_conferencier);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':organisme', $organisme);
            $stmt->bindParam(':fonction', $fonction);
            $stmt->bindParam(':password', $passwordHash);
            $stmt->bindParam(':mail', $mail);

            if ($stmt->execute()) {
                $cnx->commit(); 
                header('location: tableau_bord.php');
            } else {
                echo "Erreur lors de l'inscription du conférencier.";
            }
        } else {
            echo "Tous les champs sont obligatoires.";
        }
    } catch (PDOException $e) {
        $cnx->rollBack(); 
        echo "Erreur lors de l'inscription du conférencier : " . $e->getMessage();
    }
}
?>
