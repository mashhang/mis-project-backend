C:\xampp\htdocs\backend<?php
error_reporting(0);
ini_set('display_errors', '0');
// Allow CORS from any origin for development
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Method Not Allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['email'], $data['password'])) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid input"]);
    exit;
}

$email = $data['email'];
$password = $data['password'];

$servername = "localhost";
$username = "root";
$password_db = "Omamam@010101";
$dbname = "admission";

$conn = new mysqli($servername, $username, $password_db, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$stmt = $conn->prepare("SELECT id, password, email, is_admin FROM Users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    http_response_code(401);
    echo json_encode(["message" => "Invalid email or password"]);
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->bind_result($id, $hashed_password, $email_db, $is_admin);
$stmt->fetch();

if (password_verify($password, $hashed_password)) {
    $user = [
        "id" => $id,
        "email" => $email_db,
        "is_admin" => $is_admin
    ];
    echo json_encode(["message" => "Login successful", "user" => $user]);
} else {
    http_response_code(401);
    echo json_encode(["message" => "Invalid email or password"]);
}

$stmt->close();
$conn->close();
?>
