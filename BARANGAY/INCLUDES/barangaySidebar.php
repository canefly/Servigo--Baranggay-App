<!-- barangaySidebar.php -->
<style>
:root {
  --topbar-height: 55px;
  --bg:#f5f7fa; --card:#ffffff; --text:#222; --muted:#6b7280;
  --brand:#047857; --accent:#10b981; --border:#e5e7eb;
  --shadow:0 4px 12px rgba(0,0,0,.12); --radius:14px;
}

/* Base sidebar */
.sidebar {
  position: fixed;
  top: var(--topbar-height);
  left: 0;
  height: calc(100vh - var(--topbar-height));
  width: 240px;
  background: #fff;
  border-right: 1px solid var(--border);
  box-shadow: var(--shadow);
  padding: 20px 14px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  z-index: 3000;
  transform: translateX(-100%);
  transition: transform 0.3s ease;
}
.sidebar.show { transform: translateX(0); }

/* Overlay for mobile */
.sidebar-overlay {
  position: fixed;
  top: var(--topbar-height);
  left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.35);
  display: none;
  z-index: 2500;
}
.sidebar-overlay.active { display: block; }

/* Menu styles */
.sidebar-menu { flex: 1; margin-top: 10px; }
.sidebar-menu ul {
  list-style: none; margin: 0; padding: 0;
  display: flex; flex-direction: column; gap: 6px;
}
.sidebar-menu a {
  display: flex; align-items: center; gap: 12px;
  padding: 10px 12px;
  border-radius: var(--radius);
  color: var(--text); text-decoration: none;
  font-weight: 500;
  transition: 0.2s;
}
.sidebar-menu a:hover,
.sidebar-menu a.active {
  background: linear-gradient(135deg, var(--brand), var(--accent));
  color: #fff;
}

.sidebar-footer {
  font-size: 13px;
  color: var(--muted);
  text-align: center;
  padding-top: 10px;
  border-top: 1px solid var(--border);
}

/* Desktop */
@media (min-width: 1024px) {
  .sidebar {
    transform: none !important;
  }
  .sidebar-overlay { display: none !important; }
  .main-content { margin-left: 240px; padding: 20px; }
}

/* Mobile */
@media (max-width: 1023px) {
  .sidebar {
    width: 220px;
    top: var(--topbar-height);
  }
  .main-content { margin-left: 0; padding: 16px; }
}
</style>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar(false)"></div>

<aside class="sidebar" id="sidebar">
  <nav class="sidebar-menu">
    <ul>
      <li><a href="barangayAnnouncements.php" class="active"><i class='bx bx-list-plus'></i><span> Announcements</span></a></li>
      <li><a href="verification.php"><i class='bx bx-id-card'></i><span> Verification Status</span></a></li>
      <li><a href="requests.php"><i class='bx bx-file'></i><span> Requests</span></a></li>
      <li><a href="settings.php"><i class='bx bx-cog'></i><span> Settings</span></a></li>
      <li><a href="barangayLanding.php"><i class='bx bx-log-out'></i><span> Logout</span></a></li>
    </ul>
  </nav>
  <div class="sidebar-footer">© 2025 Servigo · Barangay</div>
</aside>

<script>
function toggleSidebar(force = null) {
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("sidebarOverlay");
  const isMobile = window.innerWidth < 1024;
  if (!isMobile) return;

  const isOpen = sidebar.classList.contains("show");
  if (force === false || (force === null && isOpen)) {
    sidebar.classList.remove("show");
    overlay.classList.remove("active");
  } else {
    sidebar.classList.add("show");
    overlay.classList.add("active");
  }
}
</script>
