<?php
// ==============================================
//  admin/student_detail.php — View One Student
// ==============================================
require_once '../includes/functions.php';
require_once '../includes/db.php';
requireAdminLogin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('students.php');

$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
if (!$student) { setFlash('danger','Student not found.'); redirect('students.php'); }

// Get their registrations
$regs = $conn->prepare(
    "SELECT su.subject_code, su.subject_name, su.credit, su.faculty, su.semester, r.date
     FROM registrations r
     JOIN subjects su ON r.subject_id = su.id
     WHERE r.student_id = ?
     ORDER BY su.subject_code"
);
$regs->bind_param("i", $id);
$regs->execute();
$subjects = $regs->get_result()->fetch_all(MYSQLI_ASSOC);
$total_credits = array_sum(array_column($subjects, 'credit'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Detail — SEUSL Admin</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="brand">SEU<span>SL</span> Admin</div>
  <div class="nav-links">
    <a href="dashboard.php">Dashboard</a>
    <a href="students.php">← All Students</a>
    <a href="logout.php">Logout</a>
  </div>
</nav>

<div class="container">
  <div class="card">
    <h2>👨‍🎓 Student Profile</h2>
    <div class="grid-2">
      <table style="font-size:14px;border-collapse:collapse;width:100%">
        <?php
        $fields = [
          'Full Name'       => $student['name'],
          'Reg. Number'     => $student['reg_number'],
          'Email'           => $student['email'],
          'Mobile'          => $student['mobile'],
          'Address'         => $student['address'],
          'Department'      => $student['department'],
          'Semester'        => 'Semester '.$student['semester'],
          'Verified'        => $student['is_verified'] ? '✅ Yes' : '❌ No',
          'Registered On'   => date('d M Y, h:i A', strtotime($student['created_at'])),
        ];
        foreach ($fields as $label => $val):
        ?>
        <tr>
          <td style="padding:7px 10px;color:#888;font-weight:600;width:40%;border-bottom:1px solid #eee"><?= $label ?></td>
          <td style="padding:7px 10px;border-bottom:1px solid #eee"><?= htmlspecialchars($val) ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
      <div>
        <div class="stat-card" style="margin-bottom:16px">
          <div class="stat-num"><?= count($subjects) ?></div>
          <div class="stat-label">Subjects Registered</div>
        </div>
        <div class="stat-card" style="border-left-color:#388e3c">
          <div class="stat-num" style="color:#388e3c"><?= $total_credits ?></div>
          <div class="stat-label">Total Credits</div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <h2>📚 Registered Subjects</h2>
    <?php if (empty($subjects)): ?>
      <div class="alert alert-info">This student has not registered any subjects yet.</div>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>#</th><th>Code</th><th>Subject Name</th><th>Credits</th><th>Faculty</th><th>Date</th></tr>
        </thead>
        <tbody>
          <?php foreach ($subjects as $i => $sub): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><span class="badge badge-info"><?= htmlspecialchars($sub['subject_code']) ?></span></td>
            <td><?= htmlspecialchars($sub['subject_name']) ?></td>
            <td><?= $sub['credit'] ?></td>
            <td><?= htmlspecialchars($sub['faculty']) ?></td>
            <td><?= date('d M Y', strtotime($sub['date'])) ?></td>
          </tr>
          <?php endforeach; ?>
          <tr style="font-weight:bold;background:#e8eaf6">
            <td colspan="3" style="text-align:right;padding:10px">Total Credits</td>
            <td colspan="3" style="padding:10px"><?= $total_credits ?></td>
          </tr>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
    <div style="margin-top:14px">
      <a href="students.php" class="btn btn-outline btn-sm">← Back to Students</a>
    </div>
  </div>
</div>

<footer>&copy; <?= date('Y') ?> South Eastern University of Sri Lanka</footer>
</body>
</html>
