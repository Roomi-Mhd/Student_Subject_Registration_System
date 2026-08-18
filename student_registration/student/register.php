<?php
// ==============================================
//  student/register.php — Student Registration
// ==============================================
require_once '../includes/functions.php';
require_once '../includes/db.php';

if (isset($_SESSION['student_id'])) redirect('dashboard.php');

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = clean($_POST['name'] ?? '');
    $reg_number = strtoupper(clean($_POST['reg_number'] ?? ''));
    $email      = clean($_POST['email'] ?? '');
    $mobile     = clean($_POST['mobile'] ?? '');
    $address    = clean($_POST['address'] ?? '');
    $department = clean($_POST['department'] ?? '');
    $semester   = (int)($_POST['semester'] ?? 0);
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';

    // Validations
    if (!$name || !$reg_number || !$email || !$mobile || !$address || !$department || !$semester || !$password) {
        $error = "All fields are required.";
    } elseif (!validateRegNumber($reg_number)) {
        $error = "Invalid registration number. Format: SEU/IS/21/AT/186";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $error = "Mobile number must be 10 digits.";
    } elseif ($semester < 1 || $semester > 8) {
        $error = "Semester must be between 1 and 8.";
    } elseif (!validateStudentPassword($password)) {
        $error = "Password: max 10 chars, letters + numbers + @ only. Example: 186@Roo12";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // ---- Verify against the approved master student list ----
        $eligibility = checkEligibleStudent($conn, $reg_number, $name);

        if (!$eligibility['ok']) {
            // Registration number / name not found on the approved list
            $error = "Please check your registration number and name. This system is only for Department of IT internal students.";
        } else {
            // Check uniqueness
            $stmt = $conn->prepare("SELECT id FROM students WHERE email=? OR reg_number=?");
            $stmt->bind_param("ss", $email, $reg_number);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "Email or Registration Number already exists.";
            } else {
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $otp    = generateOTP();
                $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

                $ins = $conn->prepare(
                    "INSERT INTO students (name,reg_number,email,mobile,address,department,semester,password,otp,otp_expiry)
                     VALUES (?,?,?,?,?,?,?,?,?,?)"
                );
                $ins->bind_param("ssssssisss",
                    $name, $reg_number, $email, $mobile, $address,
                    $department, $semester, $hashed, $otp, $expiry
                );

                if ($ins->execute()) {
                    // Store email in session for OTP page
                    $_SESSION['pending_email'] = $email;
                    sendOTPEmail($email, $name, $otp);
                    redirect('verify_otp.php');
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Registration — SEUSL</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="brand">SEU<span>SL</span> Registration</div>
  <div class="nav-links">
    <a href="login.php">Already registered? Login</a>
    <a href="../index.php">Home</a>
  </div>
</nav>

<div class="container">
  <div class="card" style="max-width:680px;margin:0 auto">
    <h2>👨‍🎓 Student Registration</h2>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="grid-2">
        <div class="form-group">
          <label>Full Name *</label>
          <input type="text" name="name" required
                 value="<?= clean($_POST['name'] ?? '') ?>"
                 placeholder="e.g. Roomi Akhtar">
        </div>
        <div class="form-group">
          <label>Registration Number *</label>
          <input type="text" name="reg_number" required
                 value="<?= clean($_POST['reg_number'] ?? '') ?>"
                 placeholder="SEU/IS/21/AT/186"
                 style="text-transform:uppercase">
          <div class="hint">Format: SEU/IS/YY/BATCH/ID</div>
        </div>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label>Email Address *</label>
          <input type="email" name="email" required
                 value="<?= clean($_POST['email'] ?? '') ?>"
                 placeholder="student@email.com">
        </div>
        <div class="form-group">
          <label>Mobile Number *</label>
          <input type="text" name="mobile" maxlength="10" required
                 value="<?= clean($_POST['mobile'] ?? '') ?>"
                 placeholder="0771234567">
        </div>
      </div>

      <div class="form-group">
        <label>Address *</label>
        <textarea name="address" rows="2" required
                  placeholder="No. 12, Main Street, Colombo"><?= clean($_POST['address'] ?? '') ?></textarea>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label>Department *</label>
          <select name="department" required>
            <option value="">-- Select Department --</option>
            <option value="Information Technology-Honours"
              <?= (($_POST['department'] ?? '') === 'Information Technology-Honours') ? 'selected' : '' ?>>
              01. Information Technology - Honours
            </option>
            <option value="Information Technology-General"
              <?= (($_POST['department'] ?? '') === 'Information Technology-General') ? 'selected' : '' ?>>
              02. Information Technology - General
            </option>
          </select>
        </div>
        <div class="form-group">
          <label>Current Semester *</label>
          <select name="semester" required>
            <option value="">-- Select Semester --</option>
            <?php for ($i = 1; $i <= 8; $i++): ?>
              <option value="<?= $i ?>"
                <?= (($_POST['semester'] ?? 0) == $i) ? 'selected' : '' ?>>
                Semester <?= $i ?>
              </option>
            <?php endfor; ?>
          </select>
        </div>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label>Password *</label>
          <div class="pwd-wrap">
            <input type="password" name="password" id="pwd" required
                   maxlength="10" placeholder="Max 10 chars, letters+numbers+@">
            <button type="button" class="pwd-toggle" onclick="togglePwd('pwd',this)">👁</button>
          </div>
          <div class="hint">Max 10 chars | letters + numbers + @ | e.g. 186@Roo12</div>
        </div>
        <div class="form-group">
          <label>Confirm Password *</label>
          <div class="pwd-wrap">
            <input type="password" name="confirm_password" id="cpwd" required
                   maxlength="10" placeholder="Re-enter password">
            <button type="button" class="pwd-toggle" onclick="togglePwd('cpwd',this)">👁</button>
          </div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block" style="margin-top:8px">
        Register &amp; Send OTP
      </button>
    </form>

    <p style="text-align:center;margin-top:16px;font-size:14px;color:#666">
      Already registered?
      <a href="login.php" style="color:#1a237e;font-weight:600">Login here</a>
    </p>
  </div>
</div>

<script>
function togglePwd(id, btn) {
    const input = document.getElementById(id);
    if (input.type === 'password') { input.type = 'text'; btn.textContent = '🙈'; }
    else { input.type = 'password'; btn.textContent = '👁'; }
}
</script>
</body>
</html>
