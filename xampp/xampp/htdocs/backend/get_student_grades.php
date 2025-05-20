<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "mis");
if ($conn->connect_error) {
  echo json_encode(["success" => false, "message" => "Connection failed"]);
  exit;
}

$studentId = $_GET["id"] ?? "";
if (!$studentId) {
  echo json_encode(["success" => false, "message" => "Missing student ID"]);
  exit;
}

$grades = [];
$res = $conn->query("SELECT student_id, subject_code, subject_title, prelims, midterms, prefinals, finals, units,
  ROUND((IFNULL(prelims, 0) + IFNULL(midterms, 0) + IFNULL(prefinals, 0) + IFNULL(finals, 0)) / 4, 2) AS average
  FROM student_subjects 
  WHERE student_id = '$studentId'");

if ($res === false) {
  echo json_encode(["success" => false, "message" => "Query failed", "error" => $conn->error]);
  exit;
}

while ($row = $res->fetch_assoc()) {
  $grades[] = $row;
  $subjects[] = [
  "student_id" => $row["student_id"],
  "subject_code" => $row["subject_code"],
  "subject_title" => $row["subject_title"],
  "prelims" => $row["prelims"],
  "midterms" => $row["midterms"],
  "prefinals" => $row["prefinals"],
  "finals" => $row["finals"],
  "average" => $row["average"],
  "units" => $row["units"]
];
}

echo json_encode(["success" => true, "subjects" => $grades]);
$conn->close();
?>
