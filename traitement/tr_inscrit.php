<?php
session_start();
require_once '../inc/connexion.php'; 
require_once '../inc/function.php'; 

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
    $date_de_naissance = filter_input(INPUT_POST, 'date_de_naissance', FILTER_SANITIZE_STRING);
    $genre = filter_input(INPUT_POST, 'genre', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $ville = filter_input(INPUT_POST, 'ville', FILTER_SANITIZE_STRING);
    $mdp = filter_input(INPUT_POST, 'mdp', FILTER_SANITIZE_STRING);

    if (empty($nom) || empty($email) || empty($mdp)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires (nom, email, mot de passe).";
        error_log("Champs obligatoires manquants pour l'inscription: nom=$nom, email=$email, mdp=" . (empty($mdp) ? 'vide' : 'rempli'));
        header('Location: ../page/inscrit.php');
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Format d'email invalide.";
        error_log("Email invalide: $email");
        header('Location: ../page/inscrit.php');
        exit();
    }

    $image_profil = null;
    if (isset($_FILES['image_profil']) && $_FILES['image_profil']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../Uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Créer le répertoire s'il n'existe pas
        }
        $file_name = basename($_FILES['image_profil']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = uniqid('profil_') . '.' . $file_ext;
            $upload_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($_FILES['image_profil']['tmp_name'], $upload_path)) {
                $image_profil = $new_file_name;
            } else {
                $_SESSION['error'] = "Erreur lors du téléchargement de l'image.";
                error_log("Échec du téléchargement de l'image: $file_name");
                header('Location: ../page/inscrit.php');
                exit();
            }
        } else {
            $_SESSION['error'] = "Format d'image non autorisé. Utilisez jpg, jpeg, png ou gif.";
            error_log("Extension non autorisée pour l'image: $file_ext");
            header('Location: ../page/inscrit.php');
            exit();
        }
    }

    $conn = dbconnect();
    $user = inserer($nom, $date_de_naissance, $genre, $email, $ville, $mdp, $image_profil, $conn);

    if ($user) {
        $_SESSION['id_membre'] = $user['id_membre'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['email'] = $user['email'];
        
        error_log("Inscription réussie pour $email, redirection vers liste.php");
        header('Location: ../page/liste.php');
        exit();
    } else {
        $error_code = mysqli_errno($conn);
        if ($error_code == 1062) { 
            $_SESSION['error'] = "Cet email est déjà utilisé.";
            error_log("Email dupliqué: $email");
        } else {
            $_SESSION['error'] = "Erreur lors de l'inscription.";
            error_log("Échec de l'inscription pour $email: " . mysqli_error($conn));
        }
        header('Location: ../page/inscrit.php');
        exit();
    }
} else {
    $_SESSION['error'] = "Méthode de requête non autorisée.";
    error_log("Méthode non POST pour tr_inscrit.php");
    header('Location: ../page/inscrit.php');
    exit();
}
?>