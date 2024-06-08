<?php
session_start();

if (isset($_SESSION['id_utilisateur'])) { 
    header('location: accueil.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user =  "raphael.daviot";
    $pass =  "1234567890";
    try {
        $cnx = new PDO('pgsql:host=sqletud.u-pem.fr; dbname=raphael.daviot_db', $user, $pass); 
        $cnx->beginTransaction(); 
    } catch (PDOException $e) {
        echo "ERREUR : La connexion a échouée";
        exit;
    }

    try {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $query = "SELECT id, username, password FROM utilisateurs WHERE username = :username FOR UPDATE";
        $stmt = $cnx->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) { 
            $_SESSION['id_utilisateur'] = $user['id'];
            $cnx->commit(); 
            header('location: accueil.php');
            exit;
        } else {
            echo "ERREUR : La connexion a échouée";
            $error = "Identifiant ou mot de passe incorrect";
        }
    } catch (PDOException $e) {
        $cnx->rollBack(); 
        echo "ERREUR : La connexion a échouée";
    }
}
?>
