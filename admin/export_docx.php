<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

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

function wx(string $s): string
{
    return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function w_cell(string $text, bool $bold = false, int $widthTwips = 1400): string
{
    $b = $bold ? '<w:b/>' : '';
    $shading = $bold ? '<w:shd w:val="clear" w:color="auto" w:fill="EDE6FB"/>' : '';
    return '<w:tc><w:tcPr><w:tcW w:w="' . $widthTwips . '" w:type="dxa"/>' . $shading . '</w:tcPr>'
        . '<w:p><w:pPr><w:spacing w:after="0"/></w:pPr><w:r><w:rPr>' . $b . '<w:sz w:val="16"/></w:rPr><w:t xml:space="preserve">' . wx($text) . '</w:t></w:r></w:p></w:tc>';
}

$widths = [1600, 2400, 700, 1700, 1500, 2400, 1400];
$headers = ['N° Inscription', 'Nom & Prénoms', 'Âge', 'Ville', 'Téléphone', 'Email', 'Date'];

$headerRow = '<w:tr>';
foreach ($headers as $i => $h) {
    $headerRow .= w_cell($h, true, $widths[$i]);
}
$headerRow .= '</w:tr>';

$bodyRows = '';
foreach ($rows as $r) {
    $bodyRows .= '<w:tr>';
    $bodyRows .= w_cell($r['registration_number'], false, $widths[0]);
    $bodyRows .= w_cell($r['nom'] . ' ' . $r['prenoms'], false, $widths[1]);
    $bodyRows .= w_cell((string)($r['age'] ?? ''), false, $widths[2]);
    $bodyRows .= w_cell((string)$r['ville_commune'], false, $widths[3]);
    $bodyRows .= w_cell((string)$r['telephone'], false, $widths[4]);
    $bodyRows .= w_cell((string)$r['email'], false, $widths[5]);
    $bodyRows .= w_cell(substr($r['created_at'], 0, 16), false, $widths[6]);
    $bodyRows .= '</w:tr>';
}

$title = 'CAMP NATIONAL ESVS 2026 — Liste des inscriptions';
$subtitle = 'Export du ' . date('d/m/Y à H:i') . ' — ' . count($rows) . ' inscription(s)' . ($q !== '' ? ' — recherche: "' . $q . '"' : '');

$documentXml = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
<w:body>
<w:p><w:pPr><w:spacing w:after="80"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="28"/><w:color w:val="5B21B6"/></w:rPr><w:t xml:space="preserve">{$title}</w:t></w:r></w:p>
<w:p><w:pPr><w:spacing w:after="200"/></w:pPr><w:r><w:rPr><w:sz w:val="18"/><w:color w:val="756F8A"/></w:rPr><w:t xml:space="preserve">{$subtitle}</w:t></w:r></w:p>
<w:tbl>
<w:tblPr><w:tblW w:w="0" w:type="auto"/><w:tblBorders>
<w:top w:val="single" w:sz="4" w:color="D9D2F0"/><w:left w:val="single" w:sz="4" w:color="D9D2F0"/>
<w:bottom w:val="single" w:sz="4" w:color="D9D2F0"/><w:right w:val="single" w:sz="4" w:color="D9D2F0"/>
<w:insideH w:val="single" w:sz="4" w:color="D9D2F0"/><w:insideV w:val="single" w:sz="4" w:color="D9D2F0"/>
</w:tblBorders></w:tblPr>
{$headerRow}
{$bodyRows}
</w:tbl>
<w:sectPr>
<w:pgSz w:w="16838" w:h="11906" w:orient="landscape"/>
<w:pgMar w:top="720" w:right="720" w:bottom="720" w:left="720" w:header="0" w:footer="0" w:gutter="0"/>
</w:sectPr>
</w:body>
</w:document>
XML;

$contentTypes = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
</Types>
XML;

$rels = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>
XML;

$tmpFile = tempnam(sys_get_temp_dir(), 'docx');
$zip = new ZipArchive();
$zip->open($tmpFile, ZipArchive::OVERWRITE);
$zip->addFromString('[Content_Types].xml', $contentTypes);
$zip->addFromString('_rels/.rels', $rels);
$zip->addFromString('word/document.xml', $documentXml);
$zip->close();

$filename = 'inscriptions_esvs2026_' . date('Y-m-d_His') . '.docx';
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($tmpFile));
readfile($tmpFile);
unlink($tmpFile);
exit;
