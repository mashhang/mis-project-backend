<?php
include '../PHP/DataConnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $studentId = $_POST['student_id'];
    $amount = $_POST['amount'];
    $date = $_POST['payment_date'];

    // 1. Update amount_paid in students table
    $updateQuery = "UPDATE students SET amount_paid = amount_paid + ? WHERE student_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("di", $amount, $studentId);

    if ($stmt->execute()) {
        // 2. Log the payment in payment_logs table using prepared statement
        $logQuery = "INSERT INTO payment_logs (student_id, amount, payment_date) VALUES (?, ?, ?)";
        $logStmt = $conn->prepare($logQuery);
        $logStmt->bind_param("ids", $studentId, $amount, $date);

        if ($logStmt->execute()) {
            $paymentId = $conn->insert_id; // Get the inserted payment log ID

            // ✅ Redirect to Receipt.php
            header("Location: Receipt.php?payment_id=$paymentId");
            exit();
        } else {
            echo "Error logging payment: " . $conn->error;
        }
    } else {
        echo "Error saving payment: " . $conn->error;
    }
}
?>
