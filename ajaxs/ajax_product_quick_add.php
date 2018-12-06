<?
/**
 * quick add product
 * option:
 * 1=> add template warehouse
 * 2=> add template sale
 */
include 'config.php';

$data = isset($_POST)? $_POST : array();

$module  = getValue('module', 'str', 'POST', '');

$result  = array(
   'code' => 500,
   'html' => '',
   'error' => '',
   'file' => __FILE__
);

if(!empty($data)){
   
   /*
      result_add example
      $arrayResult = array(
         'code' => 500,
         'usp_id' => 0,
         'error' => '',
         'file' => __FILE__
      );   
   */
   $result_add = $killProduct->quick_add_product($data);   
   
   // thêm nhanh thành công
   if(isset($result_add['code']) && $result_add['code'] == 200){
      $usp_id  = isset($result_add['usp_id'])? intval($result_add['usp_id']) : 0;
      
      // có id sản phẩm
      if($usp_id > 0){
         
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
         }else{
            $result['error'] = 'Không tạo được dòng dữ liệu';
         }
         
      }else{
         $result['error'] = 'Không có ID sản phẩm';
      }
      
   }else{
      $result['error'] = 'Thêm mới sản phẩm thông thành công';
   }
   
}else{
   $result['error'] = 'Không có thông tin sản phẩm mới';
   $result['code'] = 400;
}

echo json_encode($result);
exit();