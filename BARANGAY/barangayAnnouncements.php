<?php
// =======================================================
// Barangay Announcements (Native SQL version)
// =======================================================
require_once(__DIR__ . "/../Database/session-checker.php");
requireRole("admin");
require_once(__DIR__ . "/../Database/connection.php");
include 'Components/barangaySidebar.php';
include 'Components/barangayTopbar.php';

// Handle Deletion before HTML render to prevent output errors
if (isset($_POST["delete"]) && !empty($_POST["delete_id"])) {
    $del_id = intval($_POST["delete_id"]);
    $del = $conn->prepare("DELETE FROM announcements WHERE id = ?");
    $del->bind_param("i", $del_id);
    $del->execute();
    header("Location: announcements.php");
    exit();
}

// Handle Create
$msg = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["title"]) && !isset($_POST["delete"])) {
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $category = $_POST["category"];
    $barangay_name = $_SESSION["barangay_name"] ?? "San Isidro";
    $image_url = null;
    $image_path = null;

    // ✅ Handle image upload (if any)
    if (!empty($_FILES["image"]["name"])) {
        $upload_dir = __DIR__ . "/../uploads/announcements/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_path)) {
            $image_url = "/servigo/uploads/announcements/" . $file_name;
            $image_path = $target_path;
        } else {
            $msg = "<p class='error'>❌ Failed to upload image.</p>";
        }
    }

    // ✅ Insert into database
    if (!$msg) {
        $stmt = $conn->prepare("
            INSERT INTO announcements (barangay_name, title, description, category, image_url, image_path, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("ssssss", $barangay_name, $title, $description, $category, $image_url, $image_path);
        if ($stmt->execute()) {
            $msg = "<p class='ok'>✅ Announcement posted successfully!</p>";
        } else {
            $msg = "<p class='error'>❌ Database Error: " . $conn->error . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay · Announcements</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <style>
    :root {
      --bg:#f5f7fa;
      --card:#ffffff;
      --text:#222;
      --muted:#6b7280;
      --brand:#047857;
      --accent:#10b981;
      --error:#b91c1c;
      --ok:#166534;
      --shadow:0 2px 8px rgba(0,0,0,.08);
      --radius:14px;
      --gap:16px;
    }
    *{box-sizing:border-box;margin:0;padding:0;}
    body{
      margin:0;
      background:var(--bg);
      color:var(--text);
      font-family:system-ui,sans-serif;
    }
    .layout{display:flex;min-height:100vh;}
    .main-content{
      flex:1;
      padding:var(--gap);
      transition:margin-left .3s ease;
      max-width:100%;
    }
    @media(min-width:1024px){.main-content{margin-left:275px;}}
    .card{
      background:var(--card);
      padding:var(--gap);
      border-radius:var(--radius);
      box-shadow:var(--shadow);
      margin-bottom:var(--gap);
    }
    h2{margin-bottom:12px;font-size:1.25rem;color:var(--brand);}
    label{font-weight:600;display:block;margin-top:12px;}
    input,textarea,select{
      width:100%;
      padding:12px;
      font-size:15px;
      margin-top:6px;
      border:1px solid #e5e7eb;
      border-radius:10px;
    }
    textarea{resize:vertical;min-height:100px;}
    .btn{
      width:100%;
      margin-top:16px;
      padding:12px;
      border-radius:10px;
      border:none;
      background:linear-gradient(135deg,var(--brand),var(--accent));
      color:#fff;
      font-weight:600;
      cursor:pointer;
      text-align:center;
      transition:.2s;
    }
    .btn:hover{opacity:.9;}
    .error,.ok{
      margin-top:10px;
      padding:10px;
      border-radius:8px;
      font-size:.9rem;
    }
    .error{background:#fee2e2;color:var(--error);border:1px solid #ef4444;}
    .ok{background:#dcfce7;color:var(--ok);border:1px solid #22c55e;}
    .post{
      background:#fff;
      border:1px solid #e5e7eb;
      border-radius:12px;
      padding:14px;
      margin-bottom:16px;
      box-shadow:0 2px 6px rgba(0,0,0,.05);
      display:flex;
      flex-direction:column;
      gap:10px;
      word-wrap:break-word;
    }
    .meta{font-size:13px;color:var(--muted);}
    .post h3{margin:0;font-size:16px;color:#111;}
    .delete{
      all:unset;
      cursor:pointer;
      font-size:14px;
      font-weight:600;
      color:var(--error);
      margin-top:4px;
    }
    img{max-width:100%;border-radius:12px;margin-top:10px;}
    @media(max-width:600px){
      .card,.post{padding:12px;border-radius:10px;}
      h2{font-size:1.1rem;}
      .post h3{font-size:15px;}
      .btn{font-size:14px;padding:10px;}
    }
  </style>
</head>
<body>
<div class="layout">
  <main class="main-content">

    <!-- ==========================================================
         CREATE ANNOUNCEMENT
    =========================================================== -->
    <section class="card">
      <h2>Create Announcement</h2>
      <?php echo $msg; ?>
      <form method="POST" enctype="multipart/form-data">
        <label>Title</label>
        <input type="text" name="title" required>
        <label>Description</label>
        <textarea name="description" required></textarea>
        <label>Category</label>
        <select name="category" required>
          <option>Advisory</option>
          <option>Event</option>
          <option>Emergency</option>
        </select>
        <label>Image (optional)</label>
        <input type="file" name="image" accept="image/*">
        <button type="submit" class="btn">Post Announcement</button>
      </form>
    </section>

    <!-- ==========================================================
         ANNOUNCEMENT FEED
    =========================================================== -->
    <section class="card">
      <h2>My Announcements</h2>
      <?php
      $barangay_name = $_SESSION["barangay_name"] ?? "San Isidro";
      $res = $conn->prepare("
          SELECT id, title, description, category, image_url, created_at 
          FROM announcements 
          WHERE barangay_name = ? 
          ORDER BY created_at DESC
      ");
      $res->bind_param("s", $barangay_name);
      $res->execute();
      $result = $res->get_result();

      if ($result->num_rows === 0) {
          echo "<p>No announcements yet.</p>";
      } else {
          while ($row = $result->fetch_assoc()) {
              echo "<div class='post'>";
              echo "<div class='meta'><strong>" . htmlspecialchars($row['category']) . "</strong> • " .
                   date("F j, Y g:i A", strtotime($row['created_at'])) . "</div>";
              echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
              echo "<p>" . nl2br(htmlspecialchars($row['description'])) . "</p>";
              if (!empty($row['image_url'])) {
                  echo "<img src='" . htmlspecialchars($row['image_url']) . "' alt='Announcement Image'>";
              }
              echo "<form method='POST' style='margin-top:8px;'>
                      <input type='hidden' name='delete_id' value='" . $row['id'] . "'>
                      <button type='submit' name='delete' class='delete'>🗑 Delete</button>
                    </form>";
              echo "</div>";
          }
      }
      ?>
    </section>

  </main>
</div>
</body>
</html>
