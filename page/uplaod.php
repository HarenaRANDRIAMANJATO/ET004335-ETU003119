<?php
function handleMultipleImageUpload($files) {
    $result = ['success' => false, 'error' => '', 'paths' => []];

    // Check if files are uploaded
    if (!isset($files) || !isset($files['name']) || empty($files['name'][0])) {
        $result['error'] = "Veuillez sélectionner au moins une image.";
        return $result;
    }

    $allowed = ['jpg', 'jpeg', 'png'];
    $max_size = 50 * 1024 * 1024; // 50MB

    $upload_dir = '../Uploads/';

    // Ensure upload directory exists and is writable
    if (!is_dir($upload_dir) || !is_writable($upload_dir)) {
        $result['error'] = "Le répertoire de téléchargement n'existe pas ou n'est pas accessible.";
        return $result;
    }

    // Process each file
    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] == UPLOAD_ERR_NO_FILE) {
            continue; // Skip empty file inputs
        }

        if ($files['error'][$i] != UPLOAD_ERR_OK) {
            $result['error'] = "Erreur lors du téléchargement du fichier " . $files['name'][$i] . ".";
            return $result;
        }

        // Validate file type
        $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $result['error'] = "Le fichier " . $files['name'][$i] . " n'est pas au format JPG, JPEG ou PNG.";
            return $result;
        }

        // Validate file size
        if ($files['size'][$i] > $max_size) {
            $result['error'] = "Le fichier " . $files['name'][$i] . " dépasse la taille maximale de 5 Mo.";
            return $result;
        }

        // Generate unique filename
        $new_filename = uniqid() . '.' . $ext;
        $upload_path = $upload_dir . $new_filename;

        // Move file
        if (!move_uploaded_file($files['tmp_name'][$i], $upload_path)) {
            $result['error'] = "Erreur lors du téléchargement du fichier " . $files['name'][$i] . ".";
            return $result;
        }

        $result['paths'][] = $upload_path;
    }

    if (empty($result['paths'])) {
        $result['error'] = "Aucune image valide n'a été téléchargée.";
        return $result;
    }

    $result['success'] = true;
    return $result;
}
?>