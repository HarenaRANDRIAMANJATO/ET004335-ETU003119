<?php
require_once 'connexion.php'; 

function verifyLogin($email, $password, $conn) {
    if (!$conn) {
        error_log("Erreur: Connexion à la base de données non définie dans verifyLogin");
        return false;
    }
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
    return false; 
}

function inserer($nom, $date_de_naissance, $genre, $email, $ville, $mdp, $image_profil, $conn) {
    if (!$conn) {
        error_log("Erreur: Connexion à la base de données non définie dans inserer");
        return false;
    }
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
        $id_membre = mysqli_insert_id($conn); 
        mysqli_stmt_close($stmt);
        return [
            'id_membre' => $id_membre,
            'nom' => $nom,
            'email' => $email
        ];
    } else {
        error_log("Erreur lors de l'insertion: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }
}

function getListeObjets($conn) {
    return searchObjets($conn, null, '', false); // Default to no filters
}

function searchObjets($conn, $id_categorie, $nom_objet, $disponible) {
    if (!$conn) {
        error_log("Erreur: Connexion à la base de données non définie dans searchObjets");
        return false;
    }

    $query = "
        SELECT 
            o.id_objet,
            o.nom_objet,
            c.nom_categorie,
            m.nom AS proprietaire,
            e.date_retour,
            (SELECT nom_image FROM images_objet i WHERE i.id_objet = o.id_objet ORDER BY i.id_image LIMIT 1) AS nom_image
        FROM objet o
        LEFT JOIN categorie_objet c ON o.id_categorie = c.id_categorie
        LEFT JOIN membre m ON o.id_membre = m.id_membre
        LEFT JOIN emprunt e ON o.id_objet = e.id_objet AND e.date_retour IS NULL
        WHERE 1=1
    ";

    $params = [];
    $types = '';

    // Add category filter
    if ($id_categorie !== null) {
        $query .= " AND o.id_categorie = ?";
        $params[] = $id_categorie;
        $types .= 'i';
    }

    // Add name filter
    if (!empty($nom_objet)) {
        $query .= " AND o.nom_objet LIKE ?";
        $params[] = '%' . $nom_objet . '%';
        $types .= 's';
    }

    // Add availability filter
    if ($disponible) {
        $query .= " AND (e.date_retour IS NOT NULL OR e.id_emprunt IS NULL)";
    }

    $query .= " ORDER BY o.nom_objet";

    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        error_log("Erreur de préparation dans searchObjets: " . mysqli_error($conn));
        return false;
    }

    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        error_log("Erreur dans searchObjets: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }

    $objets = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $objets[] = $row;
    }

    mysqli_stmt_close($stmt);
    mysqli_free_result($result);
    return $objets;
}

function filtrerParCategorie($id_categorie, $conn) {
    return searchObjets($conn, $id_categorie, '', false); // Reuse searchObjets for category filter
}

function getCategories($conn) {
    return getCategorie($conn); // Alias for consistency
}

function getCategorie($conn) {
    if (!$conn) {
        error_log("Erreur: Connexion à la base de données non définie dans getCategorie");
        return false;
    }
    $query = "SELECT id_categorie, nom_categorie FROM categorie_objet ORDER BY nom_categorie";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        error_log("Erreur dans getCategorie: " . mysqli_error($conn));
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
    session_unset(); 
    session_destroy();
    header('Location:../page/login.php'); 
    exit();
}
?>