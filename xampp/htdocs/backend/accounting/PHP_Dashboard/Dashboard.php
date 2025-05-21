<?php
include '../PHP/DataConnect.php';

// === SUMMARY STATS ===
$totalStudents = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'];
$totalCollected = $conn->query("SELECT SUM(amount_paid) AS total FROM students")->fetch_assoc()['total'] ?? 0;
$unpaidStudents = $conn->query("SELECT COUNT(*) AS total FROM students WHERE amount_paid < tuition_amount")->fetch_assoc()['total'];
$totalExpenses = $conn->query("SELECT SUM(amount) AS total FROM expenses")->fetch_assoc()['total'] ?? 0;
$totalPayroll = $conn->query("SELECT SUM(net_pay) AS total FROM payrolls")->fetch_assoc()['total'] ?? 0;

// === MONTHLY EXPENSES LINE CHART ===
$monthlyExpenses = array_fill(1, 12, 0);
$expenseQuery = "SELECT MONTH(expense_date) AS month, SUM(amount) AS total 
                 FROM expenses 
                 WHERE YEAR(expense_date) = YEAR(CURRENT_DATE) 
                 GROUP BY MONTH(expense_date)";
$expenseResult = $conn->query($expenseQuery);

while ($row = $expenseResult->fetch_assoc()) {
    $monthlyExpenses[(int)$row['month']] = (float)$row['total'];
}


// === RECENT TRANSACTIONS ===
$recentPayments = $conn->query("SELECT s.full_name, c.course_name, s.year_level, s.amount_paid, s.enrollment_date
                                FROM students s
                                INNER JOIN courses c ON s.course_id = c.course_id
                                ORDER BY s.enrollment_date DESC 
                                LIMIT 5");

// === MONTHLY TUITION COLLECTION DATA ===
$monthlyTuition = array_fill(1, 12, 0); // initialize with zeros
$tuitionQuery = "SELECT MONTH(enrollment_date) AS month, SUM(amount_paid) AS total
                 FROM students
                 WHERE YEAR(enrollment_date) = YEAR(CURRENT_DATE)
                 GROUP BY MONTH(enrollment_date)";
$result = $conn->query($tuitionQuery);
while ($row = $result->fetch_assoc()) {
    $monthlyTuition[(int)$row['month']] = (float)$row['total'];
}

$courseNames = [];
$studentCounts = [];

$query = "SELECT c.course_name, COUNT(s.student_id) AS student_count
          FROM students s
          INNER JOIN courses c ON s.course_id = c.course_id
          GROUP BY c.course_name
          ORDER BY student_count DESC";

$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $courseNames[] = $row['course_name'];
    $studentCounts[] = (int)$row['student_count'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="../CSS/sidebar.css">
  <link rel="stylesheet" href="../CSS/Dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <title>Dashboard</title>
  <style>
    .dashboard-cards {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        padding: 2rem;
    }
    .card {
        flex: 1 1 200px;
        background-color: #2C3E50;
        padding: 1.5rem;
        border-radius: 15px;
        color: white;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        transition: transform 0.2s ease;
    }
    .card:hover {
        transform: translateY(-5px);
    }
    .card h3 {
        font-size: 1.2rem;
        margin-bottom: 0.5rem;
        color: #facc15;
    }
    .card p {
        font-size: 1.8rem;
        font-weight: bold;
    }
.charts-row {
    display: flex;
    justify-content: space-between; /* spread evenly */
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 2rem;
    flex-wrap: nowrap; /* keep in one row */
    overflow-x: auto;  /* scroll on small screens */
}

.chart-container {
    flex: 1 1 30%;
    max-width: 32%;
    min-width: 300px;
    background: #fff;
    padding: 0.75rem 1rem;
    border-radius: 15px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

/* Optional: smaller headers for space */
.chart-container h2 {
    font-size: 1rem;
    margin-bottom: 0.75rem;
}
    .table-container {
        margin: 2rem;
        background-color: #fff;
        padding: 1rem;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }

    th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #2C3E50;
        color: #fff;
    }

    h2 {
        margin-top: 0;
        color: #2C3E50;
    }
  </style>
</head>
<body>

<?php include '../PHP/sidebar.php'; ?>

<section class="home">


  <div class="dashboard-cards">
      <div class="card">
          <h3>Total Students</h3>
          <p><?= $totalStudents ?></p>
      </div>
      <div class="card">
          <h3>Tuition Collected</h3>
          <p>₱<?= number_format($totalCollected, 2) ?></p>
      </div>
      <div class="card">
          <h3>Unpaid Students</h3>
          <p><?= $unpaidStudents ?></p>
      </div>
      <div class="card">
          <h3>Total Expenses</h3>
          <p>₱<?= number_format($totalExpenses, 2) ?></p>
      </div>
      <div class="card">
          <h3>Total Payroll Released</h3>
          <p>₱<?= number_format($totalPayroll, 2) ?></p>
      </div>
  </div>

<div class="charts-row">
  <div class="chart-container" id="monthly-tuition">
      <h2>Monthly Tuition Collection (₱)</h2>
      <canvas id="monthlyTuitionChart"></canvas>
  </div>

  <div class="chart-container" id="students-per-course">
      <h2>Students per Course</h2>
      <canvas id="studentsPerCourseChart"></canvas>
  </div>

  <div class="chart-container" id="monthly-expenses">
      <h2>Monthly Expenses (₱)</h2>
      <canvas id="monthlyExpensesChart"></canvas>
  </div>
</div>

  <div class="table-container">
    <h2>Recent Student Payments</h2>
    <table>
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Course</th>
                <th>Year Level</th>
                <th>Amount Paid</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $recentPayments->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= htmlspecialchars($row['course_name']) ?></td>
                <td><?= htmlspecialchars($row['year_level']) ?></td>
                <td>₱<?= number_format($row['amount_paid'], 2) ?></td>
                <td><?= htmlspecialchars($row['enrollment_date']) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
  </div>
</section>

<script>
  const expensesCtx = document.getElementById('monthlyExpensesChart').getContext('2d');

new Chart(expensesCtx, {
  type: 'line',
  data: {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
             'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    datasets: [{
      label: 'Expenses',
      data: <?= json_encode(array_values($monthlyExpenses)) ?>,
      borderColor: '#dc3545',
      backgroundColor: 'rgba(220,53,69,0.1)',
      fill: true,
      tension: 0.3,
      pointRadius: 4,
      pointBackgroundColor: '#dc3545'
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: function(value) {
            return '₱' + value.toLocaleString();
          }
        }
      }
    },
    plugins: {
      legend: {
        position: 'top'
      }
    }
  }
});

  const monthlyCtx = document.getElementById('monthlyTuitionChart').getContext('2d');
  new Chart(monthlyCtx, {
    type: 'bar',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      datasets: [{
        label: 'Tuition Collected',
        data: <?= json_encode(array_values($monthlyTuition)) ?>,
        backgroundColor: '#facc15'
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return '₱' + value.toLocaleString();
            }
          }
        }
      },
      plugins: {
        legend: {
          display: false
        }
      }
    }
  });

   const courseChartCtx = document.getElementById('studentsPerCourseChart').getContext('2d');

  new Chart(courseChartCtx, {
    type: 'bar',
    data: {
      labels: <?= json_encode($courseNames) ?>,
      datasets: [{
        label: 'Number of Students',
        data: <?= json_encode($studentCounts) ?>,
        backgroundColor: '#00BFFF'
      }]
    },
    options: {
      responsive: true,
      indexAxis: 'y',
      scales: {
        x: {
          beginAtZero: true,
          ticks: {
            stepSize: 1
          }
        }
      },
      plugins: {
        legend: {
          display: false
        }
      }
    }
  });
</script>

</body>
</html>
