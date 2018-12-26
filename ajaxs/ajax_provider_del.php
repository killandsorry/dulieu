<?
/**
 * delete provider
 * option:
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
   
   $response = $killProvider->deleteById($data);   
   
   // xóa nhanh thành công
   if(isset($response['code']) && $response['code'] == 200){
      $result['data'] = $response;
      $result['code'] = 200;
   }else{
      $result['error'] = $response['error'];
   }
   
}else{
   $result['error'] = 'Không có thông tin cần xóa';
   $result['code'] = 400;
}

echo json_encode($result);
exit();