<?
/**
 * File search barcode, tên sản phẩm phục vụ mục đích nhập hàng, bán hàng, thêm sản phẩm... 
 */
header('Content-Type" => application/json'); 
include 'config.php';

$keyword       = getValue('q', 'str', 'GET', ''); // từ khóa

$result  = array();
if($keyword  != ''){
   
   $result = $killProvider->searchKeyword($keyword);
}

echo json_encode($result);
die();