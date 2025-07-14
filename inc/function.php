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

function getListeObjets($conn) {
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
        ORDER BY o.nom_objet
    ";

    $result = mysqli_query($conn, $query);

    if (!$result) {
        error_log("Erreur dans getListeObjets: " . mysqli_error($conn));
        return false;
    }

    $objets = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $objets[] = $row;
    }

    mysqli_free_result($result);
    return $objets;
}

function filtrerParCategorie($id_categorie, $conn) {
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
        WHERE o.id_categorie = ? OR ? IS NULL
        ORDER BY o.nom_objet
    ";

    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        error_log("Erreur de préparation dans filtrerParCategorie: " . mysqli_error($conn));
        return false;
    }

    $id_categorie = empty($id_categorie) ? null : $id_categorie;
    mysqli_stmt_bind_param($stmt, "ii", $id_categorie, $id_categorie);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        error_log("Erreur dans filtrerParCategorie: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }

    $objets = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $objets[] = $row;
    }

    mysqli_stmt_close($stmt);
    return $objets;
}

function getCategories($conn) {
    $query = "SELECT id_categorie, nom_categorie FROM categorie_objet ORDER BY nom_categorie";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        error_log("Erreur dans getCategories: " . mysqli_error($conn));
        return false;
    }

    $categories = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }

    mysqli_free_result($result);
    return $categories;
}

function deconnecter() {
    session_start(); 
    error_log("Déconnexion de l'utilisateur: " . ($_SESSION['email'] ?? 'inconnu'));
    session_unset(); // Supprimer toutes les variables de session
    session_destroy(); // Détruire la session
    header('Location:../page/login.php'); // Rediriger vers login.php
    exit();
}
?>