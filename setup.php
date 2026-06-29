<?php
// =============================================
// SETUP — Rulează O SINGURĂ DATĂ
// Accesează: http://localhost/paconi/setup.php
// ȘTERGE acest fișier după setup!
// =============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'paconi');

$steps = [];
$hasError = false;

try {
    // Conectare fără a specifica baza de date
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $steps[] = ['ok', 'Conexiune MySQL reușită'];

    // Creare baza de date
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `" . DB_NAME . "`");
    $steps[] = ['ok', 'Baza de date <strong>' . DB_NAME . '</strong> creată / existentă'];

    // Tabel contacte (formularul de pe landing page)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `contacts` (
        `id`         INT AUTO_INCREMENT PRIMARY KEY,
        `name`       VARCHAR(255)  NOT NULL,
        `email`      VARCHAR(255)  NOT NULL,
        `phone`      VARCHAR(50)   DEFAULT NULL,
        `message`    TEXT          NOT NULL,
        `is_read`    TINYINT(1)    NOT NULL DEFAULT 0,
        `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $steps[] = ['ok', 'Tabel <strong>contacts</strong> creat'];

    // Tabel utilizatori admin
    $pdo->exec("CREATE TABLE IF NOT EXISTS `admin_users` (
        `id`         INT AUTO_INCREMENT PRIMARY KEY,
        `username`   VARCHAR(100)  NOT NULL UNIQUE,
        `password`   VARCHAR(255)  NOT NULL,
        `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $steps[] = ['ok', 'Tabel <strong>admin_users</strong> creat'];

    // Utilizator admin implicit: admin / admin123
    $hash = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO `admin_users` (username, password) VALUES (?, ?)");
    $stmt->execute(['admin', $hash]);
    $steps[] = ['ok', 'Utilizator admin creat — <code>admin</code> / <code>admin123</code>'];

} catch (PDOException $e) {
    $steps[] = ['error', 'Eroare: ' . htmlspecialchars($e->getMessage())];
    $hasError = true;
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PACONI — Setup</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: system-ui, sans-serif; background: #f4f5f7; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
    .card { background: #fff; border-radius: 12px; padding: 40px; max-width: 540px; width: 100%; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
    h1 { font-size: 1.5rem; margin-bottom: 6px; color: #1a1a2e; letter-spacing: .05em; }
    .sub { font-size: .85rem; color: #888; margin-bottom: 32px; }
    .step { display: flex; align-items: flex-start; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f0f0f0; font-size: .9rem; color: #2B2D33; }
    .step:last-child { border-bottom: none; }
    .icon { width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: .7rem; font-weight: 700; margin-top: 1px; }
    .icon.ok    { background: #d1fae5; color: #065f46; }
    .icon.error { background: #fee2e2; color: #991b1b; }
    code, strong { color: #C75B39; }
    .actions { margin-top: 28px; display: flex; gap: 12px; flex-wrap: wrap; }
    .btn { display: inline-block; padding: 11px 22px; border-radius: 50px; font-size: .8rem; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; text-decoration: none; transition: .2s; }
    .btn-primary { background: #C75B39; color: #fff; }
    .btn-primary:hover { background: #a84a2c; }
    .btn-secondary { background: #f4f5f7; color: #2B2D33; border: 1px solid #e2e5e9; }
    .warning { margin-top: 20px; background: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; padding: 14px 16px; font-size: .82rem; color: #92400e; line-height: 1.6; }
  </style>
</head>
<body>
<div class="card">
  <h1>PACONI</h1>
  <p class="sub">Configurare bază de date</p>

  <?php foreach ($steps as [$type, $msg]): ?>
    <div class="step">
      <span class="icon <?= $type ?>"><?= $type === 'ok' ? '✓' : '✕' ?></span>
      <span><?= $msg ?></span>
    </div>
  <?php endforeach; ?>

  <?php if (!$hasError): ?>
    <div class="actions">
      <a href="admin/index.php" class="btn btn-primary">Mergi la Admin</a>
      <a href="index.html" class="btn btn-secondary">Landing Page</a>
    </div>
    <div class="warning">
      ⚠️ <strong>Șterge acest fișier</strong> (<code>setup.php</code>) după configurare pentru a proteja instalarea.
    </div>
  <?php endif; ?>
</div>
</body>
</html>
