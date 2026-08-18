<?php
// ==============================================
//  admin/registrations.php — All Registrations
// ==============================================
require_once '../includes/functions.php';
require_once '../includes/db.php';
requireAdminLogin();

// Filters
$dept   = clean($_GET['dept']   ?? '');
$sem    = (int)($_GET['sem']    ?? 0);
$search = clean($_GET['search'] ?? '');

$where = "WHERE st.is_verified = 1";
$params = [];
$types  = "";

if ($dept) { $where .= " AND st.department = ?"; $params[] = $dept; $types .= "s"; }
if ($sem)  { $where .= " AND st.semester = ?";   $params[] = $sem;  $types .= "i"; }
if ($search) {
    $like = "%$search%";
    $where .= " AND (st.name LIKE ? OR st.reg_number LIKE ? OR su.subject_code LIKE ?)";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= "sss";
}

$sql = "SELECT r.id, st.name, st.reg_number, st.department, st.semester,
               su.subject_name, su.subject_code, su.credit, r.date
        FROM registrations r
        JOIN students st ON r.student_id = st.id
        JOIN subjects  su ON r.subject_id = su.id
        $where
        ORDER BY r.date DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$registrations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Delete single registration
if (isset($_GET['delete'])) {
    $rid = (int)$_GET['delete'];
    $d = $conn->prepare("DELETE FROM registrations WHERE id=?");
    $d->bind_param("i", $rid);
    $d->execute();
    setFlash('success', 'Registration removed.');
    redirect('registrations.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Registrations — SEUSL Admin</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="brand">SEU<span>SL</span> Admin</div>
  <div class="nav-links">
    <a href="dashboard.php">Dashboard</a>
    <a href="subjects.php">Subjects</a>
    <a href="students.php">Students</a>
    <a href="logout.php">Logout</a>
  </div>
</nav>

<div class="wide-container">
  <div class="card">
    <h2>📋 All Subject Registrations (<?= count($registrations) ?>)</h2>
    <?= getFlash() ?>

    <!-- Filters -->
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px">
      <input type="text" name="search" placeholder="Search name / reg no / code"
             value="<?= htmlspecialchars($search) ?>"
             style="flex:1;min-width:180px;padding:8px 12px;border:1px solid #ccc;border-radius:6px;font-size:14px">
      <select name="dept" style="padding:8px 12px;border:1px solid #ccc;border-radius:6px;font-size:14px">
        <option value="">All Departments</option>
        <option value="Information Technology-Honours" <?= $dept==='Information Technology-Honours' ? 'selected':'' ?>>01. Information Technology - Honours</option>
        <option value="Information Technology-General" <?= $dept==='Information Technology-General' ? 'selected':'' ?>>02. Information Technology - General</option>
      </select>
      <select name="sem" style="padding:8px 12px;border:1px solid #ccc;border-radius:6px;font-size:14px">
        <option value="0">All Semesters</option>
        <?php for($i=1;$i<=8;$i++): ?>
        <option value="<?=$i?>" <?= $sem===$i ? 'selected':'' ?>>Semester <?=$i?></option>
        <?php endfor; ?>
      </select>
      <button type="submit" class="btn btn-primary btn-sm">Filter</button>
      <a href="registrations.php" class="btn btn-outline btn-sm">Reset</a>
    </form>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Student Name</th>
            <th>Reg. Number</th>
            <th>Department</th>
            <th>Sem</th>
            <th>Subject Code</th>
            <th>Subject Name</th>
            <th>Credits</th>
            <th>Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($registrations)): ?>
          <tr><td colspan="10" style="text-align:center;padding:20px;color:#888">No registrations found.</td></tr>
          <?php else: ?>
          <?php foreach ($registrations as $i => $r): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($r['name']) ?></td>
            <td><span class="badge badge-info"><?= htmlspecialchars($r['reg_number']) ?></span></td>
            <td><?= htmlspecialchars($r['department']) ?></td>
            <td>Sem <?= $r['semester'] ?></td>
            <td><span class="badge badge-success"><?= htmlspecialchars($r['subject_code']) ?></span></td>
            <td><?= htmlspecialchars($r['subject_name']) ?></td>
            <td><?= $r['credit'] ?></td>
            <td><?= date('d M Y', strtotime($r['date'])) ?></td>
            <td>
              <a href="?delete=<?= $r['id'] ?>" class="btn btn-danger btn-sm"
                 onclick="return confirm('Remove this registration?')">Remove</a>
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
<?php
// ==============================================
//  admin/registrations.php — All Registrations
// ==============================================
require_once '../includes/functions.php';
require_once '../includes/db.php';
requireAdminLogin();

// Filters
$dept   = clean($_GET['dept']   ?? '');
$sem    = (int)($_GET['sem']    ?? 0);
$search = clean($_GET['search'] ?? '');

$where = "WHERE st.is_verified = 1";
$params = [];
$types  = "";

if ($dept) { $where .= " AND st.department = ?"; $params[] = $dept; $types .= "s"; }
if ($sem)  { $where .= " AND st.semester = ?";   $params[] = $sem;  $types .= "i"; }
if ($search) {
    $like = "%$search%";
    $where .= " AND (st.name LIKE ? OR st.reg_number LIKE ? OR su.subject_code LIKE ?)";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= "sss";
}

$sql = "SELECT r.id, st.name, st.reg_number, st.department, st.semester,
               su.subject_name, su.subject_code, su.credit, r.date
        FROM registrations r
        JOIN students st ON r.student_id = st.id
        JOIN subjects  su ON r.subject_id = su.id
        $where
        ORDER BY r.date DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$registrations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Delete single registration
if (isset($_GET['delete'])) {
    $rid = (int)$_GET['delete'];
    $d = $conn->prepare("DELETE FROM registrations WHERE id=?");
    $d->bind_param("i", $rid);
    $d->execute();
    setFlash('success', 'Registration removed.');
    redirect('registrations.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Registrations — SEUSL Admin</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="brand">SEU<span>SL</span> Admin</div>
  <div class="nav-links">
    <a href="dashboard.php">Dashboard</a>
    <a href="subjects.php">Subjects</a>
    <a href="students.php">Students</a>
    <a href="logout.php">Logout</a>
  </div>
</nav>

<div class="wide-container">
  <div class="card">
    <h2>📋 All Subject Registrations (<?= count($registrations) ?>)</h2>
    <?= getFlash() ?>

    <!-- Filters -->
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px">
      <input type="text" name="search" placeholder="Search name / reg no / code"
             value="<?= htmlspecialchars($search) ?>"
             style="flex:1;min-width:180px;padding:8px 12px;border:1px solid #ccc;border-radius:6px;font-size:14px">
      <select name="dept" style="padding:8px 12px;border:1px solid #ccc;border-radius:6px;font-size:14px">
        <option value="">All Departments</option>
        <option value="Information Systems"    <?= $dept==='Information Systems'    ? 'selected':'' ?>>Information Systems</option>
        <option value="Information Technology" <?= $dept==='Information Technology' ? 'selected':'' ?>>Information Technology</option>
      </select>
      <select name="sem" style="padding:8px 12px;border:1px solid #ccc;border-radius:6px;font-size:14px">
        <option value="0">All Semesters</option>
        <?php for($i=1;$i<=8;$i++): ?>
        <option value="<?=$i?>" <?= $sem===$i ? 'selected':'' ?>>Semester <?=$i?></option>
        <?php endfor; ?>
      </select>
      <button type="submit" class="btn btn-primary btn-sm">Filter</button>
      <a href="registrations.php" class="btn btn-outline btn-sm">Reset</a>
    </form>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Student Name</th>
            <th>Reg. Number</th>
            <th>Department</th>
            <th>Sem</th>
            <th>Subject Code</th>
            <th>Subject Name</th>
            <th>Credits</th>
            <th>Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($registrations)): ?>
          <tr><td colspan="10" style="text-align:center;padding:20px;color:#888">No registrations found.</td></tr>
          <?php else: ?>
          <?php foreach ($registrations as $i => $r): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($r['name']) ?></td>
            <td><span class="badge badge-info"><?= htmlspecialchars($r['reg_number']) ?></span></td>
            <td><?= htmlspecialchars($r['department']) ?></td>
            <td>Sem <?= $r['semester'] ?></td>
            <td><span class="badge badge-success"><?= htmlspecialchars($r['subject_code']) ?></span></td>
            <td><?= htmlspecialchars($r['subject_name']) ?></td>
            <td><?= $r['credit'] ?></td>
            <td><?= date('d M Y', strtotime($r['date'])) ?></td>
            <td>
              <a href="?delete=<?= $r['id'] ?>" class="btn btn-danger btn-sm"
                 onclick="return confirm('Remove this registration?')">Remove</a>
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
