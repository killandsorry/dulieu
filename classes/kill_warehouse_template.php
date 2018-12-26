<?
/**
 * Class xử lý các phương thức kho
 */
 
class killWareHouseTemplate{
   
   function __construct(){
      
   }   
   
   function insert_template($data = array()){
      global $array_unit, $killProduct; 
      
      $result  = array(
         'code'         => 500,
         'template'     => '',
         'error'        => '',
         'total_money'  => 0,
         'total_count'  => 0,
         'item_count'   => 0,
         'item_id'      => 0,
         'item_exits'   => 0
      );
      
      $usp_id  = isset($data['usp_id'])? intval($data['usp_id']) : 0;
      if($usp_id <= 0){
         $result['error'] = 'Không có id sản phẩm';
      }else{
         
         $row  = $killProduct->get_product_by_id($usp_id);
         if(!empty($row)){
            
            $template_id            = 0;
            $unit_parent            = 1;
            $warehouse_number       = 0;
            $warehouse_total_money  = 0;
            $price_import           = 0;  
            
            // check sản phẩm đã tồn tài chưa
            // -----------------------------------------------
            $dataTemplate  = $this->get_insert_template($usp_id);            
            if(!empty($dataTemplate)){
               
               
               foreach($dataTemplate as $id => $tem){
                  $price_import  = $tem['usw_price_import'];
                  // tự tăng số lượng lên 1
                  $dataTemplate[$id]['usw_number_unit_parent'] += 1;
                  break;
               }
               
               // update template
               $dataUpdate = $this->update_template($dataTemplate);
               
               $template_id = isset($dataUpdate['item_id'])? $dataUpdate['item_id'] : 0;
               $result['item_count']   = isset($dataUpdate['item_count'])? $dataUpdate['item_count'] : 1;
               $result['item_exits']   = 1;
               $result['item_id']      = $template_id;
               
               
               $unit_parent            = $result['item_count'];
               $warehouse_number       = $unit_parent * $row['usp_packing'];
               $warehouse_total_money  = $unit_parent * $price_import;
               $price_import_small     = ceil($price_import / $row['usp_packing']);
               $result['total_count']  = format_currency($warehouse_number);
               $result['total_money']  = format_currency($warehouse_total_money);
               
            }else{
               /** Thêm example vào bảng warehouse */
               $db_ex = new db_execute_return();
               $template_id  = $db_ex->db_execute("  INSERT INTO " . TABLE_WAREHOUSE_TEMPLATE ."
                                                (usw_branch_id,usw_use_parent_id,usw_use_child_id,usw_usp_id
                                                ,usw_dat_id,usw_date_create,usw_unit,usw_price_import
                                                ,usw_type,usw_number_unit_parent,usw_number_unit_child
                                                ,usw_number,usw_status,usw_total_money_import)
                                                VALUES
                                                (". intval(BRANCH_ID) .",". intval(ADMIN_ID) .",". intval(CHILD_ID) .",". intval($usp_id) ."
                                                ,". $row['usp_dat_id'] .",". time() .",". $row['usp_unit'] .",'". doubleval($row['usp_price_import_near']) ."'
                                                ,". TYPE_NH .",1,". $row['usp_packing'] ."
                                                ,". $row['usp_packing'] .",0,". doubleval($row['usp_price_import_near']) ."
                                                )", 'File: ' . __FILE__ . ' Line: ' . __LINE__);
               unset($db_ex);                      
               
               $unit_parent            = 1;
               $price_import           = $row['usp_price_import_near'];
               $warehouse_number       = $unit_parent * $row['usp_packing'];
               $warehouse_total_money  = $unit_parent * $price_import;
               $price_import_small     = ceil($price_import / $row['usp_packing']);
                          
            }
                                    
            //----------------------------------------------------
              
            if($template_id > 0){
               
               // lấy template warehouse
               $fileTemplate  = '../template/example_warehouse.html';
               $template      = @file_get_contents($fileTemplate);
               
               // dữ liệu replate vào temp
               $dataReplate   = array(
                  'id'                    => $template_id
                  ,'usp_pro_name'         => $row['usp_pro_name']
                  ,'unit_import'          => $array_unit[$row['usp_unit_import']]
                  ,'unit'                 => $array_unit[$row['usp_unit']]
                  ,'usp_packing'          => $row['usp_packing']
                  ,'unit_parent'          => $unit_parent
                  ,'total_count'          => format_currency($warehouse_number)
                  ,'price_import'         => format_currency($price_import)
                  ,'price_export'         => 0
                  ,'total_import_money'   => format_currency($warehouse_total_money)
                  ,'price_import_small'   => format_currency($price_import_small)
                  ,'unit_note'            => '1 ' . $array_unit[$row['usp_unit_import']] . ' ' . $row['usp_packing'] . ' ' . $array_unit[$row['usp_unit']]
                  ,'lo'                   => ''
                  ,'date_expires'         => '' 
               );
               
               foreach($dataReplate as $key => $value){
                  $template = str_replace('{{'. $key .'}}', $value, $template);
               }
                              
               // return data
               $result['code']      = 200;
               $result['template']  = $template;
               
               
            }else{
               $result['error'] = 'Thêm mẫu nhập hàng không thành công';
            }                                                      
            
         }else{
            $result['error'] = 'Sản phẩm đã ngừng kinh doanh';
         }
         unset($db_pro);                                  
      }
      
      // trả về dữ liệu
      return $result;
      
   }
   
   
   // get insert template
   function get_insert_template($id = 0){
      global $array_unit;
      
      $dataTemplate  = array();
      
      $sql_where     = '';
      
      // id sản phẩm
      if($id > 0) $sql_where = " AND usw_usp_id = " . intval($id);
      
      $db_template   = new db_query("SELECT * FROM " . TABLE_WAREHOUSE_TEMPLATE . "
                                       WHERE usw_status = 0 ". $sql_where, 
                                       "File: " . __FILE__ . " Line: " . __LINE__);
      while($row     = $db_template->fetch()){
         $dataTemplate[$row['usw_id']] = $row;
      }
      unset($db_template);
      
      return $dataTemplate;
      
   }
   
   
   /**
    * 
    * Function update template warehouse
    */
   function update_template($data = array()){
      global $admin_id, $child_id, $branch_id, $array_unit;
      $result  = array(
         'code'         => 500,
         'item_count'   => 1,
         'error'        => '',
         'item_id'      => 0
      );
      
      if(empty($data)){
         $result['error'] = 'Không có dòng dữ liệu update';
      }else{
         foreach($data as $id => $row){
            $unit_parent   = intval($row['usw_number_unit_parent']);
            $unit_child    = intval($row['usw_number_unit_child']);
            $price_import  = $row['usw_price_import'];
            $price_export  = $row['usw_price_export'];
            $lo            = $row['usw_lo'];
            $date_expires  = 0;
            $arrayDate     = explode('/', $row['usw_date_expires']);
            if(!empty($arrayDate)){
               $day        = isset($arrayDate[0])? $arrayDate[0] : 0;
               $month      = isset($arrayDate[1])? $arrayDate[1] : 0;
               $year       = isset($arrayDate[2])? $arrayDate[2] : 0;
               if($day > 0 && $month > 0 && $year > 0){
                  $date_expires  = mktime(0,0,0, $month, $day, $year);
               }
            }
            
            // thông tin update
            $number              = $unit_parent * $unit_child;
            $total_money_import  = $price_import * $unit_parent;
            
            $db_ex   = new db_execute("UPDATE " . TABLE_WAREHOUSE_TEMPLATE . " SET
                                       usw_number_unit_parent = " . intval($unit_parent) . ",
                                       usw_number_unit_child   = ". intval($unit_child) .",
                                       usw_price_import     = ". doubleval($price_import) .",
                                       usw_price_export     = ". doubleval($price_export) .",
                                       usw_lo  = '". replaceMQ($lo) ."',
                                       usw_date_expires = ". intval($date_expires) .",
                                       usw_number = " . $number . ",
                                       usw_total_money_import = " . doubleval($total_money_import) . "
                                       WHERE usw_id = " . intval($id));
            unset($db_ex);
            
            $result['item_count']   = $unit_parent;
            $result['code']         = 200;
            $result['item_id']      = intval($id);
            
            break;
         }
      }
      
      return $result;
      
   }          
   
   
   /**
    * Function delete_template (delete template id)
    */
   function delete_template($usw_id = 0){
      if($usw_id > 0){
         $db_ex   = new db_execute("DELETE FROM " . TABLE_WAREHOUSE_TEMPLATE . "
                                       WHERE usw_id = " . intval($usw_id) . "
                                       AND usw_branch_id = " . BRANCH_ID . "
                                       AND usw_use_parent_id = " . ADMIN_ID);
         unset($db_ex);
      }
   }    
}