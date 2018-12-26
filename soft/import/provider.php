<?
include '../config.php';
$provider_name    = getValue('p_name', 'str', 'GET', '');
$provider_phone   = getValue('p_phone', 'str', 'GET', '');
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
   <?include('../../includes/inc_left.php')?>
   <?include('../../includes/inc_top.php')?>
   <div id="main">
      <?include '../../includes_import/inc_provider.php'?>
   </div>
   <?include '../../includes/inc_footer.php'?>
   <?include '../../includes_common/inc_add_provider.php'?>
</body>
</html>