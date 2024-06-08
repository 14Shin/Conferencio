<?php
$user =  'raphael.tanguy';
$pass =  'raphael';
try {
    $cnx = new PDO('pgsql:host=sqletud.u-pem.fr;dbname=raphael.tanguy_db',
        $user,
        $pass);
}
catch (PDOException $e) {
    echo "ERREUR : La connexion a échouée";

    echo "Error: Le mot de passe n'est pas le bon" . $e;

}

?>

