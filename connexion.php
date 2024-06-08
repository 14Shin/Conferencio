<?php
session_start();

if (isset($_SESSION['id_utilisateur'])) { //Si l'utilisateur est déjà connecté
    header('location: accueil.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = "raphael.daviot";
    $pass = "1234567890";
    
    try {
        $cnx = new PDO('pgsql:host=sqletud.u-pem.fr; dbname=raphael.daviot_db', $user, $pass);
    } catch (PDOException $e) {
        echo "ERREUR : La connexion a échouée";
        exit; 
    }

    $mail = $_POST['mail'];
    $password = $_POST['password'];

    $user_type = $_POST['user-type']; // Récupérer le type d'utilisateur sélectionné

    $_SESSION['user_type'] = $user_type;

    $temps = 300;
    if(isset($_POST['id_utilisateur'])) {
        setcookie('id_utilisateur', $_POST['id_utilisateur'], time() + $temps);
    }
    if(isset($_POST['mail'])) {
        setcookie('mail', $_POST['mail'], time() + $temps);
    }
    if(isset($_POST['password'])) {
        setcookie('password', $_POST['password'], time() + $temps);
    }
    if(isset($_POST['user-type'])) {
        setcookie('user-type', $_POST['user-type'], time() + $temps);
    }

    try {
        $cnx->beginTransaction(); 

        $query = "";
        switch ($user_type) {
            case 'participant':
                $query = "SELECT num_participant, mail, password FROM sae.participant WHERE mail = :mail FOR UPDATE";
                break;
            case 'conference-speaker':
                $query = "SELECT num_conferencier, mail_conferencier, password_conferencier FROM sae.conferencier WHERE mail_conferencier = :mail FOR UPDATE";
                break;
            case 'technician':
                $query = "SELECT num_technicien, mail_technicien, password_technicien FROM sae.technicien WHERE mail_technicien = :mail FOR UPDATE";
                break;
            case 'autres':
                $query = "SELECT num_admin, id_admin, password_admin FROM sae.admin WHERE id_admin = :mail FOR UPDATE";
                break;
            default:
                echo "Type d'utilisateur non reconnu.";
                exit;
        }
        
        $stmt = $cnx->prepare($query);
        $stmt->bindParam(':mail', $mail);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) { // Vérifie si l'utilisateur existe
            if ($user_type=='participant'){// Récupérer le mot de passe haché de la base de données
                $hashed_password = $user['password'];
            } else if ($user_type=='technician'){
                $hashed_password = $user['password_technicien'];
            } else if ($user_type=='conference-speaker'){
                $hashed_password = $user['password_conferencier'];
            } else if ($user_type=='autres'){
                $hashed_password = $user['password_admin'];
            } 
            
            if (password_verify($password, $hashed_password)) {// Vérifier si le mot de passe soumis correspond au mot de passe haché
                $_SESSION['id_utilisateur'] = $user['num_participant'];
                if($user_type=='participant'){
                    $_SESSION['id_utilisateur'] = $user['num_participant'];
                } else if ($user_type=='technician'){
                    $_SESSION['id_utilisateur'] = $user['num_technicien'];
                } else if ($user_type=='conference-speaker'){
                    $_SESSION['id_utilisateur'] = $user['num_conferencier'];
                } else if ($user_type=='autres'){
                    $_SESSION['id_utilisateur'] = $user['num_admin'];
                }

                $cnx->commit(); 

                header('location: accueil.php');
                exit;
            } else {
                echo "ERREUR : Identifiant, mot de passe ou catégorie incorrect";
            }
        } else {
            echo "ERREUR : Identifiant, mot de passe ou catégorie incorrect";
        }
    } catch (PDOException $e) {
        $cnx->rollBack(); 
        echo "ERREUR : " . $e->getMessage();
    } 
}else {
    echo "Merci de passer par le formulaire de connexion";
}
?>