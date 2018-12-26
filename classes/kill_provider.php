<?
/**
 * 
 * Class process provider
 */
class killProvider{
   
   function __construct(){
      
   }
   
   /**
    * Quick insert info provider 
    */
   function quickAddProvider($data = array()){
      $arrayResult = array(
         'code' => 500,
         'id' => 0,
         'name' => '',
         'error' => '',
         'file' => __FILE__
      );
      
      $prd_name      = isset($data['prd_name'])? trim($data['prd_name']) : '';
      $prd_phone     = isset($data['prd_phone'])? trim($data['prd_phone']) : '';
      $prd_address   = isset($data['prd_address'])? ($data['prd_address']) : '';     
      $prd_contact   = isset($data['prd_contact'])? ($data['prd_contact']) : '';
      $prd_id        = isset($data['prd_id'])? intval($data['prd_id']) : 0;
      
      // convert font về utf8
      $prd_name   = convert_utf82utf8($prd_name);
      $prd_name   = clean_string($prd_name);
      
      if($prd_name != ''){
         
         if($prd_id > 0){
            // update
            $db_ex   = new db_execute("
                                       UPDATE ". TABLE_PROVIDER ."
                                       SET 
                                       usp_name = '". replaceMQ($prd_name) ."',
                                       usp_phone = '".  replaceMQ($prd_phone) ."',
                                       usp_address = '". replaceMQ($prd_address) ."',
                                       usp_contact = '". replaceMQ($prd_contact) ."'
                                       WHERE usp_id = ". intval($prd_id));
            unset($db_ex);    
            $arrayResult['code'] = 200;
            $arrayResult['id'] = $prd_id;
            $arrayResult['name'] = $prd_name;                                   
         }else{
            
            // thêm mới
            $db_ex = new db_execute_return();
            $prd_id  = $db_ex->db_execute("  INSERT IGNORE INTO " . TABLE_PROVIDER . "
                                            (usp_name,usp_phone,usp_address,usp_contact)
                                            VALUES ('". replaceMQ($prd_name) ."','". replaceMQ($prd_phone) ."',
                                            '". replaceMQ($prd_address) ."','". replaceMQ($prd_contact) ."')"
                                            , __FILE__ . ' ' . __LINE__);
            unset($db_ex);                                         
            if($prd_id > 0){
               $arrayResult['code'] = 200;
               $arrayResult['id'] = $prd_id;
               $arrayResult['name'] = $prd_name;
            }
         }
      }else{
         $arrayResult['error'] = 'Chưa nhập tên nhà cung cấp';
      }
      
      return $arrayResult;
      
   }
   
   /**
    * Search info provider
    */
   function searchKeyword($keyword = ''){
      $result  = array();
      $db_pro  = new db_query("  SELECT *
                                 FROM " . TABLE_PROVIDER . " 
                                 WHERE usp_name LIKE '%". replaceMQ($keyword) ."%' 
                                 LIMIT 10");
      while($row  = $db_pro->fetch()){
         $result[] = array(
            'id'     => $row['usp_id'],
            'name'   => $row['usp_name'],
            'phone'  => $row['usp_phone']
         );
      }  
      unset($db_pro);
      
      return $result;
   }
   
   
   /**
    * Insert info payment 
    */
   function insertPayment($data = array()){
      
      $result  = array(
         'code' => 500,
         'error' => ''
      );
      
      $upp_provider_id  = isset($data['upp_provider_id'])? intval($data['upp_provider_id']) : 0;
      $upp_total_amount = isset($data['upp_total_amount'])? doubleval($data['upp_total_amount']) : 0;
      $upp_paid         = isset($data['upp_paid'])? doubleval($data['upp_paid']) : 0;
      $upp_pay_remain   = isset($data['upp_pay_remain'])? doubleval($data['upp_pay_remain']) : 0;
      $upp_bill_code    = isset($data['upp_bill_code'])? intval($data['upp_bill_code']) : date('ymdHi');
      
      if($upp_provider_id > 0 && $upp_bill_code > 0){
                  
         $query      = "
         INSERT IGNORE INTO ". TABLE_PROVIDER_PAY ." 
            (upp_provider_id,upp_branch_id,upp_parent_id,upp_child_id,
            upp_total_amount,upp_paid,upp_pay_remain,upp_bill_code,upp_time)
         VALUES
            (". $upp_provider_id .",". BRANCH_ID .",". ADMIN_ID .",". CHILD_ID .",
            ". $upp_total_amount .",". $upp_paid .",". $upp_pay_remain .",". $upp_bill_code .",". time() .")";
         
         $db_ex      = new db_execute_return();
         $upp_id     = $db_ex->db_execute($query);
         if($upp_id > 0){
            $result['code'] = 200;
         }else{
            $result['error'] = 'Insert dòng thanh toán không thành công';
         }                     
         unset($db_ex);
      }else{
         $result['error'] = 'Thông tin thanh toán không đúng';
      }
      
      return $result;
   }
   
   /**
    * Function get provider by id
    * return row
    */
   function getById($id = 0){
      $result  = array(
         'code' => 500,
         'data' => array(),
         'error' => ''
      );
      if($id > 0){
         $db_provider   = new db_query("SELECT * FROM " . TABLE_PROVIDER . " 
                                          WHERE usp_id = " . intval($id) . " LIMIT 1");
         if($row        = $db_provider->fetch()){
            $result['code'] = 200;
            $result['data'] = $row;
         }  
         unset($db_provider);                                        
      }else{
         $result['error'] = 'Không có id';
      }
      
      return $result;
   }    
   
   /**
    * 
    * Function delete by id
    */
   function deleteById($data = array()){
      $id   = isset($data['id'])? intval($data['id']) : 0;
      
      $result  = array(
         'code'   => 500,
         'data'   => array(),
         'error'  => '',
         'id'     => $id
      );
      
      if($id <= 0){
         $result['error']  = 'Không có id cần xóa';
      }else{
         // trước khi xóa check xem id nhà cung cấp đã nằm trong hóa đơn nào chưa, có rồi thì không đc xóa
         $db_check   = new db_query("SELECT usb_id 
                                       FROM " . TABLE_BILL . " 
                                       WHERE usb_provider_id = " . $id . "
                                       LIMIT 1");
         if($rcheck  = $db_check->fetch()){
            $result['error'] = 'Nhà cung cấp này đã có hành động nhập kho, Bạn không thể xóa thông tin này';
         }else{
            $db_del  = new db_execute("DELETE FROM " . TABLE_PROVIDER . " 
                                       WHERE usp_id = " . $id, __FILE__ . " LINE: " . __LINE__);
            unset($db_del);
            
            $result['code']   = 200; 
         } 
         unset($db_check);                                    
                                            
      }
      
      return $result;
   }
   
   /**
    * Function getList
    * return list provider
    */
   function getList($data = array()){
      $p_name     = isset($data['name'])? replaceMQ(trim($data['name'])) : '';
      $p_phone    = isset($data['phone'])? replaceMQ(trim($data['phone'])) : '';
      $page       = isset($data['page'])? intval($data['page']) : 1;
      
      $result  = array(
         'code'   => 500,
         'error'  => '',
         'data'   => array(),
         'count'  => 0
      );
      
      // query where normal
      $sql_where  = "";
      
      $sql_count  = "";
      
      if(trim($p_name)  != ''){
         $sql_where .= " AND usp_name LIKE '%". replaceMQ($p_name) ."%'";
         $sql_count .= " AND usp_name LIKE '%". replaceMQ($p_name) ."%'";
      }
      if(trim($p_phone)  != ''){
         $sql_where .= " AND usp_phone LIKE '%". replaceMQ($p_phone) ."%'";
         $sql_count .= " AND usp_phone LIKE '%". replaceMQ($p_phone) ."%'";
      }
      
      // phân trang
      if($page > 0){
         $sql_where  .= " LIMIT " . (($page - 1)*PAGE_SIZE) . "," . PAGE_SIZE;
      }
      
      // select count
      $db_count = new db_query("SELECT 
                                 COUNT(*) AS count
                                 FROM " . TABLE_PROVIDER . "
                                 WHERE 1 " . $sql_count, __FILE__ . " LINE: " . __LINE__);
      if($rcount  = $db_count->fetch()){
         $result['count'] = $rcount['count'];
      }
      unset($db_count);
      
      
      // select sql
      $db_data = new db_query("SELECT * FROM " . TABLE_PROVIDER . "
                                 WHERE 1 " . $sql_where, __FILE__ . " LINE: " . __LINE__);
      while($row  = $db_data->fetch()){
         $response[$row['usp_id']] = $row;
      }
      unset($db_data);
      
      //
      if(!empty($response)){
         $result['code']   = 200;
         $result['data']   = $response;
      }else{
         $result['error']  = 'Không có dữ liệu';
      }
      return $result;
   }
}