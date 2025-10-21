<?php
require_once(__DIR__ . "/../Database/session-checker.php");
requireRole("resident");
require_once(__DIR__ . "/../Database/connection.php");
include 'Components/topbar.php';

// 🧠 Make sure barangay_name matches admin session variable
if (!isset($_SESSION['barangay_name']) && isset($_SESSION['sg_brgy'])) {
    $_SESSION['barangay_name'] = $_SESSION['sg_brgy'];
}

// Barangay context
$barangay = $_SESSION['barangay_name'] ?? 'Unknown Barangay';

// 🧩 Filters
$q        = trim($_GET['q'] ?? '');
$from     = $_GET['from'] ?? '';
$to       = $_GET['to'] ?? '';
$category = $_GET['cat'] ?? 'All';

// 🧮 Build Query
$sql = "SELECT id, barangay_name, title, description, category, image_url, created_at 
        FROM announcements
        WHERE barangay_name = ?";
$params = [$barangay];
$types  = "s";

if ($q !== '') {
  $sql .= " AND (title LIKE ? OR description LIKE ?)";
  $params[] = "%$q%";
  $params[] = "%$q%";
  $types .= "ss";
}
if ($from !== '') {
  $sql .= " AND created_at >= ?";
  $params[] = $from;
  $types .= "s";
}
if ($to !== '') {
  $sql .= " AND created_at <= ?";
  $params[] = $to;
  $types .= "s";
}
if ($category !== 'All') {
  $sql .= " AND category = ?";
  $params[] = $category;
  $types .= "s";
}
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$announcements = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Servigo · Home (Residents)</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<style>
:root {
  --bg:#f5f7fa; --card:#ffffff; --text:#222; --muted:#6b7280;
  --brand:#1e40af; --accent:#16a34a; --border:#e5e7eb;
  --shadow:0 2px 8px rgba(0,0,0,.08); --radius:16px; --gap:14px; --pad:14px;
}
*{box-sizing:border-box}
body{margin:0;font-family:system-ui,sans-serif;background:var(--bg);color:var(--text);line-height:1.5;}
.container{max-width:1100px;margin:0 auto;padding:16px}
.navtabs{display:flex;gap:8px;justify-content:center;background:#f9fafb;padding:10px;border-bottom:1px solid var(--border);flex-wrap:wrap;}
.tabbtn{all:unset;cursor:pointer;font-weight:600;padding:8px 14px;border-radius:10px;color:var(--text);border:1px solid var(--border);background:#f3f4f6;}
.tabbtn.active{background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff;font-weight:700;}
.card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:var(--pad);box-shadow:var(--shadow);margin-bottom:20px;}
h2{margin-top:0;color:var(--brand)}.muted{color:var(--muted)}
.divider{height:1px;background:var(--border);margin:12px 0}
.controls{display:grid;grid-template-columns:1fr auto auto;gap:var(--gap);align-items:end}
@media(max-width:780px){.controls{grid-template-columns:1fr}.controls .full{grid-column:1/-1}}
label{font-size:14px;font-weight:600;margin-bottom:4px;display:block}
.input{width:100%;padding:12px;border-radius:12px;border:1px solid var(--border);font-size:15px}
.catbar{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}
.chipbtn{all:unset;cursor:pointer;padding:10px 14px;border-radius:999px;border:1px solid var(--border);color:var(--brand);background:#f9fafb;font-size:14px}
.chipbtn.active{background:linear-gradient(135deg,var(--brand),var(--accent));color:#fff;border:none}
.ghost{all:unset;cursor:pointer;padding:9px 12px;border-radius:10px;background:#f3f4f6;border:1px solid var(--border);font-weight:600;color:var(--text)}
.news{background:#fff;border:1px solid var(--border);border-radius:12px;box-shadow:var(--shadow);padding:14px 16px;margin-bottom:16px;display:flex;flex-direction:column;gap:10px;word-wrap:break-word;}
.news .meta{font-size:13px;color:var(--muted)}
.news h3{margin:0;font-size:16px;color:#111}
.news .desc{margin:0;color:var(--text);font-size:14px;line-height:1.4;overflow:hidden;white-space:pre-wrap;max-height:6.2em;}
.news.expanded .desc{max-height:none}
.news .see-toggle{all:unset;cursor:pointer;color:var(--brand);font-weight:600;font-size:14px;margin-top:4px;align-self:flex-start;}
.image-wrapper{position:relative;width:100%;border-radius:10px;overflow:hidden;background:#f0f0f0;}
.image-wrapper img{width:100%;height:auto;display:block;object-fit:contain;max-height:500px;}
.empty{padding:16px;text-align:center;color:var(--muted);border:1px dashed var(--border);border-radius:12px}
footer{color:var(--muted);text-align:center;padding:20px 12px;font-size:14px}
</style>
</head>
<body>

<nav class="navtabs">
  <a href="residentsPage.php" class="tabbtn active">News</a>
  <a href="permitsPage.php" class="tabbtn">Permits</a>
  <a href="storesPage.php" class="tabbtn">Stores</a>
  <a href="events.php" class="tabbtn">Events</a>
</nav>

<main class="container">
  <section class="card" aria-labelledby="news-title">
    <h2 id="news-title">Barangay News & Advisories</h2>
    <p class="muted">Showing updates for <strong><?= htmlspecialchars($barangay) ?></strong>.</p>
    <div class="divider"></div>

    <!-- 🧭 Filters -->
    <form method="GET" class="controls" style="margin-bottom:10px">
      <div class="full">
        <label for="q">Search</label>
        <input id="q" name="q" class="input" value="<?= htmlspecialchars($q) ?>" placeholder="e.g., vaccination, road closure, permit schedule" />
      </div>
      <div>
        <label for="from">From</label>
        <input id="from" name="from" type="date" class="input" value="<?= htmlspecialchars($from) ?>" />
      </div>
      <div>
        <label for="to">To</label>
        <input id="to" name="to" type="date" class="input" value="<?= htmlspecialchars($to) ?>" />
      </div>
      <div class="catbar" role="tablist" aria-label="Categories" style="grid-column:1/-1;">
        <?php
          $cats = ['All','Advisory','Event','Emergency'];
          foreach ($cats as $cat) {
            $active = ($cat === $category) ? 'active' : '';
            echo "<button type='submit' name='cat' value='$cat' class='chipbtn $active'>$cat</button>";
          }
        ?>
        <button type="submit" class="ghost">Apply Filters</button>
        <a href="residentsPage.php" class="ghost">Clear</a>
      </div>
    </form>

    <!-- 📰 News Feed -->
    <?php if (empty($announcements)): ?>
      <div class="empty">No results found for your filters.</div>
    <?php else: ?>
      <?php 
      foreach ($announcements as $item): 
          // Normalize image path dynamically in case /servigo/ prefix missing
          $img = $item['image_url'];
          if (!empty($img) && strpos($img, '/servigo/') === false) {
              $base = dirname($_SERVER['SCRIPT_NAME'], 2);
              $img = $base . $img;
          }
      ?>
        <article class="news">
          <div class="meta">
            <span class="tag <?= htmlspecialchars($item['category']) ?>">
              <?= htmlspecialchars($item['category']) ?>
            </span>
             • <?= htmlspecialchars($item['barangay_name']) ?>
             • <?= date('F j, Y g:i A', strtotime($item['created_at'])) ?>
          </div>
          <h3><?= htmlspecialchars($item['title']) ?></h3>
          <p class="desc"><?= nl2br(htmlspecialchars($item['description'])) ?></p>
          <?php if (!empty($img)): ?>
            <div class="image-wrapper">
              <img src="<?= htmlspecialchars($img) ?>" alt="Announcement Image" 
                   onerror="this.style.display='none'" />
            </div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</main>

<footer>
  <small>© 2025 Servigo (Prototype)</small>
</footer>

<script>
// Optional: See More / See Less toggle
document.querySelectorAll('.news').forEach(el=>{
  const desc=el.querySelector('.desc');
  if(desc && desc.textContent.length>150){
    const btn=document.createElement('button');
    btn.className='see-toggle';
    btn.textContent='See More';
    btn.onclick=()=>{el.classList.toggle('expanded');
      btn.textContent=el.classList.contains('expanded')?'See Less':'See More';};
    el.appendChild(btn);
  }
});
</script>
</body>
</html>
