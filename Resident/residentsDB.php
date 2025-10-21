<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Servigo Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
  --bg:#f5f7fa; 
  --card:#ffffff; 
  --text:#222222; 
  --muted:#6b7280;
  --brand:#1e40af;    /* official deep blue */
  --accent:#16a34a;   /* government green */
  --border:#e5e7eb;
  --radius:18px; 
  --shadow:0 2px 8px rgba(0,0,0,.08);
}

body {
  margin:0; 
  font-family:'Poppins', sans-serif; 
  background:var(--bg); 
  color:var(--text);
}

/* Hamburger */
.menu-toggle {
  position:fixed; top:16px; left:16px; z-index:1100;
  background:none; border:none; cursor:pointer;
}
.hamburger span {
  display:block; width:26px; height:3px; margin:5px 0;
  background:var(--text); border-radius:2px;
}

/* Sidebar */
.sidebar {
  position:fixed;
  top:60px; /* offset = header height */
  left:0;
  height:calc(100vh - 60px);
  width:260px;
  background:var(--card);
  border-right:1px solid var(--border);
  box-shadow:var(--shadow);
  transform:translateX(-100%);
  transition:transform .3s ease;
  z-index:1050;
  padding:20px;
}
.sidebar.show { transform:translateX(0); }

.sidebar-header {
  display:flex; align-items:center; gap:10px; margin-bottom:20px;
}
.sidebar-header .logo {
  width:40px; height:40px; border-radius:10px;
  background:linear-gradient(135deg,var(--brand),var(--accent));
  display:grid; place-items:center;
  color:#fff; font-size:18px; font-weight:700;
}
.sidebar-header h2 { margin:0; font-size:18px; }

.sidebar-menu ul { list-style:none; padding:0; margin:0; }
.sidebar-menu li { margin-bottom:10px; }
.sidebar-menu a {
  display:flex; align-items:center; gap:12px;
  padding:12px 14px; border-radius:12px;
  text-decoration:none; color:var(--text); font-weight:500;
}
.sidebar-menu a:hover, .sidebar-menu a.active {
  background:rgba(30,64,175,.08); 
  border:1px solid var(--brand);
  color:var(--brand);
}

/* Overlay */
.overlay {
  position:fixed; top:0; left:0; width:100%; height:100%;
  background:rgba(0,0,0,.35);
  opacity:0; visibility:hidden;
  transition:.3s; z-index:1000;
}
.overlay.show { opacity:1; visibility:visible; }

/* Responsive */
@media(min-width:992px){
  .sidebar { transform:translateX(0); }
  .menu-toggle, .overlay { display:none; }
}

/* Accessibility tweaks */
body.no-scroll { overflow:hidden; }
.sidebar-menu a { transition:background .2s ease; }
.sidebar-menu a:focus { outline:2px solid var(--brand); }


  </style>
</head>
<body>

  
  

 <!-- Hamburger -->
<button class="menu-toggle" onclick="toggleMenu()" aria-label="Toggle navigation" aria-expanded="false">
  <div class="hamburger"><span></span><span></span><span></span></div>
</button>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="logo">SG</div>
    <h2>Servigo</h2>
  </div>
  <nav class="sidebar-menu">
    <ul>
      <li><a href="residentsPage.php" class="active"><i class="fas fa-file-alt"></i><span>Requests</span></a></li>
      <li><a href="index.php?page=announcements"><i class="fas fa-bullhorn"></i><span>Announcements</span></a></li>
      <li><a href="index.php?page=files"><i class="fas fa-folder-open"></i><span>File Library</span></a></li>
      <li><a href="index.php?page=analytics"><i class="fas fa-chart-line"></i><span>Analytics</span></a></li>
      <li><a href="index.php?page=audit"><i class="fas fa-history"></i><span>Audit Logs</span></a></li>
      <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
    </ul>
  </nav>
</aside>

<!-- Overlay -->
<div class="overlay" id="overlay" onclick="toggleMenu()"></div>

<script>
  function toggleMenu() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const toggleBtn = document.querySelector('.menu-toggle');

    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');

    const expanded = toggleBtn.getAttribute('aria-expanded') === 'true';
    toggleBtn.setAttribute('aria-expanded', !expanded);

    document.body.classList.toggle('no-scroll', sidebar.classList.contains('show'));
  }
</script>

<style>
  body.no-scroll { overflow:hidden; }
  .sidebar-menu a { transition:background .2s ease; }
  .sidebar-menu a:focus { outline:2px solid var(--brand); }
</style>

</body>
</html>
