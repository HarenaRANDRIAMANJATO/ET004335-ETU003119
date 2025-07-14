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

// Fetch categories for dropdown
$categories = getCategorie($conn);
if ($categories === false) {
    $categories = [];
}

// Initialize search parameters
$id_categorie = isset($_GET['id_categorie']) && $_GET['id_categorie'] !== '' ? (int)$_GET['id_categorie'] : null;
$nom_objet = isset($_GET['nom_objet']) ? trim($_GET['nom_objet']) : '';
$disponible = isset($_GET['disponible']) && $_GET['disponible'] === '1' ? true : false;

// Fetch objects based on search criteria
$objets = searchObjets($conn, $id_categorie, $nom_objet, $disponible);

// Activer l'affichage des erreurs pour le débogage (à supprimer en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="fr">
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
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">Déconnexion</a>
                    <a href="ajout_objet.php" class="btn btn-outline-success btn-sm float-end me-2">Ajouter un Objet</a>
                    <a href="filtre.php" class="btn btn-outline-primary btn-sm float-end">Filtrer par Catégorie</a>

                </p>

                <!-- Search Form -->
                <form method="GET" action="liste.php" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="id_categorie" class="form-label">Catégorie</label>
                            <select name="id_categorie" id="id_categorie" class="form-select">
                                <option value="">Toutes les catégories</option>
                                <?php foreach ($categories as $categorie): ?>
                                    <option value="<?php echo $categorie['id_categorie']; ?>" 
                                            <?php echo ($id_categorie == $categorie['id_categorie']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($categorie['nom_categorie']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="nom_objet" class="form-label">Nom de l'objet</label>
                            <input type="text" name="nom_objet" id="nom_objet" class="form-control" 
                                   value="<?php echo htmlspecialchars($nom_objet); ?>" placeholder="Rechercher par nom">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" name="disponible" id="disponible" value="1" 
                                       class="form-check-input" <?php echo $disponible ? 'checked' : ''; ?>>
                                <label for="disponible" class="form-check-label">Disponible uniquement</label>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                        </div>
                    </div>
                </form>

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
                                                <a href="objet_details.php?id_objet=<?php echo $objet['id_objet']; ?>">
                                                    <img src="<?php echo htmlspecialchars($objet['nom_image']); ?>" alt="<?php echo htmlspecialchars($objet['nom_objet']); ?>" style="max-width: 50px; height: auto;">
                                                </a>
                                            <?php else: ?>
                                                <a href="objet_details.php?id_objet=<?php echo $objet['id_objet']; ?>">
                                                    <img src="../image/default.jpg" alt="Image par défaut" style="max-width: 50px; height: auto;">
                                                </a>
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