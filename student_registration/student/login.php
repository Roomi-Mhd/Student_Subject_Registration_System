<?php
// ==============================================
//  student/login.php — Student Login
// ==============================================
require_once '../includes/functions.php';
require_once '../includes/db.php';

if (isset($_SESSION['student_id'])) redirect('dashboard.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = "Please enter email and password.";
    } else {
        $stmt = $conn->prepare(
            "SELECT id, name, reg_number, password, is_verified FROM students WHERE email = ?"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();

        if (!$student) {
            $error = "No account found with this email.";
        } elseif (!$student['is_verified']) {
            $error = "Account not verified. <a href='verify_otp.php'>Verify now</a>";
            $_SESSION['pending_email'] = $email;
        } elseif (!password_verify($password, $student['password'])) {
            $error = "Incorrect password.";
        } else {
            // Final safeguard — block login if this account's name +
            // registration number no longer exactly match the
            // Department of IT master list (e.g. legacy/test data).
            $eligibility = checkEligibleStudent($conn, $student['reg_number'], $student['name']);

            if (!$eligibility['ok']) {
                $error = "Please check your registration number and name. This system is only for Department of IT internal students.";
            } else {
                $_SESSION['student_id']   = $student['id'];
                $_SESSION['student_name'] = $student['name'];
                redirect('dashboard.php');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Login — SEUSL</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="brand">SEU<span>SL</span> Registration</div>
  <div class="nav-links">
    <a href="register.php">Register</a>
    <a href="../index.php">Home</a>
  </div>
</nav>

<div class="container">
  <div class="card" style="max-width:420px;margin:0 auto">
    <h2>👨‍🎓 Student Login</h2>

    <?= getFlash() ?>
    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" required
               value="<?= clean($_POST['email'] ?? '') ?>"
               placeholder="your@email.com" autofocus>
      </div>
      <div class="form-group">
        <label>Password</label>
        <div class="pwd-wrap">
          <input type="password" name="password" id="pwd" required
                 maxlength="10" placeholder="Your password">
          <button type="button" class="pwd-toggle" onclick="togglePwd()">👁</button>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>

    <p style="text-align:center;margin-top:16px;font-size:14px;color:#666">
      Don't have an account?
      <a href="register.php" style="color:#1a237e;font-weight:600">Register here</a>
    </p>
  </div>
</div>

<script>
function togglePwd() {
    const p = document.getElementById('pwd');
    p.type = p.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
