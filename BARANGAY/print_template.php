<?php
require_once(__DIR__."/../Database/connection.php");
$id=intval($_GET['id']);
$table=$_GET['table']??'';
$q=$conn->prepare("SELECT r.*,res.* FROM $table r 
JOIN residents res ON r.resident_id=res.id WHERE r.id=?");
$q->bind_param("i",$id);
$q->execute();
$d=$q->get_result()->fetch_assoc();
if(!$d){die("Not found.");}

$barangay=$d['barangay_name'];
$fullname=$d['first_name']." ".$d['last_name'];
$purpose=$d['purpose'];
$typeMap=[
 'barangay_clearance_requests'=>'Barangay Clearance',
 'business_permit_requests'=>'Business Permit',
 'goodmoral_requests'=>'Certificate of Good Moral Character',
 'indigency_requests'=>'Certificate of Indigency',
 'latebirth_requests'=>'Certificate of Late Birth Registration',
 'norecord_requests'=>'Certificate of No Record',
 'ojt_requests'=>'Certificate of OJT / Training Endorsement',
 'residency_requests'=>'Certificate of Residency',
 'soloparent_requests'=>'Certificate of Solo Parent'
];
$doc=$typeMap[$table]??'Barangay Document';
$date=date("F d, Y");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?=$doc?> - <?=$fullname?></title>
<style>
@media print{@page{size:A4;margin:0}}
body{font-family:Arial,sans-serif;background:#fff;margin:0;}
.doc{width:210mm;min-height:297mm;margin:auto;padding:50px;}
.header{text-align:center;margin-bottom:40px;}
.header h1{margin:0;color:#047857;}
.header small{color:#444;}
h2{text-align:center;margin-bottom:25px;text-decoration:underline;}
p{font-size:15px;line-height:1.6;text-align:justify;}
.footer{text-align:right;margin-top:70px;}
</style>
</head>
<body onload="window.print()">
<div class="doc">
  <div class="header">
    <h1>Republic of the Philippines<br>City of Quezon<br><b><?=$barangay?></b></h1>
    <small>Office of the Barangay Captain</small>
  </div>

  <h2><?=$doc?></h2>
  <p>TO WHOM IT MAY CONCERN:</p>
  <p>This is to certify that <b><?=$fullname?></b> is a resident of <b><?=$barangay?></b>.
     Purpose of request: <b><?=$purpose?></b>.</p>

  <p>This certificate is issued on <b><?=$date?></b> for any legal purpose it may serve.</p>

  <div class="footer">
    <b>Hon. Barangay Captain</b><br>
    <small><?=$barangay?></small>
  </div>
</div>
</body>
</html>
