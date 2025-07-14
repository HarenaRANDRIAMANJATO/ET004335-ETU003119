<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_membre'])) {
    $_SESSION['error'] = "Veuillez vous connecter pour accéder à cette page.";
    header('Location: login.php');
    exit();
}

require_once '../inc/connexion.php';
require_once '../inc/function.php';

$conn = dbconnect();

$objets = getListeObjets($conn);

// Activer l'affichage des erreurs pour le débogage (à supprimer en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Objets</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h2 class="mb-0">Liste des Objets</h2>
            </div>
            <div class="card-body">
                <p>Connecté en tant que: <strong><?php echo htmlspecialchars($_SESSION['nom']); ?></strong>
                    (<a href="logout.php" class="btn btn-outline-danger btn-sm">Déconnexion</a>)
                    <a href="filtre.php" class="btn btn-outline-primary btn-sm float-end">Filtrer par Catégorie</a>
                    <a href="ajout_objet.php" class="btn btn-outline-success btn-sm float-end me-2">Ajouter un Objet</a>
                </p>
                
                <?php if ($objets === false): ?>
                    <div class="alert alert-danger" role="alert">
                        Erreur lors du chargement des objets.
                    </div>
                <?php elseif (empty($objets)): ?>
                    <div class="alert alert-info" role="alert">
                        Aucun objet trouvé.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Image</th>
                                    <th>Nom de l'objet</th>
                                    <th>Catégorie</th>
                                    <th>Propriétaire</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($objets as $objet): ?>
                                    <tr>
                                        <td>
                                            <?php if (isset($objet['nom_image']) && $objet['nom_image'] && file_exists($objet['nom_image'])): ?>
                                                <img src="<?php echo htmlspecialchars($objet['nom_image']); ?>" alt="<?php echo htmlspecialchars($objet['nom_objet']); ?>" style="max-width: 50px; height: auto;">
                                            <?php else: ?>
                                                <span class="text-muted">Aucune image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($objet['nom_objet']); ?></td>
                                        <td><?php echo htmlspecialchars($objet['nom_categorie'] ?: 'Non catégorisé'); ?></td>
                                        <td><?php echo htmlspecialchars($objet['proprietaire']); ?></td>
                                        <td>
                                            <?php 
                                            if ($objet['date_retour'] === null && $objet['id_objet'] !== null) {
                                                echo '<span class="badge bg-warning">Emprunté (en cours)</span>';
                                            } else {
                                                echo '<span class="badge bg-success">Disponible</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>