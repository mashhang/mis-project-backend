<?php
include '../PHP/DataConnect.php';
session_start();

// Sample user ID from session (replace with actual logic)
$user_id = $_SESSION['user_id'] ?? 1;

// Get employee full name
$employeeQuery = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
$employeeQuery->bind_param("i", $user_id);
$employeeQuery->execute();
$employeeName = $employeeQuery->get_result()->fetch_assoc()['full_name'] ?? 'Employee';

// === FUNCTIONS ===
function getPayrollHistory($conn, $user_id, $year = null) {
    if ($year) {
        $query = "SELECT * FROM payrolls WHERE user_id = ? AND YEAR(payout_date) = ? ORDER BY payout_date DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $user_id, $year);
    } else {
        $query = "SELECT * FROM payrolls WHERE user_id = ? ORDER BY payout_date DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
    }
    $stmt->execute();
    return $stmt->get_result();
}

function calculateTotalEarnings($conn, $user_id, $year = null) {
    if ($year) {
        $query = "SELECT SUM(net_pay) AS total FROM payrolls WHERE user_id = ? AND YEAR(payout_date) = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $user_id, $year);
    } else {
        $query = "SELECT SUM(net_pay) AS total FROM payrolls WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
    }
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'] ?? 0;
}

function getPayrollYears($conn, $user_id) {
    $query = "SELECT DISTINCT YEAR(payout_date) AS year FROM payrolls WHERE user_id = ? ORDER BY year DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getPayrollCount($conn, $user_id, $year = null) {
    if ($year) {
        $query = "SELECT COUNT(*) AS total FROM payrolls WHERE user_id = ? AND YEAR(payout_date) = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $user_id, $year);
    } else {
        $query = "SELECT COUNT(*) AS total FROM payrolls WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
    }
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'] ?? 0;
}

// === FILTER HANDLING ===
$selectedYear = $_GET['year'] ?? null;
$payrolls = getPayrollHistory($conn, $user_id, $selectedYear);
$totalEarnings = calculateTotalEarnings($conn, $user_id, $selectedYear);
$payrollCount = getPayrollCount($conn, $user_id, $selectedYear);
$years = getPayrollYears($conn, $user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/sidebar.css">
    <link rel="stylesheet" href="../CSS/etoaypayroll.css">
    <title>My Payroll</title>
</head>
<body>

<?php include '../PHP/sidebar.php'; ?>

<section class="home">
    <!-- ✅ Employee Name Display -->
    <h2 style="margin-left: 20px; margin-bottom: 10px;">
        Payroll Overview for <strong><?= htmlspecialchars($employeeName) ?></strong>
    </h2>

    <div class="totals-container">
        <div class="total-box">
            <h4>Total Net Pay<?= $selectedYear ? " ({$selectedYear})" : '' ?></h4>
            <div class="total-value">₱<?= number_format($totalEarnings, 2) ?></div>
        </div>
        <div class="total-box">
            <h4>Total of Records</h4>
            <div class="total-value"><i class='bx bx-spreadsheet'></i> <?= $payrollCount ?></div>
        </div>
    </div>

   <div class="filter-container" style="display: flex; align-items: center; gap: 1rem;">
    <form method="GET" style="display: flex; align-items: center;">
        <label for="year">Filter by Year: </label>
        <select name="year" id="year" onchange="this.form.submit()">
            <option value="">All Years</option>
            <?php while ($y = $years->fetch_assoc()): ?>
                <option value="<?= $y['year'] ?>" <?= ($selectedYear == $y['year']) ? 'selected' : '' ?>>
                    <?= $y['year'] ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <!-- ✅ Print Payroll Button -->
    <button onclick="printPayroll()" style="padding: 6px 12px; background: #facc15; border: none; border-radius: 5px; cursor: pointer;">
        <i class='bx bx-printer'></i> Print Payroll
    </button>
</div>

    <div class="table-container">
        <div class="responsive-table-wrapper">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Payout Date</th>
                        <th>Gross Pay</th>
                        <th>Deductions</th>
                        <th>Net Pay</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($payrolls->num_rows > 0): ?>
                        <?php while ($row = $payrolls->fetch_assoc()): ?>
                            <tr>
                                <td><?= date('F d, Y', strtotime($row['payout_date'])) ?></td>
                                <td>₱<?= number_format($row['gross_pay'], 2) ?></td>
                                <td>₱<?= number_format($row['deductions'], 2) ?></td>
                                <td>₱<?= number_format($row['net_pay'], 2) ?></td>
                                <td>
                                    <span class="status <?= strtolower($row['status']) ?>">
                                        <?= htmlspecialchars($row['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5">No payroll records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<script>
function printPayroll() {
    const printContent = document.querySelector(".table-container").innerHTML;
    const employeeName = <?= json_encode($employeeName) ?>;
    const selectedYear = <?= json_encode($selectedYear) ?>;

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>Payroll Report</title>
            <style>
                body { font-family: Arial; padding: 20px; }
                h2 { text-align: center; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
                th { background-color: #facc15; }
            </style>
        </head>
        <body>
            <h2>Payroll Report for ${employeeName}${selectedYear ? ' (' + selectedYear + ')' : ''}</h2>
            ${printContent}
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}
</script>
</body>
</html>
