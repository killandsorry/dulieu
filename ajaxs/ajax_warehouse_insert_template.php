<?
/**
 * insert template warehouse
 */
include 'config.php';

$module  = getValue('module', 'str', 'POST', '');
$usp_id  = getValue('id', 'int', 'POST', 0);

$result  = array(
   'code' => 500,
   'total_money' => 0,
   'total_count' => 0,
   'item_exits' => 0,
   'item_id' => 0,
   'item_count' => 0,
   'html' => '',
   'error' => '',
   'file' => __FILE__
);

// có id sản phẩm
if($usp_id > 0){   
   
   $result_add = array('usp_id' => $usp_id);
   
   $dataTemp   = array();
   switch($module){
      
      /** form nhập kho thì dùng class nhập kho thêm template sản phẩm */
      case 'warehouse':
         $dataTemp = $killWareHouse->insert_template($result_add);
         break;
         
      /** form bán hàng thì dùng class bán hàng thêm template sản phẩm */               
      case 'sale':
         $dataTemp = $killSale->insert_template($result_add);
         break;
   }
   
   
   if(!empty($dataTemp)){
      $result['code'] = 200;
      $result['html'] = $dataTemp['template'];
      $result['total_money'] = $dataTemp['total_money'];
      $result['total_count'] = $dataTemp['total_count'];
      $result['item_exits'] = $dataTemp['item_exits'];
      $result['item_id'] = $dataTemp['item_id'];
      $result['item_count'] = $dataTemp['item_count'];
      
   }else{
      $result['error'] = 'Không tạo được dòng dữ liệu';
   }
   
}else{
   $result['error'] = 'Không có ID sản phẩm';
}

echo json_encode($result);
die();