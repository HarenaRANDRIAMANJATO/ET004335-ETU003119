<?php
session_start();
require_once '../inc/connexion.php'; // Includes dbconnect()
require_once '../inc/function.php'; // Includes getCategorie

// Get database connection
$conn = dbconnect();
if (!$conn) {
    die("Erreur : La connexion à la base de données a échoué.");
}

// Check if user is logged in
if (!isset($_SESSION['id_membre'])) {
    header("Location: login.php");
    exit();
}

$errors = [];
$success = '';
$id_categorie = isset($_POST['id_categorie']) ? $_POST['id_categorie'] : ''; // Initialize to avoid undefined variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom_objet = trim($_POST['nom_objet']);
    $id_categorie = $_POST['id_categorie'];
    $id_membre = $_SESSION['id_membre'];

    // Validate input
    if (empty($nom_objet)) {
        $errors[] = "Le nom de l'objet est requis.";
    }
    if (empty($id_categorie)) {
        $errors[] = "Veuillez sélectionner une catégorie.";
    }

    // Include upload.php for image processing
    if (empty($errors)) {
        if (file_exists('upload.php')) {
            include 'upload.php';
            $upload_result = handleImageUpload($_FILES['../image']);
            
            if ($upload_result['success']) {
                mysqli_begin_transaction($conn);
                try {
                    // Insert into objet table
                    $query = "INSERT INTO objet (nom_objet, id_categorie, id_membre) VALUES (?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $query);
                    if (!$stmt) {
                        throw new Exception("Erreur de préparation de la requête: " . mysqli_error($conn));
                    }
                    mysqli_stmt_bind_param($stmt, "sii", $nom_objet, $id_categorie, $id_membre);
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Erreur lors de l'insertion de l'objet: " . mysqli_stmt_error($stmt));
                    }
                    $id_objet = mysqli_insert_id($conn);
                    mysqli_stmt_close($stmt);

                    // Insert into images_objet table
                    $query = "INSERT INTO images_objet (id_objet, nom_image) VALUES (?, ?)";
                    $stmt = mysqli_prepare($conn, $query);
                    if (!$stmt) {
                        throw new Exception("Erreur de préparation de la requête: " . mysqli_error($conn));
                    }
                    mysqli_stmt_bind_param($stmt, "is", $id_objet, $upload_result['path']);
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Erreur lors de l'insertion de l'image: " . mysqli_stmt_error($stmt));
                    }
                    mysqli_stmt_close($stmt);
                    
                    mysqli_commit($conn);
                    $success = "Objet ajouté avec succès !";
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $errors[] = $e->getMessage();
                }
            } else {
                $errors[] = $upload_result['error'];
            }
        } else {
            $errors[] = "Erreur : Le fichier upload.php est introuvable.";
        }
    }
}

// Fetch categories for dropdown
$categories = getCategorie($conn);
if ($categories === false) {
    $errors[] = "Erreur lors de la récupération des catégories.";
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un objet</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center">Ajouter un objet</h2>

        <?php if (!empty($success)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="ajout_objet.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label for="nom_objet" class="block text-sm font-medium text-gray-700">Nom de l'objet</label>
                <input type="text" name="nom_objet" id="nom_objet" class="mt-1 block w-full p-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>

            <div class="col-auto">
                <label for="id_categorie" class="block text-sm font-medium text-gray-700">Catégorie</label>
                <select name="id_categorie" id="id_categorie" class="form-select mt-1 block w-full p-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500" required>
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

            <div>
                <label for="image" class="block text-sm font-medium text-gray-700">Image de l'objet</label>
                <input type="file" name="image" id="image" accept=".jpg,.jpeg,.png" class="mt-1 block w-full p-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>

            <div>
                <button type="submit" class="w-full bg-indigo-600 text-white p-2 rounded-md hover:bg-indigo-700">Ajouter l'objet</button>
            </div>
             <div>
                <a href="liste.php">Retour</a>
            </div>
        </form>
    </div>
</body>
</html>