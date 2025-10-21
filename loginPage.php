<?php
session_start();
require_once(__DIR__ . "/Database/connection.php");

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = trim($_POST['password'] ?? '');

  if ($email === '' || $pass === '') {
    $error = "❌ Please enter both email and password.";
  } else {

    /* 🟩 Check Barangay Admin first */
    $stmt = $conn->prepare("SELECT id, barangay_name, email, password FROM barangay_admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($admin = $result->fetch_assoc()) {
      if ($admin['password'] === $pass) {
        // ✅ set all admin session values needed everywhere
        $_SESSION['sg_id']          = $admin['id'];
        $_SESSION['sg_name']        = 'Admin';
        $_SESSION['sg_brgy']        = $admin['barangay_name'];
        $_SESSION['sg_email']       = $admin['email'];
        $_SESSION['role']           = "admin";

        // 🟢 global unified keys used by other modules
        $_SESSION['barangay_name']  = $admin['barangay_name'];
        $_SESSION['admin_email']    = $admin['email'];

        header("Location: Barangay/requests.php");
        exit();
      } else {
        $error = "❌ Incorrect password.";
      }
    } else {
      /* 🟦 Check Residents */
      $stmt2 = $conn->prepare("SELECT id, first_name, barangay, email, password FROM residents WHERE email = ?");
      $stmt2->bind_param("s", $email);
      $stmt2->execute();
      $res2 = $stmt2->get_result();

      if ($user = $res2->fetch_assoc()) {
        if ($user['password'] === $pass) {
          $_SESSION['sg_id']         = $user['id'];
          $_SESSION['sg_name']       = $user['first_name'];
          $_SESSION['sg_brgy']       = $user['barangay'];
          $_SESSION['sg_email']      = $user['email'];
          $_SESSION['role']          = "resident";

          // 🟢 unified key for residents too
          $_SESSION['barangay_name'] = $user['barangay'];

          header("Location: Resident/residentsPage.php");
          exit();
        } else {
          $error = "❌ Incorrect password.";
        }
      } else {
        $error = "❌ No account found with that email.";
      }
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
  <title>Servigo · Login</title>
  <style>
    :root {
      --bg:#f5f7fa; --card:#ffffff; --text:#222222; --muted:#6b7280;
      --brand:#1e40af; --accent:#16a34a; --border:#e5e7eb;
      --shadow:0 4px 16px rgba(0,0,0,.08); --radius:16px;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,Inter,Arial,sans-serif;
      background:var(--bg); color:var(--text);
      display:grid; place-items:center; padding:20px;
    }
    .auth-card{
      width:min(420px,100%); background:var(--card);
      border:1px solid var(--border); border-radius:var(--radius);
      box-shadow:var(--shadow); padding:32px 26px;
      display:flex; flex-direction:column; gap:18px;
    }
    .brand{display:flex; align-items:center; gap:10px}
    .logo{
      width:40px; height:40px; border-radius:10px;
      background:linear-gradient(135deg,var(--brand),var(--accent));
      display:grid; place-items:center; font-weight:800; color:#fff;
    }
    .brand h1{margin:0; font-size:22px; color:var(--brand)}
    .muted{color:var(--muted)}
    .small{font-size:13px; text-align:center;}
    form{display:flex; flex-direction:column; gap:14px}
    label{font-weight:600; font-size:.95rem}
    .input{
      width:100%; padding:12px 14px; border-radius:12px;
      border:1px solid var(--border); font-size:15px;
      background:#fff; color:var(--text);
    }
    .input:focus{outline:2px solid rgba(30,64,175,.35); outline-offset:2px}
    .input-group{position:relative; display:flex; align-items:center;}
    .input-group input{flex:1; padding-right:70px}
    .togglePw{
      position:absolute; right:8px; top:50%; transform:translateY(-50%);
      border:none; background:#f3f4f6; color:var(--text);
      padding:6px 10px; border-radius:8px; cursor:pointer; font-weight:600;
    }
    .togglePw:hover{background:#e5e7eb}
    .btn{
      all:unset; cursor:pointer; text-align:center;
      padding:12px 14px; border-radius:12px; font-weight:700;
      background:linear-gradient(135deg,var(--brand),var(--accent)); color:#fff;
    }
    .btn:hover{opacity:.95}
    .error{
      margin:0; padding:10px 12px; font-size:.9rem;
      border:1px solid rgba(239,68,68,.35); background:rgba(239,68,68,.1);
      color:#b91c1c; border-radius:12px;
    }
    .register-hint{
      margin-top:6px; text-align:center; font-size:.95rem; color:var(--muted);
    }
    .register-hint a{color:var(--brand); font-weight:600; text-decoration:none}
    .register-hint a:hover{text-decoration:underline}
    @media(max-width:420px){
      .auth-card{padding:22px 18px}
      .brand h1{font-size:20px}
      .btn{padding:12px}
    }
  </style>
</head>
<body>
  <section class="auth-card">
    <div class="brand">
      <div class="logo">SG</div>
      <h1>Servigo</h1>
    </div>
    <p class="muted">Sign in to your barangay account</p>

    <?php if (!empty($error)): ?>
      <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div>
        <label for="email">Email</label>
        <input id="email" name="email" type="text" class="input" placeholder="e.g., admin1 or resident1" required />
      </div>

      <div>
        <label for="password">Password</label>
        <div class="input-group">
          <input id="password" name="password" type="password" class="input" placeholder="••••••••" required />
          <button type="button" class="togglePw" id="togglePw">Show</button>
        </div>
      </div>

      <button class="btn" type="submit">Sign in</button>
    </form>

    <p class="register-hint">
      Don’t have an account? 
      <a href="registrationPage.php">Create one</a>
    </p>

    <p class="muted small">© 2025 Servigo — Prototype only</p>
  </section>

  <script>
    const pw = document.getElementById('password');
    const tog = document.getElementById('togglePw');
    tog.addEventListener('click', () => {
      const isPw = pw.type === 'password';
      pw.type = isPw ? 'text' : 'password';
      tog.textContent = isPw ? 'Hide' : 'Show';
    });
  </script>
</body>
</html>
