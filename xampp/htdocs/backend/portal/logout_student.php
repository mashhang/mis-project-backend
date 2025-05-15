<?php
// api/logout_student.php

// ——— 1) Dynamic CORS & preflight ———
$allowed_origins = [
  "http://localhost:5173",
  "http://localhost:3000"
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins, true)) {
  header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

// ——— 2) Start session & destroy it ———
session_start();

// Unset all of the session variables
$_SESSION = [];

// If you want to kill the session cookie as well:
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(
    session_name(),
    '',
    time() - 42000,
    $params["path"],
    $params["domain"],
    $params["secure"],
    $params["httponly"]
  );
}

// Finally, destroy the session
session_destroy();

// ——— 3) Return JSON response ———
header('Content-Type: application/json');
echo json_encode(["message" => "Logged out successfully"]);
