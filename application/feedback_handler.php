<?php
require_once __DIR__ . '/config.php';
requireLogin();

$uid = (int)$_SESSION['user_id'];
$db  = getDB();
$act = $_REQUEST['action'] ?? '';

if ($act === 'submit') {
    $rating  = max(1, min(5, (int)($_POST['rating'] ?? 0)));
    $comment = htmlspecialchars(substr(trim($_POST['comment'] ?? ''), 0, 1000), ENT_QUOTES, 'UTF-8');
    if ($rating < 1) {
        setFlash('error', 'Please select at least 1 star.');
        redirect(BASE_URL.'/presentation/feedback.php');
    }
    $exists = $db->query("SELECT feedback_id FROM feedback WHERE user_id=$uid")->num_rows > 0;
    if ($exists) {
        $s = $db->prepare("UPDATE feedback SET rating=?,comment=?,created_at=NOW() WHERE user_id=?");
        $s->bind_param('isi', $rating, $comment, $uid);
    } else {
        $s = $db->prepare("INSERT INTO feedback(user_id,rating,comment)VALUES(?,?,?)");
        $s->bind_param('iis', $uid, $rating, $comment);
    }
    $s->execute() ? setFlash('success','Feedback saved — thank you!') : setFlash('error','DB error: '.$db->error);
    redirect(BASE_URL.'/presentation/feedback.php');
}

if ($act === 'delete') {
    $db->query("DELETE FROM feedback WHERE user_id=$uid");
    setFlash('success','Your feedback has been removed.');
    redirect(BASE_URL.'/presentation/feedback.php');
}

redirect(BASE_URL.'/presentation/feedback.php');
