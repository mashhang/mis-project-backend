<?php
include '../PHP/DataConnect.php';

function filterStudents($conn, $course = null, $section = null, $status_filter = '', $limit = 10, $offset = 0) {
    $query = "SELECT t.*, 
                     e.first_name, e.middle_name, e.last_name,
                     a.course, a.section,
                     (t.tuition_amount - t.amount_paid) AS balance,
                     CASE 
                        WHEN t.amount_paid >= t.tuition_amount THEN 'Fully Paid'
                        WHEN t.amount_paid = 0 THEN 'Unpaid'
                        ELSE 'Partial'
                     END AS payment_status
              FROM enrollments_with_tuition t
              LEFT JOIN enrollments e ON e.student_id = t.student_id
              LEFT JOIN assignments a ON a.student_id = t.student_id
              WHERE 1";

    if ($course) {
        $query .= " AND a.course = '" . $conn->real_escape_string($course) . "'";
    }
    if ($section) {
        $query .= " AND a.section = '" . $conn->real_escape_string($section) . "'";
    }

    if ($status_filter === 'Fully Paid') {
        $query .= " HAVING payment_status = 'Fully Paid'";
    } elseif ($status_filter === 'Partial') {
        $query .= " HAVING payment_status = 'Partial'";
    } elseif ($status_filter === 'Unpaid') {
        $query .= " HAVING payment_status = 'Unpaid'";
    }

    $query .= " LIMIT $limit OFFSET $offset";
    return $conn->query($query);
}

$selectedCourse = $_GET['course_filter'] ?? '';
$selectedSection = $_GET['year_filter'] ?? '';
$selectedStatus = $_GET['status_filter'] ?? '';

$limitOptions = [10, 30, 100];
$recordsPerPage = isset($_GET['limit']) && in_array((int)$_GET['limit'], $limitOptions) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
$offset = ($page - 1) * $recordsPerPage;

$countQuery = "SELECT COUNT(*) AS total FROM (
    SELECT student_id, payment_status FROM (
        SELECT t.student_id,
               (t.tuition_amount - t.amount_paid) AS balance,
               CASE 
                  WHEN t.amount_paid >= t.tuition_amount THEN 'Fully Paid'
                  WHEN t.amount_paid = 0 THEN 'Unpaid'
                  ELSE 'Partial'
               END AS payment_status,
               a.course, a.section
        FROM enrollments_with_tuition t
        LEFT JOIN assignments a ON a.student_id = t.student_id
        LEFT JOIN enrollments e ON e.student_id = t.student_id
        WHERE 1";

if ($selectedCourse) {
    $countQuery .= " AND a.course = '" . $conn->real_escape_string($selectedCourse) . "'";
}
if ($selectedSection) {
    $countQuery .= " AND a.section = '" . $conn->real_escape_string($selectedSection) . "'";
}
$countQuery .= ") AS inner_result";

if ($selectedStatus) {
    $countQuery .= " WHERE payment_status = '" . $conn->real_escape_string($selectedStatus) . "'";
}

$countQuery .= ") AS filtered_students";

$countResult = $conn->query($countQuery);
if (!$countResult) {
    die("SQL Error: " . $conn->error . "\nQuery: " . $countQuery);
}
$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = max(ceil($totalRecords / $recordsPerPage), 1);

$studentsResult = filterStudents($conn, $selectedCourse, $selectedSection, $selectedStatus, $recordsPerPage, $offset);

$courseOptions = '';
$courseQuery = "SELECT DISTINCT course FROM assignments ORDER BY course";
$courseResult = $conn->query($courseQuery);
if ($courseResult) {
    while ($course = $courseResult->fetch_assoc()) {
        $selected = ($selectedCourse == $course['course']) ? 'selected' : '';
        $courseOptions .= "<option value='{$course['course']}' $selected>" . htmlspecialchars($course['course']) . "</option>";
    }
} else {
    die("SQL Error: " . $conn->error);
}

$yearLevels = ['Section A', 'Section B', 'Section C'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Payments</title>
<link rel="stylesheet" href="../CSS/sidebar.css">
<link rel="stylesheet" href="../CSS/etoaypayment.css">
<style>
    .status.fully-paid { color: green; font-weight: bold; }
    .status.partial { color: orange; font-weight: bold; }
    .status.unpaid { color: red; font-weight: bold; }
</style>
</head>
<body>
<?php include '../PHP/sidebar.php'; ?>

<section class="home">
    <div class="search-bar-container">
        <input type="text" id="searchInput" placeholder="Search student ID...">
        <form method="GET" style="display: flex; gap: 10px;">
            <select name="course_filter" onchange="this.form.submit()">
                <option value="">Filter by Course</option>
                <?= $courseOptions ?>
            </select>
            <select name="year_filter" onchange="this.form.submit()">
                <option value="">Filter by Section</option>
                <?php foreach ($yearLevels as $level): ?>
                    <option value="<?= $level ?>" <?= $selectedSection == $level ? 'selected' : '' ?>><?= $level ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status_filter" onchange="this.form.submit()">
                <option value="">Filter by Status</option>
                <option value="Fully Paid" <?= $selectedStatus === 'Fully Paid' ? 'selected' : '' ?>>Fully Paid</option>
                <option value="Partial" <?= $selectedStatus === 'Partial' ? 'selected' : '' ?>>Partial</option>
                <option value="Unpaid" <?= $selectedStatus === 'Unpaid' ? 'selected' : '' ?>>Unpaid</option>
            </select>
        </form>
    </div>

    <div class="table-container">
        <div class="responsive-table-wrapper">
            <table class="modern-table" id="studentTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Course</th>
                        <th>Section</th>
                        <th>Tuition</th>
                        <th>Payment Type</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($studentsResult && $studentsResult->num_rows > 0) {
                    while ($row = $studentsResult->fetch_assoc()) {
                        $fullName = htmlspecialchars("{$row['first_name']} {$row['middle_name']} {$row['last_name']}");
                        $statusClass = strtolower(str_replace(' ', '-', $row['payment_status']));
                        echo "<tr>
                            <td>{$row['student_id']}</td>
                            <td>{$fullName}</td>
                            <td>{$row['course']}</td>
                            <td>{$row['section']}</td>
                            <td>₱" . number_format($row['tuition_amount'], 2) . "</td>
                            <td>{$row['tuition_type']}</td>
                            <td class='status {$statusClass}'>{$row['payment_status']}</td>
                            <td><button class='pay-btn' data-id='{$row['student_id']}' data-name='{$fullName}' data-tuition='{$row['tuition_amount']}' data-balance='{$row['balance']}'>Pay</button></td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No student records found.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>💳 Record Payment</h2>
            <form action="RecordPayment.php" method="POST">
                <input type="hidden" name="student_id" id="modalStudentId">
                <label>Student</label><input type="text" id="studentName" readonly>
                <label>Tuition</label><input type="text" id="tuitionDisplay" readonly>
                <label>Remaining Balance</label><input type="text" id="balanceDisplay" readonly>
                <label>Amount to Pay</label><input type="number" name="amount" required>
                <label>Payment Date</label><input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" required>
                <button type="submit">Submit Payment</button>
            </form>
        </div>
    </div>

    <?php if ($totalRecords > 0): ?>
    <div class="pagination-ui">
        <form method="GET">
            <label>Rows:</label>
            <select name="limit" onchange="this.form.submit()">
                <?php foreach ($limitOptions as $opt): ?>
                    <option value="<?= $opt ?>" <?= $opt == $recordsPerPage ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="course_filter" value="<?= htmlspecialchars($selectedCourse) ?>">
            <input type="hidden" name="year_filter" value="<?= htmlspecialchars($selectedSection) ?>">
            <input type="hidden" name="status_filter" value="<?= htmlspecialchars($selectedStatus) ?>">
            <button type="submit" name="page" value="<?= max($page - 1, 1) ?>" <?= $page <= 1 ? 'disabled' : '' ?>>« Prev</button>
            <span>Page <?= $page ?> of <?= $totalPages ?></span>
            <button type="submit" name="page" value="<?= min($page + 1, $totalPages) ?>" <?= $page >= $totalPages ? 'disabled' : '' ?>>Next »</button>
        </form>
    </div>
    <?php endif; ?>
</section>

<script>
document.getElementById('searchInput').addEventListener('keyup', function () {
    const searchTerm = this.value.toLowerCase();
    document.querySelectorAll('#studentTable tbody tr').forEach(row => {
        const studentId = row.cells[0].textContent.toLowerCase();
        row.style.display = studentId.includes(searchTerm) ? '' : 'none';
    });
});

document.querySelectorAll('.pay-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('modalStudentId').value = this.dataset.id;
        document.getElementById('studentName').value = this.dataset.name;
        document.getElementById('tuitionDisplay').value = `₱${parseFloat(this.dataset.tuition).toLocaleString()}`;
        document.getElementById('balanceDisplay').value = `₱${parseFloat(this.dataset.balance).toLocaleString()}`;
        document.getElementById('paymentModal').classList.add('show');
    });
});

document.querySelector('.close-modal').addEventListener('click', () => {
    document.getElementById('paymentModal').classList.remove('show');
});

window.addEventListener('click', function (e) {
    const modal = document.getElementById('paymentModal');
    if (e.target === modal) {
        modal.classList.remove('show');
    }
});
</script>

</body>
</html>