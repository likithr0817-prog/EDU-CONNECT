<?php
// ============================================================
// Secure File Server — uploads/serve.php
// Serves uploaded files (PDF, video, image) with correct headers
// Only authenticated users can access files
// ============================================================
require_once __DIR__ . '/../application/config.php';
requireLogin(); // must be logged in to view ANY file

$filePath = $_GET['f'] ?? '';
$download = !empty($_GET['download']) && $_GET['download'] === '1';

// Sanitize: strip any path traversal attempts
$filePath = ltrim(str_replace(['..', '\\', "\0"], '', $filePath), '/');

// Must start with uploads/ and be in allowed subdirs
if (!preg_match('#^uploads/(pdfs|videos|images|photos)/#', $filePath)) {
    http_response_code(403);
    die('Access denied.');
}

$fullPath = BASE_PATH . '/' . $filePath;

if (!file_exists($fullPath) || !is_file($fullPath)) {
    http_response_code(404);
    die('File not found.');
}

// Detect MIME
$ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
$mimeMap = [
    'pdf'  => 'application/pdf',
    'mp4'  => 'video/mp4',
    'avi'  => 'video/x-msvideo',
    'mov'  => 'video/quicktime',
    'mkv'  => 'video/x-matroska',
    'webm' => 'video/webm',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'webp' => 'image/webp',
];
$mime = $mimeMap[$ext] ?? 'application/octet-stream';

// Support range requests for video streaming
$fileSize = filesize($fullPath);
$start    = 0;
$end      = $fileSize - 1;

if (isset($_SERVER['HTTP_RANGE'])) {
    preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $m);
    $start = intval($m[1]);
    $end   = isset($m[2]) && $m[2] !== '' ? intval($m[2]) : $fileSize - 1;
    $end   = min($end, $fileSize - 1);
    header('HTTP/1.1 206 Partial Content');
    header("Content-Range: bytes $start-$end/$fileSize");
    header('Accept-Ranges: bytes');
    header('Content-Length: ' . ($end - $start + 1));
} else {
    header('Accept-Ranges: bytes');
    header('Content-Length: ' . $fileSize);
}

header('Content-Type: ' . $mime);

if ($download) {
    $basename = basename($fullPath);
    header('Content-Disposition: attachment; filename="' . $basename . '"');
} else {
    header('Content-Disposition: inline');
}

// Cache for 1 hour
header('Cache-Control: private, max-age=3600');

// Stream the file
$fp = fopen($fullPath, 'rb');
fseek($fp, $start);
$remaining = $end - $start + 1;
while ($remaining > 0 && !feof($fp)) {
    $chunk = fread($fp, min(8192, $remaining));
    echo $chunk;
    $remaining -= strlen($chunk);
    flush();
}
fclose($fp);
exit;
