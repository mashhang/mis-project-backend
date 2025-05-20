<?php include '../PHP/DataConnect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="../CSS/index.css">
  <title>Register</title>
  <style>
    .form-message {
      margin-top: 5px;
      margin-bottom: 8px;
      padding: 10px;
      border-radius: 6px;
      font-size: 0.95rem;
      text-align: center;
    }
    .form-message.error {
      background-color: #fee2e2;
      color: #b91c1c;
    }
    .form-message.success {
      background-color: #d1fae5;
      color: #065f46;
    }
  </style>
</head>
<body>

<div class="login-container">
  <div class="logo-section">
    <img src="../image/Loalogo.png" alt="Logo">
  </div>

  <div class="login-box">
    <h2>Register</h2>

    <form action="../PHP/RegisterProcess.php" method="POST">
      <div class="input-group">
        <i class='bx bxs-user'></i>
        <input type="text" name="full_name" placeholder="Full Name" required>
      </div>
      <div class="input-group">
        <i class='bx bx-envelope'></i>
        <input type="text" name="username" placeholder="Username" required>
      </div>
      <div class="input-group">
        <i class='bx bx-mail-send'></i>
        <input type="email" name="email" placeholder="Email" required>
      </div>
      <div class="input-group">
        <i class='bx bxs-lock-alt'></i>
        <input type="password" name="password" placeholder="Password" required>
      </div>

      <!-- ✅ Display success or error message here -->
      <?php if (isset($_GET['error'])): ?>
        <div class="form-message error"><?= htmlspecialchars($_GET['error']) ?></div>
      <?php elseif (isset($_GET['success'])): ?>
        <div class="form-message success"><?= htmlspecialchars($_GET['success']) ?></div>
      <?php endif; ?>

      <button type="submit">Register</button>
      <div class="register">
        Already have an account? <a href="../PHP/index.php">Login</a>
      </div>
    </form>
  </div>
</div>

</body>
</html>
