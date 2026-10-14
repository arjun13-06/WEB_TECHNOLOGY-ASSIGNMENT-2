<?php
session_start();
require __DIR__ . '/db.php';
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
$message = '';
function h($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function uploadFile($input, $uploadDir) {
    if (!isset($_FILES[$input]) || $_FILES[$input]['error'] === UPLOAD_ERR_NO_FILE) return null;
    $f = $_FILES[$input];
    if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Upload failed.');
    if ($f['size'] > 3 * 1024 * 1024) throw new RuntimeException('Each file must be 3 MB or smaller.');
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg'=>'image/jpeg', 'jpeg'=>'image/jpeg', 'png'=>'image/png', 'pdf'=>'application/pdf'];
    if (!isset($allowed[$ext])) throw new RuntimeException('Only JPG, PNG and PDF files are allowed.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if ($mime !== $allowed[$ext]) throw new RuntimeException('File content does not match its extension.');
    $stored = bin2hex(random_bytes(16)) . '.' . $ext; // unique name prevents overwriting
    if (!move_uploaded_file($f['tmp_name'], $uploadDir . $stored)) throw new RuntimeException('Could not store uploaded file.');
    return ['original_name'=>basename($f['name']), 'stored_name'=>$stored, 'file_path'=>'uploads/'.$stored, 'file_type'=>$mime];
}
function saveMetadata($pdo, $studentId, $file) {
    $stmt = $pdo->prepare('INSERT INTO student_files(student_id,original_name,stored_name,file_path,file_type) VALUES(?,?,?,?,?)');
    $stmt->execute([$studentId,$file['original_name'],$file['stored_name'],$file['file_path'],$file['file_type']]);
}
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        if ($action === 'save') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? ''); $roll = trim($_POST['roll_number'] ?? '');
            $email = trim($_POST['email'] ?? ''); $course = trim($_POST['course'] ?? '');
            if ($name === '' || $roll === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $course === '') throw new RuntimeException('Complete all fields and enter a valid email.');
            $pdo->beginTransaction();
            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE students SET name=?, roll_number=?, email=?, course=? WHERE id=?');
                $stmt->execute([$name,$roll,$email,$course,$id]); $studentId = $id;
            } else {
                $stmt = $pdo->prepare('INSERT INTO students(name,roll_number,email,course) VALUES(?,?,?,?)');
                $stmt->execute([$name,$roll,$email,$course]); $studentId = (int)$pdo->lastInsertId();
            }
            foreach (['profile_image','document'] as $field) { $file = uploadFile($field, $uploadDir); if ($file) saveMetadata($pdo,$studentId,$file); }
            $pdo->commit(); $message = 'Student record saved successfully.';
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('SELECT stored_name FROM student_files WHERE student_id=?'); $stmt->execute([$id]);
            $storedFiles = $stmt->fetchAll();
            $stmt = $pdo->prepare('DELETE FROM students WHERE id=?'); $stmt->execute([$id]);
            foreach ($storedFiles as $file) { $path=$uploadDir.$file['stored_name']; if (is_file($path)) unlink($path); }
            $message = 'Student deleted.';
        }
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $message = 'Error: ' . $e->getMessage();
}
$edit = null;
if (isset($_GET['edit'])) { $stmt=$pdo->prepare('SELECT * FROM students WHERE id=?'); $stmt->execute([(int)$_GET['edit']]); $edit=$stmt->fetch(); }
$students = $pdo->query('SELECT * FROM students ORDER BY id DESC')->fetchAll();
$filesByStudent = [];
foreach ($pdo->query('SELECT * FROM student_files ORDER BY upload_date DESC')->fetchAll() as $f) $filesByStudent[$f['student_id']][] = $f;
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Student Admin</title><link rel="stylesheet" href="style.css"></head><body>
<header><h1>Student Management Portal</h1><p>PHP · MySQL · File Uploads</p></header><main>
<?php if ($message): ?><p class="message"><?=h($message)?></p><?php endif; ?>
<section class="panel"><h2><?=$edit ? 'Edit Student' : 'Add Student'?></h2>
<form method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=h($edit['id'] ?? 0)?>">
<div class="grid"><label>Full name<input name="name" required maxlength="100" value="<?=h($edit['name'] ?? '')?>"></label><label>Roll number<input name="roll_number" required maxlength="30" value="<?=h($edit['roll_number'] ?? '')?>"></label><label>Email<input name="email" type="email" required value="<?=h($edit['email'] ?? '')?>"></label><label>Course<input name="course" required value="<?=h($edit['course'] ?? '')?>"></label>
<label>Profile image (JPG/PNG, max 3 MB)<input type="file" name="profile_image" accept=".jpg,.jpeg,.png, image/jpeg,image/png"></label><label>Document (PDF/JPG/PNG, max 3 MB)<input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"></label></div>
<button type="submit">Save Student</button><?php if($edit): ?> <a class="cancel" href="index.php">Cancel edit</a><?php endif; ?></form></section>
<section class="panel"><h2>Student Records</h2><div class="table-wrap"><table><thead><tr><th>Name / Roll</th><th>Email</th><th>Course</th><th>Files</th><th>Actions</th></tr></thead><tbody>
<?php foreach($students as $s): ?><tr><td><?=h($s['name'])?><br><small><?=h($s['roll_number'])?></small></td><td><?=h($s['email'])?></td><td><?=h($s['course'])?></td><td><?php foreach($filesByStudent[$s['id']] ?? [] as $f): ?><a href="file.php?id=<?=h($f['id'])?>" target="_blank" rel="noopener"><?=h($f['original_name'])?></a><br><small><?=h($f['file_type'])?> · <?=h($f['upload_date'])?></small><br><?php endforeach; ?></td><td><a href="?edit=<?=h($s['id'])?>">Edit</a><form method="post" class="inline" onsubmit="return confirm('Delete this student and their file records?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=h($s['id'])?>"><button class="danger" type="submit">Delete</button></form></td></tr><?php endforeach; ?>
<?php if(!$students): ?><tr><td colspan="5">No student records yet. Add one above.</td></tr><?php endif; ?></tbody></table></div></section></main></body></html>
