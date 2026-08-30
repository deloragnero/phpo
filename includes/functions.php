<?php
require_once __DIR__ . '/../config/db.php';

function e(?string $v): string
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

function generate_registration_number(PDO $pdo): string
{
    do {
        $num = 'ESVS2026-' . random_int(100000, 999999);
        $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM inscriptions WHERE registration_number = ?");
        $stmt->execute([$num]);
    } while ($stmt->fetch()['c'] > 0);
    return $num;
}

function calculate_age(string $dateNaissance): ?int
{
    try {
        $dob = new DateTime($dateNaissance);
        $now = new DateTime();
        return $now->diff($dob)->y;
    } catch (Exception $e) {
        return null;
    }
}

/** Nettoie une chaîne pour l'insertion en base (trim simple, la validation métier reste à faire côté appelant) */
function clean(?string $v): string
{
    return trim((string)($v ?? ''));
}

function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
