<?php
if (isset($_GET['print']) && $_GET['print'] == 1) {
  echo "<script>window.onload=()=>window.print();</script>";
}

require_once(__DIR__."/../Database/connection.php");

$id = intval($_GET['id'] ?? 0);
$table = $_GET['table'] ?? '';

$q = $conn->prepare("SELECT r.*, res.* FROM $table r 
JOIN residents res ON r.resident_id = res.id WHERE r.id = ?");
$q->bind_param("i", $id);
$q->execute();
$d = $q->get_result()->fetch_assoc();
if(!$d){ die("Record not found."); }

$barangay = $d['barangay_name'] ?? 'Barangay Name';
$fullname = trim(($d['first_name'] ?? '') . ' ' . ($d['middle_name'] ?? '') . ' ' . ($d['last_name'] ?? '') . ' ' . ($d['suffix'] ?? ''));
$purpose  = $d['purpose'] ?? '';
$date     = date("F d, Y");

$typeMap = [
  'barangay_clearance_requests' => 'Barangay Clearance',
  'residency_requests'          => 'Certificate of Residency',
  'indigency_requests'          => 'Certificate of Indigency',
  'goodmoral_requests'          => 'Certificate of Good Moral Character',
  'soloparent_requests'         => 'Certificate of Solo Parent',
  'latebirth_requests'          => 'Certificate of Late Birth Registration',
  'norecord_requests'           => 'Certificate of No Record',
  'ojt_requests'                => 'Certificate of On-the-Job Training Endorsement',
  'business_permit_requests'    => 'Business Permit',
];
$doc = $typeMap[$table] ?? 'Barangay Certification';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?=$doc?> - <?=$fullname?></title>
<style>
@media print { @page { size: A4; margin: 25mm; } }
body {
  font-family: "Times New Roman", serif;
  background: #fff;
  color: #000;
  margin: 0;
}
.doc {
  width: 210mm;
  min-height: 297mm;
  margin: auto;
  padding: 25mm;
  position: relative;
  box-sizing: border-box;
}
.header {
  text-align: center;
  line-height: 1.4;
}
.header img.left-seal {
  position: absolute;
  top: 35px;
  left: 60px;
  width: 85px;
  height: 85px;
}
.header img.right-logo {
  position: absolute;
  top: 35px;
  right: 60px;
  width: 85px;
  height: 85px;
}
.header h1 {
  margin: 0;
  font-size: 18px;
  font-weight: normal;
}
.header h2 {
  margin: 0;
  font-size: 21px;
  font-weight: bold;
  color: #065f46;
}
.header small {
  font-size: 13px;
  color: #333;
}
.certificate-title {
  text-align: center;
  font-size: 23px;
  font-weight: bold;
  text-decoration: underline;
  margin-top: 35px;
  margin-bottom: 35px;
  letter-spacing: 0.6px;
}
p {
  font-size: 16px;
  line-height: 1.75;
  text-align: justify;
  text-indent: 60px;
  margin: 0 0 18px;
}
p.center {
  text-align: center;
  text-indent: 0;
}
.signature {
  margin-top: 70px;
  display: flex;
  justify-content: space-between;
  padding: 0 40px;
}
.signature div {
  text-align: center;
  font-size: 16px;
  line-height: 1.4;
}
.signature .line {
  border-top: 1px solid #000;
  width: 250px;
  margin-bottom: 4px;
}
.footer {
  margin-top: 60px;
  font-size: 13px;
  color: #444;
  text-align: left;
}
</style>
</head>
<body onload="window.print()">
<div class="doc">

  <!-- HEADER -->
  <div class="header">
    <img src="M.png" class="left-seal" alt="Barangay Seal" onerror="this.style.display='none'">
    <img src="B.png" class="right-logo" alt="Barangay Logo" onerror="this.style.display='none'">
    <h1>Republic of the Philippines</h1>
    <h1>City of Quezon</h1>
    <h2><b>Barangay <?=$barangay?></b></h2>
    <small>Office of the Punong Barangay</small>
  </div>

  <div class="certificate-title"><?=$doc?></div>
  <p>TO WHOM IT MAY CONCERN:</p>

  <?php
  switch($table){
    case 'barangay_clearance_requests':
      echo "<p>This is to certify that <b>".strtoupper($fullname)."</b> is a law-abiding and bona fide resident of Barangay $barangay, City of Quezon. To the best of our knowledge and record, the said person has no derogatory or criminal record filed within this jurisdiction as of the date of issuance.</p>
            <p>This Barangay Clearance is hereby issued upon the request of the concerned resident for the purpose of <b>$purpose</b>.</p>";
      break;

    case 'residency_requests':
      echo "<p>This is to certify that <b>".strtoupper($fullname)."</b> is a legitimate and continuous resident of Barangay $barangay, City of Quezon, and is known to be a person of good standing in the community.</p>
            <p>This certification is being issued upon the request of the aforementioned resident for the purpose of <b>$purpose</b>.</p>";
      break;

    case 'indigency_requests':
      echo "<p>This is to certify that <b>".strtoupper($fullname)."</b> is a bona fide resident of Barangay $barangay, City of Quezon, and is known to this Barangay to be of limited financial means.</p>
            <p>This certification is issued to support the individual’s need for <b>$purpose</b> and for any legal purpose it may serve.</p>";
      break;

    case 'goodmoral_requests':
      echo "<p>This is to certify that <b>".strtoupper($fullname)."</b>, a resident of Barangay $barangay, City of Quezon, is of good moral character and has no derogatory record within this Barangay.</p>
            <p>This certification is issued upon the request of the above-named individual for the purpose of <b>$purpose</b>.</p>";
      break;

    case 'soloparent_requests':
      echo "<p>This is to certify that <b>".strtoupper($fullname)."</b> is a resident of Barangay $barangay, City of Quezon, and is recognized as a Solo Parent pursuant to Republic Act No. 8972, otherwise known as the Solo Parents’ Welfare Act of 2000.</p>
            <p>This certification is issued to serve as supporting document for <b>$purpose</b>.</p>";
      break;

    case 'latebirth_requests':
      echo "<p>This is to certify that <b>".strtoupper($fullname)."</b> is a resident of Barangay $barangay, City of Quezon. This certification is being issued to support the registration of a late birth record with the Philippine Statistics Authority (PSA).</p>
            <p>This document is released upon the request of the concerned person for <b>$purpose</b>.</p>";
      break;

    case 'norecord_requests':
      echo "<p>This is to certify that <b>".strtoupper($fullname)."</b> has no record of blotter, complaint, or pending case filed at the Barangay $barangay Hall, City of Quezon, as of this date.</p>
            <p>This certification is issued upon request of the concerned individual for <b>$purpose</b>.</p>";
      break;

    case 'ojt_requests':
      echo "<p>This is to certify that <b>".strtoupper($fullname)."</b> is a resident of Barangay $barangay, City of Quezon, and is hereby endorsed for On-the-Job Training or Internship purposes.</p>
            <p>This certification is issued upon the request of the above-named student for <b>$purpose</b>.</p>";
      break;

    case 'business_permit_requests':
      echo "<p>This is to certify that <b>".strtoupper($fullname)."</b> is the registered owner or operator of a business operating within Barangay $barangay, City of Quezon, in accordance with local ordinances and regulations.</p>
            <p>This certification is issued to support the application for <b>$purpose</b> and other lawful intents it may serve.</p>";
      break;

    default:
      echo "<p>This is to certify that <b>".strtoupper($fullname)."</b> is a bona fide resident of Barangay $barangay, City of Quezon.</p>
            <p>This document is issued upon the request of the individual for <b>$purpose</b>.</p>";
      break;
  }
  ?>

  <p>Given this <?=$date?> at the Office of the Punong Barangay, Barangay <?=$barangay?>, City of Quezon, Philippines.</p>

  <!-- SIGNATURES -->
  <div class="signature">
    <div>
      <div class="line"></div>
      <b>Hon. Barangay Captain</b><br>
      <small>Barangay <?=$barangay?></small>
    </div>
    <div>
      <div class="line"></div>
      <b>Barangay Secretary</b><br>
      <small>Authorized Personnel</small>
    </div>
  </div>

  <div class="footer">
    <b>Document No.:</b> <?=$table."-".$id?><br>
    <b>Date Issued:</b> <?=$date?><br>
    <b>Verified by:</b> Barangay Information System<br>
  </div>

</div>
</body>
</html>
