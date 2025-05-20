<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/sidebar.css">
    <title>Sidebar</title>
    <style>
        .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 999;
}

.modal-content {
    background: #fff;
    padding: 1.5rem 2rem;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
}

.modal-buttons {
    margin-top: 1.2rem;
    display: flex;
    justify-content: center;
    gap: 1rem;
}

.modal-buttons .confirm {
    background-color: #dc2626;
    color: white;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

.modal-buttons .cancel {
    background-color: #9ca3af;
    color: white;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

    </style>
</head>
<body>
    <nav class="sidebar close">
        <header>
            <div class="image-text">
                <span class="image">
                    <img src="../image/Loalogo.png" alt="logo">
                </span>
                <div class="text header-text">
                    <span class="name">Lyceum</span>
                    <span class="profession">Of Alabang</span>
                </div>
            </div>
            <i class="bx bx-chevron-right toggle"></i>
        </header>

        <div class="menu-bar">
            <div class="menu">
                <ul class="menu-links">
                    <!-- Dashboard -->
                    <!-- Dashboard -->
<!-- Dashboard -->
<li>
    <a href="../PHP_Dashboard/Dashboard.php">
        <i class='bx bx-home-alt icon'></i>
        <span class="text nav-text">Dashboard</span>
    </a>
</li>
<!-- Student -->
<li>
    <a href="../PHP_Student/Student.php">
        <i class='bx bx-id-card icon'></i>
        <span class="text nav-text">Student</span>
    </a>
</li>
<!-- Tuition Fees -->
<li>
    <a href="../PHP_Tuition/Tuition.php">
        <i class='bx bx-money-withdraw icon'></i> <!-- Updated for tuition collection -->
        <span class="text nav-text">Tuition Fees</span>
    </a>
</li>

<!-- Student Payments -->
<li>
    <a href="../PHP_StudentPayment/StudentPayment.php">
        <i class='bx bx-wallet icon'></i> <!-- Updated for personal/student payment -->
        <span class="text nav-text">Student Payments</span>
    </a>
</li>

<!-- Expense Tracking -->
<li>
    <a href="../PHP_Expenses/Expenses.php">
        <i class='bx bx-spreadsheet icon'></i> <!-- Updated for tracking or breakdown -->
        <span class="text nav-text">Expenses</span>
    </a>
</li>

<!-- Payroll -->
<li>
    <a href="../PHP_Payroll/Payroll.php">
        <i class='bx bx-user-voice icon'></i> <!-- More HR/payroll-related than check -->
        <span class="text nav-text">Payroll</span>
    </a>
</li>


            </div>

            <div class="bottom-content">
                <li class="nav-link">
                    <a href="#" id="logoutTrigger">
                        <i class='bx bx-log-out icon'></i>
                        <span class="text nav-text">Logout</span>
                    </a>
                </li>
            </div>
        </div>
    </nav>

    <!-- 🔒 Logout Confirmation Modal -->
<div id="logoutModal" class="modal-overlay">
    <div class="modal-content">
        <h3>Are you sure you want to logout?</h3>
        <div class="modal-buttons">
            <button id="confirmLogout" class="confirm">Yes, Logout</button>
            <button id="cancelLogout" class="cancel">Cancel</button>
        </div>
    </div>
</div>

<script>
    const logoutTrigger = document.getElementById("logoutTrigger");
    const logoutModal = document.getElementById("logoutModal");
    const confirmLogout = document.getElementById("confirmLogout");
    const cancelLogout = document.getElementById("cancelLogout");

    logoutTrigger.addEventListener("click", function(e) {
        e.preventDefault();
        logoutModal.style.display = "flex";
    });

    cancelLogout.addEventListener("click", function() {
        logoutModal.style.display = "none";
    });

    confirmLogout.addEventListener("click", function() {
        window.location.href = "../PHP/index.php"; // ✅ Replace with your actual logout path
    });

    // Optional: Close modal on outside click
    window.onclick = function(event) {
        if (event.target === logoutModal) {
            logoutModal.style.display = "none";
        }
    };
</script>
    <script src="../JS/sidebar.js"></script>
</body>
</html>
