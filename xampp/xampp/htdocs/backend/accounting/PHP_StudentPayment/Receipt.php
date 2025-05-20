<?php
include '../PHP/DataConnect.php';

$paymentId = $_GET['payment_id'] ?? null;

if (!$paymentId) {
    echo "Invalid receipt request.";
    exit();
}

$query = "SELECT pl.*, s.full_name, s.student_number, s.course_id, s.year_level, s.payment_type, c.course_name
          FROM payment_logs pl
          JOIN students s ON pl.student_id = s.student_id
          JOIN courses c ON s.course_id = c.course_id
          WHERE pl.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $paymentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "No receipt found.";
    exit();
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Official Receipt</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #f4f4f4;
            padding: 20px;
        }

        .receipt {
            width: 380px;
            margin: auto;
            background: white;
            padding: 20px 25px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .receipt h2 {
            text-align: center;
            margin-bottom: 8px;
        }

        .receipt .line {
            border-bottom: 1px dashed #000;
            margin: 10px 0;
        }

        .receipt .item {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }

        .receipt .item strong {
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 12px;
        }

        .print-btn {
            display: block;
            width: 100%;
            margin-top: 15px;
            padding: 8px;
            background: #2e7d32;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        @media print {
            .print-btn {
                display: none;
            }

            body {
                background: white;
            }

            .receipt {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>

<div class="receipt">
    <h2>Official Receipt</h2>
    <div class="line"></div>

    <div class="item"><strong>Student:</strong> <?= htmlspecialchars($row['full_name']) ?></div>
    <div class="item"><strong>Student #:</strong> <?= $row['student_number'] ?></div>
    <div class="item"><strong>Course:</strong> <?= htmlspecialchars($row['course_name']) ?></div>
    <div class="item"><strong>Year:</strong> <?= $row['year_level'] ?></div>
    <div class="item"><strong>Payment Type:</strong> <?= $row['payment_type'] ?></div>

    <div class="line"></div>

    <div class="item"><strong>Payment Date:</strong> <?= date('F d, Y', strtotime($row['payment_date'])) ?></div>
    <div class="item"><strong>Amount Paid:</strong> ₱<?= number_format($row['amount'], 2) ?></div>
    <div class="item"><strong>Recorded At:</strong> <?= date('F d, Y h:i A', strtotime($row['recorded_at'])) ?></div>

    <div class="line"></div>
    <div class="footer">
        Thank you for your payment!<br>
        <?= date("Y") ?> © Lyceum of Alabang Accounting
    </div>

    <button class="print-btn" onclick="window.print()">🖨️ Print Receipt</button>
</div>

</body>
</html>
