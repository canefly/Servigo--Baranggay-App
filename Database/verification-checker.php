<?php
function requireVerifiedResident($conn) {
  $resident_id = $_SESSION['sg_id'] ?? null;
  if (!$resident_id) {
    header("Location: ../loginPage.php");
    exit;
  }

  $currentFile = basename($_SERVER['PHP_SELF']);
  if ($currentFile === 'verifyAccount.php') return; // 🟩 allow verification page

  $status = "Unverified";
  $stmt = $conn->prepare("SELECT verification_status FROM residents WHERE id=?");
  $stmt->bind_param("i", $resident_id);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($row = $res->fetch_assoc()) $status = $row['verification_status'];
  $stmt->close();

  if ($status !== 'Verified') {
    echo "
    <style>
      body {font-family:'Outfit',sans-serif;background:#f9fafb;margin:0;display:flex;align-items:center;justify-content:center;height:100vh;}
      .lockbox {
        background:#fff;border:1px solid #e5e7eb;border-radius:16px;
        box-shadow:0 4px 12px rgba(0,0,0,.08);padding:40px 32px;
        text-align:center;max-width:480px;width:100%;
      }
      .lockbox i {font-size:64px;color:#1e40af;margin-bottom:10px;}
      .lockbox h2 {color:#1e40af;margin-bottom:8px;}
      .lockbox p {color:#6b7280;margin-bottom:20px;}
      .btn {background:#1e40af;color:#fff;border:none;padding:10px 18px;border-radius:8px;text-decoration:none;font-weight:600;}
      .btn:hover {opacity:.9;}
    </style>

    <div class='lockbox'>
      <i class='bx bx-lock-alt'></i>
      <h2>Verification Required</h2>
      <p>You must verify your account before using this feature.</p>
      <a href='../Resident/verifyAccount.php' class='btn'>Verify Now</a>
    </div>";
    exit;
  }
}
?>
