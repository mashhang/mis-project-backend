<?php
include '../PHP/DataConnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = $_POST['student_id'];
    $amount = $_POST['amount'];
    $date = $_POST['payment_date'];

    // 1. Update amount_paid in enrollments_with_tuition
    $updateQuery = "UPDATE enrollments_with_tuition SET amount_paid = amount_paid + ? WHERE student_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ds", $amount, $studentId);

    if ($stmt->execute()) {
        // 2. Log the payment
        $logQuery = "INSERT INTO payment_logs (student_id, amount, payment_date) VALUES (?, ?, ?)";
        $logStmt = $conn->prepare($logQuery);
        $logStmt->bind_param("sds", $studentId, $amount, $date);

        if ($logStmt->execute()) {
            $paymentId = $conn->insert_id;
            header("Location: Receipt.php?payment_id=$paymentId");
            exit();
        } else {
            echo "Error logging payment: " . $logStmt->error;
        }
        $logStmt->close();
    } else {
        echo "Error updating tuition: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
