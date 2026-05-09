<?php
require_once __DIR__ . '/config.php';
requireLogin();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$uid    = $_SESSION['user_id'];
$role   = $_SESSION['role'];
$db     = getDB();

if ($action === 'send') {
    if (!in_array($role, ['admin','teacher'])) {
        setFlash('error','Permission denied.'); redirect(BASE_URL.'/presentation/student/notifications.php');
    }
    $title   = htmlspecialchars(trim($_POST['title'] ?? ''), ENT_QUOTES);
    $message = htmlspecialchars(trim($_POST['message'] ?? ''), ENT_QUOTES);
    $target  = in_array($_POST['target_role']??'', ['all','student','teacher']) ? $_POST['target_role'] : 'all';
    // Teachers can only send to students or all
    if ($role === 'teacher' && $target === 'teacher') $target = 'student';
    if (!$title || !$message) { setFlash('error','Title and message required.'); }
    else {
        $stmt = $db->prepare("INSERT INTO notifications (sent_by,sender_role,title,message,target_role) VALUES (?,?,?,?,?)");
        $stmt->bind_param("issss",$uid,$role,$title,$message,$target);
        $stmt->execute();
        setFlash('success','Notification sent!');
    }
    $back = ($role==='admin') ? BASE_URL.'/presentation/admin/notifications.php' : BASE_URL.'/presentation/teacher/send_notification.php';
    redirect($back);

} elseif ($action === 'delete') {
    $nid = intval($_GET['id'] ?? 0);
    $row = $db->query("SELECT sent_by FROM notifications WHERE notification_id=$nid")->fetch_assoc();
    if (!$row) { setFlash('error','Not found.'); }
    elseif ($row['sent_by'] != $uid && $role !== 'admin') { setFlash('error','Permission denied.'); }
    else { $db->query("DELETE FROM notifications WHERE notification_id=$nid"); setFlash('success','Deleted.'); }
    $back = ($role==='admin') ? BASE_URL.'/presentation/admin/notifications.php' : BASE_URL.'/presentation/teacher/send_notification.php';
    redirect($back);

} elseif ($action === 'mark_read') {
    // AJAX: mark all unread as read for current user
    header('Content-Type: application/json');
    $res = $db->query("
        SELECT n.notification_id FROM notifications n
        WHERE (n.target_role='all' OR n.target_role='$role')
        AND n.notification_id NOT IN (SELECT notification_id FROM notification_reads WHERE user_id=$uid)
    ");
    while ($row = $res->fetch_assoc()) {
        $nid = $row['notification_id'];
        $db->query("INSERT IGNORE INTO notification_reads (notification_id,user_id) VALUES ($nid,$uid)");
    }
    echo json_encode(['ok'=>true]);
    exit;

} elseif ($action === 'mark_one') {
    $nid = intval($_GET['id'] ?? 0);
    $db->query("INSERT IGNORE INTO notification_reads (notification_id,user_id) VALUES ($nid,$uid)");
    header('Content-Type: application/json'); echo json_encode(['ok'=>true]); exit;
}
?>
