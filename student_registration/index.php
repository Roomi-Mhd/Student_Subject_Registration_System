<?php
// ==============================================
//  index.php — Landing page with video background
// ==============================================
require_once 'includes/functions.php';

if (isset($_SESSION['student_id'])) redirect('student/dashboard.php');
if (isset($_SESSION['admin_id']))   redirect('admin/dashboard.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Subject Registration System — SEUSL</title>
<link rel="stylesheet" href="css/style.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
html, body { width: 100%; height: 100%; }

/* ── Full Screen Video Hero ── */
.video-hero {
    position: relative;
    width: 100vw;
    height: 100vh;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #fff;
}
.video-hero video {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    min-width: 100%;
    min-height: 100%;
    width: 100vw;
    height: 100vh;
    object-fit: cover;
    z-index: 0;
}
.video-hero .overlay-dark {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(26, 35, 126, 0.60);
    z-index: 1;
}
.video-hero .hero-content {
    position: relative;
    z-index: 2;
    padding: 20px;
}
.video-hero .hero-content h1 {
    font-size: 38px;
    margin-bottom: 14px;
    text-shadow: 0 2px 8px rgba(0,0,0,0.5);
}
.video-hero .hero-content p {
    font-size: 17px;
    opacity: .90;
    max-width: 650px;
    margin: 0 auto 32px;
    text-shadow: 0 1px 4px rgba(0,0,0,0.4);
}

/* ── University Logo ── */
.university-logo {
    display: block;
    margin: 0 auto 22px auto;
    width: 130px;
    height: auto;
    filter: drop-shadow(0 2px 8px rgba(0,0,0,0.4));
}

/* ── Scroll down arrow ── */
.scroll-down {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 2;
    color: #fff;
    font-size: 28px;
    animation: bounce 1.8s infinite;
    cursor: pointer;
    text-decoration: none;
}
@keyframes bounce {
    0%, 100% { transform: translateX(-50%) translateY(0); }
    50%       { transform: translateX(-50%) translateY(10px); }
}

/* ── Feature Boxes ── */
.feature-box {
    text-align: center;
    padding: 24px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,.07);
}
.feature-box .icon { font-size: 36px; margin-bottom: 12px; }
.feature-box h3    { color: #1a237e; margin-bottom: 8px; }
.feature-box p     { color: #666; font-size: 13px; }
</style>
</head>
<body>

<!-- Popup Role Selection -->
<div class="overlay" id="role-popup">
  <div class="popup">
    <h2>🎓 Welcome to SEUSL</h2>
    <p>Student Subject Registration System<br>
       Department of Information Technology</p>
    <p><strong>Please select your role to continue:</strong></p>
    <div class="popup-btn-row">
      <button class="popup-btn student" onclick="goTo('student/register.php')">
        👨‍🎓 Student
      </button>
      <button class="popup-btn admin" onclick="goTo('admin/login.php')">
        🔐 Admin / Lecturer
      </button>
    </div>
  </div>
</div>

<!-- Navbar (fixed transparent on top of video) -->
<nav class="navbar" style="position:fixed;top:0;left:0;width:100%;z-index:999;background:rgba(26,35,126,0.75);backdrop-filter:blur(6px);">
  <div class="brand">SEU<span>SL</span> Registration</div>
  <div class="nav-links">
    <a href="student/login.php" style="color:#fff;">Student Login</a>
    <a href="admin/login.php"   style="color:#fff;">Admin Login</a>
  </div>
</nav>

<!-- FULL SCREEN Hero with Video Background -->
<div class="video-hero" id="hero">
  <video id="bgVideo" autoplay muted loop playsinline preload="auto">
    <source src="assets/SE.mp4" type="video/mp4">
  </video>
  <div class="overlay-dark"></div>

  <div class="hero-content">
    <img src="assets/SEU.png" alt="South Eastern University of Sri Lanka Logo" class="university-logo">
    <h1>Student Subject Registration System</h1>
    <p>A secure, efficient digital platform for academic subject enrollment at the
       Department of Information Technology, South Eastern University of Sri Lanka.</p>
    <button class="btn btn-outline" style="color:#fff;border-color:#fff;padding:12px 32px;font-size:16px;"
            onclick="document.getElementById('role-popup').style.display='flex'">
      Get Started
    </button>
  </div>

  <!-- Bouncing arrow -->
  <a class="scroll-down" href="#features" title="Scroll Down">&#8964;</a>
</div>

<!-- Features Section -->
<div id="features" class="container">
  <div class="grid-3" style="margin-top:40px;margin-bottom:40px;">
    <div class="feature-box">
      <div class="icon">🔒</div>
      <h3>Secure Login</h3>
      <p>OTP-based email verification and role-based access control for students and admins.</p>
    </div>
    <div class="feature-box">
      <div class="icon">📚</div>
      <h3>Easy Registration</h3>
      <p>Register for subjects that match your department and current semester in minutes.</p>
    </div>
    <div class="feature-box">
      <div class="icon">📄</div>
      <h3>PDF Confirmation</h3>
      <p>Download a formatted PDF confirmation of your subject registrations instantly.</p>
    </div>
  </div>
</div>

<footer>
  &copy; <?= date('Y') ?> South Eastern University of Sri Lanka — Department of Information Technology
</footer>

<script>
// ── Popup helpers ──
function goTo(url) {
    document.getElementById('role-popup').style.display = 'none';
    window.location.href = url;
}
document.addEventListener('click', function(e) {
    if (e.target.id === 'role-popup') e.target.style.display = 'none';
});

// ── Force video autoplay + loop ──
window.addEventListener('DOMContentLoaded', function () {
    var vid = document.getElementById('bgVideo');
    vid.muted  = true;
    vid.loop   = true;
    vid.volume = 0;

    var p = vid.play();
    if (p !== undefined) {
        p.catch(function () {
            document.body.addEventListener('click', function () { vid.play(); }, { once: true });
        });
    }

    vid.addEventListener('ended', function () { vid.currentTime = 0; vid.play(); });
    vid.addEventListener('pause', function () { if (!vid.ended) vid.play(); });

    function fillScreen() {
        vid.style.width  = window.innerWidth  + 'px';
        vid.style.height = window.innerHeight + 'px';
    }
    fillScreen();
    window.addEventListener('resize', fillScreen);
});
</script>
</body>
</html>
