<?
include '../config.php';

// khai báo module nhập hàng
$module  = 'warehouse';

?>
<!DOCTYPE HTML>
<head>
	<meta http-equiv="content-type" content="text/html, charset=utf-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?=$file_title?></title>
   <?=$css?>
   <link rel="stylesheet" href="<?=PATH_CSS . 'jquery.typeahead.min.css?v=' . $version_up?>" />
   <link rel="stylesheet" href="<?=PATH_CSS . 'import_export.css?v=' . $version_up?>" />   
   <?=$js?>
   <script src="<?=PATH_JS . 'jquery.typeahead.min.js?v=' . $version_up?>"></script>
   <script>
      var _module = '<?=$module?>';
   </script>
</head>
<body>
   <?include('../../includes/inc_left.php')?>
   <?include('../../includes/inc_top.php')?>
   <div id="main">
      <?include '../../includes_import/inc_add.php'?>      
   </div>
   <?include '../../includes/inc_footer.php'?>
   <?include '../../includes_import/inc_quick_add_product.php'?>
   <?include '../../includes_common/inc_add_provider.php'?>
</body>
</html>