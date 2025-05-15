<?php
// student_portal_api/login_student.php

// ——— 1) Dynamic CORS & preflight ———
$allowed_origins = [
  "http://localhost:5173",
  "http://localhost:3000"
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins, true)) {
  header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

// ——— 2) Only accept POST ———
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit;
}

// ——— 3) Read & validate JSON body ———
$in = json_decode(file_get_contents('php://input'), true);
$email = trim($in['email'] ?? '');
$password = trim($in['password'] ?? '');

if (!$email || !$password) {
  http_response_code(400);
  header("Content-Type: application/json");
  echo json_encode(['error' => 'Email & password required']);
  exit;
}

// ——— 4) Lookup user in database ———
require_once __DIR__ . '/config.php';

$stmt = $pdo->prepare('SELECT id, password_hash FROM students WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ——— 5) Verify password & respond ———
header("Content-Type: application/json");

if ($user && password_verify($password, $user['password_hash'])) {
  // Optionally start a session:
  // session_start();
  // $_SESSION['student_id'] = $user['id'];

  echo json_encode([
    'success' => true,
    'id' => $user['id'],
  ]);
} else {
  http_response_code(401);
  echo json_encode(['error' => 'Invalid credentials']);
}
