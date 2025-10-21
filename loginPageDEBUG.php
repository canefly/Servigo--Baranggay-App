<?php
session_start();
require_once(__DIR__ . "/Database/connection.php");

/* AUTO LOGIN HANDLER */
if (isset($_GET['debug_login'])) {
  $type = $_GET['debug_login'];

  if ($type === 'admin') {
    $email = 'admin1';
    $pass  = 'asdf1234';
    $stmt = $conn->prepare("SELECT id, barangay_name, email, password FROM barangay_admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res && $res['password'] === $pass) {
      $_SESSION['sg_id']    = $res['id'];
      $_SESSION['sg_name']  = 'Admin';
      $_SESSION['sg_brgy']  = $res['barangay_name'];
      $_SESSION['sg_email'] = $res['email'];
      $_SESSION['role']     = "admin";
      header("Location: Barangay/dashboard.php");
      exit;
    }
  }

  if ($type === 'resident') {
    $email = 'resident1';
    $pass  = 'asdf1234';
    $stmt = $conn->prepare("SELECT id, first_name, barangay, email, password FROM residents WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res && $res['password'] === $pass) {
      $_SESSION['sg_id']    = $res['id'];
      $_SESSION['sg_name']  = $res['first_name'];
      $_SESSION['sg_brgy']  = $res['barangay'];
      $_SESSION['sg_email'] = $res['email'];
      $_SESSION['role']     = "resident";
      header("Location: Resident/residentsPage.php");
      exit;
    }
  }
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = trim($_POST['password'] ?? '');

  if ($email === '' || $pass === '') {
    $error = "❌ Please enter both email and password.";
  } else {
    /* Admin Check */
    $stmt = $conn->prepare("SELECT id, barangay_name, email, password FROM barangay_admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($admin = $result->fetch_assoc()) {
      if ($admin['password'] === $pass) {
        $_SESSION['sg_id']    = $admin['id'];
        $_SESSION['sg_name']  = 'Admin';
        $_SESSION['sg_brgy']  = $admin['barangay_name'];
        $_SESSION['sg_email'] = $admin['email'];
        $_SESSION['role']     = "admin";
        header("Location: Barangay/barangayAnnouncements.php");
        exit;
      } else $error = "❌ Incorrect password.";
    } else {
      /* Resident Check */
      $stmt2 = $conn->prepare("SELECT id, first_name, barangay, email, password FROM residents WHERE email = ?");
      $stmt2->bind_param("s", $email);
      $stmt2->execute();
      $res2 = $stmt2->get_result();
      if ($user = $res2->fetch_assoc()) {
        if ($user['password'] === $pass) {
          $_SESSION['sg_id']    = $user['id'];
          $_SESSION['sg_name']  = $user['first_name'];
          $_SESSION['sg_brgy']  = $user['barangay'];
          $_SESSION['sg_email'] = $user['email'];
          $_SESSION['role']     = "resident";
          header("Location: Resident/residentsPage.php");
          exit;
        } else $error = "❌ Incorrect password.";
      } else $error = "❌ No account found with that email.";
      $stmt2->close();
    }
    $stmt->close();
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Servigo · Debug Login</title>
<style>
  :root{
    --bg:#f5f7fa;--card:#ffffff;--text:#222;--muted:#6b7280;
    --brand:#1e40af;--accent:#16a34a;--border:#e5e7eb;
    --shadow:0 4px 16px rgba(0,0,0,.08);--radius:16px;
    --warn:#b91c1c;
  }
  *{box-sizing:border-box}
  html,body{height:100%;margin:0;font-family:system-ui,sans-serif;background:var(--bg);display:grid;place-items:center;}
  .card{
    width:min(440px,100%);
    background:var(--card);
    border-radius:var(--radius);
    box-shadow:var(--shadow);
    padding:30px 24px;
    display:flex;flex-direction:column;gap:16px;
    border:1px solid var(--border);
    position:relative;
  }
  .warning{
    background:rgba(185,28,28,.15);
    border:1px solid rgba(185,28,28,.3);
    color:var(--warn);
    font-weight:700;
    text-align:center;
    padding:10px;
    border-radius:10px;
    text-transform:uppercase;
    letter-spacing:.5px;
  }
  .brand{display:flex;align-items:center;gap:10px;}
  .logo{width:40px;height:40px;border-radius:10px;
    background:linear-gradient(135deg,var(--brand),var(--accent));
    display:grid;place-items:center;color:#fff;font-weight:800;}
  h1{margin:0;color:var(--brand);}
  .btn{
    all:unset;cursor:pointer;text-align:center;
    padding:12px;border-radius:12px;
    font-weight:700;
    color:#fff;background:linear-gradient(135deg,var(--brand),var(--accent));
    transition:transform .1s;
  }
  .btn:hover{transform:scale(1.02);}
  .debug-buttons{
    display:flex;flex-direction:column;gap:10px;margin-top:10px;
  }
  .error{
    background:rgba(239,68,68,.1);
    border:1px solid rgba(239,68,68,.3);
    color:#b91c1c;
    padding:10px;border-radius:10px;text-align:center;
  }
</style>
</head>
<body>
<section class="card">
  <div class="warning">⚠ DEBUG ACCOUNTS ENABLED</div>
  <div class="brand">
    <div class="logo">SG</div>
    <h1>Servigo</h1>
  </div>
  <p class="muted">Quick access for testing accounts below:</p>

  <?php if(!empty($error)): ?>
  <p class="error"><?= $error ?></p>
  <?php endif; ?>

  <div class="debug-buttons">
    <a href="?debug_login=admin" class="btn">Login as Admin (admin1 / asdf1234)</a>
    <a href="?debug_login=resident" class="btn">Login as Resident (resident1 / asdf1234)</a>
  </div>
  <p class="muted" style="font-size:13px;text-align:center;">Manual login is disabled in debug mode.</p>
  <p class="muted" style="font-size:12px;text-align:center;">© 2025 Servigo — Prototype Debug Build</p>
</section>
</body>
</html>
