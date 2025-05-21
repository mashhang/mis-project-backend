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
$sql = "SELECT ua.id, ua.name, ua.dob, ua.course, ua.contact, ua.email, ua.address, ua.guardianName, ua.guardianRelation, ua.guardianAddress, s.name as status 
        FROM user_application ua
        LEFT JOIN status s ON ua.status_id = s.id";

$result = $conn->query($sql);

$applicants = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $applicants[] = $row;
    }
}

echo json_encode($applicants);

$conn->close();
