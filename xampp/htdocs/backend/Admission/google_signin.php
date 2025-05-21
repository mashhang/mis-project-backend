<?php
// Disable error display to prevent HTML output breaking JSON response
ini_set('display_errors', 0);
error_reporting(0);

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

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

if (!isset($data['name'], $data['email'], $data['uid'])) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid input"]);
    exit;
}

$name = $data['name'];
$email = $data['email'];
$uid = $data['uid'];

$servername = "localhost";
$username = "root";
$password_db = "";
$dbname = "admission";

$conn = new mysqli($servername, $username, $password_db, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Check if user already exists by uid or email
$stmt = $conn->prepare("SELECT id FROM Users WHERE uid = ? OR email = ?");
$stmt->bind_param("ss", $uid, $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // User exists, update name, email, uid, and last_login if needed
    $stmt->close();
    $update_stmt = $conn->prepare("UPDATE Users SET name = ?, email = ?, uid = ?, last_login = NOW() WHERE uid = ? OR email = ?");
    $update_stmt->bind_param("sssss", $name, $email, $uid, $uid, $email);
    if ($update_stmt->execute()) {
        echo json_encode(["message" => "User updated successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error updating user: " . $update_stmt->error]);
    }
    $update_stmt->close();
} else {
    // Insert new user with empty password, uid, and last_login
    $stmt->close();
    $insert_stmt = $conn->prepare("INSERT INTO Users (name, email, password, uid, last_login) VALUES (?, ?, '', ?, NOW())");
    $insert_stmt->bind_param("sss", $name, $email, $uid);
    if ($insert_stmt->execute()) {
        echo json_encode(["message" => "User created successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error creating user: " . $insert_stmt->error]);
    }
    $insert_stmt->close();
}

$conn->close();
?>
