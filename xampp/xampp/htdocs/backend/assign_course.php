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

$stmt = $conn->prepare("INSERT INTO assignments (student_id, course, section) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $data->student_id, $data->course, $data->section);
$success = $stmt->execute();

$course = $data->course;
$studentId = $data->student_id;

// Fetch subjects based on course + semester (optional: add $semester filter)
$subjQuery = $conn->query("SELECT subject_code, subject_title, semester, units FROM subjects WHERE course = '$course'");

while ($row = $subjQuery->fetch_assoc()) {
  $stmt = $conn->prepare("INSERT INTO student_subjects (student_id, subject_code, subject_title, semester, units) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("ssssi", $studentId, $row["subject_code"], $row["subject_title"], $row["semester"], $row["units"]);
  $stmt->execute();
}


echo json_encode(["success" => $success]);

$stmt->close();
$conn->close();
?>
