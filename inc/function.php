<?php
require_once 'connexion.php'; 

function verifyLogin($email, $password, $conn) {
    $query = "SELECT id_membre, nom, email, mdp FROM membre WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        error_log("Erreur: " . mysqli_error($conn));
        return false; 
    }

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
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

function inserer($nom, $date_de_naissance, $genre, $email, $ville, $mdp, $image_profil, $conn) {
    $query = "INSERT INTO membre (nom, date_de_naissance, genre, email, ville, mdp, image_profil) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        error_log("Erreur de préparation de la requête: " . mysqli_error($conn));
        return false;
    }

    $date_de_naissance = empty($date_de_naissance) ? null : $date_de_naissance;
    $genre = empty($genre) ? null : $genre;
    $ville = empty($ville) ? null : $ville;
    $image_profil = empty($image_profil) ? null : $image_profil;

    mysqli_stmt_bind_param($stmt, "sssssss", $nom, $date_de_naissance, $genre, $email, $ville, $mdp, $image_profil);
    
    if (mysqli_stmt_execute($stmt)) {
        $id_membre = mysqli_insert_id($conn); // Récupérer l'ID du membre inséré
        mysqli_stmt_close($stmt);
        return [
            'id_membre' => $id_membre,
            'nom' => $nom,
            'email' => $email
        ];
    } else {
        error_log("Erreur lors de l'insertion: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false; // Échec de l'insertion
    }
}
?>