<?php
require_once(__DIR__."/../Database/connection.php");
$id=intval($_GET["id"]);
$table=$_GET["table"];
$q=$conn->prepare("SELECT r.*,res.* FROM $table r JOIN residents res ON r.resident_id=res.id WHERE r.id=?");
$q->bind_param("i",$id);$q->execute();$d=$q->get_result()->fetch_assoc();
if(!$d){echo "Not found.";exit;}
?>
<p><b>Resident:</b> <?=$d["first_name"]." ".$d["last_name"]?><br>
<b>Email:</b> <?=$d["email"]?><br>
<b>Phone:</b> <?=$d["phone"]?><br>
<b>Barangay:</b> <?=$d["barangay"]?><br>
<b>Purpose:</b> <?=$d["purpose"]?><br>
<b>Status:</b> <?=$d["status"]?><br>
<b>Requested:</b> <?=date("F d, Y",strtotime($d["created_at"]))?></p>

<?php if(!empty($d["valid_id_url"])): ?>
<b>Uploaded ID:</b><br>
<img src="<?=$d["valid_id_url"]?>">
<?php endif; ?>

<hr><b>Requirements:</b><br>
<?php
switch($table){
 case "business_permit_requests": echo "• DTI/BIR permit<br>• Store photo<br>";break;
 case "indigency_requests": echo "• Proof of residence<br>• Valid ID<br>";break;
 case "residency_requests": echo "• Proof of stay<br>";break;
 case "goodmoral_requests": echo "• Endorsement Letter<br>";break;
 case "soloparent_requests": echo "• Proof of Solo Parenthood<br>";break;
 case "latebirth_requests": echo "• PSA or proof of birth<br>";break;
 case "norecord_requests": echo "• Request letter<br>";break;
 case "ojt_requests": echo "• School endorsement<br>";break;
 default: echo "• Standard Barangay requirements<br>";
}
?>
