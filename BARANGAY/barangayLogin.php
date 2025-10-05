<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Servigo · Barangay Login</title>
  <style>
    :root {
      --bg:#f5f7fa; --card:#fff; --brand:#047857; --accent:#10b981;
      --text:#1f2937; --muted:#6b7280; --border:#e5e7eb;
      --shadow:0 6px 16px rgba(0,0,0,.08); --radius:16px;
    }

    * { box-sizing:border-box; }

    body {
      margin:0;
      display:flex; align-items:center; justify-content:center;
      min-height:100vh;
      font-family:system-ui,-apple-system,Segoe UI,Roboto,Inter,sans-serif;
      background:var(--bg);
      padding:16px;
    }

    .card {
      background:var(--card);
      border:1px solid var(--border);
      border-radius:var(--radius);
      box-shadow:var(--shadow);
      width:100%; max-width:400px;
      padding:28px 26px;
      display:flex; flex-direction:column; gap:18px;
      animation:fadeIn .4s ease;
    }

    @keyframes fadeIn {
      from {opacity:0; transform:translateY(10px);}
      to {opacity:1; transform:translateY(0);}
    }

    .brand {
      display:flex; align-items:center; gap:10px; margin-bottom:10px;
    }
    .logo {
      width:42px; height:42px; border-radius:10px;
      background:linear-gradient(135deg,var(--brand),var(--accent));
      display:grid; place-items:center; color:#fff; font-weight:800; font-size:18px;
    }
    h2 {
      margin:0; font-size:18px; color:var(--brand);
    }

    label {
      font-weight:600; font-size:.95rem; margin-top:8px;
      display:block; color:var(--text);
    }

    input {
      width:100%; padding:12px 14px; margin-top:6px;
      border-radius:12px; border:1px solid var(--border);
      font-size:15px; background:#fff; color:var(--text);
      transition:border-color .2s, box-shadow .2s;
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
      transition:opacity .2s;
    }
    .btn:hover { opacity:.95; }

    .error {
      margin:0; padding:10px 12px; font-size:.9rem;
      border-radius:10px; border:1px solid rgba(239,68,68,.4);
      background:#fee2e2; color:#b91c1c;
      display:none;
    }

    .muted { font-size:.85rem; color:var(--muted); text-align:center; }

    @media(max-width:480px){
      .card { padding:22px 18px; }
      .logo { width:36px; height:36px; font-size:16px; }
      h2 { font-size:15px; }
    }
  </style>
</head>
<body>
  <form class="card" id="loginForm">
    <div class="brand">
      <div class="logo">SG</div>
      <h2>Servigo · Barangay Admin Login</h2>
    </div>

    <div>
      <label for="email">Email</label>
      <input id="email" type="email" name="email" placeholder="admin@barangay.ph" required>
    </div>

    <div>
      <label for="password">Password</label>
      <input id="password" type="password" name="password" placeholder="••••••••" required>
    </div>

    <button class="btn" type="submit">Login</button>
    <p id="error" class="error"></p>
    <p class="muted">© 2025 Servigo · Barangay</p>
  </form>

<script>
const SUPABASE_URL = "https://hlyjmgwpufqtghwnpgfe.supabase.co/";
const SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhseWptZ3dwdWZxdGdod25wZ2ZlIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTc3NzYxMjEsImV4cCI6MjA3MzM1MjEyMX0.G0ocq2K1DAHqM5zn3ZfyflUd5gH2QS27_TY548ZgEOw"; // replace with your Supabase anon key

document.getElementById('loginForm').addEventListener('submit', async(e)=>{
  e.preventDefault();
  const form = e.target;
  const error = document.getElementById('error');

  const email = form.email.value.trim();
  const pass  = form.password.value.trim();

  const res = await fetch(`${SUPABASE_URL}/rest/v1/barangay_admins?email=eq.${encodeURIComponent(email)}`, {
    method:"GET",
    headers:{
      "apikey":SUPABASE_KEY,
      "Authorization":"Bearer "+SUPABASE_KEY
    }
  });

  const result = await res.json();

  if(res.ok && result.length>0){
    const user = result[0];
    if(user.password === pass){
      localStorage.setItem("bg_admin", user.admin_name || "Admin");
      localStorage.setItem("bg_name", user.barangay_name || "Barangay");
      window.location.href="barangayAnnouncements.php";
    } else {
      error.textContent="❌ Incorrect password.";
      error.style.display="block";
    }
  } else {
    error.textContent="❌ No account found.";
    error.style.display="block";
  }
});
</script>
</body>
</html>
