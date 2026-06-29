<?php
// =============================================
// Admin — Vizualizare mesaj individual
// =============================================
session_start();

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: dashboard.php');
    exit;
}

$db   = getDB();
$stmt = $db->prepare('SELECT * FROM contacts WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$contact = $stmt->fetch();

if (!$contact) {
    header('Location: dashboard.php');
    exit;
}

// Marchează automat ca citit
if (!$contact['is_read']) {
    $db->prepare('UPDATE contacts SET is_read = 1 WHERE id = ?')->execute([$id]);
    $contact['is_read'] = 1;
}

$csrf     = $_SESSION['csrf_token'] ?? '';
$username = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin');
$date     = date('d/m/Y à H:i', strtotime($contact['created_at']));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PACONI Admin — Message #<?= $id ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

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

  <main class="admin-main">
    <div class="admin-container admin-container--narrow">

      <div class="page-header">
        <div>
          <a href="dashboard.php" class="back-link">
            ← Retour aux messages
          </a>
          <h1 class="page-title" style="margin-top:8px">Message #<?= $id ?></h1>
          <p class="page-sub">Reçu le <?= $date ?></p>
        </div>
        <form method="POST" action="action.php" onsubmit="return confirm('Supprimer ce message définitivement ?')">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= $id ?>">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
          <input type="hidden" name="redirect" value="dashboard.php">
          <button type="submit" class="btn-danger">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M9 6V4h6v2"/></svg>
            Supprimer
          </button>
        </form>
      </div>

      <!-- Fișa de contact -->
      <div class="view-card">

        <div class="view-grid">

          <div class="view-field">
            <span class="view-label">Nom</span>
            <span class="view-value"><?= htmlspecialchars($contact['name']) ?></span>
          </div>

          <div class="view-field">
            <span class="view-label">Email</span>
            <a href="mailto:<?= htmlspecialchars($contact['email']) ?>" class="view-value link-email">
              <?= htmlspecialchars($contact['email']) ?>
            </a>
          </div>

          <div class="view-field">
            <span class="view-label">Téléphone</span>
            <?php if ($contact['phone']): ?>
              <a href="tel:<?= htmlspecialchars($contact['phone']) ?>" class="view-value link-email">
                <?= htmlspecialchars($contact['phone']) ?>
              </a>
            <?php else: ?>
              <span class="view-value" style="color:#aaa">Non renseigné</span>
            <?php endif; ?>
          </div>

          <div class="view-field">
            <span class="view-label">Date</span>
            <span class="view-value"><?= $date ?></span>
          </div>

        </div>

        <div class="view-message">
          <span class="view-label">Message</span>
          <div class="view-message-body">
            <?= nl2br(htmlspecialchars($contact['message'])) ?>
          </div>
        </div>

        <!-- Répondre par email -->
        <div class="reply-bar">
          <a href="mailto:<?= htmlspecialchars($contact['email']) ?>?subject=Re: votre message - PACONI" class="btn-reply">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            Répondre par email
          </a>
        </div>

      </div>
    </div>
  </main>

</body>
</html>
