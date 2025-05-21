<?php
error_reporting(0);
ini_set('display_errors', '0');
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

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

// Updated query to join with status table
$sql = "SELECT s.name as status, COUNT(*) as count 
        FROM user_application ua
        LEFT JOIN status s ON ua.status_id = s.id
        GROUP BY s.name";

$result = $conn->query($sql);

$statusCounts = [
    "Approved" => 0,
    "Pending" => 0,
    "Rejected" => 0
];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $status = $row['status'];
        $count = (int)$row['count'];
        if (array_key_exists($status, $statusCounts)) {
            $statusCounts[$status] = $count;
        }
    }
}

echo json_encode($statusCounts);

$conn->close();
