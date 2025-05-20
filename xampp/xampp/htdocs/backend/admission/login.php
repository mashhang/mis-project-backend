<?php
// Development only: enable error display to help debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Headers for CORS and JSON
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

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['email'], $data['password'])) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid input"]);
    exit;
}

$email = $data['email'];
$password = $data['password'];

// Database credentials
$servername = "localhost";
$username = "root";
$password_db = "";
$dbname = "admission";

// Create DB connection
$conn = new mysqli($servername,$username, $password_db,  $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Adjust table name to lowercase `users` if that's what you use
$stmt = $conn->prepare("SELECT id, password, email, is_admin FROM users WHERE email = ?");
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

// Password verification
if (password_verify($password, $hashed_password)) {
    // ✅ Optional: Only include this if `last_login` column exists
    // $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    // $update_stmt->bind_param("i", $id);
    // $update_stmt->execute();
    // $update_stmt->close();

    echo json_encode([
        "message" => "Login successful",
        "user" => [
            "id" => $id,
            "email" => $email_db,
            "is_admin" => $is_admin
        ]
    ]);
} else {
    http_response_code(401);
    echo json_encode(["message" => "Invalid email or password"]);
}

$stmt->close();
$conn->close();
?>
