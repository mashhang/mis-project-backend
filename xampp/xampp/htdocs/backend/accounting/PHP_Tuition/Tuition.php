<?php
include '../PHP/DataConnect.php';

// Filter from enrollments_with_course
function filterStudents($conn, $course = null, $section = null, $limit = 10, $offset = 0) {
    $query = "SELECT *, 
                     CASE 
                        WHEN amount_paid >= tuition_amount THEN 'Fully Paid'
                        WHEN amount_paid = 0 THEN 'Unpaid'
                        ELSE 'Partial'
                     END AS payment_status
              FROM enrollments_with_course
              WHERE 1";

    if ($course) {
        $query .= " AND course = '" . $conn->real_escape_string($course) . "'";
    }

    if ($section) {
        $query .= " AND section = '" . $conn->real_escape_string($section) . "'";
    }

    $query .= " LIMIT $limit OFFSET $offset";
    return $conn->query($query);
}

// Filters
$selectedCourse = $_GET['course_filter'] ?? '';
$selectedSection = $_GET['section_filter'] ?? '';

// Pagination
$limitOptions = [10, 30, 100];
$recordsPerPage = isset($_GET['limit']) && in_array((int)$_GET['limit'], $limitOptions) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
$offset = ($page - 1) * $recordsPerPage;

// Count total
$countQuery = "SELECT COUNT(*) AS total FROM enrollments_with_course WHERE 1";
if ($selectedCourse) {
    $countQuery .= " AND course = '" . $conn->real_escape_string($selectedCourse) . "'";
}
if ($selectedSection) {
    $countQuery .= " AND section = '" . $conn->real_escape_string($selectedSection) . "'";
}
$countResult = $conn->query($countQuery);
$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = max(ceil($totalRecords / $recordsPerPage), 1);

// Fetch filtered students
$studentsResult = filterStudents($conn, $selectedCourse, $selectedSection, $recordsPerPage, $offset);

// Course options
$courseOptions = '';
$courseQuery = "SELECT DISTINCT course FROM enrollments_with_course ORDER BY course";
$courseResult = $conn->query($courseQuery);
while ($course = $courseResult->fetch_assoc()) {
    $selected = ($selectedCourse == $course['course']) ? 'selected' : '';
    $courseOptions .= "<option value='{$course['course']}' $selected>" . htmlspecialchars($course['course']) . "</option>";
}

// Section options
$sectionOptions = '';
$sectionQuery = "SELECT DISTINCT section FROM enrollments_with_course ORDER BY section";
$sectionResult = $conn->query($sectionQuery);
while ($section = $sectionResult->fetch_assoc()) {
    $selected = ($selectedSection == $section['section']) ? 'selected' : '';
    $sectionOptions .= "<option value='{$section['section']}' $selected>" . htmlspecialchars($section['section']) . "</option>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/sidebar.css">
    <link rel="stylesheet" href="../CSS/Tuition.css">
    <link rel="stylesheet" href="../CSS/edrel.css">
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

    <!-- 🔍 Filters -->
    <div class="search-bar-container">
        <input type="text" id="searchInput" placeholder="Search student number...">
        <form method="GET" style="display: flex; gap: 10px; align-items: center;">
            <select name="course_filter" onchange="this.form.submit()">
                <option value="">Filter by Course</option>
                <?php echo $courseOptions; ?>
            </select>
            <select name="section_filter" onchange="this.form.submit()">
                <option value="">Filter by Section</option>
                <?php echo $sectionOptions; ?>
            </select>
        </form>
    </div>

 <!-- 📊 Table -->
 <div class="table-container">
        <div class="responsive-table-wrapper">
            <table class="modern-table" id="studentTable">
            <thead>
    <tr>
        <th>Student ID</th>
        <th>Full Name</th>
        <th>Contact</th>
        <th>Address</th>
        <th>Semester</th>
        <th>School Year</th>
        <th>Birthdate</th>
        <th>Course</th>
        <th>Section</th>
        <th>Action</th> <!-- 👈 New column -->
    </tr>
</thead>
<tbody>
<?php
if ($studentsResult && $studentsResult->num_rows > 0) {
    while ($row = $studentsResult->fetch_assoc()) {
        $fullName = htmlspecialchars("{$row['first_name']} {$row['middle_name']} {$row['last_name']}");
        echo "<tr>
            <td>{$row['student_id']}</td>
            <td>{$fullName}</td>
            <td>{$row['contact_number']}</td>
            <td>{$row['address']}</td>
            <td>{$row['semester']}</td>
            <td>{$row['school_year']}</td>
            <td>{$row['date_of_birth']}</td>
            <td>{$row['course']}</td>
            <td>{$row['section']}</td>
            <td>
                <button class='open-modal-btn' data-student-id=\"{$row['student_id']}\" data-name=\"{$fullName}\">Add Tuition</button>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='11'>No student records found.</td></tr>";
}
?>
</tbody>
           
            </table>
        </div>
    </div>

<?php
if ($studentsResult && $studentsResult->num_rows > 0) {
    while ($row = $studentsResult->fetch_assoc()) {
        $fullName = htmlspecialchars("{$row['first_name']} {$row['middle_name']} {$row['last_name']}");
        echo "<tr>
            <td>{$row['student_id']}</td>
            <td>{$fullName}</td>
            <td>{$row['contact_number']}</td>
            <td>{$row['address']}</td>
            <td>{$row['semester']}</td>
            <td>{$row['school_year']}</td>
            <td>{$row['date_of_birth']}</td>
            <td>{$row['course']}</td>
            <td>{$row['section']}</td>
            <td class='status " . strtolower(str_replace(' ', '-', $row['payment_status'] ?? '')) . "'>" . ($row['payment_status'] ?? '-') . "</td>
            <td>
                <button class='open-modal-btn' data-student-id=\"{$row['student_id']}\" data-name=\"{$fullName}\">Add Tuition</button>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='11'>No student records found.</td></tr>";
}
?>
</tbody>

<!-- 💸 Add Tuition Modal -->
<div id="tuitionModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Add Tuition Fee</h3>
        <form id="tuitionForm">
            <input type="hidden" id="modalStudentId" name="student_id">
            <div>
                <label for="modalStudentName">Student Name:</label>
                <input type="text" id="modalStudentName" disabled>
            </div>
            <div>
                <label for="tuitionAmount">Tuition Amount:</label>
                <input type="number" id="tuitionAmount" name="tuition_amount" required>
            </div>
            <div>
                <label for="tuitionType">Tuition Type:</label>
                <select id="tuitionType" name="tuition_type" required>
                    <option value="">-- Select --</option>
                    <option value="Full Payment">Full Payment</option>
                    <option value="Installment">Installment</option>
                </select>
            </div>
            <button type="submit">Submit</button>
        </form>
    </div>
</div>


    <!-- 📄 Pagination -->
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
            <input type="hidden" name="section_filter" value="<?= htmlspecialchars($selectedSection) ?>">

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

<!-- 🔍 Live search -->

</body>
</html>
<script>
document.getElementById('searchInput').addEventListener('keyup', function () {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#studentTable tbody tr');
    rows.forEach(row => {
        const studentId = row.cells[0].textContent.toLowerCase();
        row.style.display = studentId.includes(searchTerm) ? '' : 'none';
    });
});

// Open modal and populate student data
document.querySelectorAll('.open-modal-btn').forEach(button => {
    button.addEventListener('click', function () {
        const studentId = this.getAttribute('data-student-id');
        const fullName = this.getAttribute('data-name');

        document.getElementById('modalStudentId').value = studentId;
        document.getElementById('modalStudentName').value = fullName;

        document.getElementById('tuitionModal').style.display = 'flex';
    });
});

// Close modal
document.querySelector('.modal .close').addEventListener('click', () => {
    document.getElementById('tuitionModal').style.display = 'none';
});

// Submit tuition form via AJAX
document.getElementById('tuitionForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('../PHP_Tuition/submit_tuition.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Tuition successfully added.');
            document.getElementById('tuitionModal').style.display = 'none';
            window.location.reload();
        } else {
            alert('Error: ' + result.error);
        }
    })
    .catch(error => {
        alert('Submission failed.');
        console.error(error);
    });
});
</script>
