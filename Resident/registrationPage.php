<?php
require_once(__DIR__ . "/../Database/connection.php");

$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $lname = $_POST['lastName'] ?? '';
  $fname = $_POST['firstName'] ?? '';
  $mname = $_POST['middleName'] ?? '';
  $suffix = $_POST['suffix'] ?? '';
  $phone = $_POST['phone'] ?? '';
  $email = $_POST['email'] ?? '';
  $birthdate = $_POST['birthdate'] ?? '';
  $house_no = $_POST['houseNo'] ?? '';
  $street = $_POST['street'] ?? '';
  $purok = $_POST['purok'] ?? null; // optional
  $subdivision = $_POST['subdivision'] ?? null; // optional
  $barangay = $_POST['barangay'] ?? '';
  $city = $_POST['city'] ?? '';
  $province = $_POST['province'] ?? '';
  $region = $_POST['region'] ?? '';
  $postal = $_POST['postal'] ?? '';
  $nationality = $_POST['nationality'] ?? '';
  $agree = isset($_POST['agree']) ? 1 : 0;
  $updates = isset($_POST['updates']) ? 1 : 0;
  $password = $_POST['password'] ?? '';
  $confirm = $_POST['confirm'] ?? '';

  if ($password !== $confirm) {
    $msg = "❌ Passwords do not match. Please try again.";
  } else {
    $hash = $password; // 🔓 plain password (for now)

    $sql = "INSERT INTO residents (
      last_name, first_name, middle_name, suffix, phone, email, birthdate,
      house_no, street, purok, subdivision, barangay, city, province, region,
      postal, nationality, agree, updates, password, verification_status
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'Unverified')";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
      "ssssssssssssssssssss",
      $lname, $fname, $mname, $suffix, $phone, $email, $birthdate,
      $house_no, $street, $purok, $subdivision, $barangay,
      $city, $province, $region, $postal, $nationality, $agree, $updates, $password
    );

    if ($stmt->execute()) {
      echo "<script>
        alert('✅ Registration successful! You can now log in.');
        window.location.href='loginPage.php';
      </script>";
      exit;
    } else {
      $msg = "❌ Error: " . $stmt->error;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Servigo · Resident Registration</title>

<style>
:root{
  --bg:#f5f7fa; --card:#ffffff; --text:#222; --muted:#6b7280;
  --brand:#047857; --accent:#10b981; --border:#e5e7eb;
  --shadow:0 10px 30px rgba(0,0,0,.08); --radius:18px; --gap:14px; --pad:16px;
}
*{box-sizing:border-box}
body{margin:0;font-family:'Poppins',sans-serif;color:var(--text);background:var(--bg);}
.container{max-width:1100px;margin:0 auto;padding:20px}
.card{
  background:var(--card);border:1px solid var(--border);border-radius:var(--radius);
  padding:var(--pad);box-shadow:var(--shadow)
}
.topbar{position:sticky;top:0;z-index:40;backdrop-filter:blur(10px);
  background:rgba(255,255,255,.85);border-bottom:1px solid var(--border)}
.topbar-inner{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 20px}
.brand{display:flex;align-items:center;gap:12px}
.logo{width:36px;height:36px;border-radius:10px;
  background:linear-gradient(135deg,var(--brand),var(--accent));
  display:grid;place-items:center;font-weight:800;color:#fff}
.brand h1{font-size:18px;margin:0;color:var(--brand)}

.muted{color:var(--muted)}
.divider{height:1px;background:linear-gradient(90deg,transparent,rgba(0,0,0,.1),transparent);margin:10px 0}

label{font-weight:600;display:block;margin-bottom:6px}
.input,select,textarea{
  width:100%;padding:12px 14px;border-radius:12px;
  background:#fff;color:var(--text);border:1px solid var(--border)
}
.input:focus,select:focus{
  border-color:var(--brand);outline:none;box-shadow:0 0 0 3px rgba(4,120,87,.15)
}
.row{display:grid;gap:var(--gap)}
.cols-2{grid-template-columns:repeat(2,1fr)}
.cols-3{grid-template-columns:repeat(3,1fr)}
.cols-4{grid-template-columns:repeat(4,1fr)}
@media(max-width:920px){.cols-3,.cols-4{grid-template-columns:repeat(2,1fr)}}
@media(max-width:560px){.cols-2,.cols-3,.cols-4{grid-template-columns:1fr}}

.btn,.ghost{
  all:unset;cursor:pointer;text-align:center;padding:12px 16px;
  border-radius:12px;font-weight:700;font-size:15px
}
.btn{background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff}
.btn:hover{opacity:.9;transform:translateY(-1px)}
.ghost{background:#f9fafb;color:var(--brand);border:1px solid var(--border)}

.message{
  margin:10px 0;padding:10px 12px;border-radius:12px;font-size:14px;
  text-align:center;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46
}
.error-msg{
  color:#b91c1c;background:#fee2e2;border:1px solid #fecaca;
  padding:10px;border-radius:8px;margin:10px 0;text-align:center
}
footer{text-align:center;color:var(--muted);padding:30px 15px;font-size:13px}
</style>

<script>
function validatePasswords(e){
  const pass=document.getElementById('password');
  const confirm=document.getElementById('confirm');
  const error=document.getElementById('errorMsg');
  if(pass.value!==confirm.value){
    e.preventDefault();
    error.textContent='❌ Passwords do not match.';
    error.style.display='block';
    confirm.focus();
  }else{
    error.style.display='none';
  }
}
</script>
</head>
<body>
  <header class="topbar" role="banner">
    <div class="topbar-inner">
      <div class="brand">
        <div class="logo" aria-hidden="true">SG</div>
        <h1>Servigo · Resident Registration</h1>
      </div>
      <a href="/servigo/loginPage.php" class="ghost" aria-label="Go to Residents Home">Residents Home</a>
    </div>
  </header>

  <main class="container">
    <section class="card" aria-labelledby="reg-title">
      <h2 id="reg-title">Create your resident profile</h2>
      <p class="muted">Fill in your legal name and complete address. Fields marked with * are required.</p>
      <div class="divider"></div>

      <?php if($msg): ?>
        <div class="message"><?= $msg ?></div>
      <?php endif; ?>

      <form id="regForm" class="row" method="POST" onsubmit="validatePasswords(event)">
        <!-- Name -->
        <div class="row cols-4">
          <div><label>Last Name *</label><input class="input" name="lastName" required placeholder="Dela Cruz"></div>
          <div><label>First Name *</label><input class="input" name="firstName" required placeholder="Juan"></div>
          <div><label>Middle Name</label><input class="input" name="middleName" placeholder="Reyes"></div>
          <div><label>Suffix</label>
            <select class="input" name="suffix">
              <option value="">—</option><option>Jr.</option><option>Sr.</option><option>I</option><option>II</option><option>III</option><option>IV</option>
            </select>
          </div>
        </div>

        <!-- Contact -->
        <div class="row cols-3">
          <div><label>Mobile Number *</label><input class="input" name="phone" required placeholder="09XXXXXXXXX" ></div>
          <div><label>Email *</label><input class="input" name="email" required type="email" placeholder="juan@example.com"></div>
          <div><label>Birthdate *</label><input class="input" name="birthdate" required type="date"></div>
        </div>

        <!-- Address -->
        <fieldset class="row" style="border:none;padding:0;margin:0">

          <div class="row cols-4">
            <div><label>House / Lot / Unit # *</label><input class="input" name="houseNo" required placeholder="12-B"></div>
            <div><label>Street *</label><input class="input" name="street" required placeholder="Mabini St."></div>
            <div><label>Purok / Zone</label><input class="input" name="purok" placeholder="Optional"></div>
            <div><label>Subdivision / Sitio</label><input class="input" name="subdivision" placeholder="Optional"></div>
          </div>

          <div class="row cols-3">
            <div><label>Barangay *</label><input class="input" name="barangay" required placeholder="San Isidro"></div>
            <div><label>City / Municipality *</label><input class="input" name="city" required placeholder="Quezon City"></div>
            <div><label>Province *</label><input class="input" name="province" required placeholder="Metro Manila"></div>
          </div>

          <div class="row cols-3">
            <div><label>Region *</label>
              <select class="input" name="region" required>
                <option value="">Select…</option>
                <option>NCR – National Capital Region</option>
                <option>Region III – Central Luzon</option>
                <option>Region IV-A – CALABARZON</option>
                <option>Region V – Bicol Region</option>
              </select>
            </div>
            <div><label>Postal Code *</label><input class="input" name="postal" required placeholder="1100" ></div>
            <div><label>Nationality *</label><input class="input" name="nationality" required placeholder="Filipino"></div>
          </div>
        </fieldset>

        <!-- Password -->
        <div class="row cols-2">
          <div><label>Password *</label><input class="input" id="password" name="password" type="password" required minlength="6" placeholder="Enter password"></div>
          <div><label>Confirm Password *</label><input class="input" id="confirm" name="confirm" type="password" required minlength="6" placeholder="Re-enter password"></div>
        </div>
        <div id="errorMsg" class="error-msg" style="display:none;"></div>

        <!-- Consents -->
        <div class="row cols-2">
          <label class="muted" style="display:flex;gap:10px;align-items:flex-start">
            <input type="checkbox" name="agree" required style="margin-top:6px">
            <span>I certify the information is accurate and I consent to its processing for barangay services.</span>
          </label>
          <label class="muted" style="display:flex;gap:10px;align-items:flex-start">
            <input type="checkbox" name="updates" style="margin-top:6px">
            <span>Send me announcements and updates.</span>
          </label>
        </div>

        <!-- Actions -->
        <div class="row cols-2">
          <button type="submit" class="btn">Create Account</button>
          <button type="reset" class="ghost">Clear</button>
        </div>
      </form>
    </section>
  </main>

  <footer><small>© 2025 Servigo · Barangay Resident Portal</small></footer>
</body>
</html>
