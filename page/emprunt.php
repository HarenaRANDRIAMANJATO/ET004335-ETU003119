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
    $day_emprunt = isset($_POST['day_emprunt']) ? (int)$_POST['day_emprunt'] : 0;
    
    // Valider le jour (entre 1 et 31)
    if ($day_emprunt < 1 || $day_emprunt > 31) {
        $error = "Veuillez entrer un jour valide (entre 1 et 31).";
    } else {
        // Construire la date complète avec l'année et le mois actuels
        $current_year = date('Y');
        $current_month = date('m');
        $date_emprunt = sprintf("%04d-%02d-%02d", $current_year, $current_month, $day_emprunt);
        
        // Vérifier si la date est valide
        if (!checkdate($current_month, $day_emprunt, $current_year)) {
            $error = "Le jour entré n'est pas valide pour le mois actuel.";
        } else {
            // Vérifier si l'objet est disponible
            $query = "SELECT * FROM objet WHERE id_objet = ? AND (date_retour IS NOT NULL OR date_retour IS NULL AND id_emprunt IS NULL)";
            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                $error = "Erreur de préparation de la requête : " . $conn->error;
            } else {
                $stmt->bind_param('i', $id_objet);
                $stmt->execute();
                $result = $stmt->get_result();
                $objet = $result->fetch_assoc();

                if ($objet) {
                    // Insérer un nouvel emprunt
                    $query = "INSERT INTO emprunt (id_membre, id_objet, date_emprunt) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($query);
                    if ($stmt === false) {
                        $error = "Erreur de préparation de la requête : " . $conn->error;
                    } else {
                        $stmt->bind_param('iis', $_SESSION['id_membre'], $id_objet, $date_emprunt);
                        if ($stmt->execute()) {
                            $success = "L'objet a été emprunté avec succès.";
                        } else {
                            $error = "Une erreur est survenue lors de l'emprunt : " . $stmt->error;
                        }
                        $stmt->close();
                    }
                } else {
                    $error = "Cet objet n'est pas disponible pour l'emprunt.";
                }
                if (isset($result)) {
                    $result->free();
                }
            }
        }
    }
}

if ($id_objet) {
    $query = "SELECT nom_objet FROM objet WHERE id_objet = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        $error = "Erreur de préparation de la requête : " . $conn->error;
        $nom_objet = 'Objet inconnu';
    } else {
        $stmt->bind_param('i', $id_objet);
        $stmt->execute();
        $result = $stmt->get_result();
        $objet = $result->fetch_assoc();
        $nom_objet = $objet ? htmlspecialchars($objet['nom_objet']) : 'Objet inconnu';
        $result->free();
        $stmt->close();
    }
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
                            <label for="day_emprunt" class="form-label">Jour de l'emprunt</label>
                            <input type="number" name="day_emprunt" id="day_emprunt" class="form-control" 
                                   min="1" max="31" placeholder="Entrez le jour (1-31)" required>
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