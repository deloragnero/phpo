<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$pdo = get_db();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM inscriptions WHERE id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch();

if (!$r) {
    header('Location: index.php');
    exit;
}

function row(string $label, ?string $value): string
{
    if ($value === null || $value === '') $value = '—';
    return '<div class="detail-row"><div class="detail-label">' . e($label) . '</div><div class="detail-value">' . nl2br(e($value)) . '</div></div>';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($r['nom'] . ' ' . $r['prenoms']) ?> — Camp ESVS 2026</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>

<header class="admin-header">
  <div class="admin-header-inner">
    <div class="brand">Camp ESVS 2026 <span>· Admin</span></div>
    <div class="admin-user"><a href="index.php">← Retour à la liste</a></div>
  </div>
</header>

<main class="admin-main">
  <div class="detail-card">
    <div class="detail-head">
      <?php if ($r['photo_participant']): ?>
        <img class="detail-photo" src="../public/uploads/<?= e($r['photo_participant']) ?>" alt="Photo">
      <?php endif; ?>
      <div>
        <h1><?= e($r['nom'] . ' ' . $r['prenoms']) ?></h1>
        <div class="reg-num-tag"><?= e($r['registration_number']) ?></div>
      </div>
    </div>

    <h3 class="section-title">Identité</h3>
    <?= row('Sexe / Genre', $r['sexe_genre']) ?>
    <?= row('Date de naissance', $r['date_naissance']) ?>
    <?= row('Âge', (string)$r['age']) ?>
    <?= row('Nationalité', $r['nationalite']) ?>
    <?= row('Ville / Commune', $r['ville_commune']) ?>

    <h3 class="section-title">Coordonnées</h3>
    <?= row('Téléphone', $r['telephone']) ?>
    <?= row('WhatsApp', $r['whatsapp']) ?>
    <?= row('Email', $r['email']) ?>
    <?= row('Organisation', $r['organisation']) ?>

    <h3 class="section-title">Participation</h3>
    <?= row('Attentes', $r['attentes']) ?>
    <?= row("Domaine d'intérêt", $r['domaine_interet']) ?>
    <?= row('Participation antérieure', $r['participation_anterieure']) ?>
    <?= row('Besoin d\'assistance', $r['besoin_assistance']) ?>

    <h3 class="section-title">Contact d'urgence</h3>
    <?= row('Nom et prénoms', $r['urgence_nom_prenoms']) ?>
    <?= row('Lien de parenté', $r['urgence_lien']) ?>
    <?= row('Téléphone', $r['urgence_telephone']) ?>

    <h3 class="section-title">Consentement &amp; paiement</h3>
    <?= row('Exactitude des informations', $r['consentement_exactitude'] ? 'Oui' : 'Non') ?>
    <?= row('Traitement des données', $r['consentement_donnees'] ? 'Oui' : 'Non') ?>
    <?= row('Règlement intérieur', $r['consentement_reglement'] ? 'Oui' : 'Non') ?>
    <?= row('Autorisation parentale', $r['autorisation_parentale'] ? 'Oui' : 'Non') ?>
    <?= row('N° / référence de paiement', $r['numero_paiement']) ?>
    <?= row('Inscrit le', $r['created_at']) ?>
  </div>
</main>
</body>
</html>
