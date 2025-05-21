<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

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

if (!isset($data['username'], $data['password'])) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid input"]);
    exit;
}

$input_username = $data['username'];
$input_password = $data['password'];

$conn = new mysqli("localhost", "root", "", "mis");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
$stmt->bind_param("s", $input_username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    http_response_code(401);
    echo json_encode(["message" => "Invalid username or password"]);
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->bind_result($id, $db_username, $db_password, $is_admin);
$stmt->fetch();

if ($input_password === $db_password || password_verify($input_password, $db_password)) {
    echo json_encode([
        "success" => true,
        "message" => "Login successful!",
        "user" => [
            "id" => $id,
            "username" => $db_username,
            "is_admin" => $is_admin
        ]
    ]);
} else {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid username or password"]);
}


$stmt->close();
$conn->close();
