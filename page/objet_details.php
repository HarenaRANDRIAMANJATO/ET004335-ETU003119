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
if (!$conn) {
    die("Erreur : La connexion à la base de données a échoué.");
}

// Vérifier si id_objet est fourni
if (!isset($_GET['id_objet']) || !is_numeric($_GET['id_objet'])) {
    $_SESSION['error'] = "ID d'objet invalide.";
    header('Location: liste.php');
    exit();
}

$id_objet = (int)$_GET['id_objet'];

// Récupérer les détails de l'objet
$query = "
    SELECT 
        o.id_objet,
        o.nom_objet,
        c.nom_categorie,
        m.nom AS proprietaire,
        e.date_retour
    FROM objet o
    LEFT JOIN categorie_objet c ON o.id_categorie = c.id_categorie
    LEFT JOIN membre m ON o.id_membre = m.id_membre
    LEFT JOIN emprunt e ON o.id_objet = e.id_objet AND e.date_retour IS NULL
    WHERE o.id_objet = ?
";
$stmt = mysqli_prepare($conn, $query);
if (!$stmt) {
    $_SESSION['error'] = "Erreur de préparation de la requête: " . mysqli_error($conn);
    header('Location: liste.php');
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $id_objet);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$objet = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$objet) {
    $_SESSION['error'] = "Objet non trouvé.";
    header('Location: liste.php');
    exit();
}

// Récupérer toutes les images de l'objet
$images_query = "SELECT nom_image FROM images_objet WHERE id_objet = ?";
$images_stmt = mysqli_prepare($conn, $images_query);
mysqli_stmt_bind_param($images_stmt, "i", $id_objet);
mysqli_stmt_execute($images_stmt);
$images_result = mysqli_stmt_get_result($images_stmt);
$images = [];
while ($row = mysqli_fetch_assoc($images_result)) {
    $images[] = $row['nom_image'];
}
mysqli_stmt_close($images_stmt);

// Récupérer l'historique des emprunts
$history_query = "
    SELECT 
        e.date_emprunt,
        e.date_retour,
        m.nom AS emprunteur
    FROM emprunt e
    LEFT JOIN membre m ON e.id_membre = m.id_membre
    WHERE e.id_objet = ?
    ORDER BY e.date_emprunt DESC
";
$history_stmt = mysqli_prepare($conn, $history_query);
mysqli_stmt_bind_param($history_stmt, "i", $id_objet);
mysqli_stmt_execute($history_stmt);
$history_result = mysqli_stmt_get_result($history_stmt);
$history = [];
while ($row = mysqli_fetch_assoc($history_result)) {
    $history[] = $row;
}
mysqli_stmt_close($history_stmt);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'Objet</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h2 class="mb-0">Détails de l'Objet</h2>
            </div>
            <div class="card-body">
                <p>Connecté en tant que: <strong><?php echo htmlspecialchars($_SESSION['nom']); ?></strong>
                    (<a href="logout.php" class="btn btn-outline-danger btn-sm">Déconnexion</a>)
                    <a href="liste.php" class="btn btn-outline-primary btn-sm float-end">Retour à la liste</a>
                </p>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <h3 class="mb-4"><?php echo htmlspecialchars($objet['nom_objet']); ?></h3>
                <div class="row">
                    <div class="col-md-6">
                        <!-- Image principale -->
                        <?php if (!empty($images) && file_exists($images[0])): ?>
                            <h5>Image principale</h5>
                            <img src="<?php echo htmlspecialchars($images[0]); ?>" alt="<?php echo htmlspecialchars($objet['nom_objet']); ?>" class="img-fluid mb-3" style="max-width: 300px; height: auto;">
                        <?php else: ?>
                            <p class="text-muted">Aucune image principale disponible</p>
                        <?php endif; ?>

                        <!-- Autres images -->
                        <?php if (count($images) > 1): ?>
                            <h5>Autres images</h5>
                            <div class="d-flex flex-wrap">
                                <?php foreach (array_slice($images, 1) as $image): ?>
                                    <?php if (file_exists($image)): ?>
                                        <img src="<?php echo htmlspecialchars($image); ?>" alt="Image supplémentaire" class="img-fluid me-2 mb-2" style="max-width: 100px; height: auto;">
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Nom de l'objet :</strong> <?php echo htmlspecialchars($objet['nom_objet']); ?></p>
                        <p><strong>Catégorie :</strong> <?php echo htmlspecialchars($objet['nom_categorie'] ?: 'Non catégorisé'); ?></p>
                        <p><strong>Propriétaire :</strong> <?php echo htmlspecialchars($objet['proprietaire']); ?></p>
                        <p><strong>Statut :</strong> 
                            <?php 
                            if ($objet['date_retour'] === null && $objet['id_objet'] !== null) {
                                echo '<span class="badge bg-warning">Emprunté (en cours)</span>';
                            } else {
                                echo '<span class="badge bg-success">Disponible</span>';
                            }
                            ?>
                        </p>
                    </div>
                </div>

                <!-- Historique des emprunts -->
                <h4 class="mt-4">Historique des emprunts</h4>
                <?php if (empty($history)): ?>
                    <p class="text-muted">Aucun emprunt enregistré pour cet objet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Emprunteur</th>
                                    <th>Date d'emprunt</th>
                                    <th>Date de retour</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $emprunt): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($emprunt['emprunteur'] ?: 'Inconnu'); ?></td>
                                        <td><?php echo htmlspecialchars($emprunt['date_emprunt']); ?></td>
                                        <td><?php echo htmlspecialchars($emprunt['date_retour'] ?: 'Non retourné'); ?></td>
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