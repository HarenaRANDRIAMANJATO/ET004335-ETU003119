<?php
function handleMultipleImageUpload($files) {
    $upload_dir = '../uploads/';
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    $max_size = 10 * 1024 * 1024; // 10MB
    $uploaded_paths = [];

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $found_valid_image = false;

    for ($i = 0; $i < count($files['name']); $i++) {
        $error = $files['error'][$i];

        if ($error === UPLOAD_ERR_OK) {
            $tmp_name = $files['tmp_name'][$i];
            $name = basename($files['name'][$i]);
            $type = mime_content_type($tmp_name);
            $size = filesize($tmp_name);

            if (!in_array($type, $allowed_types)) {
                continue; 
            }
            if ($size > $max_size) {
                continue; 
                
            }

            $unique_name = uniqid('img_') . '_' . $name;
            $target_path = $upload_dir . $unique_name;

            if (move_uploaded_file($tmp_name, $target_path)) {
                $uploaded_paths[] = $target_path;
                $found_valid_image = true;
            }
        }
    }

    if (!$found_valid_image) {
        $default_path = '../uploads/default.jpg';
        if (file_exists($default_path)) {
            $uploaded_paths[] = $default_path;
            return ['success' => true, 'paths' => $uploaded_paths];
        } else {
            return ['success' => false, 'error' => "Aucune image valide et image par défaut manquante."];
        }
    }

    return ['success' => true, 'paths' => $uploaded_paths];
}
