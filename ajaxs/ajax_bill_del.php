<?
/**
 * xóa thông tin bill 
 */
include 'config.php';

$result  = array(
   'code' => 500,
   'data' => array(),
   'error' => ''
);

$bill_code  = getValue('bill_code', 'int', 'POST', 0);
$bill_type  = getValue('bill_type', 'int', 'POST', STOCK_CARD_TYPE_IN);

if($bill_code <= 0){
   $result['error'] = 'Thông tin không có';
}else{
   $dataDel = $_POST;
   $response      = $killBill->deleteBill($dataDel);
   if($response['code'] == 200){
      $result['code']   = 200;
      $result['data']   = $response['data'];
   }else{
      $result['error']  = $response['error'];
   }
}

echo json_encode($result);
exit();