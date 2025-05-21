<?php
include '../PHP/DataConnect.php';

if (!isset($_GET['payment_id'])) {
    die("Missing payment ID.");
}

$paymentId = $_GET['payment_id'];

// Use prepared statement safely
$query = "SELECT 
            p.id AS payment_id,
            p.student_id,
            p.amount,
            p.payment_date,
            e.first_name,
            e.middle_name,
            e.last_name,
            t.tuition_amount,
            t.amount_paid
          FROM payment_logs p
          LEFT JOIN enrollments e ON p.student_id = e.student_id
          LEFT JOIN enrollments_with_tuition t ON p.student_id = t.student_id
          WHERE p.id = ?";

$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $paymentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No receipt found for Payment ID: " . htmlspecialchars($paymentId));
}

$row = $result->fetch_assoc();

$fullName = "{$row['first_name']} {$row['middle_name']} {$row['last_name']}";
$tuition = number_format($row['tuition_amount'], 2);
$amountPaid = number_format($row['amount_paid'], 2);
$paidNow = number_format($row['amount'], 2);
$balance = number_format($row['tuition_amount'] - $row['amount_paid'], 2);
$date = date("F d, Y", strtotime($row['payment_date']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            max-width: 600px;
            margin: auto;
        }
        h2 {
            text-align: center;
        }
        .receipt {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 25px;
            margin-top: 20px;
            background-color: #f9f9f9;
        }
        .receipt p {
            margin: 10px 0;
            font-size: 16px;
        }
        .highlight {
            font-weight: bold;
            color: #2c3e50;
        }
    </style>
</head>
<body>

    <h2>📄 Payment Receipt</h2>
    <div class="receipt">
        <p><span class="highlight">Student ID:</span> <?= htmlspecialchars($row['student_id']) ?></p>
        <p><span class="highlight">Name:</span> <?= htmlspecialchars($fullName) ?></p>
        <p><span class="highlight">Date Paid:</span> <?= $date ?></p>
        <hr>
        <p><span class="highlight">Tuition Amount:</span> ₱<?= $tuition ?></p>
        <p><span class="highlight">Amount Paid This Transaction:</span> ₱<?= $paidNow ?></p>
        <p><span class="highlight">Total Paid:</span> ₱<?= $amountPaid ?></p>
        <p><span class="highlight">Remaining Balance:</span> ₱<?= $balance ?></p>
        <hr>
        <p style="text-align: center;">✅ Payment successfully recorded.</p>
    </div>

</body>
</html>
