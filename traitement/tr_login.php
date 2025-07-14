<?php
session_start();
require_once '../inc/connexion.php'; 
require_once '../inc/function.php';    

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs.";
        header('Location: ../page/login.php');
        exit();
    }

    $conn = dbconnect();

    $user = verifyLogin($email, $password, $conn);

    if ($user) {
        $_SESSION['id_membre'] = $user['id_membre'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['email'] = $user['email'];
        
        header('Location:../page/liste.php');
        exit();
    } else {
        $_SESSION['error'] = "Email ou mot de passe incorrect.";
        header('Location: ../page/login.php');
        exit();
    }
} else {
    $_SESSION['error'] = "Méthode de requête non autorisée.";
    header('Location: ../page/login.php');
    exit();
}
?>