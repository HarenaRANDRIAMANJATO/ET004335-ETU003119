<?php
session_start();
require_once '../inc/connexion.php'; // Inclure la connexion à la base de données
require_once '../inc/function.php';     // Inclure la fonction verifyLogin

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et nettoyer les données du formulaire
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    // Vérifier que les champs ne sont pas vides
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs.";
        header('Location: ../page/login.php');
        exit();
    }

    // Obtenir la connexion à la base de données
    $conn = dbconnect();

    // Vérifier les informations de connexion
    $user = verifyLogin($email, $password, $conn);

    if ($user) {
        // Stocker les informations de l'utilisateur dans la session
        $_SESSION['id_membre'] = $user['id_membre'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['email'] = $user['email'];
        
        // Rediriger vers liste.php
        header('Location:../page/liste.php');
        exit();
    } else {
        // Stocker un message d'erreur et rediriger vers login.php
        $_SESSION['error'] = "Email ou mot de passe incorrect.";
        header('Location: ../page/login.php');
        exit();
    }
} else {
    // Si la méthode n'est pas POST, rediriger vers login.php
    $_SESSION['error'] = "Méthode de requête non autorisée.";
    header('Location: ../page/login.php');
    exit();
}
?>