<?php
// Database connection settings
$host = 'localhost';         // or 127.0.0.1
$username = 'root';          // your MySQL username
$password = '';              // your MySQL password (leave empty for default on XAMPP)
$database = 'mis'; // your database name

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// Optional: Uncomment to confirm connection
// echo "✅ Connected to accounting_db successfully";
?>
