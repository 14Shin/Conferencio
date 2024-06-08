<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['conference'], $_POST['equipe'], $_POST['materiel'])) {
        $num_conference = $_POST['conference'];
        $num_equipe = $_POST['equipe'];
        $num_materiel = $_POST['materiel'];

        $user = "raphael.daviot";
        $pass = "1234567890";
        try {
            $cnx = new PDO('pgsql:host=sqletud.u-pem.fr; dbname=raphael.daviot_db', $user, $pass);
            $cnx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $cnx->beginTransaction();
            
            $query = "INSERT INTO sae.superviser (num_conference, num_equipe, num_materiel) VALUES (:num_conference, :num_equipe, :num_materiel)";
            $stmt = $cnx->prepare($query);
            $stmt->bindParam(':num_conference', $num_conference);
            $stmt->bindParam(':num_equipe', $num_equipe);
            $stmt->bindParam(':num_materiel', $num_materiel);

            if ($stmt->execute()) {
                $cnx->commit(); 
                header('location: tableau_bord.php');
            } else {
                $cnx->rollBack(); 
                echo "Erreur lors de l'association du matériel.";
            }
        } catch (PDOException $e) {
            $cnx->rollBack();
            echo "Erreur lors de l'association du matériel : " . $e->getMessage();
        }
    } else {
        echo "Tous les champs sont obligatoires.";
    }
}
?>
