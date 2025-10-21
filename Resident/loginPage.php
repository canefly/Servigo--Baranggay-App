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
    .small{font-size:13px; text-align: center;}

    form{display:flex; flex-direction:column; gap:14px}
    label{font-weight:600; font-size:.95rem}
    .input{
      width:100%; padding:12px 14px; border-radius:12px;
      border:1px solid var(--border); font-size:15px;
      background:#fff; color:var(--text);
    }
    .input:focus{outline:2px solid rgba(30,64,175,.35); outline-offset:2px}

    .input-group{
      position:relative; display:flex; align-items:center;
    }
    .input-group input{flex:1; padding-right:70px}
    .togglePw{
      position:absolute; right:8px; top:50%; transform:translateY(-50%);
      border:none; background:#f3f4f6; color:var(--text);
      padding:6px 10px; border-radius:8px; cursor:pointer; font-weight:600;
    }
    .togglePw:hover{background:#e5e7eb}

    .row{display:flex; justify-content:space-between; align-items:center}
    .check{display:flex; align-items:center; gap:6px; cursor:pointer}
    .check input{accent-color:var(--brand)}

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

    <form id="loginForm" novalidate>
      <div>
        <label for="email">Email</label>
        <input id="email" name="email" type="email" class="input" placeholder="e.g., juan@demo.gov.ph" required />
      </div>

      <div>
        <label for="password">Password</label>
        <div class="input-group">
          <input id="password" name="password" type="password" class="input" placeholder="••••••••" required />
          <button type="button" class="togglePw" id="togglePw">Show</button>
        </div>
      </div>

      <button class="btn" type="submit">Sign in</button>
      <p class="error" id="error" hidden></p>
    </form>

    <p class="register-hint">
      Don’t have an account? 
      <a href="registrationPage.php">Create one</a>
    </p>

    <p class="muted small">© 2025 Servigo — Prototype only</p>
  </section>

  <script>
    const SUPABASE_URL = "https://hlyjmgwpufqtghwnpgfe.supabase.co/";
    const SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhseWptZ3dwdWZxdGdod25wZ2ZlIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTc3NzYxMjEsImV4cCI6MjA3MzM1MjEyMX0.G0ocq2K1DAHqM5zn3ZfyflUd5gH2QS27_TY548ZgEOw"; // 🔑 replace with your anon/public key

    const form = document.getElementById('loginForm');
    const err  = document.getElementById('error');
    const pw   = document.getElementById('password');
    const tog  = document.getElementById('togglePw');

    // toggle password visibility
    tog.addEventListener('click', () => {
      const isPw = pw.type === 'password';
      pw.type = isPw ? 'text' : 'password';
      tog.textContent = isPw ? 'Hide' : 'Show';
    });

    form.addEventListener('submit', async (e) => {
    e.preventDefault();
    err.hidden = true;

    const email = form.email.value.trim();
    const pass  = form.password.value.trim();

    if (!email || !pass) {
      err.textContent = "❌ Please enter both email and password.";
      err.hidden = false;
      return;
    }

    try {
      // query residents table
      const res = await fetch(`${SUPABASE_URL}/rest/v1/residents?email=eq.${encodeURIComponent(email)}`, {
        method: "GET",
        headers: {
          "apikey": SUPABASE_KEY,
          "Authorization": "Bearer " + SUPABASE_KEY
        }
      });

      const result = await res.json();

      if (res.ok && result.length > 0) {
        const user = result[0];
        if (user.password === pass) {
        // Save user info
        localStorage.setItem("sg_id", user.id);          // ✅ add this
        localStorage.setItem("sg_name", user.first_name);
        localStorage.setItem("sg_brgy", user.barangay);
        localStorage.setItem("sg_email", user.email);

        alert("✅ Login successful. Welcome " + user.first_name + "!");
        window.location.href = "residentsPage.php";
      } else {
          err.textContent = "❌ Incorrect password.";
          err.hidden = false;
        }
      } else {
        err.textContent = "❌ No account found with that email.";
        err.hidden = false;
      }
    } catch (error) {
      err.textContent = "❌ Error: " + error.message;
      err.hidden = false;
    }
  });
  </script>
</body>
</html>
