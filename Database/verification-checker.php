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
    body {
      font-family:'Outfit',sans-serif;
      background:#f9fafb;
      margin:0;
      display:flex;
      flex-direction:column;
      min-height:100vh;
    }

    .verify-wrapper {
      flex:1;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:60px 20px;
    }

    .verify-card {
      background:#fff;
      border-radius:16px;
      border:1px solid #e5e7eb;
      box-shadow:0 4px 12px rgba(0,0,0,.08);
      text-align:center;
      max-width:420px;
      width:100%;
      padding:40px 32px;
    }

    .verify-card h2 {
      color:#1e40af;
      font-weight:700;
      margin-bottom:8px;
      font-size:1.5rem;
    }

    .verify-card p {
      color:#6b7280;
      margin-bottom:20px;
      line-height:1.4;
      font-size:1rem;
    }

    .verify-card a {
      display:inline-block;
      background:#1e40af;
      color:#fff;
      text-decoration:none;
      padding:10px 20px;
      border-radius:8px;
      font-weight:600;
      transition:background .2s ease;
    }

    .verify-card a:hover {
      background:#162f6a;
    }

    @media(max-width:480px){
      .verify-card { padding:30px 24px; }
      .verify-card h2 { font-size:1.3rem; }
    }
  </style>

  <div class='verify-wrapper'>
    <div class='verify-card'>
      <h2>Verification Required</h2>
      <p>You must verify your account before using this feature.</p>
      <a href='../Resident/verifyAccount.php'>Verify Now</a>
    </div>
  </div>";
  exit;
}

}
?>
