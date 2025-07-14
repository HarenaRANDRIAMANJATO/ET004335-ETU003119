<?php
function handleImageUpload($file) {
    $result = ['success' => false, 'error' => '', 'path' => ''];

    // Check if file is uploaded
    if (!isset($file) || $file['error'] == UPLOAD_ERR_NO_FILE) {
        $result['error'] = "Veuillez sélectionner une image.";
        return $result;
    }

    // Validate file type
    $allowed = ['jpg', 'jpeg', 'png'];
    $filename = $file['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        $result['error'] = "Seuls les fichiers JPG, JPEG et PNG sont autorisés.";
        return $result;
    }
    
    // Validate file size (5MB max)
    $max_size = 5 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        $result['error'] = "L'image ne doit pas dépasser 5 Mo.";
        return $result;
    }

    // Generate unique filename and move file
    $new_filename = uniqid() . '.' . $ext;
    $upload_path = '../image/' . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        $result['success'] = true;
        $result['path'] = $upload_path;
    } else {
        $result['error'] = "Erreur lors du téléchargement de l'image.";
    }

    return $result;
}
?>