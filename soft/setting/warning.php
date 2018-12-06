<?
require_once '../config.php';
$title   = 'Danh mục thuốc, Tủ thuốc';
?>
<!DOCTYPE HTML>
<head>
	<meta http-equiv="content-type" content="text/html, charset=utf-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?=$web_sortname . ' - ' . $title?></title>
   <?=$css?>
</head>
<body>
   <?include '../../includes/inc_header.php'?>
   <div class="wrapper"><?include '../../includes_soft/inc_setting_box.php'?></div>
   <?include '../../includes/inc_footer.php'?>
</body>
<?=$js?>
</html>