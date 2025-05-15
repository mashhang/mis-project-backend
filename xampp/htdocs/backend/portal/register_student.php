<?php
// /backend/portal/api/register_student.php

// ——— 1) Dynamic CORS & preflight ———
$allowed_origins = [
  "http://localhost:5173",  // if you ever run a Vite front-end
  "http://localhost:3000"   // your Next.js dev server
];

if (
  isset($_SERVER['HTTP_ORIGIN'])
  && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins, true)
) {
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

// ——— 2) Only POST ———
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  header("Content-Type: application/json");
  echo json_encode(["error" => "Method not allowed"]);
  exit;
}

require_once __DIR__ . '/config.php';  // adjust if your config lives elsewhere

// ——— 3) Validate incoming form-data ———
$required = [
  'student_id',
  'first_name',
  'last_name',
  'email',
  'contact_number',
  'date_of_birth',
  'gender',
  'course',
  'year_level',
  'photo_url',
  'password'
];

foreach ($required as $field) {
  if (empty($_POST[$field])) {
    http_response_code(400);
    header("Content-Type: application/json");
    echo json_encode(["error" => "Missing field: $field"]);
    exit;
  }
}

// ——— 4) Gather & hash ———
$student_id = $_POST['student_id'];
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$email = $_POST['email'];
$contact_number = $_POST['contact_number'];
$date_of_birth = $_POST['date_of_birth'];
$gender = $_POST['gender'];
$course = $_POST['course'];
$year_level = (int) $_POST['year_level'];
$photo_url = $_POST['photo_url'];
$password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);

try {
  // ——— 5) Insert student ———
  $sql = "
        INSERT INTO students (
            student_id, first_name, last_name,
            email, contact_number, date_of_birth,
            gender, course, year_level,
            photo_url, password_hash
        ) VALUES (
            :student_id, :first_name, :last_name,
            :email, :contact_number, :date_of_birth,
            :gender, :course, :year_level,
            :photo_url, :password_hash
        )
    ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    ':student_id' => $student_id,
    ':first_name' => $first_name,
    ':last_name' => $last_name,
    ':email' => $email,
    ':contact_number' => $contact_number,
    ':date_of_birth' => $date_of_birth,
    ':gender' => $gender,
    ':course' => $course,
    ':year_level' => $year_level,
    ':photo_url' => $photo_url,
    ':password_hash' => $password_hash
  ]);

  // ——— 6) Return new record ———
  $newId = $pdo->lastInsertId();
  $stmt = $pdo->prepare("
        SELECT id, student_id, first_name, last_name,
               email, contact_number, date_of_birth,
               gender, course, year_level, photo_url
        FROM students
        WHERE id = :id
    ");
  $stmt->execute([':id' => $newId]);
  $student = $stmt->fetch(PDO::FETCH_ASSOC);

  header("Content-Type: application/json");
  echo json_encode($student);

} catch (PDOException $e) {
  http_response_code(500);
  header("Content-Type: application/json");
  echo json_encode([
    "error" => "Database error: " . $e->getMessage()
  ]);
}
