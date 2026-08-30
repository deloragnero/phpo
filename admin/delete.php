<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$pdo = get_db();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT photo_participant FROM inscriptions WHERE id = ?");
$stmt->execute([$id]);
if ($row = $stmt->fetch()) {
    if ($row['photo_participant']) {
        $path = UPLOADS_DIR . '/' . $row['photo_participant'];
        if (is_file($path)) unlink($path);
    }
    $del = $pdo->prepare("DELETE FROM inscriptions WHERE id = ?");
    $del->execute([$id]);
}

header('Location: index.php');
exit;
