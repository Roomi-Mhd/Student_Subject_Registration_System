<?php
// ==============================================
//  admin/login.php — Admin / Lecturer Login
// ==============================================
require_once '../includes/functions.php';
require_once '../includes/db.php';

if (isset($_SESSION['admin_id'])) redirect('dashboard.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $error = "Please enter username and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();

        if (!$admin || $admin['password'] !== md5($password)) {
            if (!$admin) {
                $error = "Admin account not found.";
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $_SESSION['admin_id']       = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_role']     = $admin['role'];
            redirect('dashboard.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login — SEUSL</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="brand">SEU<span>SL</span> Admin Portal</div>
  <div class="nav-links">
    <a href="../index.php">← Student Portal</a>
  </div>
</nav>

<div class="container">
  <div class="card" style="max-width:420px;margin:0 auto">
    <h2>🔐 Admin / Lecturer Login</h2>

    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" required
               value="<?= clean($_POST['username'] ?? '') ?>"
               placeholder="admin username" autofocus>
      </div>
      <div class="form-group">
        <label>Password</label>
        <div class="pwd-wrap">
          <input type="password" name="password" id="pwd" required
                 placeholder="Enter your password">
          <button type="button" class="pwd-toggle" onclick="togglePwd()">👁</button>
        </div>
        <div class="hint">Enter your admin password</div>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>
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