<?
include '../config.php';
$bill_code     = getValue('bill_code', 'int', 'GET', 0);

?>
<!DOCTYPE HTML>
<head>
	<meta http-equiv="content-type" content="text/html, charset=utf-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?=$file_title?></title>
   <?=$css?>
   <?=$js?>
   <link rel="stylesheet" href="<?=PATH_CSS . 'import_export.css?v=' . $version_up?>" /> 
</head>
<body>
   <?include '../../includes_import/inc_list_detail.php'?>
</body>
</html>