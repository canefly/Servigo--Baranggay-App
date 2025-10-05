<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Servigo · Barangay Portal</title>
  <style>
    :root {
      --bg:#f5f7fa; --card:#fff; --brand:#047857; --accent:#10b981;
      --text:#1f2937; --muted:#6b7280; --border:#e5e7eb;
      --shadow:0 6px 16px rgba(0,0,0,.08); --radius:16px;
    }

    * { box-sizing:border-box; }

    body {
      margin:0;
      font-family:system-ui,-apple-system,Segoe UI,Roboto,Inter,sans-serif;
      background:var(--bg);
      display:flex; align-items:center; justify-content:center;
      min-height:100vh;
      padding:20px;
    }

    .card {
      background:var(--card);
      border:1px solid var(--border);
      border-radius:var(--radius);
      box-shadow:var(--shadow);
      text-align:center;
      width:100%; max-width:420px;
      padding:32px 26px;
      animation:fadeIn .4s ease;
    }

    @keyframes fadeIn {
      from {opacity:0; transform:translateY(10px);}
      to {opacity:1; transform:translateY(0);}
    }

    .logo {
      width:56px; height:56px; border-radius:12px;
      background:linear-gradient(135deg,var(--brand),var(--accent));
      display:grid; place-items:center;
      color:#fff; font-weight:700; font-size:20px;
      margin:0 auto 16px;
    }

    h1 {
      margin:0;
      font-size:22px;
      color:var(--brand);
    }

    p {
      margin:12px 0 24px;
      color:var(--muted);
      font-size:.95rem;
    }

    .btn, .ghost {
      all:unset;
      display:block; width:100%;
      padding:12px 0px;
      border-radius:12px;
      font-weight:600; font-size:15px;
      cursor:pointer; margin:10px 0;
      transition:opacity .2s;
    }

    .btn {
      background:linear-gradient(135deg,var(--brand),var(--accent));
      color:#fff; text-align:center;
    }
    .btn:hover { opacity:.95; }

    .ghost {
      background:#f9fafb; color:var(--brand);
      border:1px solid var(--border);
      text-align:center;
    }
    .ghost:hover { background:#f3f4f6; }

    footer {
      margin-top:16px;
      font-size:.8rem;
      color:var(--muted);
    }

    @media(max-width:480px){
      .card { padding:24px 18px; }
      h1 { font-size:18px; }
      .logo { width:48px; height:48px; font-size:18px; }
    }
  </style>
</head>
<body>
  <section class="card">
    <div class="logo">BG</div>
    <h1>Servigo · Barangay Portal</h1>
    <p>Welcome Barangay Admin. Please register or sign in.</p>
    <a href="barangayLogin.php" class="btn">Login</a>
    <a href="barangayRegister.php" class="ghost">Register</a>
    <footer>© 2025 Servigo · Barangay</footer>
  </section>
</body>
</html>
