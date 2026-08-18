<?php
// ==============================================
//  student/register_subjects.php
// ==============================================
require_once '../includes/functions.php';
require_once '../includes/db.php';
requireStudentLogin();

$student_id = $_SESSION['student_id'];
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

$error = $success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected = $_POST['subjects'] ?? [];
    if (empty($selected)) {
        $error = "Please select at least one subject.";
    } else {
        $inserted = 0;
        $skipped  = 0;
        foreach ($selected as $subject_id) {
            $subject_id = (int)$subject_id;
            // Validate subject belongs to student's dept & semester
            $check = $conn->prepare(
                "SELECT id FROM subjects WHERE id=? AND department=? AND semester=?"
            );
            $check->bind_param("isi", $subject_id, $student['department'], $student['semester']);
            $check->execute();
            if ($check->get_result()->num_rows === 0) { $skipped++; continue; }

            // Avoid duplicate
            $dup = $conn->prepare(
                "SELECT id FROM registrations WHERE student_id=? AND subject_id=?"
            );
            $dup->bind_param("ii", $student_id, $subject_id);
            $dup->execute();
            if ($dup->get_result()->num_rows > 0) { $skipped++; continue; }

            $ins = $conn->prepare(
                "INSERT INTO registrations (student_id, subject_id) VALUES (?, ?)"
            );
            $ins->bind_param("ii", $student_id, $subject_id);
            if ($ins->execute()) $inserted++;
        }

        if ($inserted > 0) {
            $success = "$inserted subject(s) registered successfully!"
                     . ($skipped > 0 ? " ($skipped skipped — already registered or invalid.)" : "");
        } else {
            $error = "No new subjects registered. You may have already registered for the selected subjects.";
        }
    }
}

// Get available subjects (dept + semester)
$avail_stmt = $conn->prepare(
    "SELECT s.* FROM subjects s
     WHERE s.department = ? AND s.semester = ?
     AND s.id NOT IN (
         SELECT subject_id FROM registrations WHERE student_id = ?
     )
     ORDER BY s.subject_code"
);
$avail_stmt->bind_param("sii", $student['department'], $student['semester'], $student_id);
$avail_stmt->execute();
$available_subjects = $avail_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register Subjects — SEUSL</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="brand">SEU<span>SL</span> Registration</div>
  <div class="nav-links">
    <a href="dashboard.php">Dashboard</a>
    <a href="download_pdf.php" target="_blank">📄 Download PDF</a>
    <a href="logout.php">Logout</a>
  </div>
</nav>

<div class="container">
  <div class="card">
    <h2>📝 Register Subjects</h2>
    <p style="color:#555;font-size:14px;margin-bottom:18px">
      Showing subjects for <strong><?= htmlspecialchars($student['department']) ?></strong>
      — <strong>Semester <?= $student['semester'] ?></strong>
    </p>

    <?php if ($error)   echo "<div class='alert alert-danger'>$error</div>"; ?>
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

    <?php if (empty($available_subjects)): ?>
      <div class="alert alert-info">
        🎉 You have registered for all available subjects for this semester!
        <br><a href="dashboard.php">← Back to Dashboard</a>
      </div>
    <?php else: ?>
      <form method="POST">
        <ul class="subject-list">
          <?php foreach ($available_subjects as $sub): ?>
          <li onclick="toggleCheck('sub_<?= $sub['id'] ?>')">
            <input type="checkbox" name="subjects[]"
                   id="sub_<?= $sub['id'] ?>"
                   value="<?= $sub['id'] ?>">
            <div style="flex:1">
              <strong><?= htmlspecialchars($sub['subject_name']) ?></strong>
              <div style="font-size:12px;color:#888">
                Faculty: <?= htmlspecialchars($sub['faculty']) ?> &nbsp;|&nbsp;
                Credits: <?= $sub['credit'] ?>
              </div>
            </div>
            <span class="sub-code badge badge-info"><?= htmlspecialchars($sub['subject_code']) ?></span>
          </li>
          <?php endforeach; ?>
        </ul>

        <div style="display:flex;gap:12px;margin-top:18px">
          <button type="submit" class="btn btn-success">✔ Register Selected Subjects</button>
          <button type="button" class="btn btn-outline btn-sm" onclick="selectAll()">Select All</button>
          <button type="button" class="btn btn-outline btn-sm" onclick="clearAll()">Clear</button>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>

<script>
function toggleCheck(id) {
    const cb = document.getElementById(id);
    cb.checked = !cb.checked;
}
function selectAll() {
    document.querySelectorAll('input[type=checkbox]').forEach(c => c.checked = true);
}
function clearAll() {
    document.querySelectorAll('input[type=checkbox]').forEach(c => c.checked = false);
}
</script>
</body>
</html>
