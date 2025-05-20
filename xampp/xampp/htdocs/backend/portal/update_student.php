<?php
// api/update_student.php

// --- CORS & Preflight ---
$allowed_origins = [
  "http://localhost:3000",
  "http://localhost:5173",
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

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  header("Content-Type: application/json");
  echo json_encode(["error" => "Method not allowed"]);
  exit;
}

require_once __DIR__ . '/config.php';

// Decode input
$in = json_decode(file_get_contents('php://input'), true);

$id = $in['id'] ?? null;
$first_name = $in['first_name'] ?? '';
$last_name = $in['last_name'] ?? '';
$email = $in['email'] ?? '';
$contact_number = $in['contact_number'] ?? '';
$date_of_birth = $in['date_of_birth'] ?? '';
$gender = $in['gender'] ?? '';
$course = $in['course'] ?? '';
$year_level = isset($in['year_level']) ? (int) $in['year_level'] : null;
$photo_url = $in['photo_url'] ?? '';
$location = $in['location'] ?? '';
$about = $in['about'] ?? '';

if (!$id) {
  http_response_code(400);
  header("Content-Type: application/json");
  echo json_encode(["error" => "Missing student id"]);
  exit;
}

try {
  $sql = "
    UPDATE students SET
      first_name     = ?,
      last_name      = ?,
      email          = ?,
      contact_number = ?,
      date_of_birth  = ?,
      gender         = ?,
      course         = ?,
      year_level     = ?,
      photo_url      = ?,
      location       = ?,
      about          = ?
    WHERE id = ?
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    $first_name,
    $last_name,
    $email,
    $contact_number,
    $date_of_birth,
    $gender,
    $course,
    $year_level,
    $photo_url,
    $location,
    $about,
    $id,
  ]);

  header('Content-Type: application/json');
  echo json_encode(['success' => true]);
} catch (PDOException $e) {
  http_response_code(500);
  header('Content-Type: application/json');
  echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
