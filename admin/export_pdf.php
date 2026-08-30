<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/MiniPdf.php';

$pdo = get_db();
$q = clean($_GET['q'] ?? '');

$where = '';
$params = [];
if ($q !== '') {
    $where = "WHERE nom LIKE :q OR prenoms LIKE :q OR telephone LIKE :q OR registration_number LIKE :q OR email LIKE :q";
    $params[':q'] = '%' . $q . '%';
}

$stmt = $pdo->prepare("SELECT * FROM inscriptions $where ORDER BY created_at DESC");
$stmt->execute($params);
$rows = $stmt->fetchAll();

$pdf = new MiniPdf('L', 'A4'); // paysage pour plus de colonnes
$pageW = $pdf->getPageWidth();
$pageH = $pdf->getPageHeight();

$margin = 30;
$colX = [
    'num' => $margin,
    'nom' => $margin + 110,
    'age' => $margin + 250,
    'ville' => $margin + 285,
    'tel' => $margin + 400,
    'email' => $margin + 500,
    'date' => $margin + 660,
];

function pdf_header(MiniPdf $pdf, array $colX, int $count, string $q): float
{
    $y = 40;
    $pdf->text($colX['num'], $y, 'CAMP NATIONAL ESVS 2026 — Liste des inscriptions', 13, true);
    $y += 16;
    $sub = 'Export du ' . date('d/m/Y à H:i') . ' — ' . $count . ' inscription(s)';
    if ($q !== '') $sub .= ' — recherche: "' . $q . '"';
    $pdf->text($colX['num'], $y, $sub, 8.5);
    $y += 16;
    $pdf->text($colX['num'], $y, 'N° Inscription', 8, true);
    $pdf->text($colX['nom'], $y, 'Nom & Prénoms', 8, true);
    $pdf->text($colX['age'], $y, 'Âge', 8, true);
    $pdf->text($colX['ville'], $y, 'Ville', 8, true);
    $pdf->text($colX['tel'], $y, 'Téléphone', 8, true);
    $pdf->text($colX['email'], $y, 'Email', 8, true);
    $pdf->text($colX['date'], $y, 'Date', 8, true);
    $y += 6;
    $pdf->line($colX['num'], $y, 812, $y);
    return $y + 12;
}

$pdf->addPage();
$y = pdf_header($pdf, $colX, count($rows), $q);
$bottomLimit = $pageH - 30;

foreach ($rows as $r) {
    if ($y > $bottomLimit) {
        $pdf->addPage();
        $y = pdf_header($pdf, $colX, count($rows), $q);
    }
    $pdf->text($colX['num'], $y, $r['registration_number'], 8);
    $pdf->text($colX['nom'], $y, mb_substr($r['nom'] . ' ' . $r['prenoms'], 0, 24), 8);
    $pdf->text($colX['age'], $y, (string)($r['age'] ?? ''), 8);
    $pdf->text($colX['ville'], $y, mb_substr((string)$r['ville_commune'], 0, 20), 8);
    $pdf->text($colX['tel'], $y, (string)$r['telephone'], 8);
    $pdf->text($colX['email'], $y, mb_substr((string)$r['email'], 0, 28), 8);
    $pdf->text($colX['date'], $y, substr($r['created_at'], 0, 10), 8);
    $y += 15;
}

$pdf->output('inscriptions_esvs2026_' . date('Y-m-d_His') . '.pdf');
