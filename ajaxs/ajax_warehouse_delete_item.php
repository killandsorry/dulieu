<?
/**
 * xóa item nhập hàng tạm 
 */

include 'config.php';

$id   = getValue('id', 'int', 'POST', 0);

$result  = array(
   'code' => 500,
   'error' => ''
);

if($id <= 0){
   $result['error'] = 'Thông tin xóa lịch sử không đúng';
}else{
   $db_check   = new db_query("SELECT usw_id 
                                 FROM ". TABLE_WAREHOUSE . " 
                                 WHERE usw_id = " . intval($id) . " AND usw_status = 0
                                 LIMIT 1");
   if($row  = $db_check->fetch()){
      // xóa bản ghi
      $db_del  = new db_execute("DELETE FROM " . TABLE_WAREHOUSE . " WHERE usw_id = " . intval($id));
      unset($db_del);
      
      $result['code'] = 200;
   }else{
      $result['error'] = 'Bạn không có quyền xóa dòng dữ liệu';
   }
   unset($db_check);
}

echo json_encode($result);
exit();