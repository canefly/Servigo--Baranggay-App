<?php include 'INCLUDES/barangaySidebar.php'; ?>
<?php include 'INCLUDES/barangayTopbar.php'; ?>

<style>
body {
  margin: 0;
  font-family: system-ui, sans-serif;
  background: var(--bg);
}

/* Main layout wrapper */
.layout {
  display: flex;
  min-height: 100vh;
  
}

/* Main content */
.main-content {
  flex: 1;
  padding: 80px  20px; /* 80px top = topbar */
  transition: margin-left 0.3s ease;
}

@media (min-width: 1024px) {
  .main-content {
    margin-left: 260px; /* width of sidebar */
  }
}
</style>

<div class="layout">
  <main class="main-content">
    <h2>Welcome, Barangay Admin!</h2>
    <p>This is your dashboard content.</p>
  </main>
</div>
