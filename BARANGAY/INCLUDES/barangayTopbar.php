<!-- barangayTopbar.php -->
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
:root {
  --bg: #f5f7fa; --card: #ffffff; --text: #222; --muted: #6b7280;
  --brand: #047857; --accent: #10b981; --border: #e5e7eb;
  --shadow: 0 2px 8px rgba(0,0,0,.08); --radius: 12px;
}

body { margin:0; font-family: system-ui, sans-serif; padding-top: 55px; background: var(--bg); }

.topbar {
  position: sticky; top: 0; z-index: 2000;
  background: var(--card);
  border-bottom: 1px solid var(--border);
  padding: 10px 16px;
  display: flex; align-items: center; justify-content: space-between;
  gap: 12px;
}
.topbar .brand { display: flex; align-items: center; gap: 10px; }
.topbar .logo {
  width: 34px; height: 34px; border-radius: 8px;
  background: linear-gradient(135deg, var(--brand), var(--accent));
  display: grid; place-items: center;
  font-weight: 800; color: #fff;
}
.topbar h1 { margin: 0; font-size: 16px; color: var(--brand); white-space: nowrap; }

/* Hamburger */
.menu-toggle {
  background: none; border: none; cursor: pointer;
  font-size: 24px; color: var(--brand);
  display: none;
}

.right { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.chip {
  background: #f3f4f6; padding: 6px 10px; border-radius: 8px;
  font-size: 13px; color: var(--muted); white-space: nowrap;
}
.user-icon {
  font-size: 22px; color: var(--brand);
  background: #f3f4f6; padding: 6px;
  border-radius: 50%; cursor: pointer;
  transition: 0.2s;
}
.user-icon:hover { background: rgba(4,120,87,.1); }

/* Responsive */
@media(max-width: 1023px){
  .menu-toggle { display: block; }
  .topbar {
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
  }
  .topbar h1 { font-size: 14px; }
  .right { width: 100%; justify-content: space-between; }
  .chip { flex:1;text-align:center;font-size:12px; }
}
</style>

<header class="topbar">
  <div class="brand">
    <button class="menu-toggle" onclick="toggleSidebar()" aria-label="Toggle Sidebar">
      <i class='bx bx-menu'></i>
    </button>
    <div class="logo">SG</div>
    <h1>Servigo · Barangay</h1>
  </div>
  <div class="right">
    <span class="chip">Logged in as: <strong id="adminName">Admin</strong></span>
    <span class="chip">Barangay: <strong id="barangayName">—</strong></span>
    <i class='bx bx-user user-icon'></i>
  </div>
</header>

<script>
  document.getElementById('adminName').textContent =
    localStorage.getItem('bg_admin') || 'Admin';
  document.getElementById('barangayName').textContent =
    localStorage.getItem('bg_name') || '—';
</script>
