<?php
require_once(__DIR__ . "/../Database/session-checker.php");
requireRole("resident");
require_once(__DIR__ . "/../Database/connection.php");
include 'Components/topbar.php';

$resident_id = $_SESSION['sg_id'] ?? null;
$barangay    = $_SESSION['sg_brgy'] ?? 'Unknown Barangay';

/* ===============================
   FETCH APPROVED + ACTIVE STORES
================================= */
$stmt = $conn->prepare("
  SELECT store_name, address, open_hours, contact, classification, category AS service_type, photo_url, is_closed_today, is_closed_forever
  FROM barangay_services
  WHERE barangay_name=? 
    AND status='Approved'
    AND (is_closed_forever=0 OR is_closed_forever IS NULL)
  ORDER BY submitted_at DESC
");
$stmt->bind_param("s", $barangay);
$stmt->execute();
$stores = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Servigo · Stores & Services</title>

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300..700&family=Parkinsans:wght@300..700&display=swap" rel="stylesheet">

<!-- Icons -->
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<style>
:root {
  --accent:#16a34a;
  --bg:#fff;
  --border:#e5e7eb;
  --brand:#1e40af;
  --muted:#6b7280;
  --radius:14px;
  --text:#1e1e1e;
}
body {
  background:var(--bg);
  color:var(--text);
  font-family:"Parkinsans","Outfit",sans-serif;
  margin:0;
  padding:0;
}
.wrapper {display:flex;flex-direction:column;overflow-x:hidden;width:100%;}
.header-full {
  align-items:flex-start;
  display:flex;
  flex-wrap:wrap;
  gap:24px;
  justify-content:space-between;
  padding:40px 6vw 20px;
  width:100%;
}
.hero {flex:1 1 600px;}
.hero h1 {color:var(--brand);font-size:2.3rem;font-weight:700;margin-bottom:6px;}
.hero p {color:var(--muted);font-size:1rem;margin-bottom:18px;max-width:500px;}
.search-box {align-items:center;display:flex;flex-wrap:wrap;gap:10px;}
.search-box input {
  border:1px solid var(--border);
  border-radius:10px;
  flex:1;
  font-size:.95rem;
  max-width:320px;
  min-width:200px;
  padding:10px 14px;
}
.btn-gradient {
  background:linear-gradient(135deg,var(--brand),var(--accent));
  border:none;border-radius:10px;color:#fff;
  font-weight:600;padding:10px 16px;transition:.2s;cursor:pointer;
}
.btn-gradient:hover{opacity:.9;}
.apply-section {
  background:#fff;
  border:1px solid var(--border);
  border-radius:var(--radius);
  box-shadow:0 3px 10px rgba(0,0,0,.08);
  flex:0 0 360px;
  padding:24px 26px;
  text-align:center;
}
.apply-section h2 {
  color:var(--brand);
  font-size:1.1rem;
  font-weight:700;
  margin-bottom:6px;
  display:flex;align-items:center;justify-content:center;gap:6px;
}
.apply-section p {color:var(--muted);font-size:.9rem;margin-bottom:12px;}
.store-grid {
  display:grid;
  gap:24px;
  justify-content:center;
  grid-template-columns:repeat(auto-fit,minmax(280px,300px));
  margin:0 auto;
  padding:20px 6vw 60px;
  width:100%;
}
.card {
  border:1px solid var(--border);
  border-radius:var(--radius);
  box-shadow:0 2px 8px rgba(0,0,0,.05);
  overflow:hidden;
  background:#fff;
  position:relative;
  transition:box-shadow .15s ease,transform .15s ease;
}
.card:hover {box-shadow:0 4px 12px rgba(0,0,0,.1);transform:translateY(-2px);}
.card img {width:100%;height:180px;object-fit:cover;border-bottom:1px solid var(--border);}
.closed-banner {
  position:absolute;
  top:10px;left:10px;
  background:rgba(220,38,38,.9);
  color:#fff;
  font-weight:600;
  padding:4px 10px;
  border-radius:8px;
  font-size:.85rem;
}
.card-body {padding:14px 16px;}
.card-body h5 {color:var(--brand);font-weight:600;font-size:1.05rem;margin:0 0 4px;}
.card-text {color:var(--muted);font-size:.9rem;margin-bottom:6px;}
.tag {
  background:rgba(30,64,175,.08);
  border-radius:8px;
  color:var(--brand);
  display:inline-block;
  font-size:.8rem;
  padding:3px 9px;
  margin-right:4px;
}
footer {color:var(--muted);font-size:.9rem;padding:20px;text-align:center;width:100%;}
@media (max-width:992px){
  .header-full {display:block;padding:24px 24px 0;text-align:center;}
  .hero p {margin:auto;margin-bottom:12px;}
  .apply-section {flex:unset;margin:10px auto 0;max-width:90%;}
}
</style>
</head>

<body>
<div class="wrapper">

  <!-- Hero + Apply -->
  <div class="header-full">
    <section class="hero">
      <h1>Barangay Stores & Services</h1>
      <p>Discover verified local shops and trusted home services within your barangay.</p>
      <div class="search-box">
        <input type="text" id="searchStore" placeholder="Search store or service...">
        <button class="btn-gradient" onclick="filterStores()"><i class='bx bx-search'></i>Search</button>
      </div>
    </section>

    <section class="apply-section">
      <h2><i class='bx bx-store-alt'></i> Want your store listed?</h2>
      <p>Be part of the barangay’s verified directory and reach more residents.</p>
      <a href="myStore.php" style="text-decoration:none;">
        <button class="btn-gradient mt-2">✨ Apply Now</button>
      </a>
    </section>
  </div>

  <!-- Store Grid -->
  <div class="store-grid" id="storeGrid">
    <?php if($stores->num_rows): while($s=$stores->fetch_assoc()): ?>
      <div class="card">
        <?php if($s['photo_url']): ?>
          <img src="../<?= htmlspecialchars($s['photo_url']) ?>" alt="Store photo">
        <?php else: ?>
          <img src="../uploads/default_store.jpg" alt="No photo">
        <?php endif; ?>
        <?php if($s['is_closed_today']): ?>
          <div class="closed-banner">Closed Today</div>
        <?php endif; ?>
        <div class="card-body">
          <h5><?= htmlspecialchars($s['store_name']) ?></h5>
          <p class="card-text"><i class='bx bx-map'></i> <?= htmlspecialchars($s['address']) ?></p>
          <p class="card-text"><i class='bx bx-time-five'></i> <?= htmlspecialchars($s['open_hours']) ?></p>
          <p class="card-text"><i class='bx bx-phone'></i> <?= htmlspecialchars($s['contact']) ?></p>
          <span class="tag">
            <?= strtolower($s['classification'])=='licensed'?'📄 Licensed Business':'🏠 Barangay-Approved' ?>
          </span>
          <span class="tag">
            <?= strtolower($s['service_type'])=='home'?'🚗 Home Service':'🏪 Fixed Location' ?>
          </span>
        </div>
      </div>
    <?php endwhile; else: ?>
      <p style="color:var(--muted);text-align:center;">No approved services yet in your barangay.</p>
    <?php endif; ?>
  </div>

  <footer>© 2025 Servigo. All rights reserved.</footer>
</div>

<script>
function filterStores(){
  const term=document.getElementById('searchStore').value.toLowerCase();
  document.querySelectorAll('.store-grid .card').forEach(c=>{
    c.style.display=c.innerText.toLowerCase().includes(term)?'block':'none';
  });
}
</script>
</body>
</html>
