<?
/**
 * quick add product
 * option:
 * 1=> add template warehouse
 * 2=> add template sale
 */
include 'config.php';

$data = isset($_POST)? $_POST : array();

$result  = array(
   'code' => 500,
   'data' => '',
   'error' => '',
   'file' => __FILE__
);

if(!empty($data)){
   
   $result_add = $killProvider->quickAddProvider($data);   
   
   // thêm nhanh thành công
   if(isset($result_add['code']) && $result_add['code'] == 200){
      $result['data'] = $result_add;
      $result['code'] = 200;
   }else{
      $result['error'] = $result_add['error'];
   }
   
}else{
   $result['error'] = 'Không có thông tin sản phẩm mới';
   $result['code'] = 400;
}

echo json_encode($result);
exit();