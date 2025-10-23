<?php
ob_start();
require_once(__DIR__ . "/../Database/session-checker.php");
requireRole("admin");
require_once(__DIR__ . "/../Database/connection.php");
include 'Components/barangaySidebar.php';
include 'Components/barangayTopbar.php';

$barangay = $_SESSION['sg_brgy'] ?? 'Unknown Barangay';

/* ==========================================================
   ADD / EDIT / DELETE FACILITY
========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // --- Add ---
  if (isset($_POST['add_facility'])) {
    $name = trim($_POST['facility_name']);
    $type1 = $_POST['type_primary'] ?? '';
    $type2 = $_POST['type_secondary'] ?? '';
    $addr = trim($_POST['address']);
    $status = $_POST['status'] ?? 'Good Condition';
    $photo = '';

    if (!empty($_FILES['photo']['name'])) {
      $dir = "../uploads/facilities/";
      if (!is_dir($dir)) mkdir($dir, 0777, true);
      $filename = time() . "_" . basename($_FILES['photo']['name']);
      $target = $dir . $filename;
      move_uploaded_file($_FILES['photo']['tmp_name'], $target);
      $photo = "uploads/facilities/" . $filename;
    }

    $stmt = $conn->prepare("INSERT INTO barangay_facilities 
      (barangay_name, facility_name, type_primary, type_secondary, address, status, photo_url)
      VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param("sssssss", $barangay, $name, $type1, $type2, $addr, $status, $photo);
    $stmt->execute();

    // Notify residents
    $title = "🏗️ New Facility Added";
    $message = "A new facility <b>$name</b> has been added to your barangay.";
    $link = "Resident/facilitiesPage.php";
    $notif = $conn->prepare("INSERT INTO notifications 
      (barangay_name, recipient_type, type, title, message, link, is_read)
      VALUES (?, 'resident', 'facility_update', ?, ?, ?, 0)");
    $notif->bind_param("ssss", $barangay, $title, $message, $link);
    $notif->execute();

    echo "<script>window.location='facilitiesPage.php';</script>";
    exit();
  }

  // --- Edit ---
  if (isset($_POST['edit_facility'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['facility_name']);
    $type1 = $_POST['type_primary'] ?? '';
    $type2 = $_POST['type_secondary'] ?? '';
    $addr = trim($_POST['address']);
    $status = $_POST['status'];
    $photo = $_POST['existing_photo'] ?? '';

    if (!empty($_FILES['photo']['name'])) {
      $dir = "../uploads/facilities/";
      if (!is_dir($dir)) mkdir($dir, 0777, true);
      $filename = time() . "_" . basename($_FILES['photo']['name']);
      $target = $dir . $filename;
      move_uploaded_file($_FILES['photo']['tmp_name'], $target);
      $photo = "uploads/facilities/" . $filename;
    }

    $stmt = $conn->prepare("UPDATE barangay_facilities 
      SET facility_name=?, type_primary=?, type_secondary=?, address=?, status=?, photo_url=? WHERE id=?");
    $stmt->bind_param("ssssssi", $name, $type1, $type2, $addr, $status, $photo, $id);
    $stmt->execute();

    $title = "🏗️ Facility Updated";
    $message = "The facility <b>$name</b> has been updated.";
    $link = "Resident/facilitiesPage.php";
    $notif = $conn->prepare("INSERT INTO notifications 
      (barangay_name, recipient_type, type, title, message, link, is_read)
      VALUES (?, 'resident', 'facility_status', ?, ?, ?, 0)");
    $notif->bind_param("ssss", $barangay, $title, $message, $link);
    $notif->execute();

    echo "<script>window.location='facilitiesPage.php';</script>";
    exit();
  }

  // --- Delete ---
  if (isset($_POST['delete_facility'])) {
    $id = (int)$_POST['id'];
    $stmt = $conn->prepare("DELETE FROM barangay_facilities WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "<script>window.location='facilitiesPage.php';</script>";
    exit();
  }
}

/* ==========================================================
   FETCH FACILITIES + FILTER
========================================================== */
$typeFilter = $_GET['type'] ?? 'All';
$sql = "SELECT * FROM barangay_facilities WHERE barangay_name=?";
$params = [$barangay];
$types = "s";
if ($typeFilter !== 'All') {
  $sql .= " AND (type_primary=? OR type_secondary=?)";
  $params[] = $typeFilter;
  $params[] = $typeFilter;
  $types .= "ss";
}
$sql .= " ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$data = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Facilities · Servigo</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --bg:#f5f7fa; --card:#ffffff;
  --text:#222; --muted:#6b7280; --border:#e5e7eb;
  --brand:#047857; --accent:#10b981;
  --declined:#ef4444;
  --radius:14px; --shadow:0 2px 8px rgba(0,0,0,.08);
  --sidebar-width:240px;
}
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--text);}
.layout{display:flex;min-height:100vh;}
.main-content{flex:1;padding:24px;margin-left:var(--sidebar-width);width:calc(100% - var(--sidebar-width));}

/* Header */
.header{background:var(--card);border:1px solid var(--border);padding:14px 20px;
  border-radius:var(--radius);box-shadow:var(--shadow);display:flex;align-items:center;
  justify-content:space-between;margin-bottom:20px;}
.header h2{color:var(--brand);font-weight:700;font-size:1.4rem;}
.add-btn{background:var(--brand);color:#fff;border:none;padding:8px 16px;
  border-radius:10px;font-weight:600;cursor:pointer;}
.add-btn:hover{background:var(--accent);}

/* Filter tabs */
.filter-tabs{display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;}
.filter-tab{
  all:unset;cursor:pointer;padding:8px 18px;border-radius:999px;font-weight:600;font-size:.95rem;
  border:1px solid var(--border);background:#f3f4f6;color:var(--brand);transition:.2s;
}
.filter-tab.active{
  background:linear-gradient(135deg,var(--brand),var(--accent));
  color:#fff;border:none;box-shadow:0 2px 6px rgba(0,0,0,.1);
}

/* Facilities Grid */
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;}
.card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);
  box-shadow:var(--shadow);padding:16px;display:flex;flex-direction:column;gap:8px;}
.card img{width:100%;height:160px;object-fit:cover;border-radius:10px;}
.name{font-weight:700;font-size:1.1rem;}
.types{font-size:.9rem;color:var(--muted);}
.status{padding:4px 10px;border-radius:8px;font-weight:600;font-size:.85rem;display:inline-block;}
.good{background:#10b981;color:#fff;}
.closed{background:#ef4444;color:#fff;}
.maint{background:#facc15;color:#222;}
.actions{display:flex;gap:8px;margin-top:8px;}
.btn{padding:6px 12px;border:none;border-radius:8px;font-weight:600;cursor:pointer;}
.edit{background:var(--accent);color:#fff;}
.delete{background:var(--declined);color:#fff;}
.edit:hover,.delete:hover{opacity:.9;}

/* Modal */
.modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);
  align-items:center;justify-content:center;z-index:999;}
.modal-bg.active{display:flex;}
.modal{background:#fff;padding:22px;border-radius:var(--radius);
  box-shadow:0 4px 20px rgba(0,0,0,.15);width:90%;max-width:500px;}
.modal h3{margin-bottom:10px;color:var(--brand);}
.modal input,.modal select{width:100%;padding:8px;margin-bottom:10px;border:1px solid var(--border);border-radius:8px;}
.modal label{font-weight:600;color:var(--text);}
.modal .actions{justify-content:flex-end;}
</style>
</head>
<body>
<div class="layout">
  <main class="main-content">
    <div class="header">
      <h2>Manage Barangay Facilities</h2>
      <button class="add-btn" onclick="openModal('addModal')">+ Add Facility</button>
    </div>

    <nav class="filter-tabs">
      <?php
        $tabs = ['All','Court','Health Center','Evacuation Center','Day Care Center','Covered Court'];
        foreach ($tabs as $t) {
          $active = ($t == $typeFilter) ? 'active' : '';
          echo "<button class='filter-tab $active' onclick=\"window.location='?type=".urlencode($t)."'\">$t</button>";
        }
      ?>
    </nav>

    <section class="grid">
      <?php if ($data->num_rows > 0): while($f=$data->fetch_assoc()):
        $photo = $f['photo_url'] ?: '../assets/no-image.png';
        $badge = match($f['status']){'Good Condition'=>'good','Closed'=>'closed',default=>'maint'};
      ?>
      <div class="card">
        <img src="../<?= htmlspecialchars($photo) ?>" alt="">
        <div class="name"><?= htmlspecialchars($f['facility_name']) ?></div>
        <div class="types"><?= htmlspecialchars($f['type_primary'] . ($f['type_secondary'] ? ', ' . $f['type_secondary'] : '')) ?></div>
        <div class="status <?= $badge ?>"><?= htmlspecialchars($f['status']) ?></div>
        <div class="actions">
          <button class="btn edit" onclick="openEdit(<?= $f['id'] ?>)">Edit</button>
          <form method="POST" style="margin:0;">
            <input type="hidden" name="id" value="<?= $f['id'] ?>">
            <button type="submit" name="delete_facility" class="btn delete">Delete</button>
          </form>
        </div>
      </div>

      <!-- Edit Modal -->
      <div id="editModal<?= $f['id'] ?>" class="modal-bg">
        <form class="modal" method="POST" enctype="multipart/form-data">
          <h3>Edit Facility</h3>
          <input type="hidden" name="id" value="<?= $f['id'] ?>">
          <input type="hidden" name="existing_photo" value="<?= htmlspecialchars($f['photo_url']) ?>">

          <label>Facility Name</label>
          <input type="text" name="facility_name" value="<?= htmlspecialchars($f['facility_name']) ?>" required>

          <label>Primary Type</label>
          <select name="type_primary" required>
            <?php
              $opts = ['Court','Health Center','Evacuation Center','Day Care Center','Covered Court'];
              foreach ($opts as $opt) {
                $sel = $opt == $f['type_primary'] ? 'selected' : '';
                echo "<option value='$opt' $sel>$opt</option>";
              }
            ?>
          </select>

          <label>Secondary Type (optional)</label>
          <select name="type_secondary">
            <option value="">None</option>
            <?php
              foreach ($opts as $opt) {
                $sel = $opt == $f['type_secondary'] ? 'selected' : '';
                echo "<option value='$opt' $sel>$opt</option>";
              }
            ?>
          </select>

          <label>Status</label>
          <select name="status">
            <?php foreach(['Good Condition','Closed','Under Maintenance'] as $opt): ?>
              <option value="<?= $opt ?>" <?= $opt==$f['status']?'selected':'' ?>><?= $opt ?></option>
            <?php endforeach; ?>
          </select>

          <label>Address</label>
          <input type="text" name="address" value="<?= htmlspecialchars($f['address']) ?>">

          <label>Replace Photo</label>
          <input type="file" name="photo" accept="image/*">

          <div class="actions">
            <button type="submit" name="edit_facility" class="btn edit">Save</button>
            <button type="button" class="btn delete" onclick="closeEdit(<?= $f['id'] ?>)">Cancel</button>
          </div>
        </form>
      </div>
      <?php endwhile; else: ?>
        <p style="color:var(--muted)">No facilities found.</p>
      <?php endif; ?>
    </section>
  </main>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal-bg">
  <form class="modal" method="POST" enctype="multipart/form-data">
    <h3>Add Facility</h3>
    <label>Facility Name</label>
    <input type="text" name="facility_name" required>

    <label>Primary Type</label>
    <select name="type_primary" required>
      <option value="Court">Court</option>
      <option value="Health Center">Health Center</option>
      <option value="Evacuation Center">Evacuation Center</option>
      <option value="Day Care Center">Day Care Center</option>
      <option value="Covered Court">Covered Court</option>
    </select>

    <label>Secondary Type (optional)</label>
    <select name="type_secondary">
      <option value="">None</option>
      <option value="Court">Court</option>
      <option value="Health Center">Health Center</option>
      <option value="Evacuation Center">Evacuation Center</option>
      <option value="Day Care Center">Day Care Center</option>
      <option value="Covered Court">Covered Court</option>
    </select>

    <label>Status</label>
    <select name="status">
      <option value="Good Condition">Good Condition</option>
      <option value="Closed">Closed</option>
      <option value="Under Maintenance">Under Maintenance</option>
    </select>

    <label>Address</label>
    <input type="text" name="address" placeholder="Facility location">

    <label>Photo</label>
    <input type="file" name="photo" accept="image/*">

    <div class="actions">
      <button type="submit" name="add_facility" class="btn edit">Save</button>
      <button type="button" class="btn delete" onclick="closeModal('addModal')">Cancel</button>
    </div>
  </form>
</div>

<script>
function openModal(id){document.getElementById(id).classList.add('active');}
function closeModal(id){document.getElementById(id).classList.remove('active');}
function openEdit(id){document.getElementById('editModal'+id).classList.add('active');}
function closeEdit(id){document.getElementById('editModal'+id).classList.remove('active');}
</script>
</body>
</html>
<?php ob_end_flush(); ?>
