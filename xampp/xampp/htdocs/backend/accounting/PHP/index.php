<?php include '../PHP/DataConnect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="../CSS/index.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

  <title>Login</title>
 <style>
.back-button {
  position: absolute;
  top: 1rem;
  left: 1rem;
  font-size: 0.875rem;
  color: #6b7280;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  transition: color 0.3s ease;
}

.back-button:hover {
  color: #2563eb;
  text-decoration: underline;
}

.back-icon {
  font-size: 2.2rem; /* Increase this for a larger icon */
}


 </style>
</head>
<body>

<a href="http://localhost:3000/" class="back-button">
  <i class='bx bx-arrow-back back-icon'></i>
</a>

<div class="login-container">
  <div class="logo-section">
    <img src="../image/Loalogo.png" alt="Logo">
  </div>

  <div class="login-box">
    <h2>Login</h2>
    <form action="../PHP/LoginProcess.php" method="POST">
      <div class="input-group">
        <i class='bx bxs-user'></i>
        <input type="text" name="username" placeholder="Username" required>
      </div>
      <div class="input-group">
        <i class='bx bxs-lock-alt'></i>
        <input type="password" name="password" placeholder="Password" required>
      </div>
      <button type="submit">Sign In</button>
      <div class="register">
        Don’t have an account? <a href="register.php">Register</a>
      </div>
    </form>
  </div>
</div>

</body>
</html>
