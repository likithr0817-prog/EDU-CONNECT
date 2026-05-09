<?php
require_once __DIR__ . '/config.php';
requireLogin();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
if ($action === 'update')       handleUpdate();
elseif ($action === 'upload_photo') handlePhotoUpload();
elseif ($action === 'remove_photo') handlePhotoRemove();

function handleUpdate() {
    $db      = getDB();
    $user_id = $_SESSION['user_id'];
    $role    = $_SESSION['role'];
    $name    = sanitize($_POST['name'] ?? '');
    $college = sanitize($_POST['college_name'] ?? '');
    $contact = sanitize($_POST['contact_number'] ?? '');
    $age     = intval($_POST['age'] ?? 0);
    if (empty($name)) { setFlash('error','Name is required.'); redirect(BASE_URL."/presentation/$role/profile.php"); }
    $stmt = $db->prepare("UPDATE users SET name=?,college_name=?,contact_number=?,age=? WHERE user_id=?");
    $stmt->bind_param("sssii",$name,$college,$contact,$age,$user_id); $stmt->execute();
    if ($role==='student') {
        $class=$db->real_escape_string(sanitize($_POST['class_name']??''));
        $db->query("UPDATE students SET class_name='$class' WHERE user_id=$user_id");
    } elseif ($role==='teacher') {
        $sub=$db->real_escape_string(sanitize($_POST['subject']??''));
        $db->query("UPDATE teachers SET subject='$sub' WHERE user_id=$user_id");
    }
    $_SESSION['name'] = $name;
    setFlash('success','Profile updated!');
    redirect(BASE_URL."/presentation/$role/profile.php");
}

function handlePhotoUpload() {
    $db      = getDB();
    $user_id = $_SESSION['user_id'];
    $role    = $_SESSION['role'];
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        $errs=[UPLOAD_ERR_INI_SIZE=>'File too large.',UPLOAD_ERR_NO_FILE=>'No file selected.',UPLOAD_ERR_PARTIAL=>'Partial upload.'];
        $code=$_FILES['photo']['error']??UPLOAD_ERR_NO_FILE;
        setFlash('error',$errs[$code]??"Upload failed (code $code)."); redirect(BASE_URL."/presentation/$role/profile.php");
    }
    $file=$_FILES['photo'];
    if ($file['size']>5*1024*1024) { setFlash('error','Max 5MB for photos.'); redirect(BASE_URL."/presentation/$role/profile.php"); }
    $allowed=['image/jpeg','image/png','image/gif','image/webp'];
    $ext=strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
    $extMap=['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','webp'=>'image/webp'];
    $mime=function_exists('finfo_open')?(function(){$fi=finfo_open(FILEINFO_MIME_TYPE);$m=finfo_file($fi,$_FILES['photo']['tmp_name']);finfo_close($fi);return $m;})():($extMap[$ext]??'application/octet-stream');
    if (!in_array($mime,$allowed)) { setFlash('error','Only JPG, PNG, GIF, WEBP allowed.'); redirect(BASE_URL."/presentation/$role/profile.php"); }
    // Delete old photo
    $old=$db->query("SELECT profile_photo FROM users WHERE user_id=$user_id")->fetch_assoc();
    if (!empty($old['profile_photo'])) { $op=BASE_PATH.'/'.$old['profile_photo']; if(file_exists($op)) unlink($op); }
    // Save new
    $photoDir=BASE_PATH.'/uploads/photos/';
    if (!is_dir($photoDir)) mkdir($photoDir,0755,true);
    $filename=$user_id.'_'.uniqid().'.'.$ext;
    if (!move_uploaded_file($file['tmp_name'],$photoDir.$filename)) { setFlash('error','Failed to save. Check folder permissions.'); redirect(BASE_URL."/presentation/$role/profile.php"); }
    $path='uploads/photos/'.$filename;
    $stmt=$db->prepare("UPDATE users SET profile_photo=? WHERE user_id=?");
    $stmt->bind_param("si",$path,$user_id); $stmt->execute();
    setFlash('success','Profile photo updated!');
    redirect(BASE_URL."/presentation/$role/profile.php");
}

function handlePhotoRemove() {
    $db      = getDB();
    $user_id = $_SESSION['user_id'];
    $role    = $_SESSION['role'];
    $old=$db->query("SELECT profile_photo FROM users WHERE user_id=$user_id")->fetch_assoc();
    if (!empty($old['profile_photo'])) { $op=BASE_PATH.'/'.$old['profile_photo']; if(file_exists($op)) unlink($op); }
    $db->query("UPDATE users SET profile_photo=NULL WHERE user_id=$user_id");
    setFlash('success','Photo removed.');
    redirect(BASE_URL."/presentation/$role/profile.php");
}
?>
