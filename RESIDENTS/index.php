<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Servigo - My Barangay App</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <style>
    :root {
      --bg:#f5f7fa; --bg-2:#ffffff; --card:#ffffff; --text:#222222; --muted:#6b7280;
      --brand:#1e40af; --accent:#16a34a; --border:#e5e7eb;
      --shadow:0 2px 8px rgba(0,0,0,.08); --radius:16px; --gap:14px; --pad:16px;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,Inter,Arial,sans-serif;
      background:var(--bg); color:var(--text);
    }
    .container{max-width:1100px;margin:0 auto;padding:20px}

    /* Topbar */
    .topbar{position:sticky;top:0;z-index:40;backdrop-filter:blur(8px);
      background:#fff;border-bottom:1px solid var(--border)}
    .topbar-inner{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 20px}
    .brand{display:flex;align-items:center;gap:12px}
    .logo{width:36px;height:36px;border-radius:10px;
      background:linear-gradient(135deg,var(--brand),var(--accent));
      display:grid;place-items:center;font-weight:800;color:#fff}
    .brand h1{font-size:18px;margin:0;color:var(--brand)}

    nav a{margin-left:10px;text-decoration:none}
    .btn,.ghost{all:unset;cursor:pointer;padding:10px 14px;border-radius:10px;font-weight:600}
    .btn{background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff}
    .btn:hover{opacity:.9}
    .ghost{background:#f3f4f6;color:var(--text);border:1px solid var(--border)}
    .ghost:hover{background:rgba(30,64,175,.08)}

    /* Hero */
    header.hero{text-align:center;padding:70px 20px 50px}
    header.hero h1{font-size:2.2rem;margin:0 0 12px;color:var(--brand)}
    header.hero p{max-width:600px;margin:0 auto 24px;font-size:1.05rem;color:var(--muted);line-height:1.6}
    header.hero .actions{display:flex;justify-content:center;gap:12px;flex-wrap:wrap}

    /* Features */
    .features{margin:50px auto;max-width:1000px;padding:0 15px}
    .features h2{text-align:center;margin-bottom:25px;font-size:1.6rem;color:var(--brand)}
    .feature-grid{display:grid;gap:var(--gap);grid-template-columns:repeat(auto-fit,minmax(250px,1fr))}
    .feature{padding:18px;border-radius:var(--radius);background:var(--card);border:1px solid var(--border);box-shadow:var(--shadow);
      transition:transform .2s ease, box-shadow .2s ease;}
    .feature:hover{transform:translateY(-4px);box-shadow:0 4px 12px rgba(0,0,0,.12)}
    .feature h3{margin-top:0;font-size:1.15rem;color:var(--brand);display:flex;align-items:center;gap:8px}
    .feature p{color:var(--muted);font-size:.95rem;line-height:1.5;margin:8px 0 0}

    /* Footer */
    footer{color:var(--muted);text-align:center;padding:30px 15px;font-size:14px;border-top:1px solid var(--border)}

    /* Mobile */
    @media(max-width:600px){
      header.hero{padding:50px 15px}
      header.hero h1{font-size:1.6rem}
      header.hero p{font-size:.95rem}
      .features h2{font-size:1.3rem}
      .feature{padding:14px}
      .feature h3{font-size:1rem}
      .feature p{font-size:.9rem}
      .btn,.ghost{padding:8px 12px;font-size:.95rem}
      .topbar-inner{flex-wrap:wrap;justify-content:center;text-align:center}
    }
  </style>
</head>
<body>

  <!-- Topbar -->
  <div class="topbar">
    <div class="topbar-inner">
      <div class="brand">
        <div class="logo">SG</div>
        <h1>Servigo</h1>
      </div>
      <nav>
        <a href="#features" class="ghost">Features</a>
        <a href="loginPage.php" class="btn">Login</a>
      </nav>
    </div>
  </div>

  <!-- Hero -->
  <header class="hero">
    <h1>Barangay Services Made Simple</h1>
    <p>Request documents, get real-time updates, and connect with barangay-verified services—all from one secure platform.</p>
    <div class="actions">
      <a href="#features" class="btn">Explore Features</a>
    </div>
  </header>

  <!-- Features -->
  <section id="features" class="features container">
    <h2>App Highlights</h2>
    <div class="feature-grid">
      <div class="feature">
        <h3><i class='bx bx-file'></i> Document Requests</h3>
        <p>Apply for barangay clearances, residency certificates, and permits without lining up at the hall.</p>
      </div>
      <div class="feature">
        <h3><i class='bx bx-time'></i> Real-Time Tracking</h3>
        <p>Check the status of your requests anytime — Pending, Processing, or Ready for pickup.</p>
      </div>
      <div class="feature">
        <h3><i class='bx bx-megaphone'></i> Announcements</h3>
        <p>Stay updated with schedules, advisories, and emergency alerts straight from officials.</p>
      </div>
      <div class="feature">
        <h3><i class='bx bx-group'></i> Local Services</h3>
        <p>Find trusted, barangay-verified providers like electricians, tutors, barbers, and cleaners.</p>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <p>© 2025 Servigo | Bestlink College of the Philippines</p>
  </footer>

</body>
</html>
