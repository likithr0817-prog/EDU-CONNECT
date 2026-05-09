<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!isset($_FILES['file'])) {
        die("❌ No file uploaded");
    }

    $file = $_FILES['file'];
    $fileName = $file['name'];
    $tempName = $file['tmp_name'];
    $fileSize = $file['size'];

    // get extension
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // allowed types
    $imageTypes = ['jpg','jpeg','png','gif'];
    $videoTypes = ['mp4','avi','mov'];
    $pdfTypes = ['pdf'];

    // decide folder
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

if ($ext == 'pdf') {
    $folder = "pdfs";
} elseif (in_array($ext, ['jpg','jpeg','png','gif'])) {
    $folder = "images";
} elseif (in_array($ext, ['mp4','avi','mov'])) {
    $folder = "videos";
} else {
    die("❌ File type not allowed: " . $ext);
}
    // destination path
   $publicPath = "C:/xampp/htdocs/edu-connect/uploads/$folder/";

if (!file_exists($publicPath)) {
    mkdir($publicPath, 0777, true);
}

copy($destDir . $newName, $publicPath . $newName);


    // check writable
    if (!is_writable($destDir)) {
        die("❌ Folder not writable: " . $destDir);
    }

    // limit size (50MB)
    if ($fileSize > 50 * 1024 * 1024) {
        die("❌ File too large");
    }

    // unique filename
    $newName = time() . "_" . basename($fileName);

    // move file
    if (move_uploaded_file($tempName, $destDir . $newName)) {
        echo "✅ Upload success<br>";
        echo "✅ Upload success<br>";
echo "View file: http://localhost/edu-connect/uploads/$folder/$newName";
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