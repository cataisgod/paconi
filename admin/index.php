<?php
// =============================================
// Admin — Pagina de autentificare
// =============================================
session_start();

if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/db.php';

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username !== '' && $password !== '') {
        try {
            $db   = getDB();
            $stmt = $db->prepare('SELECT id, username, password FROM admin_users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username']  = $user['username'];
                $_SESSION['admin_id']        = $user['id'];
                // Generare token CSRF
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Nume de utilizator sau parolă incorectă.';
            }
        } catch (PDOException $e) {
            $error = 'Eroare de conexiune la baza de date.';
        }
    } else {
        $error = 'Completează toate câmpurile.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PACONI — Administration</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">

  <div class="login-card">
    <div class="login-brand">
      <span class="login-logo">PACONI</span>
      <p class="login-subtitle">Espace Administration</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="" class="login-form">
      <div class="form-group">
        <label for="username">Nom d'utilisateur</label>
        <input
          type="text" id="username" name="username"
          value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
          autocomplete="username" required autofocus>
      </div>
      <div class="form-group">
        <label for="password">Mot de passe</label>
        <input
          type="password" id="password" name="password"
          autocomplete="current-password" required>
      </div>
      <button type="submit" class="btn-login">Se connecter</button>
    </form>
  </div>

</body>
</html>
