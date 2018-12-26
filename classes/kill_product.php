<?

/**
 * class có nhiệm vụ xử lý thông tin sản phẩm ..
 * 
 */
class killProduct{
   
   function __construct(){
      
   }
   
   // add product
   function quick_add_product($data = array()){
      global $admin_id, $child_id, $branch_id, $array_unit;
      
      $arrayResult = array(
         'code' => 500,
         'usp_id' => 0,
         'error' => '',
         'file' => __FILE__
      );
      
      
      $usp_name = isset($data['usp_name'])? trim($data['usp_name']) : '';
      $usp_barcode = isset($data['usp_barcode'])? trim($data['usp_barcode']) : '';
      $usp_cat = isset($data['usp_cat'])? intval($data['usp_cat']) : 0;     
      $usp_unit_import = isset($data['usp_unit_import'])? intval($data['usp_unit_import']) : 0;
      $usp_unit = isset($data['usp_unit'])? intval($data['usp_unit']) : 0;
      $usp_packing = isset($data['usp_packing'])? intval($data['usp_packing']) : 1;
      $temp_id = isset($data['temp_id'])? intval($data['temp_id']) : 0;
      
      // convert font về utf8
      $usp_name   = convert_utf82utf8($usp_name);
      $usp_name   = clean_string($usp_name);
      
      if($usp_name != '' && $usp_unit_import > 0 && $usp_unit > 0 && $usp_packing > 0){
         
         $usp_alias  = removeAccent( mb_strtolower($usp_name, 'UTF-8') );
         $usp_md5    = md5($usp_alias);
         
         // check trung thông tin
         $db_check = new db_query("SELECT usp_id FROM " . TABLE_PRODUCT . "
                                    WHERE usp_md5 = '". $usp_md5 ."' LIMIT 1", __FILE__ . " " . __LINE__);
         if($rcheck = $db_check->fetch()){
            $arrayResult['error'] = 'Thông tin sản phẩm đã tồn tại';
            $arrayResult['usp_id'] = $rcheck['usp_id'];
            $arrayResult['code'] = 200;            
            return $arrayResult;
         }                                    
         
         // bắt đầu thêm thông tin
         $db_ex = new db_execute_return();
         $usp_id  = $db_ex->db_execute("  INSERT IGNORE INTO " . TABLE_PRODUCT . "
                                         (usp_pro_name,usp_pro_name_alias,usp_md5,usp_dat_id,usp_cat_id,usp_unit,
                                         usp_unit_import,usp_packing,usp_use_parent_id,usp_use_child_id,
                                         usp_branch_id,usp_last_update,usp_barcode)
                                         VALUES ('". replaceMQ($usp_name) ."','". replaceMQ($usp_alias) ."','". $usp_md5 ."',
                                         ". intval($temp_id) .",". intval($usp_cat) .",". intval($usp_unit) .",
                                         ". intval($usp_unit_import) .",". intval($usp_packing) .",
                                         ". $admin_id .",". $child_id .",". $branch_id .",
                                         ". time() .",'". replaceMQ($usp_barcode) ."')", __FILE__ . ' ' . __LINE__);
         
         if($usp_id > 0){
            $arrayResult = array(
               'code' => 200,
               'usp_id' => $usp_id,
               'error' => ''
            );
         }      
         unset($db_ex);                                   
         
         
      }else{
         $arrayResult['error'] = 'Thông tin không đủ để thêm mới 01 sản phẩm';
      }
      
      return $arrayResult;
   }
   
   
   /**
    * Get product by id
    * @input : int
    * :usp_id
    * 
    * return array() 
    *
    */
   function get_product_by_id($id = 0){
      global $admin_id, $child_id, $branch_id, $array_unit;
      
      $result  = array();
      $usp_id  = intval($id);
      if($usp_id <= 0) return $result;
      
      $db_pro  = new db_query("SELECT * FROM " . TABLE_PRODUCT . "
                                 WHERE usp_id = " . intval($usp_id) . " 
                                 AND usp_active = 1
                                 LIMIT 1", __FILE__ . " Line: " . __LINE__);
      if($row  = $db_pro->fetch()){
         $row['unit_import_name']    = $array_unit[$row['usp_unit_import']];
         $row['unit_name']           = $array_unit[$row['usp_unit']];
      }
      unset($db_pro);
      
      if(isset($row)) return $row;
   }
   
   /**
    * Function update coc, price_inport, remain..
    * 
    */
   function update_coc_remain($data = array()){
      $result  = array(
         'code' => 500,
         'error' => ''
      );
      
      $usp_id     = isset($data['usp_id'])? intval($data['usp_id']) : 0;
      $usp_price_import_near  = isset($data['price_import'])? doubleval($data['price_import']) : 0;
      $usp_remain = isset($data['remain'])? intval($data['remain']) : 0;
      
      if($usp_id <= 0){
         $result['error']   = 'Không có id sản phẩm';
      }else{
      
         $db_ex      = new db_execute("UPDATE " . TABLE_PRODUCT . " SET 
                                       usp_price_import_near = " . $usp_price_import_near . ",
                                       usp_remain = " . $usp_remain . "
                                       WHERE usp_id = " . intval($usp_id) . "
                                       AND usp_branch_id = " . BRANCH_ID . " AND usp_use_parent_id = " . ADMIN_ID);
         unset($db_ex);
         $result['code']   = 200;
      }
      return $result;
   }      
}