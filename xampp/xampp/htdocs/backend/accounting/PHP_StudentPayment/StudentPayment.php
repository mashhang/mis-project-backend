<?php
include '../PHP/DataConnect.php';

// Filter students with pagination and status
function filterStudents($conn, $course_id = null, $year_level = null, $status_filter = '', $limit = 10, $offset = 0) {
    $query = "SELECT s.*, c.course_name,
                     (tuition_amount - amount_paid) AS balance,
                     CASE 
                        WHEN amount_paid >= tuition_amount THEN 'Fully Paid'
                        WHEN amount_paid = 0 THEN 'Unpaid'
                        ELSE 'Partial'
                     END AS payment_status
              FROM students s 
              JOIN courses c ON s.course_id = c.course_id 
              WHERE 1";

    if ($course_id) {
        $query .= " AND s.course_id = " . intval($course_id);
    }
    if ($year_level) {
        $query .= " AND s.year_level = '" . $conn->real_escape_string($year_level) . "'";
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

// Filters
$selectedCourse = $_GET['course_filter'] ?? '';
$selectedYear   = $_GET['year_filter'] ?? '';
$selectedStatus = $_GET['status_filter'] ?? '';

// Pagination settings
$limitOptions = [10, 30, 100];
$recordsPerPage = isset($_GET['limit']) && in_array((int)$_GET['limit'], $limitOptions) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
$offset = ($page - 1) * $recordsPerPage;

// Count total records for pagination
$countQuery = "SELECT COUNT(*) AS total FROM (
    SELECT (tuition_amount - amount_paid) AS balance,
           CASE 
              WHEN amount_paid >= tuition_amount THEN 'Fully Paid'
              WHEN amount_paid = 0 THEN 'Unpaid'
              ELSE 'Partial'
           END AS payment_status
    FROM students s
    JOIN courses c ON s.course_id = c.course_id 
    WHERE 1";

if ($selectedCourse) {
    $countQuery .= " AND s.course_id = " . intval($selectedCourse);
}
if ($selectedYear) {
    $countQuery .= " AND s.year_level = '" . $conn->real_escape_string($selectedYear) . "'";
}
if ($selectedStatus) {
    $countQuery .= " HAVING payment_status = '" . $conn->real_escape_string($selectedStatus) . "'";
}
$countQuery .= ") AS filtered_students";

$countResult = $conn->query($countQuery);
$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = max(ceil($totalRecords / $recordsPerPage), 1);

// Get paginated students
$studentsResult = filterStudents($conn, $selectedCourse, $selectedYear, $selectedStatus, $recordsPerPage, $offset);

// Fetch course options
$courseOptions = '';
$courseQuery = "SELECT course_id, course_name FROM courses";
$courseResult = $conn->query($courseQuery);
while ($course = $courseResult->fetch_assoc()) {
    $selected = ($selectedCourse == $course['course_id']) ? 'selected' : '';
    $courseOptions .= "<option value='{$course['course_id']}' $selected>" . htmlspecialchars($course['course_name']) . "</option>";
}

// Define year level options
$yearLevels = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/sidebar.css">
    <link rel="stylesheet" href="../CSS/etoaypayment.css">
    <title>Student Management</title>
    <style>
        .status.fully-paid {
            color: green;
            font-weight: bold;
        }
        .status.partial {
            color: orange;
            font-weight: bold;
        }
        .status.unpaid {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

<?php include '../PHP/sidebar.php'; ?>

<section class="home">

    <!-- 🔍 Search, Add, Filter -->
    <div class="search-bar-container">
        <input type="text" id="searchInput" placeholder="Search student number...">

        <form method="GET" style="display: flex; gap: 10px; align-items: center;">
            <select name="course_filter" onchange="this.form.submit()">
                <option value="">Filter by Course</option>
                <?php echo $courseOptions; ?>
            </select>

            <select name="year_filter" onchange="this.form.submit()">
                <option value="">Filter by Year Level</option>
                <?php
                foreach ($yearLevels as $level) {
                    $selected = ($selectedYear == $level) ? 'selected' : '';
                    echo "<option value='$level' $selected>$level</option>";
                }
                ?>
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
        <th>Student Number</th>
        <th>Full Name</th>
        <th>Course</th>
        <th>Year Level</th>
        <th>Tuition</th>
        <th>Payment Type</th>
        <th>Status</th>
        <th>Action</th> <!-- NEW -->
    </tr>
</thead>
<tbody>
    <?php
    if ($studentsResult && $studentsResult->num_rows > 0) {
        while ($row = $studentsResult->fetch_assoc()) {
            $studentId = $row['student_id'];
            $studentNumber = htmlspecialchars($row['student_number']);
            $fullName = htmlspecialchars($row['full_name']);
            $courseName = htmlspecialchars($row['course_name']);
            $yearLevel = $row['year_level'];
            $tuition = number_format($row['tuition_amount'], 2);
            $paymentType = $row['payment_type'];
            $status = $row['payment_status'];
            $statusClass = strtolower(str_replace(' ', '-', $status));
            $balance = number_format($row['tuition_amount'] - $row['amount_paid'], 2, '.', '');
            $tuitionRaw = number_format($row['tuition_amount'], 2, '.', '');

            echo "<tr>
                <td>{$studentId}</td>
                <td class='student-number'>{$studentNumber}</td>
                <td>{$fullName}</td>
                <td>{$courseName}</td>
                <td>{$yearLevel}</td>
                <td>₱{$tuition}</td>
                <td>{$paymentType}</td>
                <td class='status {$statusClass}'>{$status}</td>
                <td>
                    <button class='pay-btn'
    data-id='{$row['student_id']}'
    data-name='" . htmlspecialchars($row['full_name']) . "'
    data-tuition='{$row['tuition_amount']}'
    data-balance='" . ($row['tuition_amount'] - $row['amount_paid']) . "'>
    Pay
</button>
                </td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='9'>No student records found.</td></tr>";
    }
    ?>
</tbody>
        </table>
    </div>
</div>
<div id="paymentModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2 class="modal-title">💳 Record Payment</h2>

        <form action="RecordPayment.php" method="POST">
            <input type="hidden" name="student_id" id="modalStudentId">

            <label for="studentName">Student</label>
            <input type="text" id="studentName" readonly>

            <label for="tuitionDisplay">Tuition</label>
            <input type="text" id="tuitionDisplay" readonly>

            <label for="balanceDisplay">Remaining Balance</label>
            <input type="text" id="balanceDisplay" readonly>

            <label for="amount">Amount to Pay</label>
            <input type="number" name="amount" placeholder="₱0.00" step="0.01" required>

            <label for="payment_date">Payment Date</label>
            <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" required>

            <button type="submit" class="submit-btn">Submit Payment</button>
        </form>
    </div>
</div>
    <?php if ($totalRecords > 0): ?>
    <div class="pagination-ui">
        <form method="GET" class="pagination-form">
            <label for="limit">Rows per page:</label>
            <select name="limit" id="limit" onchange="this.form.submit()">
                <?php foreach ($limitOptions as $opt): ?>
                    <option value="<?= $opt ?>" <?= $opt == $recordsPerPage ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
            </select>

            <input type="hidden" name="course_filter" value="<?= htmlspecialchars($selectedCourse) ?>">
            <input type="hidden" name="year_filter" value="<?= htmlspecialchars($selectedYear) ?>">
            <input type="hidden" name="status_filter" value="<?= htmlspecialchars($selectedStatus) ?>">

            <button type="submit" name="page" value="<?= max($page - 1, 1) ?>" <?= $page <= 1 ? 'disabled' : '' ?>>« Prev</button>

            <span>Page <?= $page ?> of <?= $totalPages ?></span>

            <button type="submit" name="page" value="<?= min($page + 1, $totalPages) ?>" <?= $page >= $totalPages ? 'disabled' : '' ?>>Next »</button>

            <span>Jump to:</span>
            <input type="number" name="page" min="1" max="<?= $totalPages ?>" style="width: 60px;" value="<?= $page ?>">
            <button type="submit">Go</button>
        </form>
    </div>
    <?php endif; ?>
</section>

<script>
// 🔍 Live search for student_number
document.getElementById('searchInput').addEventListener('keyup', function () {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#studentTable tbody tr');

    rows.forEach(row => {
        const studentNumber = row.querySelector('.student-number').textContent.toLowerCase();
        row.style.display = studentNumber.includes(searchTerm) ? '' : 'none';
    });
});

const modal = document.getElementById('paymentModal');

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
