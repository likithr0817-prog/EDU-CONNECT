<?php
// ============================================================
// Application Layer: Config & Session Helper
// File: application/config.php
// ============================================================

session_start();

// Base paths
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', 'http://localhost/edu-connect');
define('UPLOAD_PATH', BASE_PATH . '/uploads/');

// Allowed file types
define('ALLOWED_PDF',   ['application/pdf']);
define('ALLOWED_VIDEO', ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska', 'video/webm']);
define('ALLOWED_IMAGE', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB

// Include DB
require_once BASE_PATH . '/data/db.php';

// Auto-create upload subdirectories if missing (fixes fresh installs)
foreach (['pdfs', 'videos', 'images'] as $_subdir) {
    $_path = BASE_PATH . '/uploads/' . $_subdir;
    if (!is_dir($_path)) {
        mkdir($_path, 0755, true);
    }
}

// ============================================================
// Session Helpers
// ============================================================

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/presentation/login.php');
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        header('Location: ' . BASE_URL . '/presentation/login.php');
        exit;
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function generateUniqueId($role) {
    $prefix = strtoupper(substr($role, 0, 3));
    $db = getDB();
    $result = $db->query("SELECT COUNT(*) as cnt FROM users WHERE role = '$role'");
    $row = $result->fetch_assoc();
    $num = str_pad($row['cnt'] + 1, 6, '0', STR_PAD_LEFT);
    return "$prefix-$num";
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function getUnreadNotifications($user_id, $role) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT COUNT(*) as cnt FROM notifications n
        WHERE (n.target_role = 'all' OR n.target_role = ?)
        AND n.notification_id NOT IN (
            SELECT notification_id FROM notification_reads WHERE user_id = ?
        )
    ");
    $stmt->bind_param("si", $role, $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row['cnt'];
}
?>
