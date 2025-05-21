<?php
include '../PHP/DataConnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = $_POST['student_id'];
    $tuitionAmount = $_POST['tuition_amount'];
    $tuitionType = $_POST['tuition_type'];
    $downpayment = $_POST['downpayment_amount'];

    // Check if student already exists in enrollments_with_tuition
    $check = $conn->prepare("SELECT id FROM enrollments_with_tuition WHERE student_id = ?");
    $check->bind_param("s", $studentId);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Update existing record
        $stmt = $conn->prepare("UPDATE enrollments_with_tuition SET tuition_amount = ?, tuition_type = ?, amount_paid = ? WHERE student_id = ?");
        $stmt->bind_param("dsds", $tuitionAmount, $tuitionType, $downpayment, $studentId);
    } else {
        // Insert new record
        $stmt = $conn->prepare("INSERT INTO enrollments_with_tuition (student_id, tuition_amount, tuition_type, amount_paid) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdsd", $studentId, $tuitionAmount, $tuitionType, $downpayment);
    }

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>