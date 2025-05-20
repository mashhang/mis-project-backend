<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "admission";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Example seed data for Users table
$adminName = "Admin";
$adminEmail = "admin@example.com";
$adminPassword = password_hash("adminpassword", PASSWORD_DEFAULT);
$isAdmin = 1;

$stmt_admin = $conn->prepare("INSERT INTO Users (name, email, password, is_admin) VALUES (?, ?, ?, ?)");
$stmt_admin->bind_param("sssi", $adminName, $adminEmail, $adminPassword, $isAdmin);
$stmt_admin->execute();
$stmt_admin->close();

// Example seed data for Users table (regular user)
$name = "User One";
$email = "user1@example.com";
$password = password_hash("userpassword", PASSWORD_DEFAULT);

$stmt_user = $conn->prepare("INSERT INTO Users (name, email, password, is_admin) VALUES (?, ?, ?, 0)");
$stmt_user->bind_param("sss", $name, $email, $password);
$stmt_user->execute();
$user_id = $conn->insert_id; // last inserted user id
$stmt_user->close();

// Example seed data for user_application table
$name = "User One Application";
$dob = "2000-01-01";
$course = "Computer Science";
$contact = "1234567890";
$email = "user1@example.com";
$address = "123 Main St";
$guardianName = "Guardian One";
$guardianRelation = "Parent";
$guardianAddress = "123 Guardian St";

$stmt_app = $conn->prepare("INSERT INTO user_application (user_id, name, dob, course, contact, email, address, guardianName, guardianRelation, guardianAddress) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt_app->bind_param("isssssssss", $user_id, $name, $dob, $course, $contact, $email, $address, $guardianName, $guardianRelation, $guardianAddress);
$stmt_app->execute();
$stmt_app->close();

$conn->close();
?>
