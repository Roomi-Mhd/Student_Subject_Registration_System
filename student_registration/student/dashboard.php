<?php
// ==============================================
//  student/dashboard.php — Student Dashboard
// ==============================================
require_once '../includes/functions.php';
require_once '../includes/db.php';
requireStudentLogin();

$student_id = $_SESSION['student_id'];

// Get student data
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Count registered subjects
$cnt = $conn->prepare(
    "SELECT COUNT(*) as total FROM registrations WHERE student_id = ?"
);
$cnt->bind_param("i", $student_id);
$cnt->execute();
$total_registered = $cnt->get_result()->fetch_assoc()['total'];

// Count available subjects for this dept & semester
$avail = $conn->prepare(
    "SELECT COUNT(*) as total FROM subjects WHERE department=? AND semester=?"
);
$avail->bind_param("si", $student['department'], $student['semester']);
$avail->execute();
$total_available = $avail->get_result()->fetch_assoc()['total'];

// Get registered subjects
$regs = $conn->prepare(
    "SELECT s.subject_name, s.subject_code, s.credit, s.faculty, r.date
     FROM registrations r
     JOIN subjects s ON r.subject_id = s.id
     WHERE r.student_id = ?
     ORDER BY r.date DESC"
);
$regs->bind_param("i", $student_id);
$regs->execute();
$registered_subjects = $regs->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Dashboard — SEUSL</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="brand">SEU<span>SL</span> Registration</div>
  <div class="nav-links">
    <a href="register_subjects.php">Register Subjects</a>
    <a href="download_pdf.php" target="_blank">📄 Download PDF</a>
    <a href="logout.php">Logout</a>
  </div>
</nav>

<div class="wide-container">

  <!-- Welcome -->
  <div class="card">
    <h2>Welcome, <?= htmlspecialchars($student['name']) ?> 👋</h2>
    <div class="grid-3">
      <div>
        <span style="color:#888;font-size:13px">Registration No.</span><br>
        <strong><?= htmlspecialchars($student['reg_number']) ?></strong>
      </div>
      <div>
        <span style="color:#888;font-size:13px">Department</span><br>
        <strong><?= htmlspecialchars($student['department']) ?></strong>
      </div>
      <div>
        <span style="color:#888;font-size:13px">Semester</span><br>
        <strong>Semester <?= $student['semester'] ?></strong>
      </div>
    </div>
  </div>

  <!-- Stats -->
  <div class="grid-3" style="margin-bottom:24px">
    <div class="stat-card">
      <div class="stat-num"><?= $total_registered ?></div>
      <div class="stat-label">Subjects Registered</div>
    </div>
    <div class="stat-card" style="border-left-color:#388e3c">
      <div class="stat-num" style="color:#388e3c"><?= $total_available ?></div>
      <div class="stat-label">Available for Your Semester</div>
    </div>
    <div class="stat-card" style="border-left-color:#f57f17">
      <div class="stat-num" style="color:#f57f17"><?= $total_available - $total_registered ?></div>
      <div class="stat-label">Not Yet Registered</div>
    </div>
  </div>

  <!-- Registered Subjects Table -->
  <div class="card">
    <h2>📚 My Registered Subjects</h2>

    <?php if (empty($registered_subjects)): ?>
      <div class="alert alert-info">
        You have not registered for any subjects yet.
        <a href="register_subjects.php" style="font-weight:600">Register now →</a>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Subject Code</th>
              <th>Subject Name</th>
              <th>Credits</th>
              <th>Faculty</th>
              <th>Registered On</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($registered_subjects as $i => $sub): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><span class="badge badge-info"><?= htmlspecialchars($sub['subject_code']) ?></span></td>
              <td><?= htmlspecialchars($sub['subject_name']) ?></td>
              <td><?= $sub['credit'] ?></td>
              <td><?= htmlspecialchars($sub['faculty']) ?></td>
              <td><?= date('d M Y, h:i A', strtotime($sub['date'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div style="margin-top:16px;display:flex;gap:12px">
        <a href="register_subjects.php" class="btn btn-primary btn-sm">+ Register More</a>
        <a href="download_pdf.php" target="_blank" class="btn btn-success btn-sm">📄 Download PDF</a>
      </div>
    <?php endif; ?>
  </div>

</div>

<footer>&copy; <?= date('Y') ?> South Eastern University of Sri Lanka</footer>
</body>
</html>
