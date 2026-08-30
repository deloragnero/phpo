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

$filename = 'inscriptions_esvs2026_' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
// BOM UTF-8 pour qu'Excel affiche correctement les accents
fwrite($out, "\xEF\xBB\xBF");

// Le séparateur ";" est utilisé pour une meilleure compatibilité avec Excel en locale française
fputcsv($out, [
    'N° Inscription', 'Nom', 'Prénoms', 'Sexe/Genre', 'Date de naissance', 'Âge',
    'Nationalité', 'Ville/Commune', 'Téléphone', 'WhatsApp', 'Email', 'Organisation',
    'Attentes', "Domaine d'intérêt", 'Participation antérieure', "Besoin d'assistance",
    "Contact urgence - Nom", "Contact urgence - Lien", "Contact urgence - Téléphone",
    'Consentement exactitude', 'Consentement données', 'Consentement règlement', 'Autorisation parentale',
    'N° paiement', "Date d'inscription",
], ';');

foreach ($rows as $r) {
    fputcsv($out, [
        $r['registration_number'], $r['nom'], $r['prenoms'], $r['sexe_genre'], $r['date_naissance'], $r['age'],
        $r['nationalite'], $r['ville_commune'], $r['telephone'], $r['whatsapp'], $r['email'], $r['organisation'],
        $r['attentes'], $r['domaine_interet'], $r['participation_anterieure'], $r['besoin_assistance'],
        $r['urgence_nom_prenoms'], $r['urgence_lien'], $r['urgence_telephone'],
        $r['consentement_exactitude'] ? 'Oui' : 'Non',
        $r['consentement_donnees'] ? 'Oui' : 'Non',
        $r['consentement_reglement'] ? 'Oui' : 'Non',
        $r['autorisation_parentale'] ? 'Oui' : 'Non',
        $r['numero_paiement'], $r['created_at'],
    ], ';');
}

fclose($out);
exit;
