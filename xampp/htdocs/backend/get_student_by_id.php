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

// Get personal + course info (latest)
$sql = "SELECT e.*, a.course, a.section 
        FROM enrollments e
        JOIN assignments a ON e.student_id = a.student_id
        WHERE e.student_id = '$studentId'
        ORDER BY e.created_at DESC LIMIT 1";

$result = $conn->query($sql);
if ($result->num_rows === 0) {
  echo json_encode(["success" => false, "message" => "Student not found"]);
  exit;
}
$student = $result->fetch_assoc();

// Get all enrollment history
$history = [];
$histQuery = $conn->query("SELECT school_year, '4th year' AS year_level, 'REGULAR' AS status 
                           FROM enrollments 
                           WHERE student_id = '$studentId'");
while ($row = $histQuery->fetch_assoc()) {
  $history[] = $row;
}

// Get all subjects
$subjects = [];
$subjQuery = $conn->query("SELECT subject_code, subject_title, units, semester, 
       prelims, midterms, prefinals, finals, average
FROM student_subjects
WHERE student_id = '$studentId'");
while ($row = $subjQuery->fetch_assoc()) {
  $subjects[] = $row;

}

echo json_encode([
  "success" => true,
  "data" => $student,
  "history" => $history,
  "subjects" => $subjects
]);

$conn->close();
?>
