<?
/**
 * 
 * Class process provider
 */
class killProvider{
   
   function __construct(){
      
   }
   
   function quickAddProvider($data = array()){
      $arrayResult = array(
         'code' => 500,
         'id' => 0,
         'name' => '',
         'error' => '',
         'file' => __FILE__
      );
      
      $prd_name = isset($data['prd_name'])? trim($data['prd_name']) : '';
      $prd_phone = isset($data['prd_phone'])? trim($data['prd_phone']) : '';
      $prd_address = isset($data['prd_address'])? ($data['prd_address']) : '';     
      $prd_contact = isset($data['prd_contact'])? ($data['prd_contact']) : '';
      
      // convert font về utf8
      $prd_name   = convert_utf82utf8($prd_name);
      $prd_name   = clean_string($prd_name);
      
      if($prd_name != ''){
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
      }else{
         $arrayResult['error'] = 'Chưa nhập tên nhà cung cấp';
      }
      
      return $arrayResult;
      
   }
   
   
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
   
}