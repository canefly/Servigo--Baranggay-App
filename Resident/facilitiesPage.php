<?php
require_once(__DIR__ . "/../Database/session-checker.php");
requireRole("resident");
require_once(__DIR__ . "/../Database/connection.php");
include 'Components/topbar.php';

$resident_id = $_SESSION['sg_id'] ?? null;
$barangay    = $_SESSION['sg_brgy'] ?? 'Unknown Barangay';

/* ---------- Filters ---------- */
$type = $_GET['type'] ?? 'All';

/* ---------- Fetch Facilities ---------- */
$sql = "SELECT id, facility_name, address, type_primary, type_secondary, status, photo_url 
        FROM barangay_facilities
        WHERE barangay_name = ?";
$params = [$barangay];
$typestr = "s";

if ($type !== '' && $type !== 'All') { 
  $sql .= " AND (type_primary = ? OR type_secondary = ?)";
  $params[] = $type; 
  $params[] = $type; 
  $typestr .= "ss"; 
}
$sql .= " ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($typestr, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$facilities = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Servigo · Facilities</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&family=Parkinsans:wght@300..800&display=swap" rel="stylesheet">
<style>
:root {
  --brand-green:#047857; --accent-blue:#3b82f6;
  --bg:#f3f4f6; --white:#fff; --text:#1e293b; --muted:#6b7280;
  --radius:14px; --shadow:0 2px 10px rgba(0,0,0,.08);
}

/* Base */
*{box-sizing:border-box;margin:0;padding:0;}
body{
  font-family:"Parkinsans","Outfit",system-ui,sans-serif;
  background:var(--bg);color:var(--text);
  min-height:100vh;display:flex;flex-direction:column;
}
main{flex:1;width:100%;max-width:1600px;margin:0 auto;padding:1rem 1rem 3rem;}

/* Header */
.page-header{display:flex;align-items:center;gap:.6rem;margin:.5rem 0 1rem;}
.page-header i{color:var(--brand-green);font-size:1.35rem;}
.page-header h2{color:var(--brand-green);font-size:1.6rem;font-weight:700;}

/* Tabs */
.tabs{
  display:flex;gap:.75rem;border-bottom:2px solid #e5e7eb;margin-bottom:.75rem;
  overflow-x:auto;white-space:nowrap;padding:0 .75rem;
}
.tabs::-webkit-scrollbar{height:6px;}
.tabs::-webkit-scrollbar-thumb{background:var(--accent-blue);border-radius:10px;}
.filter-tab{
  background:none;border:none;font-size:.95rem;font-weight:600;
  color:var(--muted);padding:.45rem .9rem;cursor:pointer;border-radius:999px;
  transition:background .3s,color .3s;flex-shrink:0;
}
.filter-tab:hover{background:#e9eefb;}
.filter-tab.active{color:#fff;background:var(--brand-green);}

/* Legend Bar */
.legend-bar{
  display:flex;gap:1rem;align-items:center;
  background:#fff;border:1px solid #e5e7eb;
  border-radius:var(--radius);box-shadow:var(--shadow);
  padding:.6rem 1rem;margin-bottom:1rem;font-size:.9rem;
}
.legend-item{display:flex;align-items:center;gap:.45rem;}
.legend-dot{width:12px;height:12px;border-radius:50%;}
.good-dot{background:#10b981;}
.closed-dot{background:#ef4444;}
.maint-dot{background:#facc15;}

/* Facilities */
.facilities-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(220px,1fr));
  gap:1rem;
}
.facility-card{
  background:var(--white);
  border-radius:var(--radius);
  box-shadow:var(--shadow);
  overflow:hidden;
  display:flex;
  flex-direction:column;
  transition:transform .25s ease,box-shadow .25s ease;
}
.facility-card:hover{transform:translateY(-4px);box-shadow:0 6px 20px rgba(0,0,0,.08);}
.facility-photo img{
  width:100%;
  height:130px;
  object-fit:cover;
  display:block;
}
.facility-body{
  padding:.75rem .9rem;
  display:flex;
  flex-direction:column;
  gap:.4rem;
}
.facility-name{
  font-size:1rem;
  font-weight:700;
  color:var(--text);
  text-overflow:ellipsis;
  white-space:nowrap;
  overflow:hidden;
}
.facility-meta{
  font-size:.85rem;
  color:var(--muted);
  line-height:1.3;
}
.facility-meta i{margin-right:.35rem;color:var(--accent-blue);}
.facility-status{
  font-size:.75rem;font-weight:600;
  padding:.25rem .6rem;
  border-radius:7px;
  color:#fff;
  width:max-content;
  margin-top:.25rem;
}
.facility-status.good{background:#10b981;}
.facility-status.closed{background:#ef4444;}
.facility-status.maint{background:#facc15;color:#222;}

.empty-state{text-align:center;color:var(--muted);margin-top:1rem;font-size:.95rem;}

@media(max-width:640px){
  .page-header h2{font-size:1.35rem;}
  .facilities-grid{grid-template-columns:1fr 1fr;}
}
</style>
</head>
<body>

<main>
  <div class="page-header">
    <i class="bi bi-building"></i>
    <h2>Barangay Facilities</h2>
  </div>

  <!-- Tabs -->
  <div class="tabs">
    <button class="filter-tab <?= $type==='All'?'active':'' ?>" onclick="window.location='?type=All'">All</button>
    <?php
      $fixedTabs = ['Court','Health Center','Evacuation Center','Day Care Center','Covered Court'];
      foreach ($fixedTabs as $cat):
    ?>
      <button class="filter-tab <?= $type===$cat?'active':'' ?>" onclick="window.location='?type=<?= urlencode($cat) ?>'"><?= htmlspecialchars($cat) ?></button>
    <?php endforeach; ?>
  </div>

  <!-- Legend -->
  <div class="legend-bar">
    <div class="legend-item"><span class="legend-dot good-dot"></span> Good Condition</div>
    <div class="legend-item"><span class="legend-dot closed-dot"></span> Closed</div>
    <div class="legend-item"><span class="legend-dot maint-dot"></span> Under Maintenance</div>
  </div>

  <!-- Facilities -->
  <div class="facilities-grid">
    <?php if (empty($facilities)): ?>
      <div class="empty-state"><i class="bi bi-emoji-frown"></i> No facilities found.</div>
    <?php else: ?>
      <?php foreach ($facilities as $f):
        $photo = $f['photo_url'] ?: '../assets/no-image.png';
        $types = trim($f['type_primary'] . ($f['type_secondary'] ? ', ' . $f['type_secondary'] : ''));
        $statusClass = match($f['status']) {
          'Good Condition'=>'good','Closed'=>'closed',default=>'maint'
        };
      ?>
      <article class="facility-card">
        <div class="facility-photo"><img src="../<?= htmlspecialchars($photo) ?>" alt=""></div>
        <div class="facility-body">
          <div class="facility-name"><?= htmlspecialchars($f['facility_name']) ?></div>
          <div class="facility-meta"><i class="bi bi-geo-alt"></i><?= htmlspecialchars($f['address'] ?: 'No address') ?></div>
          <div class="facility-meta"><i class="bi bi-diagram-3"></i><?= htmlspecialchars($types ?: '—') ?></div>
          <span class="facility-status <?= $statusClass ?>"><?= htmlspecialchars($f['status']) ?></span>
        </div>
      </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>

</body>
</html>
