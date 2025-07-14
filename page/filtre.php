<?php
session_start();

if (!isset($_SESSION['id_membre'])) {
    $_SESSION['error'] = "Veuillez vous connecter pour accéder à cette page.";
    header('Location: login.php');
    exit();
}

require_once '../inc/connexion.php'; // Inclure la connexion à la base de données
require_once '../inc/function.php';  // Inclure les fonctions

$conn = dbconnect();

$categories = getCategories($conn);

$id_categorie = filter_input(INPUT_POST, 'id_categorie', FILTER_VALIDATE_INT) ?: null;

$objets = filtrerParCategorie($id_categorie, $conn);

// Activer l'affichage des erreurs pour le débogage (à supprimer en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filtrer les Objets par Catégorie</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h2 class="mb-0">Filtrer les Objets par Catégorie</h2>
            </div>
            <div class="card-body">
                <p>Connecté en tant que: <strong><?php echo htmlspecialchars($_SESSION['nom']); ?></strong>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">Déconnexion</a>
                </p>
                
                <form action="filtre.php" method="post" class="mb-4">
                    <div class="row g-2 align-items-center">
                        <div class="col-auto">
                            <label for="id_categorie" class="form-label">Catégorie:</label>
                        </div>
                        <div class="col-auto">
                            <select name="id_categorie" id="id_categorie" class="form-select">
                                <option value="">Toutes les catégories</option>
                                <?php if ($categories): ?>
                                    <?php foreach ($categories as $categorie): ?>
                                        <option value="<?php echo $categorie['id_categorie']; ?>" 
                                                <?php echo ($id_categorie == $categorie['id_categorie']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($categorie['nom_categorie']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="">Aucune catégorie disponible</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">Filtrer</button>
                        </div>
                    </div>
                </form>

                <?php if ($objets === false): ?>
                    <div class="alert alert-danger" role="alert">
                        Erreur lors du chargement des objets.
                    </div>
                <?php elseif (empty($objets)): ?>
                    <div class="alert alert-info" role="alert">
                        Aucun objet trouvé pour cette catégorie.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nom de l'objet</th>
                                    <th>Catégorie</th>
                                    <th>Propriétaire</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($objets as $objet): ?>
                                    <tr>
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