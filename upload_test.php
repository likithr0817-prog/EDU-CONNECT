<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $destDir = "C:/xampp/htdocs/edu-connect/uploads/images/";

    // FORCE CREATE with permissions
    if (!file_exists($destDir)) {
        mkdir($destDir, 0777, true);
    }

    clearstatcache();

    if (!is_writable($destDir)) {
        chmod($destDir, 0777); // force permission
    }

    if (!is_writable($destDir)) {
        die("❌ STILL not writable: " . $destDir);
    }

    $fileName = $_FILES['file']['name'];
    $tempName = $_FILES['file']['tmp_name'];

    if (move_uploaded_file($tempName, $destDir . $fileName)) {
        echo "✅ Upload success";
    } else {
        echo "❌ Upload failed";
        print_r($_FILES);
    }
}
?>