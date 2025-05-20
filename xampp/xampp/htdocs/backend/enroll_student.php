<?php
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  header("Access-Control-Allow-Origin: *");
  header("Access-Control-Allow-Headers: Content-Type");
  header("Access-Control-Allow-Methods: POST, OPTIONS");
  http_response_code(204);
  exit;
}

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"));

$conn = new mysqli("localhost", "root", "", "mis");
if ($conn->connect_error) {
  echo json_encode(["success" => false, "message" => "DB connection error"]);
  exit;
}

$stmt = $conn->prepare("INSERT INTO enrollments 
  (student_id, first_name, middle_name, last_name, semester, school_year, date_of_birth, contact_number, address)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param(
  "sssssssss",
  $data->student_id,
  $data->first_name,
  $data->middle_name,
  $data->last_name,
  $data->semester,
  $data->school_year,
  $data->date_of_birth,
  $data->contact_number,
  $data->address
);
$success = $stmt->execute();

echo json_encode(["success" => $success]);

$stmt->close();
$conn->close();
?>
