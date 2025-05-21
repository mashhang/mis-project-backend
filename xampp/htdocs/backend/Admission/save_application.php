<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

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

$required_fields = ['user_id', 'name', 'dob', 'course', 'contact', 'email', 'address', 'guardianName', 'guardianRelation', 'guardianAddress'];

foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        http_response_code(400);
        echo json_encode(["message" => "Missing or empty field: $field"]);
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

$stmt = $conn->prepare("INSERT INTO user_application (user_id, name, dob, course, contact, email, address, guardianName, guardianRelation, guardianAddress) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssssssss", $data['user_id'], $data['name'], $data['dob'], $data['course'], $data['contact'], $data['email'], $data['address'], $data['guardianName'], $data['guardianRelation'], $data['guardianAddress']);

if ($stmt->execute()) {
    echo json_encode(["message" => "Application saved successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
