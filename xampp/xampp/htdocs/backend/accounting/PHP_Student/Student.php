<?php
include '../PHP/DataConnect.php';

// Auto-generate student number
function generateStudentNumber($conn) {
    $query = "SELECT MAX(id) AS max_id FROM enrollments";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $nextId = $row['max_id'] + 1;
    return date('Y') . '-' . str_pad($nextId, 2, '0', STR_PAD_LEFT); // e.g., 2025-01
}

// Fetch enrollments with pagination and filtering
function filterEnrollments($conn, $semesterFilter = null, $limit = 10, $offset = 0) {
    $query = "SELECT * FROM enrollments WHERE 1";

    if ($semesterFilter) {
        $query .= " AND semester = '" . $conn->real_escape_string($semesterFilter) . "'";
    }

    $query .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
    return $conn->query($query);
}

$autoStudentNumber = generateStudentNumber($conn);

// Filters
$selectedSemester = $_GET['year_filter'] ?? '';

// Pagination settings
$limitOptions = [10, 30, 100];
$recordsPerPage = isset($_GET['limit']) && in_array((int)$_GET['limit'], $limitOptions) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
$offset = ($page - 1) * $recordsPerPage;

// Count total records
$countQuery = "SELECT COUNT(*) AS total FROM enrollments WHERE 1";
if ($selectedSemester) {
    $countQuery .= " AND semester = '" . $conn->real_escape_string($selectedSemester) . "'";
}
$countResult = $conn->query($countQuery);
$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = max(ceil($totalRecords / $recordsPerPage), 1);

// Fetch paginated records
$studentsResult = filterEnrollments($conn, $selectedSemester, $recordsPerPage, $offset);

// Define semester options
$yearLevels = ['1st Semester', '2nd Semester'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/sidebar.css">
    <link rel="stylesheet" href="../CSS/Student.css">
    <title>Student Management</title>
</head>
<body>

<?php include '../PHP/sidebar.php'; ?>

<section class="home">

    <div class="search-bar-container">
        <input type="text" id="searchInput" placeholder="Search student number...">

        <form method="GET" style="display: flex; gap: 10px; align-items: center;">
            <select name="year_filter" onchange="this.form.submit()">
                <option value="">Filter by Semester</option>
                <?php foreach ($yearLevels as $level): ?>
                    <option value="<?= $level ?>" <?= ($selectedSemester === $level) ? 'selected' : '' ?>><?= $level ?></option>
                <?php endforeach; ?>
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
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Semester</th>
                        <th>School Year</th>
                        <th>Date of Birth</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($studentsResult && $studentsResult->num_rows > 0) {
                        while ($row = $studentsResult->fetch_assoc()) {
                            $fullName = htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']);
                            echo "<tr>
                                <td>{$row['id']}</td>
                                <td class='student-number'>" . htmlspecialchars($row['student_id']) . "</td>
                                <td>{$fullName}</td>
                                <td>" . htmlspecialchars($row['contact_number']) . "</td>
                                <td>" . htmlspecialchars($row['address']) . "</td>
                                <td>{$row['semester']}</td>
                                <td>{$row['school_year']}</td>
                                <td>{$row['date_of_birth']}</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8'>No student records found.</td></tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
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

            <input type="hidden" name="year_filter" value="<?= htmlspecialchars($selectedSemester) ?>">

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
document.getElementById('searchInput').addEventListener('keyup', function () {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#studentTable tbody tr');

    rows.forEach(row => {
        const studentNumber = row.querySelector('.student-number').textContent.toLowerCase();
        row.style.display = studentNumber.includes(searchTerm) ? '' : 'none';
    });
});

document.getElementById('AddStudentModal').addEventListener('click', () => {
    document.getElementById('testModal').style.display = 'block';
});

document.querySelector('.close-button').addEventListener('click', () => {
    document.getElementById('testModal').style.display = 'none';
});

window.addEventListener('click', (event) => {
    const modal = document.getElementById('testModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});
</script>

</body>
</html>
