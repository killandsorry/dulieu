<?
/**
 * File action save quick edit
 * save data from dialog list_detail
 */

include 'config.php';

$result  = array(
   'code' => 500,
   'error' => ''
);

$dataEdit   = $_POST;

if(empty($dataEdit)){
   $result['error'] = 'Không có dữ liệu';
}else{
   if(!isset($dataEdit['field']) || !isset($dataEdit['new_value'])){
      $result['error'] = 'Thông tin không đúng';
      
   }else{
      
      $dataResponse  = $killStockCard->quick_edit_import($dataEdit);
      $result = $dataResponse;
      
   }
}
echo json_encode($result);
exit();