<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $destDir = __DIR__ . "/uploads/images/";

    // create folder if not exists
    if (!file_exists($destDir)) {
        mkdir($destDir, 0777, true);
    }

    // check writable
    if (!is_writable($destDir)) {
        die("❌ Folder not writable: " . $destDir);
    }

    if (!isset($_FILES['file'])) {
        die("❌ No file received");
    }

    $fileName = $_FILES['file']['name'];
    $tempName = $_FILES['file']['tmp_name'];

    echo "Temp file: " . $tempName . "<br>";

    if (move_uploaded_file($tempName, $destDir . $fileName)) {
        echo "✅ Upload success";
    } else {
        echo "❌ Upload failed";
        print_r($_FILES);
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file" required>
    <button type="submit">Upload</button>
</form>