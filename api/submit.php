<?php
// =============================================
// API — Primire formular de contact
// POST /api/submit.php (JSON sau form-data)
// =============================================

header('Content-Type: application/json; charset=utf-8');

// Permite cereri din aceeași origine (XAMPP local)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

// Acceptă atât JSON cât și form-data
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$name    = trim($data['name']    ?? '');
$email   = trim($data['email']   ?? '');
$phone   = trim($data['phone']   ?? '');
$message = trim($data['message'] ?? '');

// Validare
$errors = [];
if ($name === '')                              $errors[] = 'Le nom est requis.';
if ($email === '')                             $errors[] = "L'email est requis.";
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'adresse email est invalide.";
if ($message === '')                           $errors[] = 'Le message est requis.';

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

try {
    $db   = getDB();
    $stmt = $db->prepare(
        'INSERT INTO contacts (name, email, phone, message) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$name, $email, $phone ?: null, $message]);

    echo json_encode(['success' => true, 'message' => 'Message envoyé avec succès.']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur. Veuillez réessayer.']);
}
