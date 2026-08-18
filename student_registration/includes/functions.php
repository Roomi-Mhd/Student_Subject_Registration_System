<?php
// ==============================================
//  includes/functions.php — Utility Functions
// ==============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---- Redirect helpers ----
function redirect($url) {
    header("Location: $url");
    exit();
}

function requireStudentLogin() {
    if (!isset($_SESSION['student_id'])) {
        redirect('../index.php');
    }
}

function requireAdminLogin() {
    if (!isset($_SESSION['admin_id'])) {
        redirect('../admin/login.php');
    }
}

// ---- Input sanitisation ----
function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// ---- Validate SEU registration number ----
// Format: SEU/IS/YY/XX/NNN
function validateRegNumber($reg) {
    $parts = explode('/', $reg);
    if (count($parts) !== 5) return false;
    if (strtoupper($parts[0]) !== 'SEU') return false;
    if (!ctype_alnum($parts[1])) return false;  // IS, IT, etc.
    if (!ctype_digit($parts[2])) return false;  // year: 21, 22 …
    if (!ctype_alpha($parts[3])) return false;  // batch: AT, BT …
    if (!ctype_digit($parts[4])) return false;  // unique ID: 186
    return true;
}

// ---- Validate student password ----
// Max 10 chars | letters + digits + @ only
function validateStudentPassword($pwd) {
    if (strlen($pwd) > 10) return false;
    return (bool) preg_match('/^[a-zA-Z0-9@]+$/', $pwd);
}

// ---- Validate admin password ----
// Must start with ADD or LEC
function validateAdminPassword($pwd) {
    return (bool) preg_match('/^(ADD|LEC)/i', $pwd);
}

// ---- Normalise a registration number for comparison ----
// Strips everything except letters/digits and upper-cases it, so
// "SEU/IS/21/AT/186", "Seu. Is 21.at.186" and "SEU-IS-21-AT-186"
// all normalise to the same value: "SEUIS21AT186"
function normalizeRegNumber($reg) {
    return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $reg ?? ''));
}

// ---- Normalise a name for loose comparison ----
function normalizeName($name) {
    $name = strtolower(trim($name ?? ''));
    $name = preg_replace('/\s+/', ' ', $name);
    return $name;
}

// ---- Check whether a student is on the approved master list ----
// Returns:
//   ['ok' => true,  'record' => [...]]                     -> allowed to register
//   ['ok' => false, 'reason' => 'not_found'|'name_mismatch']-> blocked
function checkEligibleStudent($conn, $reg_number, $name) {
    $regNorm = normalizeRegNumber($reg_number);

    $stmt = $conn->prepare(
        "SELECT id, name, reg_number_raw
         FROM eligible_students WHERE reg_number_norm = ?"
    );
    $stmt->bind_param("s", $regNorm);
    $stmt->execute();
    $record = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$record) {
        return ['ok' => false, 'reason' => 'not_found'];
    }

    // STRICT match — normalised name must be EXACTLY the same as the
    // name on the approved list (from the Excel/PDF master data).
    // Normalising only removes case/extra-space differences, e.g.
    // "  Mohamed   Roomi" and "mohamed roomi" are treated as equal,
    // but any real difference in the name is rejected.
    $submitted = normalizeName($name);
    $onFile    = normalizeName($record['name']);

    if ($submitted !== $onFile) {
        return ['ok' => false, 'reason' => 'name_mismatch'];
    }

    return ['ok' => true, 'record' => $record];
}

// ---- Generate OTP ----
function generateOTP() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

// ---- Simple mail sender (uses PHP mail()) ----
// For production replace with PHPMailer + SMTP
function sendOTPEmail($to, $name, $otp) {
    $subject = "Your OTP - Student Subject Registration System";
    $body    = "Dear $name,\n\nYour OTP for account verification is: $otp\n"
             . "This OTP expires in 10 minutes.\n\n"
             . "South Eastern University of Sri Lanka\n"
             . "Department of Information Technology";
    $headers = "From: noreply@seu.ac.lk";
    return mail($to, $subject, $body, $headers);
}

// ---- Flash messages ----
function setFlash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash() {
    if (!isset($_SESSION['flash'])) return '';
    $f   = $_SESSION['flash'];
    $cls = $f['type'] === 'success' ? '#2e7d32' : '#c62828';
    unset($_SESSION['flash']);
    return "<div style='background:{$cls};color:#fff;padding:10px 16px;border-radius:6px;margin-bottom:14px;font-size:14px'>"
         . htmlspecialchars($f['msg'])
         . "</div>";
}
?>
