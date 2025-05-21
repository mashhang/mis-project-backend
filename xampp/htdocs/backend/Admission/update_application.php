<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Method Not Allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$required_fields = ['id', 'name', 'dob', 'course', 'contact', 'email', 'address', 'guardianName', 'guardianRelation', 'guardianAddress', 'status_id'];

foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(["message" => "Missing field: $field"]);
        exit;
    }
}

$servername = "localhost";
$username = "root";
$password_db = "";
$dbname = "mis";

$conn = new mysqli($servername, $username, $password_db, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$stmt = $conn->prepare("UPDATE user_application SET name=?, dob=?, course=?, contact=?, email=?, address=?, guardianName=?, guardianRelation=?, guardianAddress=?, status_id=? WHERE id=?");
$stmt->bind_param("ssssssssiii", $data['name'], $data['dob'], $data['course'], $data['contact'], $data['email'], $data['address'], $data['guardianName'], $data['guardianRelation'], $data['guardianAddress'], $data['status_id'], $data['id']);

if ($stmt->execute()) {
    echo json_encode(["message" => "Application updated successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
