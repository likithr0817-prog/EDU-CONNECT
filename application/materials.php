<?php
// ============================================================
// Application Layer: Materials Handler - FIXED
// File: application/materials.php
// ============================================================

require_once __DIR__ . '/config.php';
requireLogin();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'upload') handleUpload();
elseif ($action === 'delete') handleDelete();

function handleUpload() {
    $db          = getDB();
    $user_id     = $_SESSION['user_id'];
    $role        = $_SESSION['role'];
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $file_type   = trim($_POST['file_type'] ?? '');

    if (empty($title) || empty($file_type)) {
        setFlash('error', 'Title and file type are required.');
        goBack($role);
    }

    $file_path     = null;
    $external_link = null;

    if ($file_type === 'link') {
        $external_link = trim($_POST['external_link'] ?? '');
        if (empty($external_link)) {
            setFlash('error', 'External link cannot be empty.');
            goBack($role);
        }
        if (!filter_var($external_link, FILTER_VALIDATE_URL)) {
            setFlash('error', 'Please enter a valid URL starting with http:// or https://');
            goBack($role);
        }
    } else {
        // File upload
        if (!isset($_FILES['file'])) {
            setFlash('error', 'No file received. Ensure the form uses enctype="multipart/form-data".');
            goBack($role);
        }

        $errCode = $_FILES['file']['error'];
        if ($errCode !== UPLOAD_ERR_OK) {
            $errMessages = [
                UPLOAD_ERR_INI_SIZE   => 'File too large. Increase upload_max_filesize in php.ini.',
                UPLOAD_ERR_FORM_SIZE  => 'File exceeds form size limit.',
                UPLOAD_ERR_PARTIAL    => 'File only partially uploaded.',
                UPLOAD_ERR_NO_FILE    => 'No file was selected.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temp folder on server.',
                UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk.',
                UPLOAD_ERR_EXTENSION  => 'Upload blocked by PHP extension.',
            ];
            setFlash('error', $errMessages[$errCode] ?? "Upload error code: $errCode");
            goBack($role);
        }

        $file = $_FILES['file'];

        if ($file['size'] > MAX_FILE_SIZE) {
            setFlash('error', 'File exceeds 50MB limit.');
            goBack($role);
        }

        // MIME detection with finfo fallback to extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (function_exists('finfo_open')) {
            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        } else {
            $extMap   = ['pdf'=>'application/pdf','mp4'=>'video/mp4','avi'=>'video/x-msvideo',
                         'mov'=>'video/quicktime','mkv'=>'video/x-matroska','webm'=>'video/webm',
                         'jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png',
                         'gif'=>'image/gif','webp'=>'image/webp'];
            $mimeType = $extMap[$ext] ?? 'application/octet-stream';
        }

        switch ($file_type) {
            case 'pdf':   $allowed = ALLOWED_PDF;   break;
            case 'video': $allowed = ALLOWED_VIDEO; break;
            case 'image': $allowed = ALLOWED_IMAGE; break;
            default:      $allowed = [];
        }

        if (!in_array($mimeType, $allowed)) {
            setFlash('error', "Wrong file type \"$mimeType\" for category \"$file_type\".");
            goBack($role);
        }

        $subdir   = $file_type . 's';
        $destDir  = BASE_PATH . '/uploads/' . $subdir . '/';
        $filename = $user_id . '_' . uniqid() . '.' . $ext;
        $dest     = $destDir . $filename;

        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        if (!is_writable($destDir)) {
            setFlash('error', 'Upload folder is not writable.');
            goBack($role);
        }

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            setFlash('error', 'Failed to save uploaded file.');
            goBack($role);
        }

        $file_path = 'uploads/' . $subdir . '/' . $filename;
    }

    $title       = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');

    $stmt = $db->prepare("INSERT INTO materials (user_id, title, description, file_type, file_path, external_link) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("isssss", $user_id, $title, $description, $file_type, $file_path, $external_link);

    if (!$stmt->execute()) {
        setFlash('error', 'Database error: ' . $stmt->error);
        goBack($role);
    }

    setFlash('success', 'Material uploaded successfully!');
    goBack($role);
}

function handleDelete() {
    $db          = getDB();
    $user_id     = $_SESSION['user_id'];
    $role        = $_SESSION['role'];
    $material_id = intval($_GET['id'] ?? 0);

    $stmt = $db->prepare("SELECT * FROM materials WHERE material_id = ?");
    $stmt->bind_param("i", $material_id);
    $stmt->execute();
    $mat = $stmt->get_result()->fetch_assoc();

    if (!$mat) { setFlash('error', 'Material not found.'); goBack($role); }

    if ($mat['user_id'] != $user_id && $role !== 'admin') {
        setFlash('error', 'Permission denied.');
        goBack($role);
    }

    if (!empty($mat['file_path'])) {
        $full = BASE_PATH . '/' . $mat['file_path'];
        if (file_exists($full)) unlink($full);
    }

    $stmt = $db->prepare("DELETE FROM materials WHERE material_id = ?");
    $stmt->bind_param("i", $material_id);
    $stmt->execute();

    setFlash('success', 'Material deleted.');
    goBack($role);
}

function goBack($role) {
    $map = ['teacher' => 'teacher/materials.php', 'admin' => 'admin/materials.php', 'student' => 'student/materials.php'];
    redirect(BASE_URL . '/presentation/' . ($map[$role] ?? 'student/materials.php'));
}
?>
