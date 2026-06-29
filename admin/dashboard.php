<?php
// =============================================
// Admin — Panou principal (mesaje primite)
// =============================================
session_start();

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$db = getDB();

// Statistici
$total   = (int) $db->query('SELECT COUNT(*) FROM contacts')->fetchColumn();
$unread  = (int) $db->query('SELECT COUNT(*) FROM contacts WHERE is_read = 0')->fetchColumn();
$thisWeek = (int) $db->query(
    "SELECT COUNT(*) FROM contacts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
)->fetchColumn();

// Filtrare
$filter = $_GET['filter'] ?? 'all';
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset  = ($page - 1) * $perPage;

$where = match($filter) {
    'unread' => 'WHERE is_read = 0',
    'read'   => 'WHERE is_read = 1',
    default  => '',
};

$countFiltered = (int) $db->query("SELECT COUNT(*) FROM contacts $where")->fetchColumn();
$totalPages    = max(1, (int) ceil($countFiltered / $perPage));

$stmt = $db->prepare(
    "SELECT id, name, email, phone, message, is_read, created_at
     FROM contacts $where
     ORDER BY created_at DESC
     LIMIT $perPage OFFSET $offset"
);
$stmt->execute();
$contacts = $stmt->fetchAll();

$csrf = $_SESSION['csrf_token'] ?? '';
$username = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PACONI Admin — Messages</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- ===== HEADER ===== -->
  <header class="admin-header">
    <div class="admin-header__inner">
      <a href="dashboard.php" class="admin-logo">PACONI</a>
      <div class="admin-header__right">
        <span class="admin-user">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
          <?= $username ?>
        </span>
        <a href="logout.php" class="btn-logout">Déconnexion</a>
      </div>
    </div>
  </header>

  <!-- ===== MAIN ===== -->
  <main class="admin-main">
    <div class="admin-container">

      <!-- Page title -->
      <div class="page-header">
        <h1 class="page-title">
          Messages reçus
          <?php if ($unread > 0): ?>
            <span class="badge badge-unread"><?= $unread ?> non lu<?= $unread > 1 ? 's' : '' ?></span>
          <?php endif; ?>
        </h1>
        <p class="page-sub">Formulaire de contact — Landing Page Paconi</p>
      </div>

      <!-- Stats -->
      <div class="stats-grid">
        <div class="stat-card">
          <span class="stat-icon">📬</span>
          <div>
            <p class="stat-value"><?= $total ?></p>
            <p class="stat-label">Total messages</p>
          </div>
        </div>
        <div class="stat-card stat-card--accent">
          <span class="stat-icon">🔴</span>
          <div>
            <p class="stat-value"><?= $unread ?></p>
            <p class="stat-label">Non lus</p>
          </div>
        </div>
        <div class="stat-card">
          <span class="stat-icon">📅</span>
          <div>
            <p class="stat-value"><?= $thisWeek ?></p>
            <p class="stat-label">Cette semaine</p>
          </div>
        </div>
      </div>

      <!-- Table card -->
      <div class="table-card">

        <!-- Filters -->
        <div class="table-toolbar">
          <div class="filter-tabs">
            <a href="?filter=all"    class="filter-tab <?= $filter === 'all'    ? 'active' : '' ?>">Tous (<?= $total ?>)</a>
            <a href="?filter=unread" class="filter-tab <?= $filter === 'unread' ? 'active' : '' ?>">Non lus (<?= $unread ?>)</a>
            <a href="?filter=read"   class="filter-tab <?= $filter === 'read'   ? 'active' : '' ?>">Lus (<?= $total - $unread ?>)</a>
          </div>
        </div>

        <!-- Table -->
        <?php if (empty($contacts)): ?>
          <div class="empty-state">
            <p>Aucun message trouvé.</p>
          </div>
        <?php else: ?>
          <div class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th style="width:50px">#</th>
                  <th>Nom</th>
                  <th>Email</th>
                  <th>Téléphone</th>
                  <th>Aperçu</th>
                  <th>Date</th>
                  <th style="width:90px">Statut</th>
                  <th style="width:110px">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($contacts as $c): ?>
                  <?php
                    $preview  = mb_strimwidth(strip_tags($c['message']), 0, 55, '…');
                    $date     = date('d/m/Y H:i', strtotime($c['created_at']));
                    $isUnread = !$c['is_read'];
                    $rowClass = $isUnread ? 'row-unread' : '';
                  ?>
                  <tr class="<?= $rowClass ?>">
                    <td class="cell-id"><?= $c['id'] ?></td>
                    <td class="cell-name">
                      <?php if ($isUnread): ?><span class="dot"></span><?php endif; ?>
                      <?= htmlspecialchars($c['name']) ?>
                    </td>
                    <td><a href="mailto:<?= htmlspecialchars($c['email']) ?>" class="link-email"><?= htmlspecialchars($c['email']) ?></a></td>
                    <td><?= htmlspecialchars($c['phone'] ?? '—') ?></td>
                    <td class="cell-preview"><?= htmlspecialchars($preview) ?></td>
                    <td class="cell-date"><?= $date ?></td>
                    <td>
                      <?php if ($isUnread): ?>
                        <span class="status status-new">Nouveau</span>
                      <?php else: ?>
                        <span class="status status-read">Lu</span>
                      <?php endif; ?>
                    </td>
                    <td class="cell-actions">
                      <a href="view.php?id=<?= $c['id'] ?>" class="action-btn action-view" title="Voir">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                      </a>
                      <form method="POST" action="action.php" style="display:inline" onsubmit="return confirm('Supprimer ce message ?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="redirect" value="dashboard.php?filter=<?= htmlspecialchars($filter) ?>&page=<?= $page ?>">
                        <button type="submit" class="action-btn action-delete" title="Supprimer">
                          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <?php if ($totalPages > 1): ?>
            <div class="pagination">
              <?php if ($page > 1): ?>
                <a href="?filter=<?= $filter ?>&page=<?= $page - 1 ?>" class="page-btn">← Précédent</a>
              <?php endif; ?>
              <span class="page-info">Page <?= $page ?> / <?= $totalPages ?></span>
              <?php if ($page < $totalPages): ?>
                <a href="?filter=<?= $filter ?>&page=<?= $page + 1 ?>" class="page-btn">Suivant →</a>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>

      </div>
    </div>
  </main>

</body>
</html>
