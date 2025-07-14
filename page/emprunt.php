<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_membre'])) {
    $_SESSION['error'] = "Veuillez vous connecter pour emprunter un objet.";
    header('Location: login.php');
    exit();
}

require_once '../inc/connexion.php';
require_once '../inc/function.php';

$conn = dbconnect();

$id_objet = isset($_GET['id_objet']) ? (int)$_GET['id_objet'] : null;
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_emprunt = isset($_POST['date_emprunt']) ? trim($_POST['date_emprunt']) : '';
    
    if (empty($date_emprunt)) {
        $error = "Veuillez sélectionner une date d'emprunt.";
    } else {
        // Vérifier si l'objet est disponible
        $query = "SELECT * FROM objet WHERE id_objet = :id_objet AND (date_retour IS NOT NULL OR date_retour IS NULL AND id_emprunt IS NULL)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id_objet', $id_objet, PDO::PARAM_INT);
        $stmt->execute();
        $objet = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($objet) {
            // Insérer un nouvel emprunt
            $query = "INSERT INTO emprunt (id_membre, id_objet, date_emprunt) VALUES (:id_membre, :id_objet, :date_emprunt)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id_membre', $_SESSION['id_membre'], PDO::PARAM_INT);
            $stmt->bindParam(':id_objet', $id_objet, PDO::PARAM_INT);
            $stmt->bindParam(':date_emprunt', $date_emprunt, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $success = "L'objet a été emprunté avec succès.";
            } else {
                $error = "Une erreur est survenue lors de l'emprunt.";
            }
        } else {
            $error = "Cet objet n'est pas disponible pour l'emprunt.";
        }
    }
}

if ($id_objet) {
    $query = "SELECT nom_objet FROM objet WHERE id_objet = :id_objet";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_objet', $id_objet, PDO::PARAM_INT);
    $stmt->execute();
    $objet = $stmt->fetch(PDO::FETCH_ASSOC);
    $nom_objet = $objet ? htmlspecialchars($objet['nom_objet']) : 'Objet inconnu';
} else {
    $error = "Aucun objet sélectionné.";
    $nom_objet = 'Objet inconnu';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emprunter un Objet</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h2 class="mb-0">Emprunter: <?php echo $nom_objet; ?></h2>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                        <a href="liste.php" class="btn btn-primary btn-sm mt-2">Retour à la liste</a>
                    </div>
                <?php elseif ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $success; ?>
                        <a href="liste.php" class="btn btn-primary btn-sm mt-2">Retour à la liste</a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="emprunt.php?id_objet=<?php echo $id_objet; ?>" class="mb-4">
                        <div class="mb-3">
                            <label for="date_emprunt" class="form-label">Jour de l'emprunt</label>
                            <input type="date" name="date_emprunt" id="date_emprunt" class="form-control" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Confirmer</button>
                        <a href="liste.php" class="btn btn-outline-secondary">Annuler</a>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>