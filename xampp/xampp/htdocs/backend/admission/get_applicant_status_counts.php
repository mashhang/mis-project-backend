<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$servername = "localhost";
$username = "root";
$password_db = "";
$dbname = "mis";

$conn = new mysqli($servername, $username, $password_db, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$sql = "
    SELECT 
        status_id,
        COUNT(*) as count
    FROM user_application
    GROUP BY status_id
";

$result = $conn->query($sql);

$statusCounts = [
    "Approved" => 0,
    "Pending" => 0,
    "Rejected" => 0,
];

while ($row = $result->fetch_assoc()) {
    if ($row['status_id'] == 1) $statusCounts["Pending"] = (int)$row['count'];
    if ($row['status_id'] == 2) $statusCounts["Approved"] = (int)$row['count'];
    if ($row['status_id'] == 3) $statusCounts["Rejected"] = (int)$row['count'];
}

$conn->close();

// Clean output and return JSON
ob_clean();
echo json_encode($statusCounts);
?>
