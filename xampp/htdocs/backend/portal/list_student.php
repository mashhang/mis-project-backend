<?php
// api/list_student.php

// ——— 1) Dynamic CORS & preflight ———
$allowed_origins = [
  "http://localhost:5173",
  "http://localhost:3000"
];

if (
  isset($_SERVER['HTTP_ORIGIN']) &&
  in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins, true)
) {
  header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

// ——— 2) Only accept GET ———
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(405);
  header("Content-Type: application/json");
  echo json_encode(["error" => "Method not allowed"]);
  exit;
}

// ——— 3) Validate input ———
if (empty($_GET['id'])) {
  http_response_code(400);
  header("Content-Type: application/json");
  echo json_encode(["error" => "Missing student id"]);
  exit;
}

$id = $_GET['id'];

require_once __DIR__ . '/config.php';

try {
  // ——— 4) Fetch student record ———
  $stmt = $pdo->prepare("SELECT
        id,
        student_id,
        first_name,
        last_name,
        email,
        contact_number,
        date_of_birth,
        gender,
        course,
        year_level,
        photo_url,
        created_at
    FROM students
    WHERE id = :id
    LIMIT 1");
  $stmt->execute([':id' => $id]);
  $student = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$student) {
    http_response_code(404);
    header("Content-Type: application/json");
    echo json_encode(["error" => "Student not found"]);
    exit;
  }

  // ——— 5) Return JSON ———
  header("Content-Type: application/json");
  echo json_encode($student);

} catch (PDOException $e) {
  http_response_code(500);
  header("Content-Type: application/json");
  echo json_encode([
    "error" => "Database error: " . $e->getMessage()
  ]);
}
