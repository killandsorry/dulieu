<?
/**
 * File search barcode, tên sản phẩm phục vụ mục đích nhập hàng, bán hàng, thêm sản phẩm... 
 */
header('Content-Type" => application/json'); 
include 'config.php';

$keyword       = getValue('q', 'str', 'GET', ''); // từ khóa
$module        = getValue('module', 'str', 'GET', ''); // modul thực thi

$result  = array();
if($keyword  != ''){
   
   if($killSearch->detectBarcode($keyword)){
      $result = $killSearch->searchBarcode($keyword);
   }else{
      $result = $killSearch->searchKeyword($keyword, $module);
      
   }
}

echo json_encode($result);
die();