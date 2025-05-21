<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["message" => "Method Not Allowed"]);
    exit;
}

if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(["message" => "Missing user_id parameter"]);
    exit;
}

$user_id = intval($_GET['user_id']);

if ($user_id === 0) {
    http_response_code(200);
    echo json_encode(["message" => "User is non-admin or applicant", "status_id" => null]);
    exit;
}

$servername = "localhost";
$username = "root";
$password_db = "";
$dbname = "mis";

$conn = new mysqli($servername, $username, $password_db, $dbname);

if ($conn->connect_error) {
    error_log("DB connection failed: " . $conn->connect_error);
    http_response_code(500);
    echo json_encode(["message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$stmt = $conn->prepare("SELECT user_id, name, dob, course, contact, email, address, guardianName, guardianRelation, guardianAddress, status_id FROM user_application WHERE user_id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["message" => "Prepare failed: " . $conn->error]);
    exit;
}
$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["message" => "Execute failed: " . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["message" => "No application found for this user"]);
    $stmt->close();
    $conn->close();
    exit;
}

$data = $result->fetch_assoc();

echo json_encode($data);

$stmt->close();
$conn->close();
?>
