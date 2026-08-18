<?php
// ==============================================
//  student/download_pdf.php
//  Generates a styled HTML page then uses
//  browser print-to-PDF — no library needed.
// ==============================================
require_once '../includes/functions.php';
require_once '../includes/db.php';
requireStudentLogin();

$student_id = $_SESSION['student_id'];

$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

$regs = $conn->prepare(
    "SELECT s.subject_code, s.subject_name, s.credit, s.faculty, r.date
     FROM registrations r
     JOIN subjects s ON r.subject_id = s.id
     WHERE r.student_id = ?
     ORDER BY s.subject_code"
);
$regs->bind_param("i", $student_id);
$regs->execute();
$subjects = $regs->get_result()->fetch_all(MYSQLI_ASSOC);

$total_credits = array_sum(array_column($subjects, 'credit'));
$generated_on  = date('d F Y, h:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Subject Registration Confirmation — <?= htmlspecialchars($student['reg_number']) ?></title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'Times New Roman', serif; padding: 40px; color: #000; }
  .header { text-align: center; border-bottom: 3px double #1a237e; padding-bottom: 16px; margin-bottom: 20px; }
  .header h1 { font-size: 20px; color: #1a237e; }
  .header h2 { font-size: 16px; color: #333; font-weight: normal; margin-top: 4px; }
  .header h3 { font-size: 14px; color: #555; font-weight: normal; margin-top: 4px; }
  .title-box { text-align: center; background: #1a237e; color: #fff; padding: 10px; margin: 16px 0; border-radius: 4px; }
  .title-box h2 { font-size: 16px; letter-spacing: 1px; }
  .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px; }
  .info-table td { padding: 6px 10px; border: 1px solid #ccc; }
  .info-table td:first-child { font-weight: bold; width: 35%; background: #f5f5f5; }
  .subject-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 20px; }
  .subject-table th { background: #1a237e; color: #fff; padding: 9px 10px; text-align: left; }
  .subject-table td { padding: 8px 10px; border: 1px solid #ddd; }
  .subject-table tr:nth-child(even) td { background: #f9f9f9; }
  .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; border-top: 1px solid #ccc; padding-top: 12px; }
  .stamp { border: 2px solid #1a237e; display: inline-block; padding: 8px 20px; color: #1a237e; font-size: 13px; margin-top: 20px; }
  .total-row td { font-weight: bold; background: #e8eaf6 !important; }
  @media print {
    .no-print { display: none; }
    body { padding: 20px; }
  }
</style>
</head>
<body>

<!-- Print Button (hidden when printing) -->
<div class="no-print" style="text-align:right;margin-bottom:16px">
  <button onclick="window.print()"
          style="background:#1a237e;color:#fff;border:none;padding:10px 22px;
                 border-radius:6px;font-size:14px;cursor:pointer">
    🖨️ Print / Save as PDF
  </button>
  <a href="dashboard.php"
     style="margin-left:10px;color:#1a237e;text-decoration:none;font-size:14px">
    ← Back to Dashboard
  </a>
</div>

<!-- Document Header -->
<div class="header">
  <h1>SOUTH EASTERN UNIVERSITY OF SRI LANKA</h1>
  <h2>Faculty of Arts &amp; Culture — Department of Information Technology</h2>
  <h3>Academic Year <?= date('Y') ?> / <?= date('Y') + 1 ?></h3>
</div>

<div class="title-box">
  <h2>SUBJECT REGISTRATION CONFIRMATION</h2>
</div>

<!-- Student Info -->
<table class="info-table">
  <tr><td>Student Name</td><td><?= htmlspecialchars($student['name']) ?></td></tr>
  <tr><td>Registration Number</td><td><?= htmlspecialchars($student['reg_number']) ?></td></tr>
  <tr><td>Email Address</td><td><?= htmlspecialchars($student['email']) ?></td></tr>
  <tr><td>Department</td><td><?= htmlspecialchars($student['department']) ?></td></tr>
  <tr><td>Current Semester</td><td>Semester <?= $student['semester'] ?></td></tr>
  <tr><td>Document Generated</td><td><?= $generated_on ?></td></tr>
</table>

<!-- Subjects Table -->
<table class="subject-table">
  <thead>
    <tr>
      <th>#</th>
      <th>Subject Code</th>
      <th>Subject Name</th>
      <th>Credits</th>
      <th>Faculty</th>
      <th>Registration Date</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($subjects)): ?>
    <tr><td colspan="6" style="text-align:center;padding:20px;color:#888">
      No subjects registered yet.
    </td></tr>
    <?php else: ?>
    <?php foreach ($subjects as $i => $sub): ?>
    <tr>
      <td><?= $i + 1 ?></td>
      <td><?= htmlspecialchars($sub['subject_code']) ?></td>
      <td><?= htmlspecialchars($sub['subject_name']) ?></td>
      <td><?= $sub['credit'] ?></td>
      <td><?= htmlspecialchars($sub['faculty']) ?></td>
      <td><?= date('d M Y', strtotime($sub['date'])) ?></td>
    </tr>
    <?php endforeach; ?>
    <tr class="total-row">
      <td colspan="3" style="text-align:right">Total Credits</td>
      <td colspan="3"><?= $total_credits ?> Credits</td>
    </tr>
    <?php endif; ?>
  </tbody>
</table>

<!-- Stamp -->
<div style="text-align:center;margin-top:20px">
  <div class="stamp">
    ✓ VERIFIED — SEUSL Department of Information Technology
  </div>
</div>

<div class="footer">
  <p>This is a system-generated document. No signature required.</p>
  <p>South Eastern University of Sri Lanka | www.seu.ac.lk</p>
</div>

</body>
</html>
