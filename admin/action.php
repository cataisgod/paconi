<?php
// =============================================
// Admin — Handler acțiuni (ștergere, marcare)
// Acceptă doar POST cu token CSRF valid
// =============================================
session_start();

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

// Verificare CSRF
$csrf = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
    http_response_code(403);
    die('Token de securitate invalid. <a href="dashboard.php">Înapoi</a>');
}

require_once __DIR__ . '/../config/db.php';

$action   = $_POST['action']   ?? '';
$id       = (int)($_POST['id'] ?? 0);
$redirect = $_POST['redirect'] ?? 'dashboard.php';

// Sanitizare redirect (permite doar pagini admin interne)
if (!preg_match('/^(dashboard|view)\.php/', $redirect)) {
    $redirect = 'dashboard.php';
}

if ($id > 0) {
    $db = getDB();

    match ($action) {
        'delete' => $db->prepare('DELETE FROM contacts WHERE id = ?')->execute([$id]),
        'mark_read' => $db->prepare('UPDATE contacts SET is_read = 1 WHERE id = ?')->execute([$id]),
        'mark_unread' => $db->prepare('UPDATE contacts SET is_read = 0 WHERE id = ?')->execute([$id]),
        default => null,
    };
}

header('Location: ' . $redirect);
exit;
