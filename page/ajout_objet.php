<?php
session_start();
require_once '../inc/connexion.php';
require_once '../inc/function.php';

$conn = dbconnect();
if (!$conn) {
    die("Erreur : Connexion à la base de données échouée.");
}

if (!isset($_SESSION['id_membre'])) {
    header("Location: login.php");
    exit();
}

$errors = [];
$success = '';
$id_categorie = $_POST['id_categorie'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_objet = trim($_POST['nom_objet']);
    $id_categorie = $_POST['id_categorie'] ?? '';
    $id_membre = $_SESSION['id_membre'];

    if (empty($nom_objet)) $errors[] = "Le nom de l'objet est requis.";
    if (empty($id_categorie)) $errors[] = "Veuillez sélectionner une catégorie.";

    if (empty($errors)) {
        if (file_exists('upload.php')) {
            include 'upload.php';
            $upload_results = handleMultipleImageUpload($_FILES['images']);

            if ($upload_results['success']) {
                mysqli_begin_transaction($conn);
                try {
                    $query = "INSERT INTO objet (nom_objet, id_categorie, id_membre) VALUES (?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "sii", $nom_objet, $id_categorie, $id_membre);
                    mysqli_stmt_execute($stmt);
                    $id_objet = mysqli_insert_id($conn);
                    mysqli_stmt_close($stmt);

                    foreach ($upload_results['paths'] as $image_path) {
                        $stmt = mysqli_prepare($conn, "INSERT INTO images_objet (id_objet, nom_image) VALUES (?, ?)");
                        mysqli_stmt_bind_param($stmt, "is", $id_objet, $image_path);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                    }

                    mysqli_commit($conn);
                    $success = "Objet ajouté avec succès !";
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    foreach ($upload_results['paths'] as $path) {
                        if (file_exists($path)) unlink($path);
                    }
                    $errors[] = "Erreur lors de l'ajout : " . $e->getMessage();
                }
            } else {
                $errors[] = $upload_results['error'];
            }
        } else {
            $errors[] = "Le fichier d'upload est introuvable.";
        }
    }
}

$categories = getCategorie($conn) ?: [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un objet</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center">Ajouter un objet</h2>

        <?php if (!empty($success)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label for="nom_objet" class="block text-sm font-medium text-gray-700">Nom de l'objet</label>
                <input type="text" name="nom_objet" id="nom_objet" required class="mt-1 w-full p-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="id_categorie" class="block text-sm font-medium text-gray-700">Catégorie</label>
                <select name="id_categorie" id="id_categorie" required class="mt-1 w-full p-2 border rounded-md">
                    <option value="">-- Choisir une catégorie --</option>
                    <?php foreach ($categories as $categorie): ?>
                        <option value="<?= $categorie['id_categorie'] ?>" <?= $id_categorie == $categorie['id_categorie'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($categorie['nom_categorie']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="images" class="block text-sm font-medium text-gray-700">Images (JPG/PNG, 10 Mo max)</label>
                <input type="file" name="images[]" id="images" accept=".jpg,.jpeg,.png" multiple class="mt-1 w-full p-2 border rounded-md">
            </div>

            <div>
                <button type="submit" class="w-full bg-indigo-600 text-white p-2 rounded-md hover:bg-indigo-700">Ajouter l'objet</button>
            </div>
            <div class="text-center">
                <a href="liste.php" class="text-indigo-600 hover:underline">Retour à la liste</a>
            </div>
        </form>
    </div>
</body>
</html>
