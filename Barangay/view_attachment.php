<?php
// view_attachment.php
require_once(__DIR__ . "/../Database/session-checker.php");

$file  = $_GET['file']  ?? '';
$label = $_GET['label'] ?? 'Attachment';

$realPath = realpath(__DIR__ . '/../' . ltrim($file, '/'));
if (!$file || !$realPath || !file_exists($realPath)) {
  die("<h2 style='font-family:sans-serif;color:red;text-align:center;margin-top:40px;'>File not found.</h2>");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($label) ?> · Servigo</title>
<style>
body {
  margin: 0;
  background: #111;
  color: #fff;
  font-family: system-ui, sans-serif;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
}
img, iframe {
  max-width: 95%;
  max-height: 95%;
  border-radius: 10px;
  box-shadow: 0 0 20px rgba(0,0,0,.6);
}
a.back {
  position: fixed;
  top: 20px; left: 20px;
  background: #16a34a; color: #fff;
  padding: 8px 14px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
}
</style>
</head>
<body>
<a href="javascript:history.back()" class="back">← Back</a>
<?php if (preg_match('/\.pdf$/i', $file)): ?>
  <iframe src="<?= htmlspecialchars('../' . $file) ?>" width="100%" height="100%" style="border:none;"></iframe>
<?php else: ?>
  <img src="<?= htmlspecialchars('../' . $file) ?>" alt="<?= htmlspecialchars($label) ?>">
<?php endif; ?>

</body>
</html>
