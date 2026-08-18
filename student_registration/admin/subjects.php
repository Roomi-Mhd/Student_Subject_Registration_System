<?php
// ==============================================
//  admin/subjects.php — Subject Management
// ==============================================
require_once '../includes/functions.php';
require_once '../includes/db.php';
requireAdminLogin();

$error  = $success = '';
$action = $_GET['action'] ?? 'list';
$edit_id = (int)($_GET['id'] ?? 0);

// ---- DELETE ----
if (isset($_GET['delete'])) {
    $id  = (int)$_GET['delete'];
    $del = $conn->prepare("DELETE FROM subjects WHERE id=?");
    $del->bind_param("i", $id);
    $del->execute();
    setFlash('success', 'Subject deleted.');
    redirect('subjects.php');
}

// ---- SAVE (Add / Edit) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = clean($_POST['subject_name'] ?? '');
    $subject_code = strtoupper(clean($_POST['subject_code'] ?? ''));
    $department   = clean($_POST['department'] ?? '');
    $faculty      = clean($_POST['faculty'] ?? '');
    $semester     = (int)($_POST['semester'] ?? 0);
    $credit       = (int)($_POST['credit']   ?? 0);
    $id           = (int)($_POST['id']       ?? 0);

    if (!$subject_name || !$subject_code || !$department || !$faculty || !$semester || !$credit) {
        $error = "All fields are required.";
    } else {
        if ($id > 0) {
            $stmt = $conn->prepare(
                "UPDATE subjects SET subject_name=?,subject_code=?,department=?,faculty=?,semester=?,credit=? WHERE id=?"
            );
            $stmt->bind_param("ssssiii", $subject_name, $subject_code, $department, $faculty, $semester, $credit, $id);
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO subjects (subject_name,subject_code,department,faculty,semester,credit) VALUES (?,?,?,?,?,?)"
            );
            $stmt->bind_param("ssssii", $subject_name, $subject_code, $department, $faculty, $semester, $credit);
        }
        if ($stmt->execute()) {
            setFlash('success', $id > 0 ? 'Subject updated.' : 'Subject added.');
            redirect('subjects.php');
        } else {
            $error = "Operation failed: " . $conn->error;
        }
    }
}

// Editing?
$edit_subject = null;
if ($edit_id > 0) {
    $s = $conn->prepare("SELECT * FROM subjects WHERE id=?");
    $s->bind_param("i", $edit_id);
    $s->execute();
    $edit_subject = $s->get_result()->fetch_assoc();
    $action = 'add';
}

$subjects = $conn->query("SELECT * FROM subjects ORDER BY department, semester, subject_code")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Subjects Management — SEUSL Admin</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="brand">SEU<span>SL</span> Admin</div>
  <div class="nav-links">
    <a href="dashboard.php">Dashboard</a>
    <a href="students.php">Students</a>
    <a href="registrations.php">Registrations</a>
    <a href="logout.php">Logout</a>
  </div>
</nav>
<div class="wide-container">
  <?php if ($action === 'add'): ?>
  <div class="card">
    <h2><?= $edit_subject ? 'Edit Subject' : '+ Add New Subject' ?></h2>
    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST">
      <input type="hidden" name="id" value="<?= $edit_subject['id'] ?? 0 ?>">
      <div class="grid-2">
        <div class="form-group">
          <label>Subject Name *</label>
          <input type="text" name="subject_name" required value="<?= htmlspecialchars($edit_subject['subject_name'] ?? '') ?>" placeholder="e.g. Web Programming">
        </div>
        <div class="form-group">
          <label>Subject Code *</label>
          <input type="text" name="subject_code" required value="<?= htmlspecialchars($edit_subject['subject_code'] ?? '') ?>" placeholder="e.g. ITM 22013" style="text-transform:uppercase">
        </div>
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Department *</label>
          <select name="department" required>
            <option value="">-- Select Department --</option>
            <option value="Information Systems" <?= (($edit_subject['department']??'')==='Information Systems')?'selected':'' ?>>Information Systems (Honours)</option>
            <option value="Information Technology" <?= (($edit_subject['department']??'')==='Information Technology')?'selected':'' ?>>Information Technology (General)</option>
          </select>
        </div>
        <div class="form-group">
          <label>Faculty / Lecturer *</label>
          <input type="text" name="faculty" required value="<?= htmlspecialchars($edit_subject['faculty'] ?? '') ?>" placeholder="e.g. Dr. K. Fernando">
        </div>
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Semester *</label>
          <select name="semester" required>
            <option value="">-- Select --</option>
            <?php for($i=1;$i<=8;$i++): ?>
            <option value="<?=$i?>" <?=(($edit_subject['semester']??0)==$i)?'selected':''?>>Semester <?=$i?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Credit Hours *</label>
          <input type="number" name="credit" min="1" max="6" required value="<?= $edit_subject['credit'] ?? '' ?>" placeholder="3">
        </div>
      </div>
      <div style="display:flex;gap:12px">
        <button type="submit" class="btn btn-success"><?= $edit_subject ? 'Update Subject' : 'Add Subject' ?></button>
        <a href="subjects.php" class="btn btn-outline">Cancel</a>
      </div>
    </form>
  </div>
  <?php endif; ?>

  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
      <h2 style="margin:0;border:none;padding:0">All Subjects (<?= count($subjects) ?>)</h2>
      <?php if ($action!=='add'): ?>
      <a href="?action=add" class="btn btn-primary btn-sm">+ Add New Subject</a>
      <?php endif; ?>
    </div>
    <?= getFlash() ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>#</th><th>Code</th><th>Subject Name</th><th>Department</th><th>Semester</th><th>Credits</th><th>Faculty</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($subjects as $i => $sub): ?>
          <tr>
            <td><?=$i+1?></td>
            <td><span class="badge badge-info"><?= htmlspecialchars($sub['subject_code']) ?></span></td>
            <td><?= htmlspecialchars($sub['subject_name']) ?></td>
            <td><?= htmlspecialchars($sub['department']) ?></td>
            <td>Sem <?=$sub['semester']?></td>
            <td><?=$sub['credit']?></td>
            <td><?= htmlspecialchars($sub['faculty']) ?></td>
            <td style="white-space:nowrap">
              <a href="?action=add&id=<?=$sub['id']?>" class="btn btn-outline btn-sm">Edit</a>
              <a href="?delete=<?=$sub['id']?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this subject?')">Delete</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<footer>&copy; <?= date('Y') ?> South Eastern University of Sri Lanka</footer>
</body>
</html>
