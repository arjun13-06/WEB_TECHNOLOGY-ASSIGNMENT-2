<?php
require __DIR__ . '/db.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('Invalid file id.'); }
$stmt = $pdo->prepare('SELECT stored_name,file_type,original_name FROM student_files WHERE id=?');
$stmt->execute([$id]); $file = $stmt->fetch();
if (!$file) { http_response_code(404); exit('File not found.'); }
$path = __DIR__ . '/uploads/' . basename($file['stored_name']);
if (!is_file($path)) { http_response_code(404); exit('Stored file is missing.'); }
header('X-Content-Type-Options: nosniff');
header('Content-Type: ' . $file['file_type']);
header('Content-Disposition: inline; filename="' . str_replace('"','',basename($file['original_name'])) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
