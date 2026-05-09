<?php
// ============================================================
// Application Layer: Admin Handler
// File: application/admin_actions.php
// ============================================================

require_once __DIR__ . '/config.php';
requireRole('admin');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'approve')         handleApproval('approved');
elseif ($action === 'reject')      handleApproval('rejected');
elseif ($action === 'delete_user') handleDeleteUser();
elseif ($action === 'delete_mat')  handleDeleteMaterial();
elseif ($action === 'notify')      handleNotification();

function handleApproval($decision) {
    $db      = getDB();
    $admin   = $_SESSION['user_id'];
    $user_id = intval($_GET['id'] ?? 0);
    $remarks = sanitize($_POST['remarks'] ?? '');

    $stmt = $db->prepare("UPDATE users SET is_approved = ? WHERE user_id = ?");
    $stmt->bind_param("si", $decision, $user_id);
    $stmt->execute();

    $stmt = $db->prepare("INSERT INTO approvals (user_id, admin_id, action, remarks) VALUES (?,?,?,?)");
    $stmt->bind_param("iiss", $user_id, $admin, $decision, $remarks);
    $stmt->execute();

    setFlash('success', 'User has been ' . $decision . '.');
    redirect(BASE_URL . '/presentation/admin/users.php');
}

function handleDeleteUser() {
    $db      = getDB();
    $user_id = intval($_GET['id'] ?? 0);

    $stmt = $db->prepare("DELETE FROM users WHERE user_id = ? AND role != 'admin'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    setFlash('success', 'User deleted.');
    redirect(BASE_URL . '/presentation/admin/users.php');
}

function handleDeleteMaterial() {
    $db          = getDB();
    $material_id = intval($_GET['id'] ?? 0);

    $stmt = $db->prepare("SELECT file_path FROM materials WHERE material_id = ?");
    $stmt->bind_param("i", $material_id);
    $stmt->execute();
    $mat = $stmt->get_result()->fetch_assoc();

    if ($mat && $mat['file_path'] && file_exists(BASE_PATH . '/' . $mat['file_path'])) {
        unlink(BASE_PATH . '/' . $mat['file_path']);
    }

    $stmt = $db->prepare("DELETE FROM materials WHERE material_id = ?");
    $stmt->bind_param("i", $material_id);
    $stmt->execute();

    setFlash('success', 'Material deleted.');
    redirect(BASE_URL . '/presentation/admin/materials.php');
}

function handleNotification() {
    $db     = getDB();
    $sender = $_SESSION['user_id'];
    $title  = sanitize($_POST['title'] ?? '');
    $msg    = sanitize($_POST['message'] ?? '');
    $target = sanitize($_POST['target_role'] ?? 'all');

    if (empty($title) || empty($msg)) {
        setFlash('error', 'Title and message are required.');
        redirect(BASE_URL . '/presentation/admin/notifications.php');
    }

    $stmt = $db->prepare("INSERT INTO notifications (sent_by, title, message, target_role) VALUES (?,?,?,?)");
    $stmt->bind_param("isss", $sender, $title, $msg, $target);
    $stmt->execute();

    setFlash('success', 'Notification sent!');
    redirect(BASE_URL . '/presentation/admin/notifications.php');
}
?>
