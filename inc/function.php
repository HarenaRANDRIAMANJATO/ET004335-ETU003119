<?php
require_once 'connexion.php'; 

function verifyLogin($email, $password, $conn) {
    // Préparer la requête pour éviter les injections SQL
    $query = "SELECT id_membre, nom, email, mdp FROM membre WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        error_log("Erreur de préparation de la requête: " . mysqli_error($conn));
        return false; // Erreur de préparation de la requête
    }

    // Lier le paramètre email
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    // Vérifier si un utilisateur existe avec cet email
    if ($row = mysqli_fetch_assoc($result)) {
        // Vérifier le mot de passe (stocké en clair dans la colonne mdp)
        if ($password === $row['mdp']) {
            mysqli_stmt_close($stmt);
            return [
                'id_membre' => $row['id_membre'],
                'nom' => $row['nom'],
                'email' => $row['email']
            ];
        } else {
            error_log("Mot de passe incorrect pour l'email: $email");
        }
    } else {
        error_log("Aucun utilisateur trouvé pour l'email: $email");
    }
    
    mysqli_stmt_close($stmt);
    return false; // Échec de l'authentification
}
?>