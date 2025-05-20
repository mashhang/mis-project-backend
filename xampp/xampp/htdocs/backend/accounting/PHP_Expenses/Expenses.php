<?php
include '../PHP/DataConnect.php';

// Fetch expenses
$query = "SELECT * FROM expenses ORDER BY expense_date DESC";
$result = $conn->query($query);

// Fetch totals
$totalQuery = "SELECT SUM(amount) AS grand_total, COUNT(*) AS total_expenses FROM expenses";
$totalResult = $conn->query($totalQuery);
$totals = $totalResult->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/sidebar.css">
    <link rel="stylesheet" href="../CSS/Expenses.css">
    <title>Expense Records</title>
</head>
<body>

<?php include '../PHP/sidebar.php'; ?>

<section class="home">

<!-- ✅ Total Summary Section -->
    <div class="totals-container">
        <div class="total-box">
            <h4>Grand Total</h4>
            <div class="total-value">
                <span>₱</span><?= number_format($totals['grand_total'], 2) ?>
            </div>
        </div>
        <div class="total-box">
            <h4>Total of Expenses</h4>
            <div class="total-value">
                <i class='bx bx-spreadsheet'></i> <?= (int)$totals['total_expenses'] ?>
            </div>
        </div>
    </div>


    <div class="expense-header">
    <button class="btn-new-expense" onclick="openExpenseModal()">+ New Expense</button>
    <button class="btn-print-expense" onclick="printExpenseReport()">🖨️ Print Expenses</button>
    <input type="text" id="expenseSearch" class="expense-search" placeholder="Search by description...">
    
    <select id="categoryFilter" class="expense-filter">
        <option value="">All Categories</option>
        <?php
        $categoryQuery = "SELECT DISTINCT category FROM expenses ORDER BY category ASC";
        $categoryResult = $conn->query($categoryQuery);
        while ($cat = $categoryResult->fetch_assoc()) {
            echo "<option value='" . htmlspecialchars($cat['category']) . "'>" . htmlspecialchars($cat['category']) . "</option>";
        }
        ?>
    </select>
</div>

    <div class="table-container">
        <div class="responsive-table-wrapper">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Amount (₱)</th>
                        <th>Payment Method</th>
                        <th>Notes</th>
                        <th>Expense Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['expense_id'] ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td><?= htmlspecialchars($row['category']) ?></td>
                                <td><?= (int)$row['quantity'] ?></td>
                                <td>₱<?= number_format($row['amount'], 2) ?></td>
                                <td><?= htmlspecialchars($row['payment_method']) ?></td>
                                <td><?= htmlspecialchars($row['notes']) ?></td>
                                <td><?= date('F d, Y', strtotime($row['expense_date'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8">No expenses recorded.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- 🧾 Add Expense Modal -->
<div class="modal" id="expenseModal">
    <div class="modal-content">
        <span class="close-expense-modal" onclick="closeExpenseModal()">&times;</span>
        <h3>Add New Expense</h3>
        <form action="SaveExpense.php" method="POST">
            <input type="text" name="description" placeholder="Description" required>
            <input type="text" name="category" placeholder="Category" required>
            <input type="number" name="quantity" placeholder="Quantity" min="1" required>
            <input type="number" name="amount" placeholder="Amount (₱)" step="0.01" required>
            <select name="payment_method" required>
                <option value="">-- Payment Method --</option>
                <option value="Cash">Cash</option>
                <option value="Check">Check</option>
                <option value="Transfer">Transfer</option>
            </select>
            <textarea name="notes" placeholder="Notes (optional)" rows="3"></textarea>
            <input type="date" name="expense_date" value="<?= date('Y-m-d') ?>" required>
            <button type="submit">Save Expense</button>
        </form>
    </div>
</div>

<script>
function openExpenseModal() {
    document.getElementById('expenseModal').classList.add('show');
}

function closeExpenseModal() {
    document.getElementById('expenseModal').classList.remove('show');
}

// Unified Filter (Description + Category)
document.getElementById("expenseSearch").addEventListener("input", filterExpenses);
document.getElementById("categoryFilter").addEventListener("change", filterExpenses);

function filterExpenses() {
    const searchTerm = document.getElementById("expenseSearch").value.toLowerCase();
    const selectedCategory = document.getElementById("categoryFilter").value.toLowerCase();
    const rows = document.querySelectorAll(".modern-table tbody tr");

    rows.forEach(row => {
        const description = row.cells[1].textContent.toLowerCase(); // Description column
        const category = row.cells[2].textContent.toLowerCase();    // Category column

        const matchDesc = description.includes(searchTerm);
        const matchCat = selectedCategory === "" || category === selectedCategory;

        row.style.display = (matchDesc && matchCat) ? "" : "none";
    });
}

// Print Report
function printExpenseReport() {
    const printWindow = window.open('', '_blank');
    const tableHTML = document.querySelector('.modern-table').outerHTML;

    const currentDate = new Date().toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    const printContent = `
        <html>
        <head>
            <title>Expense Report</title>
            <style>
                @media print {
                    @page {
                        size: A4 portrait;
                        margin: 20mm;
                    }
                }

                body {
                    font-family: 'Segoe UI', sans-serif;
                    font-size: 12px;
                    color: #000;
                    margin: 0;
                    padding: 0 20px;
                }

                .report-header {
                    text-align: center;
                    margin-top: 20px;
                }

                .report-header h2 {
                    font-size: 18px;
                    margin: 0;
                }

                .report-header h4 {
                    font-size: 14px;
                    margin: 4px 0;
                    font-weight: normal;
                }

                .report-header p {
                    font-size: 12px;
                    margin-top: 5px;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }

                th, td {
                    border: 1px solid #444;
                    padding: 6px 8px;
                    text-align: left;
                    vertical-align: top;
                    font-size: 11px;
                }

                th {
                    background-color: #f4f4f4;
                    font-weight: bold;
                }

                .footer-signature {
                    margin-top: 60px;
                    display: flex;
                    justify-content: flex-end;
                }

                .footer-signature div {
                    text-align: center;
                    font-size: 12px;
                }

                .footer-signature p {
                    margin: 2px 0;
                }
            </style>
        </head>
        <body>
            <div class="report-header">
                <h2>Lyceum of Alabang College</h2>
                <h4>Expense Report</h4>
                <p>Date Printed: ${currentDate}</p>
            </div>
            ${tableHTML}
            <div class="footer-signature">
                <div>
                    <p>Prepared By:</p>
                    <br><br><br>
                    <p>_______________________</p>
                </div>
            </div>
        </body>
        </html>
    `;

    printWindow.document.open();
    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.print();
}
</script>


</body>
</html>
