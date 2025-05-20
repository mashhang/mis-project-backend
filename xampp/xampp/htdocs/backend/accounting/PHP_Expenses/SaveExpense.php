<?php
include '../PHP/DataConnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $description = $_POST['description'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];
    $amount = $_POST['amount'];
    $payment_method = $_POST['payment_method'];
    $notes = $_POST['notes'];
    $expense_date = $_POST['expense_date'];

    $stmt = $conn->prepare("INSERT INTO expenses (description, category, quantity, amount, payment_method, notes, expense_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdsss", $description, $category, $quantity, $amount, $payment_method, $notes, $expense_date);

    if ($stmt->execute()) {
        header("Location: ../PHP_Expenses/Expenses.php?added=1");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
