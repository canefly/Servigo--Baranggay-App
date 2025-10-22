<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Servigo · Barangay Registration</title>
  <style>
    :root {
      --bg:#f5f7fa; --card:#fff; --brand:#047857; --accent:#10b981;
      --text:#1f2937; --muted:#6b7280; --border:#e5e7eb;
      --shadow:0 4px 12px rgba(0,0,0,.08); --radius:16px;
    }
    * { box-sizing:border-box; }

    body {
      margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,Inter,sans-serif;
      background:var(--bg);
    }

    /* 🔹 Topbar */
    .topbar {
      position:sticky; top:0; z-index:100;
      background:var(--card);
      border-bottom:1px solid var(--border);
      box-shadow:var(--shadow);
    }
    .topbar-inner {
      display:flex; justify-content:space-between; align-items:center;
      padding:12px 20px;
    }
    .brand { display:flex; align-items:center; gap:10px; }
    .logo {
      width:36px; height:36px; border-radius:8px;
      background:linear-gradient(135deg,var(--brand),var(--accent));
      display:grid; place-items:center; font-weight:800; color:#fff;
    }
    .brand h1 {
      margin:0; font-size:18px; color:var(--brand);
    }
    .ghost {
      all:unset; cursor:pointer; font-weight:600;
      background:#f3f4f6; padding:8px 14px; border-radius:8px;
      border:1px solid var(--border); font-size:.9rem; color:var(--text);
    }
    .ghost:hover { background:#e5e7eb; }

    /* 🔹 Card form */
    .container {
      display:flex; justify-content:center; align-items:center;
      padding:30px 16px;
    }
    .card {
      background:var(--card); border:1px solid var(--border);
      border-radius:var(--radius); box-shadow:var(--shadow);
      width:100%; max-width:500px; padding:28px 26px;
      display:flex; flex-direction:column; gap:14px;
    }
    h2 { margin:0 0 10px; color:var(--brand); font-size:20px; }

    label { font-weight:600; font-size:.95rem; margin-top:6px; display:block; color:var(--text); }
    input {
      width:100%; padding:12px 14px; margin-top:6px;
      border-radius:12px; border:1px solid var(--border);
      font-size:15px;
      transition:.2s;
    }
    input:focus {
      border-color:var(--accent);
      outline:none;
      box-shadow:0 0 0 3px rgba(16,185,129,.15);
    }

    .btn {
      all:unset; cursor:pointer; text-align:center;
      padding:12px 14px; border-radius:12px; font-weight:700;
      background:linear-gradient(135deg,var(--brand),var(--accent));
      color:#fff; margin-top:14px;
    }
    .btn:hover { opacity:.95; }

    .error,.ok {
      margin:0; padding:10px 12px; border-radius:10px; font-size:.9rem;
      display:none;
    }
    .error { background:#fee2e2; border:1px solid #ef4444; color:#b91c1c; }
    .ok { background:#dcfce7; border:1px solid #22c55e; color:#166534; }

    .muted { font-size:.85rem; color:var(--muted); text-align:center; margin-top:10px; }

    @media(max-width:480px){
      .brand h1 { font-size:15px; }
      .card { padding:22px 18px; }
    }
  </style>
</head>
<body>

  <!-- 🔹 Topbar -->
  <header class="topbar" role="banner">
    <div class="topbar-inner">
      <div class="brand">
        <div class="logo">SG</div>
        <h1>Servigo · Barangay Registration</h1>
      </div>
      <a href="barangayLanding.php" class="ghost">← Back to Residents</a>
    </div>
  </header>

  <!-- 🔹 Registration Form -->
  <main class="container">
    <form class="card" id="regForm">
      <h2>Create Barangay Account</h2>

      <label>Barangay Name *</label>
      <input type="text" name="barangay_name" required>
      <label>City *</label>
      <input type="text" name="city" required>
      <label>Province *</label>
      <input type="text" name="province" required>
      <label>Region *</label>
      <input type="text" name="region" required>
      <label>Email *</label>
      <input type="email" name="email" required>
      <label>Password *</label>
      <input type="password" name="password" required>
      <label>Contact No *</label>
      <input type="text" name="contact_no" required>

      <button class="btn" type="submit">Register</button>
      <p id="msg" class="error"></p>
      <p class="muted">© 2025 Servigo · Barangay</p>
    </form>
  </main>

<script>
const SUPABASE_URL = "https://hlyjmgwpufqtghwnpgfe.supabase.co/";
const SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhseWptZ3dwdWZxdGdod25wZ2ZlIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTc3NzYxMjEsImV4cCI6MjA3MzM1MjEyMX0.G0ocq2K1DAHqM5zn3ZfyflUd5gH2QS27_TY548ZgEOw";

document.getElementById('regForm').addEventListener('submit', async(e)=>{
  e.preventDefault();
  const form = e.target;
  const msg = document.getElementById('msg');

  const data = Object.fromEntries(new FormData(form).entries());

  const res = await fetch(`${SUPABASE_URL}/rest/v1/barangay_admins`, {
    method:"POST",
    headers:{
      "apikey":SUPABASE_KEY,
      "Authorization":"Bearer "+SUPABASE_KEY,
      "Content-Type":"application/json",
      "Prefer":"return=representation"
    },
    body:JSON.stringify(data)
  });

  const result = await res.json();
  if(res.ok){
    msg.className="ok";
    msg.textContent="✅ Registration successful!";
    msg.style.display="block";
    form.reset();
  } else {
    msg.className="error";
    msg.textContent="❌ "+(result.message || JSON.stringify(result));
    msg.style.display="block";
  }
});
</script>

</body>
</html>


