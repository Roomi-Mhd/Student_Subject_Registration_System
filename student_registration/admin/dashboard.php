<?php
// ==============================================
//  admin/dashboard.php — Admin Dashboard
// ==============================================
require_once '../includes/functions.php';
require_once '../includes/db.php';
requireAdminLogin();

// Stats
$total_students     = $conn->query("SELECT COUNT(*) FROM students WHERE is_verified=1")->fetch_row()[0];
$total_subjects     = $conn->query("SELECT COUNT(*) FROM subjects")->fetch_row()[0];
$total_registrations= $conn->query("SELECT COUNT(*) FROM registrations")->fetch_row()[0];
$pending_verify     = $conn->query("SELECT COUNT(*) FROM students WHERE is_verified=0")->fetch_row()[0];

// Recent registrations
$recent = $conn->query(
    "SELECT st.name, st.reg_number, su.subject_name, su.subject_code, r.date
     FROM registrations r
     JOIN students st ON r.student_id = st.id
     JOIN subjects su ON r.subject_id = su.id
     ORDER BY r.date DESC LIMIT 10"
)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard — SEUSL</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="brand">SEU<span>SL</span> Admin</div>
  <div class="nav-links">
    <a href="students.php">Students</a>
    <a href="subjects.php">Subjects</a>
    <a href="registrations.php">Registrations</a>
    <a href="logout.php">Logout</a>
  </div>
</nav>

<div class="wide-container">

  <div class="card">
    <h2>Admin Dashboard</h2>
    <p style="color:#666;font-size:14px">
      Welcome, <strong><?= htmlspecialchars($_SESSION['admin_username']) ?></strong>
      (<?= ucfirst($_SESSION['admin_role']) ?>)
    </p>
  </div>

  <!-- Stats -->
  <div class="grid-3" style="margin-bottom:24px">
    <div class="stat-card">
      <div class="stat-num"><?= $total_students ?></div>
      <div class="stat-label">Verified Students</div>
    </div>
    <div class="stat-card" style="border-left-color:#388e3c">
      <div class="stat-num" style="color:#388e3c"><?= $total_subjects ?></div>
      <div class="stat-label">Total Subjects</div>
    </div>
    <div class="stat-card" style="border-left-color:#f57f17">
      <div class="stat-num" style="color:#f57f17"><?= $total_registrations ?></div>
      <div class="stat-label">Total Registrations</div>
    </div>
  </div>
  <?php if ($pending_verify > 0): ?>
  <div class="alert alert-info">
    ⚠️ <strong><?= $pending_verify ?></strong> student(s) pending email verification.
    <a href="students.php?filter=unverified">View →</a>
  </div>
  <?php endif; ?>

  <!-- Quick Actions -->
  <div class="card">
    <h2>⚡ Quick Actions</h2>
    <div style="display:flex;gap:12px;flex-wrap:wrap">
      <a href="subjects.php?action=add" class="btn btn-primary">+ Add Subject</a>
      <a href="students.php" class="btn btn-outline">View All Students</a>
      <a href="registrations.php" class="btn btn-outline">View All Registrations</a>
    </div>
  </div>

  <!-- Recent Registrations -->
  <div class="card">
    <h2>📋 Recent Registrations</h2>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Student Name</th>
            <th>Reg. Number</th>
            <th>Subject</th>
            <th>Code</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recent)): ?>
          <tr><td colspan="5" style="text-align:center;color:#888;padding:20px">No registrations yet.</td></tr>
          <?php else: ?>
          <?php foreach ($recent as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['name']) ?></td>
            <td><span class="badge badge-info"><?= htmlspecialchars($r['reg_number']) ?></span></td>
            <td><?= htmlspecialchars($r['subject_name']) ?></td>
            <td><span class="badge badge-success"><?= htmlspecialchars($r['subject_code']) ?></span></td>
            <td><?= date('d M Y, h:i A', strtotime($r['date'])) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div style="margin-top:12px">
      <a href="registrations.php" class="btn btn-outline btn-sm">View All →</a>
    </div>
  </div>

</div>

<footer>&copy; <?= date('Y') ?> South Eastern University of Sri Lanka</footer>
</body>
</html>
