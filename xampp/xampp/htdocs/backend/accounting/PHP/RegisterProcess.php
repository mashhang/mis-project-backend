<?php
include '../PHP/DataConnect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        header("Location: ../PHP/Register.php?error=Username or email already exists.");
        exit();
    } else {
        $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $full_name, $username, $email, $password);

        if ($stmt->execute()) {
            header("Location: ../PHP/Register.php?success=Registration successful. Please login.");
            exit();
        } else {
            header("Location: ../PHP/Register.php?error=Registration failed. Please try again.");
            exit();
        }
    }
}
?>
