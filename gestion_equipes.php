<?php
session_start();

// Connexion à la base de données
$user = "raphael.daviot";
$pass = "1234567890";
try {
    $cnx = new PDO('pgsql:host=sqletud.u-pem.fr; dbname=raphael.daviot_db', $user, $pass);
    $cnx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "ERREUR : La connexion a échouée";
    exit;
}

try {
    $cnx->beginTransaction(); // Début de la transaction

    // Récupérer toutes les équipes techniques
    $query_equipes = "SELECT * FROM sae.equipe_technique FOR UPDATE";
    $stmt_equipes = $cnx->query($query_equipes);
    $equipes = $stmt_equipes->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer tous les techniciens
    $query_techniciens = "SELECT * FROM sae.technicien FOR UPDATE";
    $stmt_techniciens = $cnx->query($query_techniciens);
    $techniciens = $stmt_techniciens->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer tout le matériel à disposition
    $query_materiel = "SELECT * FROM sae.materiel FOR UPDATE";
    $stmt_materiel = $cnx->query($query_materiel);
    $materiel = $stmt_materiel->fetchAll(PDO::FETCH_ASSOC);

    // Récupération des associations de la table superviser
    $query = "SELECT * FROM sae.superviser FOR UPDATE";
    $stmt = $cnx->query($query);
    $associations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer toutes les conférences
    $query_conferences = "SELECT * FROM sae.conference FOR UPDATE";
    $stmt_conferences = $cnx->query($query_conferences);
    $conferences = $stmt_conferences->fetchAll(PDO::FETCH_ASSOC);

    $cnx->commit(); 
} catch (PDOException $e) {
    $cnx->rollBack();
    echo "ERREUR : " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="accueil.css">
    <title>Tableau de Bord</title>
</head>
<body>


<main> 

    <section id="equipe_technique">
        <h2>Equipe Technique</h2>
        
        <h3>Liste des équipes techniques</h3>
            <ul>
                <?php foreach ($equipes as $equipe) : ?>
                    <li>
                        <?php echo $equipe['num_equipe']; ?>
                        <ul>
                            <li>Responsable technique: <?php echo $equipe['responsable_technique']; ?></li>
                            <li>Responsable organisationnel: <?php echo $equipe['responsable_organisationnel']; ?></li>
                            <li>Membres:
                                <ul>
                                    <?php 
                                    // Récupérer les membres de l'équipe
                                    $query_membres = "SELECT * FROM sae.technicien WHERE num_technicien IN (SELECT num_technicien FROM sae.appartenir WHERE num_equipe = :num_equipe)";
                                    $stmt_membres = $cnx->prepare($query_membres);
                                    $stmt_membres->bindParam(':num_equipe', $equipe['num_equipe']);
                                    $stmt_membres->execute();
                                    $membres = $stmt_membres->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    // Afficher les membres de l'équipe
                                    foreach ($membres as $membre) : ?>
                                        <li><?php echo $membre['nom_technicien'] . ' ' . $membre['prenom_technicien']; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        </ul>
                    </li>
                <?php endforeach; ?>
            </ul>
        

        <h3>Liste des techniciens</h3>
        <ul>
            <?php foreach ($techniciens as $technicien) : ?>
                <li><?php echo $technicien['nom_technicien'] . ' ' . $technicien['prenom_technicien']; ?></li>
            <?php endforeach; ?>
        </ul>

        <a href="inscription_technicien.php"><button>Nouveau Technicien</button></a>

        <h3>Liste du matériel à disposition</h3>
        <ul>
            <?php foreach ($materiel as $item) : ?>
                <li><?php echo $item['libelle']; ?></li>
            <?php endforeach; ?>
        </ul>
        
        <h2>Association d'une Equipe technique et du Matériel à une conférence</h2>
        <form action="association_materiel_process.php" method="post">
            <label for="conference">Conférence :</label><br>
            <select id="conference" name="conference" required>
                <option value="">Sélectionnez une conférence</option>
                <?php foreach ($conferences as $conference) : ?>
                    <option value="<?php echo $conference['num_conference']; ?>"><?php echo $conference['num_conference']; ?></option>
                <?php endforeach; ?>
            </select><br><br>
            
            <label for="equipe">Équipe :</label><br>
            <select id="equipe" name="equipe" required>
                <option value="">Sélectionnez une équipe</option>
                <?php foreach ($equipes as $equipe) : ?>
                    <option value="<?php echo $equipe['num_equipe']; ?>"><?php echo $equipe['num_equipe']; ?></option>
                <?php endforeach; ?>
            </select><br><br>
            
            <label for="materiel">Matériel :</label><br>
            <select id="materiel" name="materiel" required>
                <option value="">Sélectionnez du matériel</option>
                <?php foreach ($materiel as $item) : ?>
                    <option value="<?php echo $item['num_materiel']; ?>"><?php echo $item['libelle']; ?></option>
                <?php endforeach; ?>
            </select><br><br>
            
            <button type="submit">Associer Matériel</button>
        </form>
        <h3>Liste des associations de matériel :</h3>
        <ul>
            <?php foreach ($associations as $association) : ?>
                <li>
                    Conférence : <?php echo $association['num_conference']; ?>,
                    Équipe : <?php echo $association['num_equipe']; ?>,
                    Matériel : <?php echo $association['num_materiel']; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>



</main>

    <footer>
        <a href="accueil.php">Accueil</a>
        <a href="conferences.php">Catalogue des conférences</a>
        <a href="informations_personnelles.php">Informations personnelles</a>
        <a href="#">Paramètres</a>
        <a href="deconnexion.php">Se déconnecter</a>
        <hr>
        <a href="#">À propos de nous</a>
        <a href="#">Mentions légales</a>
        <a href="#">Politique de confidentialité</a>
    </footer>
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
                        echo '<li><a href="informations_personnelles.php">INFORMATIONS PERSONNELLES</a></li>'; 
                    } else if ($user_type=='technician'){
                        echo '<li><a href="informations_personnelles_technicien.php">INFORMATIONS PERSONNELLES</a></li>';
                    } else if ($user_type=='conference-speaker'){
                        echo '<li><a href="informations_personnelles_conferencier.php">INFORMATIONS PERSONNELLES</a></li>';
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
                                echo '<li><a href="gestion_equipes.php" class="active">GESTION DES EQUIPES TECHNIQUES</a></li>';
                                echo '<li><a href="tableau_bord.php">TABLEAU DE BORD ADMINISTRATEUR</a></li>';
                                break;
                            case 2:
                                echo '<li><a href="gestion_equipes.php" class="active">GESTION DES EQUIPES TECHNIQUES</a></li>';
                                break;
                            case 3:
                                echo '<li><a href="gestion_conferences.php" class="active">GESTION DES CONFERENCES</a></li>';
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


</body>
</html>
