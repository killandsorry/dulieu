<?

/**
 * class có nhiệm vụ search ..
 * 
 */
class killSearch{
   
   var $minResult = 5;
   
   public function __construct(){
      
   }
   
   /**
    * input string
    * return true: is barcode
    * return false: is keyword
    */
   public function detectBarcode($input = ''){
      if($input == '') return false;
      if(is_numeric($input)) return true;
      
      return false;
   }
   
   
   function searchKeyword($keyword = '', $module = ''){
      global $admin_id, $child_id, $branch_id, $array_unit;
      
      $result  = array();
      $db_pro  = new db_query("  SELECT usp_pro_name, usp_id, usp_dat_id, usp_remain, 	usp_price, usp_unit, usp_unit_import, usp_packing
                                 FROM " . TABLE_PRODUCT . " 
                                 WHERE usp_pro_name LIKE '%". replaceMQ($keyword) ."%' 
                                 LIMIT 10");
      while($row  = $db_pro->fetch()){
         $unit_import   = isset($array_unit[$row['usp_unit_import']])? $array_unit[$row['usp_unit_import']] : '';
         $unit          = isset($array_unit[$row['usp_unit']])? $array_unit[$row['usp_unit']] : '';
         $result[] = array(
            'id'     => $row['usp_id'],
            'name'   => $row['usp_pro_name'],
            'price'  => format_currency($row['usp_price']),
            'unit'   => '1 ' . $unit_import . ' / ' . $row['usp_packing'] . ' ' . $unit,
            'type'   => 'Danh sách thuốc trong kho',            
            'temp' => $row['usp_dat_id'],
            'remain' => format_currency($row['usp_remain']),
            'remain_name' => ' - Tồn: <b class="cl0 fs11">'. format_currency($row['usp_remain']) .'</b> ' . $unit
         );
      }  
      unset($db_pro);     
      
      // nếu là module nhập hàng thì suggest thêm kết quả nếu <= 5
      if(count($result) < $this->minResult && $module == 'warehouse'){
         
         // lấy thêm từ bảng temp
         $db_tem  = new db_query("  SELECT dat_id, dat_name, dat_unit_import, dat_unit, dat_specifi
                                    FROM data_temp
                                    WHERE dat_name LIKE '%". replaceMQ($keyword) ."%' AND dat_active = 1
                                    LIMIT 6");
         while($rtem = $db_tem->fetch()){
            $unit_import   = isset($array_unit[$rtem['dat_unit_import']])? $array_unit[$rtem['dat_unit_import']] : '';
            $unit          = isset($array_unit[$rtem['dat_unit']])? $array_unit[$rtem['dat_unit']] : '';
            $result[] = array(
               'id'     => 0,
               'name'   => $rtem['dat_name'],
               'price'  => 0,
               'unit'   => '1 ' . $unit_import . ' / ' . $rtem['dat_specifi'] . ' ' . $unit,
               'type'   => 'Thuốc chưa có trong kho cần nhập mới thông tin',               
               'temp'   => $rtem['dat_id'],
               'remain' => 0,
               'remail_name' => ''
            );
         }
         unset($db_tem);
                  
      }
      
      return $result;                          
   }
   
   function searchBarcode($barcode = ''){
      
   }
}