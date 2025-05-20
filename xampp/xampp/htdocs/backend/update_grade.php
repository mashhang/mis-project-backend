<?php
// ✅ CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$conn = new mysqli("localhost", "root", "", "mis");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));
$studentId = $data->student_id ?? null;
$subjectCode = $data->subject_code ?? null;

if (!$studentId || !$subjectCode) {
  echo json_encode(["success" => false, "message" => "Missing student_id or subject_code"]);
  exit;
}

$prelims   = $data->prelims ?? null;
$midterms  = $data->midterms ?? null;
$prefinals = $data->prefinals ?? null;
$finals    = $data->finals ?? null;

// ✅ Validate allowed grades
$allowed = [1.00, 1.25, 1.50, 1.75, 2.00, 2.25, 2.50, 2.75, 3.00, 4.00, 5.00];
foreach ([$prelims, $midterms, $prefinals, $finals] as $grade) {
    if (!is_null($grade) && !in_array((float)$grade, $allowed)) {
        echo json_encode(["success" => false, "message" => "Invalid grade: $grade"]);
        exit;
    }
}

// ✅ Calculate average only if all grades are numbers
$average = null;
if (is_numeric($prelims) && is_numeric($midterms) && is_numeric($prefinals) && is_numeric($finals)) {
    $average = round(($prelims + $midterms + $prefinals + $finals) / 4, 2);
}

$stmt = $conn->prepare("UPDATE student_subjects 
  SET prelims = ?, midterms = ?, prefinals = ?, finals = ?, average = ? 
  WHERE student_id = ? AND subject_code = ?");
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Prepare failed", "error" => $conn->error]);
    exit;
}
$stmt->bind_param("dddddss", $prelims, $midterms, $prefinals, $finals, $average, $studentId, $subjectCode);
$success = $stmt->execute();

echo json_encode(["success" => $success, "message" => $success ? "Updated" : "Failed", "error" => $stmt->error]);
