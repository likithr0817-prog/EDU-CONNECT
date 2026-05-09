<?php
require_once __DIR__ . '/config.php';
requireLogin();

$uid  = (int)$_SESSION['user_id'];
$db   = getDB();
$act  = $_REQUEST['action'] ?? '';

function ytId(string $url): ?string {
    preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_\-]{11})/', $url, $m);
    return $m[1] ?? null;
}

/* ── redirect actions (no JSON header yet) ── */
if ($act === 'post') {
    $url   = trim($_POST['youtube_url'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $desc  = substr(trim($_POST['description'] ?? ''), 0, 500);
    $yid   = ytId($url);
    if (!$yid || !$title) {
        setFlash('error', 'Please enter a valid YouTube URL and title.');
    } else {
        $s = $db->prepare("INSERT INTO study_videos (user_id,title,description,youtube_url,youtube_id) VALUES(?,?,?,?,?)");
        $s->bind_param('issss', $uid, $title, $desc, $url, $yid);
        $s->execute() ? setFlash('success','Video shared!') : setFlash('error','DB error: '.$db->error);
    }
    redirect(BASE_URL.'/presentation/videos.php');
}

if ($act === 'delete') {
    $vid = (int)($_GET['id'] ?? 0);
    $row = $db->query("SELECT user_id FROM study_videos WHERE video_id=$vid")->fetch_assoc();
    if ($row && ($row['user_id']==$uid || $_SESSION['role']==='admin')) {
        $db->query("DELETE FROM study_videos WHERE video_id=$vid");
        setFlash('success','Video removed.');
    } else {
        setFlash('error','Not found or permission denied.');
    }
    redirect(BASE_URL.'/presentation/videos.php');
}

/* ── AJAX JSON actions ── */
header('Content-Type: application/json; charset=utf-8');

if ($act === 'like') {
    $vid = (int)($_GET['id'] ?? 0);
    $has = $db->query("SELECT 1 FROM video_likes WHERE video_id=$vid AND user_id=$uid")->num_rows > 0;
    if ($has) { $db->query("DELETE FROM video_likes WHERE video_id=$vid AND user_id=$uid"); $liked=false; }
    else       { $db->query("INSERT INTO video_likes(video_id,user_id)VALUES($vid,$uid)");  $liked=true;  }
    $n = (int)$db->query("SELECT COUNT(*) FROM video_likes WHERE video_id=$vid")->fetch_row()[0];
    echo json_encode(['ok'=>true,'liked'=>$liked,'count'=>$n]); exit;
}

if ($act === 'save') {
    $vid = (int)($_GET['id'] ?? 0);
    $has = $db->query("SELECT 1 FROM video_saves WHERE video_id=$vid AND user_id=$uid")->num_rows > 0;
    if ($has) { $db->query("DELETE FROM video_saves WHERE video_id=$vid AND user_id=$uid"); $saved=false; }
    else       { $db->query("INSERT INTO video_saves(video_id,user_id)VALUES($vid,$uid)");  $saved=true;  }
    $n = (int)$db->query("SELECT COUNT(*) FROM video_saves WHERE video_id=$vid")->fetch_row()[0];
    echo json_encode(['ok'=>true,'saved'=>$saved,'count'=>$n]); exit;
}

if ($act === 'comment') {
    $vid  = (int)($_GET['id'] ?? 0);
    $text = htmlspecialchars(substr(trim($_POST['comment'] ?? ''), 0, 500), ENT_QUOTES, 'UTF-8');
    if (!$vid || $text === '') { echo json_encode(['ok'=>false,'error'=>'Empty']); exit; }
    $s = $db->prepare("INSERT INTO video_comments(video_id,user_id,comment)VALUES(?,?,?)");
    $s->bind_param('iis', $vid, $uid, $text);
    echo json_encode(['ok'=>$s->execute()]); exit;
}

if ($act === 'comments') {
    $vid  = (int)($_GET['id'] ?? 0);
    $rows = $db->query("
        SELECT vc.comment, DATE_FORMAT(vc.created_at,'%d %b') AS dt, u.name
        FROM video_comments vc JOIN users u ON vc.user_id=u.user_id
        WHERE vc.video_id=$vid ORDER BY vc.created_at ASC LIMIT 50
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['ok'=>true,'comments'=>$rows]); exit;
}

echo json_encode(['ok'=>false,'error'=>'Unknown action']);
