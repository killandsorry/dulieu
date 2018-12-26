<?
include '../config.php';

$bill_code     = getValue('bill_code', 'int', 'GET', 0);
$start_date    = getValue('start_date', 'str', 'GET', date('d/m/Y', (time() - 10*86400)));
$end_date      = getValue('end_date', 'str', 'GET', date('d/m/Y'));
if($start_date != ''){
   $start_date = convertDateTime($start_date, '00:00:00');
}

if($end_date != ''){
   $end_date   = convertDateTime($end_date, '23:59:59');
}


?>
<!DOCTYPE HTML>
<head>
	<meta http-equiv="content-type" content="text/html, charset=utf-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?=$file_title?></title>
   <?=$css?>
   <?=$js?>
</head>
<body>
   <?include('../../includes/inc_left.php')?>
   <?include('../../includes/inc_top.php')?>
   <div id="main">
      <?include '../../includes_import/inc_list.php'?>      
   </div>
   <?include '../../includes/inc_footer.php'?>
</body>
</html>