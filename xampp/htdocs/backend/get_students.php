<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "mis");

if ($conn->connect_error) {
  echo json_encode(["success" => false, "message" => "Database connection failed"]);
  exit;
}

$sql = "SELECT e.student_id, e.first_name, e.middle_name, e.last_name, a.course, a.section 
        FROM enrollments e
        JOIN assignments a ON e.student_id = a.student_id";

$result = $conn->query($sql);

$students = [];

while ($row = $result->fetch_assoc()) {
  $students[] = [
    "id" => $row["student_id"],
    "name" => $row["first_name"] . " " . $row["middle_name"] . " " . $row["last_name"],
    "course" => $row["course"],
    "year" => "4th Year",  // Optional: Replace with real value if stored
    "status" => "REGULAR", // Optional: Replace with real value if stored
  ];
}

echo json_encode(["success" => true, "data" => $students]);

$conn->close();
?>
