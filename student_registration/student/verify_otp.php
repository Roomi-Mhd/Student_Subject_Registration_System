<?php
// ==============================================
//  student/verify_otp.php — OTP Verification
// ==============================================
require_once '../includes/functions.php';
require_once '../includes/db.php';

if (!isset($_SESSION['pending_email'])) redirect('register.php');
$email = $_SESSION['pending_email'];
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = clean($_POST['otp'] ?? '');

    $stmt = $conn->prepare(
        "SELECT id, name, reg_number, otp, otp_expiry FROM students WHERE email = ? AND is_verified = 0"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        $error = "Account not found or already verified.";
    } elseif ($otp !== $row['otp']) {
        $error = "Invalid OTP. Please check your email.";
    } elseif (strtotime($row['otp_expiry']) < time()) {
        $error = "OTP has expired. Please register again.";
    } else {
        // Final safety check — confirm the registration number and name
        // still exactly match the Department of IT master list before
        // activating the account.
        $eligibility = checkEligibleStudent($conn, $row['reg_number'], $row['name']);

        if (!$eligibility['ok']) {
            // Reject and remove the unverified account
            $del = $conn->prepare("DELETE FROM students WHERE id=?");
            $del->bind_param("i", $row['id']);
            $del->execute();
            unset($_SESSION['pending_email']);
            $error = "Please check your registration number and name. This system is only for Department of IT internal students.";
        } else {
            // Mark verified
            $upd = $conn->prepare("UPDATE students SET is_verified=1, otp=NULL, otp_expiry=NULL WHERE id=?");
            $upd->bind_param("i", $row['id']);
            $upd->execute();

            unset($_SESSION['pending_email']);
            setFlash('success', 'Welcome, Your Registration Successfully');
            redirect('login.php');
        }
    }
}

// Resend OTP
if (isset($_GET['resend'])) {
    $stmt = $conn->prepare("SELECT id, name FROM students WHERE email = ? AND is_verified = 0");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) {
        $otp    = generateOTP();
        $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $conn->prepare("UPDATE students SET otp=?, otp_expiry=? WHERE id=?")
             ->execute_with_bind("ssi", $otp, $expiry, $row['id']);
        // Simplified for XAMPP — just use mail()
        sendOTPEmail($email, $row['name'], $otp);
        $success = "A new OTP has been sent to your email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Verify OTP — SEUSL</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="brand">SEU<span>SL</span> Registration</div>
</nav>

<div class="container">
  <div class="card" style="max-width:420px;margin:0 auto;text-align:center">
    <h2>📧 Verify Your Email</h2>
    <p style="color:#666;margin-bottom:20px;font-size:14px">
      An OTP has been sent to <strong><?= htmlspecialchars($email) ?></strong>.<br>
      Enter the 6-digit code below.
    </p>

    <?= getFlash() ?>
    <?php if ($error)  echo "<div class='alert alert-danger'>$error</div>"; ?>
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

    <form method="POST">
      <div class="form-group">
        <label>OTP Code</label>
        <input type="text" name="otp" maxlength="6" required
               placeholder="Enter 6-digit OTP"
               style="text-align:center;letter-spacing:8px;font-size:22px;font-weight:700">
      </div>
      <button type="submit" class="btn btn-primary btn-block">Verify OTP</button>
    </form>

    <p style="margin-top:16px;font-size:13px;color:#888">
      Didn't receive the OTP?
      <a href="?resend=1" style="color:#1a237e">Resend OTP</a>
    </p>

    <div class="alert alert-info" style="margin-top:16px;text-align:left">
      <strong>⚠️ XAMPP Note:</strong> If PHP <code>mail()</code> is not configured,
      check your database directly:<br>
      <code>SELECT otp FROM students WHERE email='<?= htmlspecialchars($email) ?>';</code>
    </div>
  </div>
</div>
</body>
</html>
