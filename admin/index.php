<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$pdo = get_db();

// --- Recherche & pagination ---
$q = clean($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = '';
$params = [];
if ($q !== '') {
    $where = "WHERE nom LIKE :q OR prenoms LIKE :q OR telephone LIKE :q OR registration_number LIKE :q OR email LIKE :q";
    $params[':q'] = '%' . $q . '%';
}

$total = $pdo->prepare("SELECT COUNT(*) AS c FROM inscriptions $where");
$total->execute($params);
$totalRows = (int)$total->fetch()['c'];
$totalPages = max(1, (int)ceil($totalRows / $perPage));

$stmt = $pdo->prepare("SELECT * FROM inscriptions $where ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

// --- Statistiques rapides ---
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) c FROM inscriptions")->fetch()['c'],
    'hommes' => $pdo->query("SELECT COUNT(*) c FROM inscriptions WHERE sexe_genre = 'Masculin'")->fetch()['c'],
    'femmes' => $pdo->query("SELECT COUNT(*) c FROM inscriptions WHERE sexe_genre = 'Féminin'")->fetch()['c'],
    'mineurs' => $pdo->query("SELECT COUNT(*) c FROM inscriptions WHERE age < 18")->fetch()['c'],
];

$qs = $q !== '' ? '&q=' . urlencode($q) : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Inscriptions — Admin Camp ESVS 2026</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>

<header class="admin-header">
  <div class="admin-header-inner">
    <div class="brand">Camp ESVS 2026 <span>· Admin</span></div>
    <div class="admin-user">
      Connecté : <strong><?= e($_SESSION['admin_username']) ?></strong>
      · <a href="logout.php">Se déconnecter</a>
    </div>
  </div>
</header>

<main class="admin-main">

  <div class="stats-grid">
    <div class="stat-card"><div class="stat-num"><?= (int)$stats['total'] ?></div><div class="stat-label">Inscriptions</div></div>
    <div class="stat-card"><div class="stat-num"><?= (int)$stats['hommes'] ?></div><div class="stat-label">Hommes</div></div>
    <div class="stat-card"><div class="stat-num"><?= (int)$stats['femmes'] ?></div><div class="stat-label">Femmes</div></div>
    <div class="stat-card"><div class="stat-num"><?= (int)$stats['mineurs'] ?></div><div class="stat-label">Mineurs (&lt;18 ans)</div></div>
  </div>

  <div class="toolbar">
    <form method="get" class="search-form">
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Rechercher nom, téléphone, email, n° d'inscription...">
      <button type="submit" class="btn btn-outline">Rechercher</button>
    </form>

    <div class="export-buttons">
      <a class="btn btn-outline" href="export_csv.php?<?= $q !== '' ? 'q=' . urlencode($q) : '' ?>">⬇ Excel (CSV)</a>
      <a class="btn btn-outline" href="export_pdf.php?<?= $q !== '' ? 'q=' . urlencode($q) : '' ?>">⬇ PDF</a>
      <a class="btn btn-outline" href="export_docx.php?<?= $q !== '' ? 'q=' . urlencode($q) : '' ?>">⬇ Word</a>
    </div>
  </div>

  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>N° Inscription</th>
          <th>Nom &amp; Prénoms</th>
          <th>Âge</th>
          <th>Ville</th>
          <th>Téléphone</th>
          <th>Date</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="7" class="empty">Aucune inscription trouvée.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td class="mono"><?= e($r['registration_number']) ?></td>
            <td><?= e($r['nom']) ?> <?= e($r['prenoms']) ?></td>
            <td><?= e((string)$r['age']) ?></td>
            <td><?= e($r['ville_commune']) ?></td>
            <td><?= e($r['telephone']) ?></td>
            <td><?= e(substr($r['created_at'], 0, 16)) ?></td>
            <td class="row-actions">
              <a href="view.php?id=<?= (int)$r['id'] ?>">Voir</a>
              <a href="delete.php?id=<?= (int)$r['id'] ?>" class="danger" onclick="return confirm('Supprimer cette inscription ?');">Suppr.</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($totalPages > 1): ?>
  <div class="pagination">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
      <a href="?page=<?= $p ?><?= $qs ?>" class="<?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>

</main>
</body>
</html>
