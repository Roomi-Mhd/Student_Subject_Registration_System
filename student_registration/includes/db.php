<?php
// ==============================================
//  includes/db.php — Database Connection
//  XAMPP default settings
// ==============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');               // XAMPP default: empty password
define('DB_NAME', 'student_registration');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("<div style='font-family:sans-serif;color:#c00;padding:20px'>
          <strong>Database Connection Failed:</strong><br>
          " . $conn->connect_error . "<br><br>
          Make sure XAMPP is running and you have imported <code>database.sql</code>.
         </div>");
}

$conn->set_charset('utf8mb4');
?>
