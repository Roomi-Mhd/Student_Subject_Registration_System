<?php
// ==============================================
//  admin/students.php — View All Students
// ==============================================
require_once '../includes/functions.php';
require_once '../includes/db.php';
requireAdminLogin();

// Delete student
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $d = $conn->prepare("DELETE FROM students WHERE id=?");
    $d->bind_param("i", $id);
    $d->execute();
    setFlash('success', 'Student deleted.');
    redirect('students.php');
}

$filter = $_GET['filter'] ?? '';
$where  = $filter === 'unverified' ? 'WHERE is_verified = 0' : 'WHERE is_verified = 1';

$students = $conn->query(
    "SELECT s.*, COUNT(r.id) as total_registrations
     FROM students s
     LEFT JOIN registrations r ON s.id = r.student_id
     $where
     GROUP BY s.id
     ORDER BY s.created_at DESC"
)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Students — SEUSL Admin</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="brand">SEU<span>SL</span> Admin</div>
  <div class="nav-links">
    <a href="dashboard.php">Dashboard</a>
    <a href="subjects.php">Subjects</a>
    <a href="registrations.php">Registrations</a>
    <a href="logout.php">Logout</a>
  </div>
</nav>

<div class="wide-container">
  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
      <h2 style="margin:0;border:none;padding:0">
        👨‍🎓 <?= $filter === 'unverified' ? 'Unverified' : 'Verified' ?> Students (<?= count($students) ?>)
      </h2>
      <div style="display:flex;gap:8px">
        <a href="students.php" class="btn btn-outline btn-sm <?= !$filter ? 'btn-primary' : '' ?>">Verified</a>
        <a href="students.php?filter=unverified" class="btn btn-outline btn-sm">Unverified</a>
      </div>
    </div>
    <?= getFlash() ?>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Reg. Number</th>
            <th>Email</th>
            <th>Department</th>
            <th>Semester</th>
            <th>Subjects</th>
            <th>Joined</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($students)): ?>
          <tr><td colspan="9" style="text-align:center;padding:20px;color:#888">No students found.</td></tr>
          <?php else: ?>
          <?php foreach ($students as $i => $s): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($s['name']) ?></td>
            <td><span class="badge badge-info"><?= htmlspecialchars($s['reg_number']) ?></span></td>
            <td><?= htmlspecialchars($s['email']) ?></td>
            <td><?= htmlspecialchars($s['department']) ?></td>
            <td>Sem <?= $s['semester'] ?></td>
            <td><span class="badge badge-success"><?= $s['total_registrations'] ?></span></td>
            <td><?= date('d M Y', strtotime($s['created_at'])) ?></td>
            <td>
              <a href="student_detail.php?id=<?= $s['id'] ?>" class="btn btn-outline btn-sm">View</a>
              <a href="?delete=<?= $s['id'] ?>" class="btn btn-danger btn-sm"
                 onclick="return confirm('Delete this student and all their registrations?')">Delete</a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<footer>&copy; <?= date('Y') ?> South Eastern University of Sri Lanka</footer>
</body>
</html>
