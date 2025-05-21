<?php
include '../PHP/DataConnect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch user data by username
    // $stmt = $conn->prepare("SELECT id, username, password FROM user WHERE username = ?");
    // $stmt->bind_param("s", $username);
    // $stmt->execute();
    // $result = $stmt->get_result();
    $stmt = $conn->prepare("SELECT * FROM user WHERE username = ? AND password = ?");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

    // if ($result->num_rows === 1) {
    //     $user = $result->fetch_assoc();

    //     if (password_verify($password, $user['password'])) {
    //         // Start session
    //         $_SESSION['id'] = $user['id'];
    //         $_SESSION['username'] = $user['username'];
  

    //         header("Location: ../backend/PHP_Dashboard/Dashboard.php"); // Change if needed
    //         exit();
    //     } else {
    //         echo "<script>alert('Incorrect password.'); window.history.back();</script>";
    //     }
    // } else {
    //     echo "<script>alert('Username not found.'); window.history.back();</script>";
    // }

    if ($result->num_rows > 0) {
        echo json_encode(["success" => true, "message" => "Login successful!"]);
        header("Location: ../PHP_Student/Student.php");
    } else {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Invalid credentials."]);
    }
}
?>
