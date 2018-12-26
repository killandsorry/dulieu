<?
include '../config.php';
$start_date    = getValue('start_date', 'str', 'GET', date('d/m/Y', (time() - 30*86400)));
$end_date      = getValue('end_date', 'str', 'GET', date('d/m/Y'));
if($start_date != ''){
   $start_date = convertDateTime($start_date, '00:00:00');
}

if($end_date != ''){
   $end_date   = convertDateTime($end_date, '23:59:59');
}

$provider_id   = getValue('id');
$provider_name   = getValue('name', 'str', 'GET', '');

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
   <?include '../../includes_import/inc_provider_history.php'?>
</body>
</html>