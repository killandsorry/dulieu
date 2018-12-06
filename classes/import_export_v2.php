<?
/**
 * Nhập - xuất thuốc
 */
define("MOVE_STOCKS_ZERO", 0);
define("MOVE_STOCKS_MOVING", 1);
define("MOVE_STOCKS_ACCESS", 2);
define("MOVE_STOCKS_CANCEL", 3);

if($_SERVER['SERVER_NAME'] == 'localhost'){
   define('URL_WEB', 'http://localhost:1030');
}else{
   define('URL_WEB', 'http://quanlybanthuoc.com');   
}


class import_export{
   
   
   function detect_barcode($keyword = ''){
      return preg_match('/^-?[0-9]+$/', $keyword) ? 1 : 0;
   }
   
   function  detect_me_barcode($keyword = ''){
      $firstCode     = substr($keyword, 0, 5) * 1;
      return ($firstCode % 10000 == 0)? 1 : 0;
   }
   
   
   /**
    * Function brock unit 
    * $str
    *    chai 20ml
    *    hộp 10 viên
    *    hộp 2 vỉ 10 viên
    */
   function decode_unit($str = ''){
      global $array_unit_text;
      $str  = trim($str);
      if($str == '') return 0;
      
      $str = str_replace(array('*', '-', ',', '.'), ' ', $str);
      $str = str_replace('  ', ' ', $str);
      $str = str_replace('  ', ' ', $str);
      $arr = explode(' ', $str);
      
      $count   = count($arr);
      if($count < 2) return 0;
      
      $array_unit = array(
         'unit_import' => 0,
         'unit' => 0,
         'specifi' => 1
      );
      
      $unit = '';
      $unit_import   = '';
      $specifi = 1;
      
      switch ($count){
         case 2:
            $unit_import   = isset($arr[0])? $this->clean_keyword($arr[0]) : "don vi";
            $specifi       = 1;
            $unit          = $unit_import;
            
            break;
         case 3:
            $unit_import   = isset($arr[0])? $this->clean_keyword($arr[0]) : "don vi";
            $specifi       = isset($arr[1])? intval($arr[1]) : 1;
            $unit          = isset($arr[2])? $this->clean_keyword($arr[2]) : 'don vi';
            if($unit == 'ml') $unit = $unit_import;
            
            break;
         case 5:
            $unit_import   = isset($arr[0])? $this->clean_keyword($arr[0]) : "don vi";
            $quantity_p    = isset($arr[1])? intval($arr[1]) : 1;
            $quantity_c    = isset($arr[3])? intval($arr[3]) : 1;
            
            $specifi = $quantity_p * $quantity_c;
            $unit          = isset($arr[4])? $this->clean_keyword($arr[4]) : 'don vi';
            break;
      }
      
      if($unit != ''){
         if(isset($array_unit_text[$unit])) $array_unit['unit'] = $array_unit_text[$unit];
      }
      
      if($unit_import != ''){
         if(isset($array_unit_text[$unit_import])) $array_unit['unit_import'] = $array_unit_text[$unit_import];
      }
      
      if($specifi > 1){
         $array_unit['specifi'] = $specifi;
      }
      
      return $array_unit;
   }
    
   
   /**
    * Hàm search keyword in product and data
    * keyword :
    * table_product:
    * return array
    */
   function search_keyword($data = array()){
      global $admin_id, $branch_id, $array_unit, $myuser, $mysetting;
      $array_return = array();
      
      $setting_suggest  = isset($mysetting['suggest']['vl'])? intval($mysetting['suggest']['vl']) : 0;
      $show_sold        = isset($mysetting['sold']['vl'])? intval($mysetting['sold']['vl']) : 0;
      $sale             = isset($mysetting['sale']['vl'])? intval($mysetting['sale']['vl']) : 1;
      
      $keyword = isset($data['keyword'])? $data['keyword'] : '';
      $module  = isset($data['module'])? $data['module'] : '';
      
      $sql_keyword   = '';
      if($keyword != '') $sql_keyword  = " AND usp_pro_name LIKE'%". replaceMQ($keyword) ."%' ";
      
      
      $array_return['html'] = '<ul class="listProduct"><li>';
      $array_return['count'] = 0;
      
      $data = array();
      $idnot_in   = array();
      
      $sql_active = " AND usp_active = 1";
      if($module=='stock') $sql_active = " AND usp_active <> 2";
      
      
      if($module == 'sale'){
         // tìm kiếm thuốc trong liều thuốc xem có không để hiển thị lên đầu
         $key_combo  = removeAccent(mb_strtolower($keyword, 'UTF-8'));
         $db_combo   = new db_query("SELECT * FROM " . USER_COMBO . " 
                                       WHERE stc_use_parent_id = " . intval($admin_id) . " AND stc_branch_id = " . intval($branch_id). "
                                       AND stc_name_accent LIKE '%". replaceMQ($key_combo) ."%'");
         while($rcombo  = $db_combo->fetch()){
            $rcombo['type'] = 'combo';
            $data[] = $rcombo;
         }
         unset($db_combo);
         
      }
      $db_products = new db_query("SELECT * FROM " . USER_PRODUCTS . " 
                                    WHERE usp_use_parent_id = " . intval($admin_id) . " 
                                          AND usp_branch_id = " . intval($branch_id) . $sql_keyword . $sql_active ."
                                    ORDER BY usp_active DESC LIMIT 30");
      while($row  = mysqli_fetch_assoc($db_products->result)){
         $data[] = $row;
      }
      unset($db_products);
      
      if(!empty($data)){
         $array_return['html'] .= '<p class="sale_label">Thuốc trong kho</p>';
         $is_not_active = 0;
         $txt_not_active = '';
         foreach($data  as $dpid => $row){
            $type = isset($row['type'])? $row['type'] : '';
            if($type == 'combo'){
               $array_return['html'] .= '<p class="key_products" data-id="'. $row['stc_id'] .'" data-type="combo" data-name="'. $row['stc_name'] .'" >                     
                                          <span class="product_name" onclick="add_combo('. $row['stc_id'] .');">'. $row['stc_name'] .'</span>
                                       </p>';   
               $array_return['count'] += 1;
            }else{
               $txt_not_active = '';
               $show_click = 1;
               if($row['usp_active'] == 0 && $module == 'stock'){
                  $txt_not_active = '<i class="price" style="font-size:11px;"> (ngừng bán)</i>';
                  if($is_not_active == 0){
                     $array_return['html'] .= '<p class="sale_label price">Thuốc đã ngừng bán</p>';
                  }
                  $is_not_active++;
               }
               
               // check thuốc hết hàng thì có đc bán hay không
               if($module == 'sale' && $sale == 0 && $row['usp_quantity'] <= 0){
                  $txt_not_active = '<i class="price" style="font-size:11px;"> (Thuốc hết hàng)</i>';
                  $show_click = 0;
               }
               
               $idnot_in[] = $row['usp_dat_id'];
               $unit = '';
               $sold = '';
               if($row['usp_specifi'] > 0 && $row['usp_unit_import'] > 0 && $row['usp_unit'] > 0){
                  $unit_import   = isset($array_unit[$row['usp_unit_import']])? $array_unit[$row['usp_unit_import']] : '';
                  $unit_name     = isset($array_unit[$row['usp_unit']])? $array_unit[$row['usp_unit']] : '';
                  
                  if($show_sold == 1){
                     $sold = ', Tồn: ' . $row['usp_quantity'];
                  }
                  
                  $unit = '<i class="item_unit "> ('. (($row['usp_specifi'] > 1)? $unit_import . ' '. $row['usp_specifi'] . ' ' . $unit_name : $unit_name) . $sold .')</i>';
               }
               
               if($show_sold == 1 && $unit == ''){
                  $unit = '<i class="item_unit "> (Tồn: ' . $row['usp_quantity'] . ')</i>';
               }
               
               
               $array_return['html'] .= '<p class="key_products" data-datid="'. $row['usp_dat_id'] .'" data-id="'. $row['usp_id'] .'" data-name="'. $row['usp_pro_name'] .'" data-price="'. $row['usp_price'] .'" data-quantity="'. $row['usp_quantity'] .'">                     
                                          <span class="product_name" onclick="'. ( ($show_click == 1)?  "addToOrder(this);" : '' ) .'">'. $row['usp_pro_name'] . $txt_not_active . $unit .'</span>
                                       </p>';   
               $array_return['count'] += 1;
            }
         }
      }  
      
      // nếu trong kho có ít hơn 5 thuốc thì lấy thêm trong data
      if($module != 'move_stock'){
         $is_suggest = 1;
         if($module == 'sale' && $setting_suggest == 0) $is_suggest = 0;
         if(count($data) < 6 && $is_suggest == 1){
            $data = array();
            
            $sqlWhere = " AND dat_name LIKE '%". replaceMQ($keyword) ."%'";
            if(!empty($idnot_in)){
               $sqlWhere   .= " AND dat_id NOT IN(". implode(',', $idnot_in) .") ";
            }
            
            $db_products = new db_query("SELECT * FROM datas
                                          WHERE 1 " . $sqlWhere . " AND dat_active = 1 LIMIT 30");
            while($row  = mysqli_fetch_assoc($db_products->result)){               
               $data[$row['dat_id']] = $row;
            }
            unset($db_products);
            
            
            if(!empty($data)){
               $array_return['html'] .= '<p class="sale_label">Bán và Yêu cầu nhập thuốc vào kho</p>';
               foreach($data as $did => $row_d){       
                  $unit = '';
                  if($row_d['dat_specifi'] > 0 && $row_d['dat_unit_import'] > 0 && $row_d['dat_unit'] > 0){
                     $unit_import   = isset($array_unit[$row_d['dat_unit_import']])? $array_unit[$row_d['dat_unit_import']] : '';
                     $unit_name     = isset($array_unit[$row_d['dat_unit']])? $array_unit[$row_d['dat_unit']] : '';                  
                     $unit = '<i class="item_unit "> ('. (($row_d['dat_specifi'] > 1)?  $unit_import .' ' . $row_d['dat_specifi'] . ' ' . $unit_name : $unit_name) .')</i>';
                  }
                  $array_return['html'] .= '<p class="key_products" data-datid="'. $row_d['dat_id'] .'"  data-name="'. $row_d['dat_name'] .'" data-price="0" data-quantity="0">                     
                                             <span class="product_name" onclick="addToOrder(this);">'. $row_d['dat_name'] . $unit .'</span>
                                          </p>';  
                  $array_return['count'] += 1;
               }
            } 
            
         }
      }
      
      $array_return['html'] .= '</li></ul>';
      
      if($array_return['count'] > 0) $array_return['status'] = 1;
      if($array_return['count'] == 0){
         $notice  = '<p>Chúng tôi không tìm thấy thuốc nào như <b>'. $keyword .'</p>';
         if($myuser->isParent() || $myuser->checkRight(USER_RIGHT_STOCK_ADD)){
            $notice  = '<p>Chúng tôi không tìm thấy thuốc nào như <b>'. $keyword .'</b>. <span onclick="add_product();" class="status_btn bg_acc"><i class="icon_add">+</i>Thêm thuốc</span> vào kho.</p>';
         }
         if($module == 'move_stock'){
            $notice = '<p>Bạn chỉ được chuyển những thuốc có trong kho đến chi nhánh khác</p>';
         }
         $array_return['html'] = '<ul class="listProduct">
                                    <li>
                                       <div class="add_new_pro">'. $notice .'</div>
                                    </li>
                                  </ul>';
      }
      
      return $array_return;
      
   }

   /**
   * Ham lay ra du lieu chua chot so
   *
   */
   function getOrderNotCloseBook(){
      global $admin_id, $branch_id, $myuser;
      //lây du lieu trong bang ỏrder ra
      $db_select = new db_query("SELECT uso_id,uso_date,uso_total_money FROM
                                 " . USER_SALE . "
                                 WHERE uso_ucb_id = 0
                                 AND uso_branch_id = " . intval($branch_id) . "
                                 AND uso_use_parent_id = " . intval($admin_id) . "
                                 AND uso_use_child_id = " . intval($myuser->u_id) . "
                                 AND uso_status = 1
                                 ",__FILE__);
      $arrayReturn = array();
      while($row = mysqli_fetch_assoc($db_select->result)){
        $date = strtotime(date("d-m-Y",$row["uso_date"]));
        $arrayReturn[$date]["date"] = $date;
        if(!isset($arrayReturn[$date]["min_time"])) $arrayReturn[$date]["min_time"]  = $row["uso_date"];
        if($arrayReturn[$date]["min_time"] > $row["uso_date"]) $arrayReturn[$date]["min_time"] = $row["uso_date"];
        if(!isset($arrayReturn[$date]["max_time"])) $arrayReturn[$date]["max_time"]  = $row["uso_date"];
        if($arrayReturn[$date]["max_time"] < $row["uso_date"]) $arrayReturn[$date]["max_time"] = $row["uso_date"];
        if(!isset($arrayReturn[$date]["total_money"])){
            $arrayReturn[$date]["total_money"]  = $row["uso_total_money"];
          }else{
            $arrayReturn[$date]["total_money"]  += $row["uso_total_money"];
          }
        $arrayReturn[$date]["listid"][$row["uso_id"]] = $row["uso_id"];
      }
      krsort($arrayReturn);
      return $arrayReturn;
   }
   
   /**
    * Hàm xóa dòng dữ liệu bán hàng
    * param(
    *    'sale_id' =>
    * )
    * return array
    */
   function sale_delete_record($data = array()){
      global $admin_id, $branch_id, $myuser;
      $child_id   = $myuser->u_id;
      
      $arrayReturn   = array(
         'status' => 0,
         'error' => ''
      );
      
      // id của bản ghi
      $sale_id = isset($data['sale_id'])? intval($data['sale_id']) : 0;
      
      if($sale_id <= 0){
         $arrayReturn['error'] = 'Không có bản ghi cần xóa';
      }else{
         // kiểm tra xem user hiện tại có quyền vs bản ghi này không
         $db_check   = new db_query("SELECT * FROM " . USER_SALE . "
                                     WHERE uso_id = " . intval($sale_id) . " LIMIT 1");
         if($row_sale  = mysqli_fetch_assoc($db_check->result)){
            // check xem có phải bản ghi của mình không, của mình mới đc xóa không thì thôi
            if(!$myuser->isParent() || $row_sale['uso_use_child_id'] != $child_id || $row_sale['uso_branch_id'] != $branch_id){
               $arrayReturn['error'] = 'Bạn không có quyền xóa thông tin bán hàng này';
            }
            
            // có quyền xóa rồi thì kiểm tra có còn thời hạn được xóa không (nếu đẫ chốt sổ thì không đc xóa)
            
            if($row_sale['uso_date'] == 2){
               $arrayReturn['error'] = 'Dữ liệu đã được chốt sổ bạn không có quyền xóa bản ghi này';
            }else{
               $pro_id     = $row_sale['uso_pro_id'];
               $quantity   = $row_sale['uso_quantity'];
               
               // thực hiện xóa bản ghi và trả lại số lượng cho bảng nhập kho               
               $db_del  = new db_execute("DELETE FROM " . USER_SALE . " WHERE uso_id = " . intval($sale_id));
               unset($db_del);
               
               // xóa bản ghi giảm giá
               $db_del_bonus  = new db_execute("DELETE FROM bonus WHERE bon_code = " . $row_sale['uso_code']);
               unset($db_del_bonus);
               
               // cập nhật trong danh sách product
               $db_update  = new db_execute("UPDATE " . USER_PRODUCTS . " 
                                             SET usp_quantity = usp_quantity + " . intval($quantity) ."
                                             WHERE usp_id = " . intval($pro_id));
               unset($db_update);
               
               $des_logs   = "\n Xóa bản ghi bán hàng có số lượng bán: <b>" . $row_sale['uso_quantity'] . "</b>, Giá bán: <b>". format_number($row_sale['uso_total_money']) ."</b>"; 
               // ghi log hành động
                $data_action  = array(
                  'file' => json_encode(debug_backtrace()),
                  'action' => 'edit',
                  'des' => $des_logs,
                  'pro_id' => $pro_id
                );

                log_action($data_action);
               $arrayReturn['status'] = 1;
            }
            
            
         }else{
            $arrayReturn['error'] = 'Không tồn tại bản ghi cần xóa';
         }
         unset($db_check);
      }
      
      return $arrayReturn;
   }
   
   /**
    * Hàm thêm vào bảng order
    * $data = array(
    *    pro_id => 
    *    quantity =>  
    * )
    */
   function sale_add_to_order($data = array()){
      global $admin_id, $myuser, $branch_id, $array_unit;
      
      
      $arrayReturn     = array(
         'status' => 0,
         'error' => '',
         'oid' => 0,
         'html' => ''
      );
      
      if($admin_id <= 0 || $branch_id <= 0 || empty($myuser)){
         $arrayReturn['error'] = 'Thông tin không đúng';
         return $arrayReturn; 
      } 
      
      
      $pro_quantity  = isset($data['quantity'])? intval($data['quantity']): 1;  
      $pro_id        = isset($data['pro_id'])? intval($data['pro_id']) : 0;
      $wls           = isset($data['wls'])? intval($data['wls']) : 0;
      
      if($pro_id <= 0){
         $arrayReturn['error'] = 'Thông tin không đúng';
         return $arrayReturn; 
      }
      
      $sql_wls = " AND uso_wholesale = 0 ";
      if($wls == 1){
         $sql_wls = " AND uso_wholesale = 1 ";
      }
      
      // kiểm tra thông tin sản phẩm có phải của admin không không phải thì báo không có quyền
      $db_products   = new db_query("  SELECT * FROM " . USER_PRODUCTS . "
                                       WHERE usp_id = " . intval($pro_id) . "
                                       AND usp_use_parent_id = " . intval($admin_id) . "
                                       AND usp_branch_id = " . intval($branch_id) . "
                                       LIMIT 1");
      if($row  = mysqli_fetch_assoc($db_products->result)){
         // kiểm tra sản phẩm này có trong đơn hàng tạm chưa. chưa thì thêm mới không thì cộng thêm vào
         $db_order   = new db_query("SELECT * FROM " . USER_SALE . "
                                     WHERE uso_branch_id = " . intval($branch_id) . " 
                                     AND uso_use_parent_id = " . intval($admin_id) . " 
                                     AND uso_use_child_id = " . intval($myuser->u_id) . "
                                     AND uso_pro_id = " . intval($pro_id) . $sql_wls . "
                                     AND uso_status = 0 LIMIT 1");
         if($row_order  = mysqli_fetch_assoc($db_order->result)){
            // có rồi thì chỉ cập nhật lại số lượng và giá tiền bán
            $quantity      = $row_order['uso_quantity'];
            $total_money   = ($quantity + 1) * $row_order['uso_price_out'];
                               
            $db_ex   = new db_execute("UPDATE " . USER_SALE . "
                                       SET uso_quantity = uso_quantity + 1,
                                       uso_total_money = " . doubleval($total_money) . "
                                       WHERE uso_id = " . intval($row_order['uso_id']));
            if($db_ex->total > 0){
               $arrayReturn['status'] = 1;
               $arrayReturn['oid'] = $row_order['uso_id'];
            }else{
               $arrayReturn['error'] = 'Không cập nhật thêm số lượng được';         
            }
            unset($db_ex);
         }else{
            // nếu đúng của mình thì thêm vào bảng order
            $db_ex   = new db_execute_return();
            $pr   = ($row['usp_price'] > 0) ? $row['usp_price'] : 0;
            
            if($wls == 1){
               if(isset($row['usp_wholesale']) && $row['usp_wholesale'] > 0){
                  $pr = $row['usp_wholesale'];
               }
            }
            $total_price   = $pr * $pro_quantity;
            $oid = $db_ex->db_execute("INSERT INTO " . USER_SALE . " 
                                          (uso_pro_id,uso_dat_id,uso_branch_id,uso_use_parent_id,uso_use_child_id,uso_date,uso_quantity,uso_price_out,uso_price_import,uso_total_money,uso_unit,uso_sale_money,uso_wholesale)
                                       VALUES(". intval($pro_id) .",". $row['usp_dat_id'] .",". intval($branch_id) .",". intval($admin_id) .",". intval($myuser->u_id) .",". intval(time()) .",". intval($pro_quantity) .",". doubleval($pr) .",0,". doubleval($total_price) .",". $row['usp_unit'] .",". doubleval($row['usp_sale_money']) .",". $wls .")");
            
            if($oid > 0){
               
               $arrayReturn['oid'] = $oid;
               $arrayReturn['status'] = 1;
               $unit = isset($array_unit[$row['usp_unit']])? $array_unit[$row['usp_unit']] : '';
               $unit_import = isset($array_unit[$row['usp_unit_import']])? $array_unit[$row['usp_unit_import']] : '';
               $quycach = intval($row['usp_specifi']);
               $unit_name  = ($unit != '')? '<span class="item_unit">Đơn vị: '. $unit . ( ($unit_import != '' && $quycach > 1)? ' ('. $unit_import . ' ' . $quycach  .' '. $unit .')' : '' ) .'</span>': '';
               $arrayReturn['html'] = '<tr class="item_order" id="item_order_'. $oid .'" data-oid="'. $oid .'">
                                             <td width="20" class="text_c"><span class="item_order_del" onclick="delete_item_order(this)">x</span></td>
                                             <td>
                                                <span class="item_order_name">'. $row['usp_pro_name'] .'</span>
                                                '. $unit_name .'
                                             </td>
                                             <td width="30" class="text_c">
                                                <p><input autocomplete="off" maxlength="8" name="quantity['. $oid .']" onblur="setUpdate(this); setTotalPrice();" title="Số lượng '. $unit .'" data-type="quantity" class="tooltip number item_order_quantity type_number text_c" id="item_order_quantity_'. $oid .'" type="tel" data-vl="'. $pro_quantity .'" value="'. $pro_quantity .'" /></p>
                                             </td>
                                             <td width="70" class="text_r">
                                                <p><input autocomplete="off" maxlength="10" name="price['. $oid .']" onblur="setUpdate(this); setTotalPrice();" title="Giá bán / 1 '. $unit .'" data-type="price_out" class="tooltip number item_order_price type_number text_r" id="item_order_price_'. $oid .'" type="tel" data-vl="'. $pr .'" data-update="'. (($pr == 0)? 1 : 0) .'" value="'. format_number($pr) .'" /></p>
                                             </td>
                                             <td width="" class="text_r">
                                                <input type="tel" autocomplete="off" data-vl="'. $total_price .'" maxlength="14" onblur="addClass_text(this); setUpdate(this); setTotalPrice();" data-type="price_total" onfocus="removeClass_text(this);" class="item_order_price_item number type_number text_text" id="item_order_price_item_'. $oid .'" value="'.format_number($total_price).'" />                                                
                                             </td>
                                          </tr>';
               //<span class="price item_order_price_item" id="item_order_price_item_'. $oid .'">'. format_number($row['usp_price']) .'</span>
            }else{
               $arrayReturn['error'] = 'Không thêm được vào hóa đơn';
            }
            unset($db_ex);
         }
         unset($db_order); 
      }else{
         $arrayReturn['error'] = 'Bạn không có quyền với sản phẩm này';
      }
      unset($db_products);
      
      return $arrayReturn;
   }
   
   /**
    * Hàm update số lượng. giá bán vào bảng order
    * 
    * $data = array(
    *    'order_id' => ,
    *    'value' => ,
    *    'type' => 
    * )
    * 
    */
   function sale_update_to_order($data = array()){
      global $admin_id, $myuser, $branch_id, $array_unit;
      
      $order_id   = isset($data['order_id'])? intval($data['order_id']) : 0;
      $value      = isset($data['value'])? intval($data['value']) : 0;
      $type       = isset($data['type'])? $data['type'] : '';
      $update     = isset($data['update'])? $data['update'] : 0;
      $wls     = isset($data['wls'])? intval($data['wls']) : 0;
      
      $field      = '';
      $field_caculator  = '';
      
      if($order_id <= 0 || $type == '') return 0;
      $status  = 0;
      
      switch($type){
         case 'quantity':
            $field = 'uso_quantity';
            $field_caculator  = 'uso_price_out';
            break;
         case 'price_out':
            $field = 'uso_price_out';
            $field_caculator  = 'uso_quantity';
            break;
      }
      
      if($field_caculator == '') return 0;
      
      $db_check   = new db_query("SELECT * FROM " . USER_SALE . "
                                  WHERE uso_id = " . intval($order_id) . "
                                  AND uso_branch_id = " . intval($branch_id) . " 
                                  AND uso_use_parent_id = " . intval($admin_id) . " 
                                  AND uso_use_child_id = " . intval($myuser->u_id) . "
                                  LIMIT 1");
      if($row = mysqli_fetch_assoc($db_check->result)){
         $total_money = $row[$field_caculator] * $value;
         $db_update  = new db_execute("UPDATE " . USER_SALE . " SET ". $field ." = " . intval($value) . ", uso_total_money = ". doubleval($total_money) ." 
                                       WHERE uso_id = " . intval($order_id));
         if($db_update->total >= 0){
            
            if($update == 1 && $type == 'price_out'){
               if($wls == 0){
                  $db_uppro   = new db_execute("UPDATE " . USER_PRODUCTS . " SET usp_price = " . doubleval($value) . "
                                                   WHERE usp_id = " . intval($row['uso_pro_id']));
                  unset($db_uppro);
               }else{
                  $db_uppro   = new db_execute("UPDATE " . USER_PRODUCTS . " SET usp_wholesale = " . doubleval($value) . "
                                                   WHERE usp_id = " . intval($row['uso_pro_id']));
                  unset($db_uppro);
               }
            }
            $status = 1;
         }
         unset($db_update);
      }
      unset($db_check);
      
      return $status;
   }
   
   /**
    * Hàm sửa thông tin bán hàng khi đã bán hàng xong
    * param:
    * $oid_id : id đơn hàng
    * $quantity : số lượng
    * $price_out : thành tiền
    */
   function sale_update_order_item($data = array()){
      
      $oid_id  = isset($data['oid'])? intval($data['oid']) : 0;
      $quantity   = isset($data['quantity'])? intval($data['quantity']) : 0;
      $price_out  = isset($data['price'])? doubleval($data['price']) : 0;
      $sale_code  = isset($data['code'])? intval($data['code']) : 0;
      $cus_id     = isset($data['cus_id'])? intval($data['cus_id']) : 0;
      $date_create  = isset($data['date_create'])? intval($data['date_create']) : 0;
      $wls     = isset($data['wls'])? intval($data['wls']) : 0;
      
      if($oid_id <= 0 || $quantity <= 0 || $price_out <= 0) return 0;
      global $myuser, $admin_id, $branch_id;
      $old_quantity  = $quantity;
      $moneyImport   = 0;
      $dat_id        = 0;
      $pro_id        = 0;
      
      $des_logs      = '<b>(Sửa bán hàng)</b>';
      
      // lấy thông tin sản phẩm
      $sql_where  = " AND uso_use_child_id = " . $myuser->u_id;
      
      $sql_wls    = "";
      if($wls == 1) $sql_wls = " AND uso_wholesale = 1 ";
      
      if($myuser->isParent()) $sql_where = "";
      $db_order = new db_query("SELECT * FROM " . USER_SALE . " 
                                    WHERE uso_id = " . intval($oid_id) . "
                                    AND uso_branch_id = " . intval($branch_id) . "
                                    AND uso_use_parent_id = " . intval($admin_id). $sql_where . $sql_wls ."
                                    LIMIT 1");
      if($row_order = mysqli_fetch_assoc($db_order->result)){
         $pro_id  = $row_order['uso_pro_id'];
         // update bảng user pro set quantity giảm đi);
         $sql_update = "";
         if($date_create > 0){
            if(date('d/m/Y', $date_create) !== date('d/m/Y', $row_order['uso_date'])){
               $sql_update = " uso_date = " . $date_create . ",";
            }
         } 
         $db_sale   = new db_execute("UPDATE " . USER_SALE . " 
                                       SET ". $sql_update ." uso_quantity = ". intval($old_quantity) . ",
                                       uso_price_out=". doubleval($price_out) . ",
                                       uso_total_money = ". doubleval($old_quantity*$price_out) ."
                                       WHERE uso_id = " . intval($oid_id));
         unset($db_sale);
         
         // update số lượng bảng product
         $sl = $row_order['uso_quantity'] - $old_quantity;
         $db_pro  = new db_execute("UPDATE " . USER_PRODUCTS . " SET usp_quantity = usp_quantity + (" . $sl . "),usp_lats_update = ". time() ." 
                                    WHERE usp_id = " . intval($pro_id) . " AND usp_use_parent_id = " . intval($admin_id));
         
         if($row_order['uso_quantity'] != $old_quantity){
            $des_logs .= "\n Sửa số lượng bán từ <b>" . $row_order['uso_quantity'] . "</b> sang <b>" . $old_quantity . "</b>";
         }
         if($row_order['uso_price_out'] != $price_out){
            $des_logs .= "\n Sửa giá bán từ <b>" . format_number($row_order['uso_price_out']) . "</b> sang <b>" . format_number($price_out) . "</b>";
         }
         
         $data_action  = array(
            'file' => json_encode(debug_backtrace()),
            'action' => 'edit',
            'des' => $des_logs,
            'pro_id' => $pro_id
         );            
         log_action($data_action);
         
         return 1;
      }else{
         return 0;
      }
      unset($db_pro);
   }
   
   /**
    * Hàm cập nhật thông tin bán hàng
    * param:
    * $oid_id :
    * $quantity : 
    * $price_out : 
    */
   function sale_caculator_stock($data = array()){
      
      $oid_id  = isset($data['oid'])? intval($data['oid']) : 0;
      $quantity   = isset($data['quantity'])? intval($data['quantity']) : 0;
      $price_out  = isset($data['price'])? doubleval($data['price']) : 0;
      $sale_code  = isset($data['code'])? intval($data['code']) : 0;
      $cus_id     = isset($data['cus_id'])? intval($data['cus_id']) : 0;
      $date_create  = isset($data['date_create'])? intval($data['date_create']) : 0;
      $bonus      = isset($data['bonus'])? intval($data['bonus']) : 0;
      $wls      = isset($data['wls'])? intval($data['wls']) : 0;
      
      if($oid_id <= 0 || $quantity <= 0 || $price_out <= 0) return 0;
      global $myuser, $admin_id, $branch_id;
      $old_quantity  = $quantity;
      $moneyImport   = 0;
      $dat_id        = 0;
      $pro_id        = 0;
      $sale_code     = intval($sale_code);
      
      $sql_wls    = "";
      if($wls == 1) $sql_wls = " AND uso_wholesale = 1 ";
      
      // lấy thông tin sản phẩm
      $db_order = new db_query("SELECT * FROM " . USER_SALE . " 
                                    WHERE uso_id = " . intval($oid_id) . "
                                    AND uso_branch_id = " . intval($branch_id) . "
                                    AND uso_use_parent_id = " . intval($admin_id). " 
                                    AND uso_use_child_id = " . intval($myuser->u_id) . $sql_wls . "
                                    AND uso_status = 0 LIMIT 1");
      if($row_order = $db_order->fetch()){         
         $pro_id  = $row_order['uso_pro_id'];
         
         $sale_money = 0;
         $sale_percent = 0;
         $default_price = 0;
         // lấy thông tin sản phẩm
         $db_pro  = new db_query("SELECT * FROM " . USER_PRODUCTS . " 
                                 WHERE usp_id = " . $pro_id . " LIMIT 1");
         if($rpro = $db_pro->fetch()){
            $sale_money = $rpro['usp_sale_money'];
            $sale_percent  = $rpro['usp_sale_percent'];
            $default_price = $rpro['usp_price'];
         }
         unset($db_pro);
         
         $sql_sale_money   = "";
         if($sale_percent > 0){
            if($default_price != $price_out && $price_out > $default_price && $default_price > 0){
               $sql_sale_money = " uso_sale_money = " . (($sale_percent * $price_out) / 100) . ",";
            }
         }
         
         
         // lấy thông tin bản ghi cuois cùng nhập kho của sản phẩm này
         $arrayLast  = $this->import_get_last_stocks(array('pro_id' => $pro_id, 'quantity' => 1));
         if(!empty($arrayLast)){
            $cogs = isset($arrayLast['uss_cogs'])? $arrayLast['uss_cogs'] : 0;
            
            
            $sql_update = "";
            if($date_create > 0) $sql_update = " uso_date = " . $date_create . ",";
            if($bonus > 0){
               $sql_sale_money = "";
               $sql_update .= " uso_sale_money = 1,";
            } 
            
            $db_order   = new db_execute("UPDATE " . USER_SALE . " 
                                          SET ". $sql_update . $sql_sale_money ." uso_quantity = ". intval($old_quantity) . ",
                                          uso_price_out=". doubleval($price_out) . ",
                                          uso_price_import =". doubleval($moneyImport) .",
                                          uso_total_money = ". doubleval( $price_out * $old_quantity ) ." ,
                                          uso_status = 1,
                                          uso_uss_id = ". $arrayLast['uss_id'] . ",
                                          uso_cogs = ". $cogs .",
                                          uso_code = ". $sale_code . ",
                                          uso_cus_id = ". intval($cus_id) . "
                                          WHERE uso_id = " . intval($oid_id));
            if($db_order->total > 0){
               // update bảng user pro set quantity giảm đi
               //file_put_contents('../logs/abcd.txt', "UPDATE " . USER_PRODUCTS . " SET usp_quantity = usp_quantity -" . intval($old_quantity) . ",usp_lats_update = ". time() ." 
                //                          WHERE usp_id = " . intval($pro_id) . " AND usp_use_parent_id = " . intval($admin_id));
               $db_pro  = new db_execute("UPDATE " . USER_PRODUCTS . " SET usp_quantity = usp_quantity -" . intval($old_quantity) . ",usp_lats_update = ". time() ." 
                                          WHERE usp_id = " . intval($pro_id) . " AND usp_use_parent_id = " . intval($admin_id));
               unset($db_pro);
               return 1;
            }else{
               return 0;
            }
         }
      }else{
         return 0;
      }
   }
   
   
   /**
    * Hàm cập nhật thông tin giảm giá bán hàng
    * param:
    * $bonus :
    * $code : 
    */
   function sale_bonus($data = array()){
      $bonus  = isset($data['bonus'])? doubleval($data['bonus']) : 0;
      $total_amount  = isset($data['total_amount'])? doubleval($data['total_amount']) : 0;
      $code   = isset($data['code'])? intval($data['code']) : 0;
      $time   = isset($data['time'])? intval($data['time']) : time();
      
      if($bonus <= 0 || $code <= 0) return 0;
      global $myuser, $admin_id, $branch_id;
      
      $db_bonus   = new db_execute("INSERT INTO bonus (bon_use_parent_id,bon_use_child_id,bon_branch_id,bon_date,bon_code,bon_money,bon_total_money)
                                    VALUES(". $admin_id .",". $myuser->u_id .",". $branch_id .",". $time .",". intval($code) .",'". doubleval($bonus) ."','". doubleval($total_amount) ."')");
      unset($db_bonus);
      
      return 1;
   }
   
   function clean_keyword($key = ''){
      if(trim($key) == '') return '';
      
      $keyword  = removeAccent(trim($key));
      $keyword  = mb_strtolower($keyword, 'UTF-8');
      $keyword  = str_replace(array('*',',','"',"'",'%','&','#','@','!', '$', '-', '_','(', ')', '<', '>', '?', ':', '.', "\\", '/'), ' ', $keyword);
      $keyword  = str_replace('  ', ' ', $keyword);
      $keyword  = str_replace('  ', ' ', $keyword);
      
      return $keyword;
   }
   
   /**
    * hàm thêm sản phẩm từ data sang product
    * array(
    *    dat_id => 
    * )
    * return pro_id
    */
    
   function import_data_to_product($data = array()){
      global   $admin_id, $branch_id, $myuser, $notification,$setting_specifi;
      $dat_id  = isset($data['dat_id'])? intval($data['dat_id']) : 0;
      $dat_unit_import  = isset($data['pro_unit_import'])? intval($data['pro_unit_import']) : 0;
      $dat_unit  = isset($data['pro_unit'])? intval($data['pro_unit']) : 0;
      $dat_specifi  = isset($data['pro_specifi'])? intval($data['pro_specifi']) : 1;
      if($setting_specifi==1)$dat_specifi = 1;
      $pro_price     = isset($data['pro_price'])? doubleval($data['pro_price']) : 0;
      $pro_price_import     = isset($data['pro_price_import'])? doubleval($data['pro_price_import']) : 0;
      $pro_barcode      = isset($data['pro_barcode'])? $data['pro_barcode'] : 0;
      $pro_name         = isset($data['pro_name'])? $data['pro_name'] : '';
      $pro_box_id       = isset($data['pro_box_id'])? intval($data['pro_box_id']) : 0;
      
      // nếu lấy thông tin đơn vị từ nhập thì cho add from dat = 1
      $add_from_dat  = isset($data['add_from_dat'])? intval($data['add_from_dat']) : 0;
      if($dat_unit_import > 0 && $dat_unit > 0) $add_from_dat = 1;
      
      if($dat_id <= 0) return 0;
      $pro_id  = 0; // mặc định product id
      $child_id   = $myuser->u_id;
      
      // lấy thông tin data lưu sang bảng product
      // lấy tên thuốc để phân tích
      $pro_alias  = replaceMQ($this->clean_keyword($pro_name));
      $db_dat  = new db_query("SELECT * FROM datas WHERE dat_id = " . $dat_id  . " LIMIT 1");
      if($rdat = mysqli_fetch_assoc($db_dat->result)){
          if($pro_barcode == '' || $pro_barcode == 0){
            $pro_barcode = $rdat['dat_barcode'];
          }
      }
      unset($db_dat);
      
      if($pro_alias != ''){
         // kiểm tra lại 1 lần nữa xem tên có trùng bảng product không
         $db_check_pro  = new db_query("SELECT * FROM " . USER_PRODUCTS . " WHERE usp_alias = '". $pro_alias ."' 
                                       AND usp_use_parent_id = ". intval($admin_id) ."
                                       AND usp_branch_id = ". intval($branch_id) ." 
                                       LIMIT 1");
         if($rpro = $db_check_pro->fetch()){
            return 0;
         }
         unset($db_check_pro);
      }
      
      if($add_from_dat == 1){
         $sql = "INSERT INTO ". USER_PRODUCTS ."(usp_price_import,usp_dat_id,usp_pro_name,usp_alias,usp_barcode,usp_me_barcode,usp_unit,usp_unit_import,usp_specifi,usp_branch_id,usp_use_parent_id,usp_use_child_id,usp_quantity,usp_price,usp_active,usp_lats_update,usp_date_expires,usp_dat_active, usp_box) 
                  SELECT ". $pro_price_import .",dat_id,dat_name,'". replaceMQ($pro_alias) ."','". $pro_barcode ."',dat_me_barcode,". intval($dat_unit) .",". intval($dat_unit_import) .",". intval($dat_specifi) .",". intval($branch_id) .",". intval($admin_id) .",". intval($child_id) .",0,". doubleval($pro_price) .",1,". time() .",0,1,". $pro_box_id ." FROM datas WHERE dat_id = ". intval($dat_id) ;
         $db_ex   = new db_execute_return();
         $pro_id  = $db_ex->db_execute($sql);
      }else{
         $sql = "INSERT INTO ". USER_PRODUCTS ."(usp_price_import,usp_dat_id,usp_pro_name,usp_alias,usp_barcode,usp_me_barcode,usp_unit,usp_unit_import,usp_specifi,usp_branch_id,usp_use_parent_id,usp_use_child_id,usp_quantity,usp_price,usp_active,usp_lats_update,usp_date_expires,usp_dat_active, usp_box) 
                  SELECT ". $pro_price_import .",dat_id,dat_name,'". replaceMQ($pro_alias) ."','". $pro_barcode ."',dat_me_barcode,dat_unit,dat_unit_import,dat_specifi,". intval($branch_id) .",". intval($admin_id) .",". intval($child_id) .",0,". doubleval($pro_price) .",1,". time() .",0,1,". $pro_box_id ." FROM datas WHERE dat_id = ". intval($dat_id) ;
         $db_ex   = new db_execute_return();
         $pro_id  = $db_ex->db_execute($sql);
      }
      
      if($pro_id > 0) $notification->setNewProductInDay();
      unset($db_ex);
      return $pro_id;
   }
   
   /**
    * Hàm thêm từ product sang product
    * param (
    *    'pro_id' =>
    * )
    * 
    * return array()
    */
   function import_product_to_product($data = array()){
      global   $admin_id, $branch_id, $myuser;
      
      $pro_id  = isset($data['pro_id'])? intval($data['pro_id']) : 0;
      $price_import  = isset($data['price_import'])? doubleval($data['price_import']) : 0;
      $price_export  = isset($data['price_export'])? doubleval($data['price_export']) : 0;
      $quantity  = isset($data['quantity'])? intval($data['quantity']) : 0;
      if($pro_id <= 0) return array();
      
      $array_return  = array();
      $db_ex   = new db_execute_return();
      $id      = $db_ex->db_execute("INSERT INTO " . USER_PRODUCTS . "
                                 (usp_dat_id,usp_pro_name,usp_alias,usp_barcode,usp_me_barcode,usp_unit,usp_unit_import,usp_specifi,usp_branch_id,usp_use_parent_id,usp_use_child_id,usp_quantity,usp_price,usp_active,usp_dat_active,usp_lats_update)
                                 SELECT usp_dat_id,usp_pro_name,usp_alias,usp_barcode,usp_me_barcode,usp_unit,usp_unit_import,usp_specifi,". intval($branch_id).",". intval($admin_id) .",". intval($myuser->u_id) .",". intval($quantity) .",". doubleval($price_export) .",1,usp_dat_active,". time() . "
                                 FROM " . USER_PRODUCTS . " WHERE usp_id = " . intval($pro_id));
      if($id > 0){
         $db_pro  = new db_query("SELECT * FROM ". USER_PRODUCTS . " WHERE usp_id = " . intval($id) . " LIMIT 1");
         if($row  = mysqli_fetch_assoc($db_pro->result)){
            $array_return  = $row;
         }
         unset($db_pro);
      }
      unset($db_ex);
      
      return $array_return;
      
   }
   
   /**
    * Hàm thêm vào bảng stocks
    * $data = array(
    *    pro_id => 
    *    quantity =>  
    *    quantity_parent =>
    *    quantity_child =>
    *    price_import =>
    *    price_out =>
    *    save_import => 0 || 1
    * )
    */
   function import_add_to_stocks($data = array()){
      global $admin_id, $myuser, $branch_id, $array_unit,$setting_specifi;
      
      
      $arrayReturn     = array(
         'status' => 0,
         'error' => '',
         'oid' => 0,
         'html' => ''
      );
      
      if($admin_id <= 0 || $branch_id <= 0 || empty($myuser)){
         $arrayReturn['error'] = 'Thông tin không đúng';
         return $arrayReturn; 
      } 
      
      
      
      $pro_id        = isset($data['pro_id'])? intval($data['pro_id']) : 0;
      $quantity      = isset($data['quantity'])? intval($data['quantity']) : 1;
      $quantity_parent = isset($data['quantity_parent'])? intval($data['quantity_parent']) : $quantity;
      $quantity_child = isset($data['quantity_child'])? intval($data['quantity_child']) : 1;
      $price_import = isset($data['price_import'])? doubleval($data['price_import']) : 0;
      $price_export = isset($data['price_out'])? doubleval($data['price_out']) : 0;
      $save_import = isset($data['save_import'])? intval($data['save_import']) : 0;
      $pro_date_expires = isset($data['date_expires'])? intval($data['date_expires']) : 0;
      $status     = ($save_import == 1)? 1 : 0;
      
      //@file_put_contents('../logs/a.cfn', '1: ' . $quantity . ' ' . __LINE__ . "\n", FILE_APPEND);
      
      if($pro_id <= 0){
         $arrayReturn['error'] = 'Thông tin không đúng';
         return $arrayReturn; 
      }
            
      $db_products   = new db_query("  SELECT * FROM " . USER_PRODUCTS . "
                                       WHERE usp_id = " . intval($pro_id) . "
                                       AND usp_use_parent_id = " . intval($admin_id) . "
                                       AND usp_branch_id = " . intval($branch_id) . "
                                       LIMIT 1");
      if($row = mysqli_fetch_assoc($db_products->result)){
         // kiểm tra sản phẩm này có trong đơn hàng tạm chưa. chưa thì thêm mới không thì cộng thêm vào
         $db_order   = new db_query("SELECT * FROM " . USER_STOCK . "
                                     WHERE uss_branch_id = " . intval($branch_id) . " 
                                     AND uss_use_parent_id = " . intval($admin_id) . " 
                                     AND uss_use_child_id = " . intval($myuser->u_id) . "
                                     AND uss_pro_id = " . intval($pro_id) . "
                                     AND uss_status = 0 LIMIT 1");
         if($row_order  = mysqli_fetch_assoc($db_order->result)){
            // có rồi thì chỉ cập nhật lại số lượng và giá tiền bán
            $quantity_p      = $row_order['uss_quantity_unit_parent'];
            $quantity_c      = $row_order['uss_quantity_unit_child'];
            
            $quantity        = ($quantity_p + 1) * $quantity_c;
            
            $total_money   = ($quantity + 1) * $row['usp_price'];
                               
            $db_ex   = new db_execute("UPDATE " . USER_STOCK . "
                                       SET uss_quantity_unit_parent = uss_quantity_unit_parent + 1,
                                       uss_quantity = " . intval($quantity) . "
                                       WHERE uss_id = " . intval($row_order['uss_id']));
            if($db_ex->total > 0){
               $arrayReturn['status'] = 1;
               $arrayReturn['oid'] = $row_order['uss_id'];
            }else{
               $arrayReturn['error'] = 'Không cập nhật thêm số lượng được';         
            }
            unset($db_ex);
            //@file_put_contents('../logs/a.cfn', '2: ' . $quantity . ' ' . __LINE__ . "\n", FILE_APPEND);
         }else{
            //@file_put_contents('../logs/a.cfn', '3: ' . $quantity . ' ' . __LINE__ . "\n", FILE_APPEND);
            // nếu đúng của mình thì thêm vào bảng order            
            $price_export   = ($row['usp_price'] > 0) ? $row['usp_price'] : 0;
            //$price_import   = isset($data['price_import'])? $data['price_import'] : 0;
            
            // suggess giá nhập
            $db_import  = new db_query("SELECT * FROM " . USER_STOCK . "
                                        WHERE uss_branch_id = " . intval($branch_id) . " 
                                        AND uss_use_parent_id = " . intval($admin_id) . " 
                                        AND uss_pro_id = " . intval($pro_id) . "
                                        AND uss_status = 1 ORDER BY uss_id DESC LIMIT 1");
            if($rim  = mysqli_fetch_assoc($db_import->result)){
               $price_import  = ($rim['uss_price_import']>0)? $rim['uss_price_import'] : $price_import;
               $price_export  = ($rim['uss_price_out'] > 0)? $rim['uss_price_out'] : $price_export;
            }
            unset($db_import);
            if($setting_specifi==1)$row['usp_specifi'] = 1;
            $quantity   = $row['usp_specifi'] * $quantity_parent;
            $specifi    = $row['usp_specifi'];
            
            //@file_put_contents('../logs/a.cfn', '4: ' . $quantity . ' ' . __LINE__ . "\n", FILE_APPEND);
            // thêm vào bảng stocks
            $db_ex   = new db_execute_return();
            $oid = $db_ex->db_execute("INSERT INTO " . USER_STOCK . " 
                                          (uss_date,uss_date_expires,uss_use_parent_id,uss_use_child_id,uss_price_import,uss_price_out,uss_quantity,uss_unit,uss_pro_id,uss_dat_id,uss_branch_id,uss_status,uss_quantity_unit_parent,uss_quantity_unit_child)
                                       VALUES(". time() .",". intval($pro_date_expires) .",". intval($admin_id) .",". intval($myuser->u_id) .",". doubleval($price_import) .",". doubleval($price_export) ."," . intval($quantity) .",". intval($row['usp_unit']) .",". intval($row['usp_id']). ",". $row['usp_dat_id'] .",". intval($branch_id) .",". intval($status) .",". intval($quantity_parent) .",". intval($specifi) .")");
            
            if($oid > 0){
                              
               $arrayReturn['oid'] = $oid;
               $arrayReturn['status'] = 1;
               
               $unit = (isset($array_unit[$row['usp_unit']]) && $row['usp_unit'] > 0)? $array_unit[$row['usp_unit']] : 'đơn vị bán';
               $unit_import = (isset($array_unit[$row['usp_unit_import']]) && $row['usp_unit_import'] > 0)? $array_unit[$row['usp_unit_import']] : 'đơn vị nhập';
               $quycach = intval($row['usp_specifi']);
               $unit_name  = ($unit != '')? '<span class="item_unit">Đơn vị: '. $unit . ( ($unit_import != '' && $quycach > 1)? ' ('.$unit_import . ' ' . $quycach . ' ' . $unit.')' : '' ) .'</span>': '';
               
               $total_money   = $quantity * $price_import;
               $arrayReturn['html'] = '<tr class="item_order" id="item_order_'. $oid .'" data-oid="'. $oid .'">
                                             <td width="20" class="text_c"><span class="item_order_del" onclick="delete_item_order(this)">x</span></td>
                                             <td>
                                                <div class="stocks_name">
                                                   <span class="item_order_name">'. $row['usp_pro_name'] .'</span>
                                                   '. $unit_name . '
                                                </div>
                                             </td>                                             
                                             <td width="40" class="text_c bnone">
                                                <p><input autocomplete="off" maxlength="8" name="quantity_parent['. $oid .']" onblur="setUpdate(this); setTotalPrice();" title="Số lượng '. $unit_import .'" data-type="quantity_parent" class="tooltip number item_order_quantity quantity_parent type_number text_c" id="item_order_quantity_parent_'. $oid .'" type="tel" data-vl="'. $quantity_parent .'" value="'. $quantity_parent .'" /></p>
                                             </td>
                                             <td width="6" class="bnone">x</td>
                                             <td width="40" class="text_c bnone">
                                                <p><input autocomplete="off" maxlength="8" name="quantity_child['. $oid .']" onblur="addClass_text(this);  setUpdate(this); setTotalPrice();" title="Số lượng '. $unit .' / 1 '. $unit_import .'" data-type="quantity_child" onfocus="removeClass_text(this)" class="tooltip number item_order_quantity quantity_child type_number text_c '. (($specifi > 0)? 'text_text' : '') .'" id="item_order_quantity_child_'. $oid .'" type="tel" data-vl="1" value="'. format_number($specifi) .'" /></p>
                                             </td>
                                             <td width="6" class="bnone">=</td>
                                             <td class="text_r bnone" width="20">
                                                <b class="price" id="quantity_'. $oid .'">'. ($specifi * $quantity_parent) .'</b>
                                             </td>
                                             <td width="30" class="text_c"><i class="item_unit ww">('. $unit .')</i></td>
                                             
                                             <td width="70" class="text_r">
                                                <p><input autocomplete="off" maxlength="10" name="price_import['. $oid .']" onblur="setUpdate(this); setTotalPrice();" title="Giá nhập / 1 '. $unit .'" data-type="price_import" class="tooltip number item_order_price price_import type_number text_r" id="item_order_price_import_'. $oid .'" type="tel" data-vl="'. $price_import .'" data-update="'. (($price_import == 0)? 1 : 0) .'" value="'. format_number($price_import) .'" /></p>
                                             </td>
                                             <td width="70" class="text_r">
                                                <p><input autocomplete="off" maxlength="10" name="price_export['. $oid .']" onblur="setUpdate(this); setTotalPrice();" title="Giá bán / 1 '. $unit .'" data-type="price_export" class="tooltip number item_order_price price_out type_number text_r" id="item_order_price_export_'. $oid .'" type="tel" data-vl="'. $price_export .'" data-update="'. (($price_export == 0)? 1 : 0) .'" value="'. format_number($price_export) .'" /></p>
                                             </td>
                                             <td width="" class="text_r">
                                                <span class="ww price item_order_price_item" id="item_order_price_item_'. $oid .'">'. format_number($total_money) .'</span>
                                             </td>
                                             <td>
                                                <input onchange="setUpdate(this);" data-vl="'. (($pro_date_expires > 0)? date('d/m/Y', $pro_date_expires) : 0) .'" data-type="date_expires" id="add_date_ex_'. $oid .'" type="text" value="'. (($pro_date_expires > 0)? date('d/m/Y', $pro_date_expires) : '') .'" class="date_input add_date_ex" name="add_date_ex['. $oid .']" />
                                                <script>$("#add_date_ex_'. $oid .'").datepicker({dateFormat: "dd/mm/yy"});</script>
                                             </td>
                                          </tr>';
            }else{
               $arrayReturn['error'] = 'Không thêm được vào hóa đơn';
            }
            unset($db_ex);
         }
         unset($db_order); 
      }else{
         $arrayReturn['error'] = 'Bạn không có quyền với sản phẩm này';
      }
      
      return $arrayReturn;
   }
   
   
   /**
    * Hàm cập nhật thay đổi số lượng trong khi nhập hàng
    * param{
      array(
         'stocks_id' => id của bảng nhập kho 
         'value' => giá trị cần thay đổi
         'type' => tên trường cần cập nhật
         'update' => có thay đổi đến bảng danh sách thuốc không
      )
    }
    
    return 0 || 1
    */
   function import_update_to_stocks($data = array()){
      global $admin_id, $branch_id, $array_unit, $myuser;
      $child_id   = $myuser->u_id;
      
      $status  = 0;
      $stocks_id  = isset($data['stocks_id'])? intval($data['stocks_id']) : 0;
      $value      = isset($data['value'])? intval($data['value']) : 0;
      $type       = isset($data['type'])? $data['type'] : '';
      
      // nếu 1 trong 3 tham số trên không thỏa mãn thì return 0
      if($stocks_id <= 0 || $value <= 0 || $type == '') return $status;
      
      // kiểm tra trong stocks có phải của mình không, không phải thì báo không có quyền
      $db_check   = new db_query("SELECT * FROM ". USER_STOCK . " 
                                  WHERE uss_id = " . intval($stocks_id) . "
                                  LIMIT 1");
      if($row  = mysqli_fetch_assoc($db_check->result)){
         if($row['uss_use_parent_id'] != $admin_id || $row['uss_use_child_id'] != $child_id || $row['uss_branch_id'] != $branch_id){
            return 0;
         }
         
         // đã check là của mình thì bắt đầu cập nhật
         $field_update  = '';
         switch($type){
            case 'quantity_parent':
               $toal_quantity = $row['uss_quantity_unit_child'] * $value;
               $field_update = " uss_quantity =" . $toal_quantity .",uss_quantity_unit_parent=". $value;
               break;
            case 'quantity_child':
               $toal_quantity = $row['uss_quantity_unit_parent'] * $value;
               $field_update = " uss_quantity =" . $toal_quantity .",uss_quantity_unit_child=". $value;
               break;
            case 'price_import':
               $field_update = " uss_price_import =" . doubleval($value);
               break;
            case 'price_export':
               $field_update = " uss_price_out =" . doubleval($value);
               break;
            case 'date_expires':
               $field_update = " uss_date_expires =" . intval($value);
               break;
         }
         
         // cập nhật thay đổi
         $db_ex   = new db_execute("UPDATE " . USER_STOCK . " SET " . $field_update . " WHERE uss_id = " . intval($stocks_id));
         unset($db_ex);
         $status = 1;
      }
      unset($db_check);
      
      return $status;
   }
   
   /**
    * Hàm cập nhật trạng thái thành công của đơn nhập hàng
    * param
    * array(
    *    'stocks_id' => id của stock
    *    'quantity_parent' => 
    *    'quantity_child' =>
    *    'price_import' => 
    *    'price_export' => 
    * )
    * return 0 || 1
    */
   function import_save_stocks($data = array()){
      global $admin_id, $branch_id, $myuser;
      $child_id   = $myuser->u_id;
      $status  = 0;
      
      $stocks_id  = isset($data['stocks_id'])? intval($data['stocks_id']) : 0;
      $quantity_parent  = isset($data['quantity_parent'])? intval($data['quantity_parent']) : 0;
      $quantity_child  = isset($data['quantity_child'])? intval($data['quantity_child']) : 0;
      $price_import  = isset($data['price_import'])? doubleval($data['price_import']) : 0;
      $price_export  = isset($data['price_export'])? doubleval($data['price_export']) : 0;
      $date_expires  = isset($data['date_expires'])? intval($data['date_expires']) : 0;
      $date_create   = isset($data['date_create'])? intval($data['date_create']) : date('d/m/Y');
      $bill_code   = isset($data['bill_code'])? intval($data['bill_code']) : 0;
      
      if($stocks_id <= 0 || $quantity_parent <= 0 || $quantity_child <= 0 || $price_import < 0 || $price_export <= 0) return 0;
      
      // kiểm tra trong stocks có phải của mình không, không phải thì báo không có quyền
      $db_check   = new db_query("SELECT * FROM ". USER_STOCK . " 
                                  WHERE uss_id = " . intval($stocks_id) . "
                                  LIMIT 1");
      if($row  = mysqli_fetch_assoc($db_check->result)){
         if($row['uss_use_parent_id'] != $admin_id || $row['uss_branch_id'] != $branch_id){
            return 0;
         }
         
         // mặc định giá vốn bằng giá nhập
         $toal_quantity = $quantity_parent * $quantity_child;
         $cogs       = $price_import;
         $total_sold = $toal_quantity;
         
         // lấy bản ghi cuối cùng của lần nhập sản phẩm này để lấy giá nhập rồi tính giá bình quân
         $arrayStock_last  = array();
         
         $check_update_record_last  = 0;
         $last_id = 0;
         $pro_id  = 0;
         $old_quanlity  = 0;
         $db_last = new db_query("SELECT * FROM " . USER_STOCK . "
                                  WHERE uss_use_parent_id =" . intval($admin_id) . "
                                  AND uss_branch_id = " . intval($branch_id) . "
                                  AND uss_pro_id = " . intval($row['uss_pro_id']) . "
                                  AND uss_status = 1
                                  ORDER BY uss_id DESC
                                  LIMIT 1");
         if($arrayStock_last  = mysqli_fetch_assoc($db_last->result)){
            $cogs = format_number(((($arrayStock_last['uss_cogs'] * $arrayStock_last['uss_new_sold']) + ($toal_quantity * $price_import)) / ( $arrayStock_last['uss_new_sold'] + $toal_quantity )), 2);
            $total_sold = $arrayStock_last['uss_new_sold'] + $toal_quantity;
            $pro_id  = $arrayStock_last['uss_pro_id'];
            $old_quanlity  = $arrayStock_last['uss_quantity'];
            if($arrayStock_last['uss_quantity'] == 0){
               $check_update_record_last = 1;
               $last_id = $arrayStock_last['uss_id'];
            }
         }
         unset($db_last);
         
         if($check_update_record_last == 1){
            $db_ex   = new db_execute("UPDATE " . USER_STOCK . " SET 
                                       uss_quantity = ". intval($toal_quantity) .",
                                       uss_quantity_unit_parent = ". intval($quantity_parent) . ",
                                       uss_quantity_unit_child = ". intval($quantity_child) . ",
                                       uss_price_import = ". doubleval($price_import) . ",
                                       uss_price_out = ". doubleval($price_export) .",
                                       uss_cogs = ". doubleval($cogs) .",
                                       uss_new_sold = ". intval($total_sold) .",
                                       uss_sold = ". intval($total_sold) .",
                                       uss_date_expires = ". intval($date_expires) .",
                                       uss_date = ". $date_create .",
                                       uss_status = 1,
                                       uss_bill_code = ". $bill_code ."
                                       WHERE uss_id = " . intval($last_id));                                    
            unset($db_ex);
            
            $data_action  = array(
               'file' => base64_url_encode(json_encode(debug_backtrace())),
               'action' => 'edit',
               'des' => 'Sử dụng import excel cộng dồn kho thành số lượng mới từ:<b>'. $old_quanlity .'</b> Thành <b>'. $toal_quantity .'</b>',
               'pro_id' => $pro_id
            );            
            log_action($data_action);
            
            // xóa bản ghi hiện tại
            $db_del  = new db_query("DELETE FROM " . USER_STOCK . " WHERE uss_id = " . intval($stocks_id));
            unset($db_del);
            
            // cập nhật giá vốn của bảng bán
            $db_up   = new db_execute("UPDATE " . USER_SALE . " SET uso_cogs = " . doubleval($cogs) . "
                                       WHERE uso_uss_id = " . intval($last_id));
            unset($db_up);
            
         }else{
            // đúng là của mình thì được cập nhật         
            $db_ex   = new db_execute("UPDATE " . USER_STOCK . " SET 
                                       uss_quantity = ". intval($toal_quantity) .",
                                       uss_quantity_unit_parent = ". intval($quantity_parent) . ",
                                       uss_quantity_unit_child = ". intval($quantity_child) . ",
                                       uss_price_import = ". doubleval($price_import) . ",
                                       uss_price_out = ". doubleval($price_export) .",
                                       uss_cogs = ". doubleval($cogs) .",
                                       uss_new_sold = ". intval($total_sold) .",
                                       uss_sold = ". intval($total_sold) .",
                                       uss_date_expires = ". intval($date_expires) .",
                                       uss_date = ". $date_create .",
                                       uss_status = 1,
                                       uss_bill_code = ". $bill_code ."
                                       WHERE uss_id = " . intval($stocks_id));                                    
            unset($db_ex);
            
            $data_action  = array(
               'file' => base64_url_encode(json_encode(debug_backtrace())),
               'action' => 'edit',
               'des' => 'Sử dụng import excel cộng dồn kho thành số lượng mới từ:<b>'. $old_quanlity .'</b> Thành <b>'. $toal_quantity .'</b>',
               'pro_id' => $pro_id
            );            
            log_action($data_action);
         }
         
         
         
         // cập nhật giá bán mới nhất cho bảng product
         $db_pro     = new db_execute("UPDATE " . USER_PRODUCTS . "
                                       SET usp_price = " . doubleval($price_export) . ",
                                       usp_price_import = ". doubleval($price_import) . ",
                                       usp_quantity = usp_quantity + " . intval($toal_quantity) .",
                                       usp_active = 1,
                                       usp_cogs = ". doubleval($cogs) . "
                                       WHERE usp_id = " . intval($row['uss_pro_id']));
         unset($db_pro);
         $status = 1;
         
      }
      unset($db_check);
      
      return $status;
   }
   
   /**
    * Hàm thêm mới sản phẩm => thêm luôn vào bảng datas
    * param{
      'pro_name' =>
      'pro_unit' =>
      'pro_specifi' =>
      'pro_unit_import' =>
      'pro_barcode' =>
      'pro_price' =>      
    }
    */
   function import_add_new_product($data = array()){
      global $admin_id, $branch_id, $myuser, $notification, $setting_specifi;
      
      $pro_id  = 0;
      
      $pro_name   = isset($data['pro_name'])? convert_utf82utf8(trim($data['pro_name'])) : '';
      $pro_unit   = isset($data['pro_unit'])? intval($data['pro_unit']) : 0;
      $pro_specifi   = isset($data['pro_specifi'])? intval($data['pro_specifi']) : 1;
      if($setting_specifi==1)$pro_specifi = 1;
      $pro_unit_import   = isset($data['pro_unit_import'])? intval($data['pro_unit_import']) : 0;
      $pro_barcode   = isset($data['pro_barcode'])? $data['pro_barcode'] : '';
      $pro_price   = isset($data['pro_price'])? doubleval(parse_type_number($data['pro_price'])) : 0;
      $pro_price_import   = isset($data['pro_price_import'])? doubleval(parse_type_number($data['pro_price_import'])) : 0;
      $pro_box_id    = isset($data['pro_box_id'])? intval($data['pro_box_id']) : 0;
      
      if($pro_name == '') return 0;
      
      // kiểm tra trong bảng data có dữ lieuj chưa
      $dat_id        = 0;
      $name_accent   = convert_utf82utf8($pro_name, 1);
      $db_check   = new db_query("SELECT * FROM datas 
                                  WHERE dat_name_accent = '". replaceMQ($name_accent) ."' LIMIT 1");
      if($rdat    = $db_check->fetch()){
         $dat_id  = $rdat['dat_id'];
      }else{
         $db_datas   = new db_execute_return();
         $dat_id     = $db_datas->db_execute("INSERT INTO datas (dat_name,dat_name_accent,dat_barcode,dat_unit,dat_unit_import,dat_specifi, dat_use_parent_id, dat_price_out)
                                              VALUES('". replaceMQ($pro_name) ."','". replaceMQ($name_accent) ."','". replaceMQ($pro_barcode) ."',". intval($pro_unit) .",". intval($pro_unit_import) .",". intval($pro_specifi) .",". intval($admin_id) .",". doubleval($pro_price) .")");
         unset($db_datas);
      }
      unset($db_check);
      
      
      if($dat_id > 0){
         // tạo barcode
         $mecode  = 100000000000 + $dat_id;
         $me_barcode = generate_me_barcode($mecode);
         
         $db_update  = new db_execute("UPDATE datas SET dat_me_barcode = '". $me_barcode ."' WHERE dat_id =" . intval($dat_id));
         unset($db_update);
         
         $pro_id  = $this->import_data_to_product(array('pro_name' => $pro_name,'dat_id' => $dat_id, 'pro_price_import' => $pro_price_import, 'pro_unit_import' => $pro_unit_import, 'pro_unit' => $pro_unit, 'pro_specifi' => $pro_specifi, 'pro_price' => $pro_price, 'pro_box_id' => $pro_box_id));
         $notification->setNewProductInDay();
         
         if($pro_id > 0) return $pro_id;
         
      }
      
      return $pro_id;
   }
   
   /**
    * Hàm sửa thông tin nhập kho
    * param(
         'stocks_id' => 
         'quantity_parent' =>
         'quantity_child' =>
         'price_import' =>
         'price_export' =>      
    )
    
    return 1 || 0
    */
   function import_repair_stocks($data = array()){
      global $admin_id, $branch_id, $myuser;
      $child_id     = $myuser->u_id;
      
      $status  = 0;
      
      $stocks_id  = isset($data['stocks_id'])? intval($data['stocks_id']) : 0;
      $quantity_parent  = isset($data['quantity_parent'])? intval(parse_type_number($data['quantity_parent'])) : 0;
      $quantity_child  = isset($data['quantity_child'])? intval(parse_type_number($data['quantity_child'])) : 0;
      $price_import  = isset($data['price_import'])? doubleval(parse_type_number($data['price_import'])) : 0;
      $price_export  = isset($data['price_export'])? doubleval(parse_type_number($data['price_export'])) : 0;
      $date_expires  = isset($data['date_expires'])? intval($data['date_expires']) : 0;
      $date_create  = isset($data['date_create'])? intval($data['date_create']) : 0;
      
      if($stocks_id <= 0) return $status;
      
      $des_logs = '<b>(Sửa nhập kho)</b>';
      
      // kiểm tra trong stocks có phải của mình không, không phải thì báo không có quyền
      $db_check   = new db_query("SELECT * FROM ". USER_STOCK . " 
                                  WHERE uss_id = " . intval($stocks_id) . "
                                  LIMIT 1");
      if($row  = mysqli_fetch_assoc($db_check->result)){
         if($row['uss_use_parent_id'] != $admin_id || $row['uss_branch_id'] != $branch_id){
            return 0;
         }
         
         
         
         // đã là của mình
         // tổng số lượng mới
         $total_quantity   = $quantity_parent * $quantity_child;
         $quantity_pro  = $total_quantity - $row['uss_quantity']; // số lương chênh lệch khi cũ và mới
         
         if($row['uss_quantity'] != $total_quantity || $row['uss_price_out'] != $price_export){
            //update product
            $db_pro  = new db_execute("UPDATE " . USER_PRODUCTS . " SET 
                                       usp_quantity = usp_quantity + " . intval($quantity_pro) .",
                                       usp_price = " . doubleval($price_export) . "
                                       WHERE usp_id = " . intval($row['uss_pro_id']));
            unset($db_pro);
         }
         
         //if($row['uss_quantity'] != $total_quantity || $row['uss_price_out'] != $price_export || $row['uss_quantity_unit_parent'] != $quantity_parent || $row['uss_quantity_unit_child'] != $quantity_child || $row['uss_price_import'] != $price_import){
         // cập nhật kho hàng
         $cogs = $price_import;
         $sold = $total_quantity;
         if($price_import == 0 && $total_quantity == 0){
            $cogs = 0;
            $sold = $row['uss_new_sold'] - $row['uss_quantity'];
         }
         
         $str_date_create  = '';
         if($date_create > 0) $str_date_create = " uss_date = " . $date_create . ",";
         $sql  = "UPDATE " . USER_STOCK ." SET 
                                       uss_quantity = " . intval($total_quantity) . ",
                                       uss_price_out = " . doubleval($price_export) . ",
                                       uss_price_import = " . doubleval($price_import) . ",
                                       uss_quantity_unit_parent = " . intval($quantity_parent) . ",
                                       uss_quantity_unit_child = " . intval($quantity_child) .",
                                       uss_cogs = ". doubleval($cogs) .",
                                       uss_sold = ". intval($sold) .",
                                       uss_new_sold = ". intval($sold) .",". $str_date_create . "
                                       uss_date_expires = ". intval($date_expires) . "
                                       WHERE uss_id = " . intval($stocks_id);
         //file_put_contents('../logs/a.txt', $sql);
         $db_stocks = new db_execute($sql);
         $param   = array(
            'stocks_id' => $stocks_id,
            'pro_id' => $row['uss_pro_id']
         );
         
         
         
         // ghi log hành động
         if($row['uss_quantity'] != $total_quantity){
            $des_logs .= "\n sửa tổng số lượng từ: <b>". $row['uss_quantity'] . "</b> sang <b>". $total_quantity ."</b>";
         }
         if($row['uss_quantity_unit_parent'] != $quantity_parent){
            $des_logs .= "\n sửa số lượng nhập từ: <b>". $row['uss_quantity_unit_parent'] . "</b> sang <b>". $quantity_parent ."</b>";
         }
         if($row['uss_quantity_unit_child'] != $quantity_child){
            $des_logs .= "\n sửa quy cách từ: <b>". $row['uss_quantity_unit_child'] . "</b> sang <b>". $quantity_child ."</b>";
         }
         if($row['uss_price_import'] != $price_import){
            $des_logs .= "\n sửa giá nhập từ: <b>". format_number($row['uss_price_import']) . "</b> sang <b>". format_number($price_import) ."</b>";
         }
         if($row['uss_price_out'] != $price_export){
            $des_logs .= "\n sửa giá bán từ: <b>". format_number($row['uss_price_out']) . "</b> sang <b>". format_number($price_export) ."</b>";
         }
         
         $data_action  = array(
            'file' => base64_url_encode(json_encode(debug_backtrace())),
            'action' => 'edit',
            'des' => $des_logs,
            'pro_id' => $row['uss_pro_id']
         );            
         log_action($data_action);
         
         // dù không có thay đổi gì nhưng cứ click sửa là tính lại giá vốn
         $this->import_recount_cogs($param);           
         
         $status = 1; 
      }
      unset($db_check);
      
      return $status;
   }
   
   /**
    * Hàm xóa lịch sử nhập kho
    * param{
       'stocks_id' =>
    }
    return 1 || 0
    */
   function import_delete_stocks($data = array()){
      global $admin_id, $branch_id, $myuser;
      
      $arrayReturn   = array(
         'status' => 0,
         'error' => ''
      );
      
      $stocks_id  = isset($data['stocks_id'])? intval($data['stocks_id']) : 0;
      
      if($stocks_id <= 0){
         $arrayReturn['error'] = 'Thông tin nhập kho không đúng';
      }
      
      // check
      $db_check   = new db_query("SELECT * FROM " . USER_STOCK . "
                                  WHERE uss_id = " . intval($stocks_id) . " LIMIT 1");
      if($row  = mysqli_fetch_assoc($db_check->result)){
         
         $quantity   = $row['uss_quantity'];
         $pro_id     = $row['uss_pro_id'];
         if($row['uss_use_parent_id'] ==  $admin_id && $row['uss_branch_id'] == $branch_id && ($row['uss_use_child_id'] == $myuser->u_id || $myuser->u_parent_id == $admin_id)){
            $db_ex   = new db_execute("DELETE FROM " . USER_STOCK . " 
                                       WHERE uss_id = " . intval($stocks_id));
            unset($db_ex);
            
            //cập nhật số lượng
            $db_update  = new db_execute("UPDATE ". USER_PRODUCTS . "
                                          SET usp_quantity = usp_quantity - " . intval($quantity) . " 
                                          WHERE usp_id = " . intval($pro_id));
            unset($db_update);
            
            $arrayReturn['status'] = 1;
            
            $des_logs   = "\n Xóa bản ghi nhập hàng có số lượng nhập: <b>" . $row['uss_quantity'] . "</b>, Giá nhập: <b>". format_number($row['uss_price_import']) ."</b>"; 
            // ghi log hành động
            $data_action  = array(
               'file' => json_encode(debug_backtrace()),
               'action' => 'edit',
               'des' => $des_logs,
               'pro_id' => $pro_id
            );
            
            log_action($data_action);
            
         }else{
            $arrayReturn['error'] = 'Bạn không có quyền xóa thông tin nhập kho này';
         }
      }else{
         $arrayReturn['error'] = 'Thông tin nhập kho không tồn tại';
      }
      unset($db_check);
      
      
      
      return $arrayReturn;
   }
   
   /**
    * Hàm chuyển kho hàng
    * param(
    *    'pro_id' =>
    * )
    * return array
    */
   function move_stocks($data = array()){
      global $admin_id, $branch_id, $myuser,$pathRoot, $array_unit, $setting_specifi;
      
      $arrayReturn   = array(
         'status' => 0,
         'error' => ''
      );
      
      $pro_id  = isset($data['pro_id'])? intval($data['pro_id']) : 0;
      if($pro_id <= 0){
         $arrayReturn['error'] = 'Không có thuốc chuyển kho';
         return $arrayReturn;
      }
      
      // check quyền user có đc chuyển hàng không
      if(!$myuser->checkRight(USER_RIGHT_STOCKS_MOVE)){
         $arrayReturn['error'] = 'Bạn không có quyền tạo chuyển kho thuốc';
         return $arrayReturn;
      }
      
      // kiểm tra thuốc còn hàng không
      $db_pro  = new db_query("SELECT * FROM " . USER_PRODUCTS . "
                               WHERE usp_id = " . intval($pro_id) . " AND usp_use_parent_id = ". intval($admin_id) ."
                               LIMIT 1");
      if($row  = mysqli_fetch_assoc($db_pro->result)){
         
         if($setting_specifi==1)$row['usp_specifi']=1;
         
         // kiểm tra trong bảng có chưa có thềm cộng thêm, chưa thì thêm mới
         $db_check = new db_query("SELECT * FROM temp_stocks_move 
                                   WHERE tsm_use_parent_id = " . intval($admin_id) . "
                                   AND tsm_branch_from = " . intval($branch_id) . "
                                   AND tsm_pro_id = " . intval($pro_id) . "
                                   AND tsm_status =" . MOVE_STOCKS_ZERO . "
                                   LIMIT 1");
         if($rcheck  = mysqli_fetch_assoc($db_check->result)){
            $db_update  = new db_execute("UPDATE temp_stocks_move SET
                                          tsm_quantity_parent = tsm_quantity_parent + 1,
                                          tsm_quantity = tsm_quantity_parent * tsm_quantity_child
                                          WHERE tsm_id = " . $rcheck['tsm_id']);
            if($db_update->total > 0){
               $arrayReturn['status'] = 1;
               $arrayReturn['oid'] = $rcheck['tsm_id'];
               $arrayReturn['url'] = URL_WEB . $pathRoot . 'move_product.php';
            }
            unset($db_update);
            
         }else{
            // thêm mới vào bảng temp_stocks
            $db_insert  = new db_execute_return();         
            $oid = $db_insert->db_execute("
               INSERT INTO temp_stocks_move (tsm_use_parent_id,tsm_branch_from,tsm_pro_id,tsm_quantity_parent,tsm_quantity_child,tsm_quantity,tsm_date,tsm_use_move)
               SELECT ". intval($admin_id) .",". intval($branch_id) .",". intval($pro_id) .",1,".intval($row['usp_specifi']). ",". intval($row['usp_specifi']). ",". time() .",". intval($myuser->u_id) ." FROM ". USER_PRODUCTS ." WHERE usp_id = " . intval($pro_id));
            unset($db_insert);
            
            if($oid > 0){
               $arrayReturn['status'] = 1;
               $arrayReturn['oid'] = $oid;
               $arrayReturn['url'] = URL_WEB . $pathRoot . 'move_product.php?st=move';
               
               $unit = (isset($array_unit[$row['usp_unit']]) && $row['usp_unit'] > 0)? $array_unit[$row['usp_unit']] : 'đơn vị bán';
               $unit_import = (isset($array_unit[$row['usp_unit_import']]) && $row['usp_unit_import'] > 0)? $array_unit[$row['usp_unit_import']] : 'đơn vị nhập';
               $quycach = intval($row['usp_specifi']);
               $unit_name  = ($unit != '')? '<span class="item_unit">Đơn vị: '. $unit . ( ($unit_import != '' && $quycach > 1)? ' ('.$unit_import . ' ' . $quycach . ' ' . $unit.')' : '' ) .'</span>': '';
               $specifi    = intval($row['usp_specifi']);
               $quantity   = $row['usp_specifi'] * 1;
                              
               $arrayReturn['html'] = '<tr class="item_order" id="item_order_'. $oid .'" data-oid="'. $oid .'">
                                             <td width="20" class="text_c"><span class="item_order_del" onclick="delete_item_order(this)">x</span></td>
                                             <td>
                                                <div class="stocks_name">
                                                   <span class="item_order_name">'. $row['usp_pro_name'] .'</span>
                                                   '. $unit_name . '
                                                </div>
                                             </td>
                                             <td class="text_c">'. $row['usp_quantity'] .'</td>
                                             <td width="40" class="text_c bnone">
                                                <p><input autocomplete="off" maxlength="8" name="quantity_parent['. $oid .']" onblur="setUpdate(this); setTotalPrice();" title="Số lượng '. $unit_import .'" data-type="quantity_parent" class="tooltip number item_order_quantity quantity_parent type_number text_c" id="item_order_quantity_parent_'. $oid .'" type="tel" data-vl="1" value="1" /></p>
                                             </td>
                                             <td width="6" class="bnone">x</td>
                                             <td width="40" class="text_c bnone">
                                                <p><input autocomplete="off" maxlength="8" name="quantity_child['. $oid .']" onblur="addClass_text(this);  setUpdate(this); setTotalPrice();" title="Số lượng '. $unit .' / 1 '. $unit_import .'" data-type="quantity_child" onfocus="removeClass_text(this)" class="tooltip number item_order_quantity quantity_child type_number text_c '. (($specifi > 0)? 'text_text' : '') .'" id="item_order_quantity_child_'. $oid .'" type="tel" data-vl="1" value="'. format_number($specifi) .'" /></p>
                                             </td>
                                             <td width="6" class="bnone">=</td>
                                             <td class="text_r bnone" width="20">
                                                <b class="price" id="quantity_'. $oid .'">'. $quantity .'</b>
                                             </td>
                                             <td width="30" class="text_c"><i class="item_unit ww">('. $unit .')</i></td>
                                          </tr>';
            }
         }
         unset($db_check);
            
         
         return $arrayReturn;
      }else{
         $arrayReturn['error'] = 'Thuốc không tồn tại ở chi nhánh hiện tại';
         return $arrayReturn;
      }
      unset($db_pro);
      
   }
   
   /**
    * Hàm ghi nhận trạng thái thành công của chuyển hàng
    * param
    * array(
    *    'tsm_id' => id của bảng chuyển hang
    *    'quantity_parent' => 
    *    'quantity_child' =>
    *    'branch_to' =>
    * )
    * return 0 || 1
    */
   function move_save($data = array()){
      global $admin_id, $branch_id, $myuser;
      $child_id   = $myuser->u_id;
      $status  = 0;
      
      $tsm_id  = isset($data['tsm_id'])? intval($data['tsm_id']) : 0;
      $quantity_parent  = isset($data['quantity_parent'])? intval($data['quantity_parent']) : 0;
      $quantity_child  = isset($data['quantity_child'])? intval($data['quantity_child']) : 0;
      $branch_to        = isset($data['branch_to'])? intval($data['branch_to']) : 0;
      
      if($tsm_id <= 0 || $quantity_parent <= 0 || $quantity_child <= 0) return 0;
      
      // kiểm tra trong stocks có phải của mình không, không phải thì báo không có quyền
      $db_check   = new db_query("SELECT * FROM temp_stocks_move
                                  WHERE tsm_id = " . intval($tsm_id) . "
                                  LIMIT 1");
      if($row  = mysqli_fetch_assoc($db_check->result)){
         if($row['tsm_use_parent_id'] != $admin_id || $row['tsm_branch_from'] != $branch_id){
            return 0;
         }
         
         // mặc định giá vốn bằng giá nhập
         $toal_quantity = $quantity_parent * $quantity_child;
                  
         // đúng là của mình thì được cập nhật         
         $db_ex   = new db_execute("UPDATE temp_stocks_move SET 
                                    tsm_quantity = ". intval($toal_quantity) .",
                                    tsm_quantity_parent = ". intval($quantity_parent) . ",
                                    tsm_quantity_child = ". intval($quantity_child) . ",
                                    tsm_status = 1,
                                    tsm_date = ". time() .",
                                    tsm_branch_to = ". intval($branch_to) ."
                                    WHERE tsm_id = " . intval($tsm_id));
         unset($db_ex);
         
         $status = 1;
         
      }
      unset($db_check);
      
      return $status;
   }
   
   /**
    * Hàm cập nhật thay đổi số lượng trong khi chuyển kho hàng
    * param{
      array(
         'tsm_id' => id của bảng nhập kho 
         'value' => giá trị cần thay đổi
         'type' => tên trường cần cập nhật
      )
    }
    
    return 0 || 1
    */
   function move_update_stocks($data = array()){
      global $admin_id, $branch_id, $array_unit, $myuser;
      $child_id   = $myuser->u_id;
      
      $status  = 0;
      $tsm_id  = isset($data['tsm_id'])? intval($data['tsm_id']) : 0;
      $value      = isset($data['value'])? intval($data['value']) : 0;
      $type       = isset($data['type'])? $data['type'] : '';
      
      //$this->log($tsm_id . $value . $type);
      // nếu 1 trong 3 tham số trên không thỏa mãn thì return 0
      if($tsm_id <= 0 || $value <= 0 || $type == '') return $status;
      
      // kiểm tra trong stocks có phải của mình không, không phải thì báo không có quyền
      $db_check   = new db_query("SELECT * FROM temp_stocks_move 
                                  WHERE tsm_id = " . intval($tsm_id) . "
                                  LIMIT 1");
      if($row  = mysqli_fetch_assoc($db_check->result)){
         if($row['tsm_branch_from'] != $branch_id || $row['tsm_use_parent_id'] != $admin_id){
            return 0;
         }
         
         // đã check là của mình thì bắt đầu cập nhật
         $field_update  = '';
         switch($type){
            case 'quantity_parent':
               $toal_quantity = $row['tsm_quantity_child'] * $value;
               $field_update = " tsm_quantity =" . $toal_quantity .",tsm_quantity_parent=". $value;
               break;
            case 'quantity_child':
               $toal_quantity = $row['tsm_quantity_parent'] * $value;
               $field_update = " tsm_quantity =" . $toal_quantity .",tsm_quantity_child=". $value;
               break;
         }
         
         // cập nhật thay đổi
         $db_ex   = new db_execute("UPDATE temp_stocks_move SET " . $field_update . " WHERE tsm_id = " . intval($tsm_id));
         unset($db_ex);
         $status = 1;
      }
      unset($db_check);
      
      return $status;
   }
   
   /**
    * Xóa chuyển kho
    */
   function move_delete_record($data = array()){
      global $admin_id, $branch_id, $myuser;
      
      $arrayReturn   = array(
         'status' => 0,
         'error' => ''
      );
      
      $tsm_id  = isset($data['tsm_id'])? intval($data['tsm_id']) : 0;      
      if($tsm_id <= 0) $arrayReturn['error'] = 'Thông tin không đúng';
      
      // check xem có phải mình tạo ra không
      $db_check   = new db_query("SELECT * FROM temp_stocks_move 
                                   WHERE tsm_use_parent_id = " . intval($admin_id) . "
                                   AND tsm_branch_from = " . intval($branch_id) . "
                                   AND tsm_id = " . intval($tsm_id) . "
                                   LIMIT 1");
      if($row  = mysqli_fetch_assoc($db_check->result)){
         
         if($row['tsm_status'] == MOVE_STOCKS_ACCESS){
            $arrayReturn['error'] = 'Hàng đã được chuyển bạn không được xóa';
            return $arrayReturn;
         }
         // xóa
         $db_del  = new db_execute("DELETE FROM temp_stocks_move WHERE tsm_id = " . intval($tsm_id));
         if($db_del->total > 0){
            $arrayReturn['status'] = 1;
         }
         unset($db_del);
      }else{
         $arrayReturn['error'] = 'Bạn không có quyển xóa bản ghi này';
      }
      unset($db_check);
      
      return $arrayReturn;
   }
   
   
   /**
    * Hàm xác nhận chuyển hàng thành công
    * param(
    *    'tsm_id' =>
    *    'pro_id' => 0: tạo mới sản phẩm, > 0 thêm trực tiếp vào thuốc được chọn
    * )
    * 
    * return array(
    *    'status' =>
    *    'error' =>
    * )
    */
   function move_access($data = array()){
      global $admin_id, $branch_id, $myuser, $arrayBranch, $class_import_export;
      
      $tsm_id  = isset($data['tsm_id'])? $data['tsm_id'] : 0;
      $chose_pro_id  = isset($data['pro_id'])? $data['pro_id'] : 0;
      
      $arrayReturn   = array(
         'status' => 0,
         'error' => ''
      );
      
      if($tsm_id <= 0){
         $arrayReturn['error'] = 'Thông tin không đúng';
         return $arrayReturn; 
      } 
      
      // thông tin sản phẩm ở bên nhận hàng
      $array_pro  = array();
      
      /* tiến hành cập nhật trạng thái nhận hàng
         
         b1: trừ kho của bên chuyển đi
         b2: nhập hàng cho kho được nhận
      */
      
      $db_tsm  = new db_query("SELECT * FROM temp_stocks_move
                               WHERE tsm_use_parent_id = " . intval($admin_id) . "
                               AND tsm_branch_to = " . intval($branch_id) . "
                               AND tsm_status = ". MOVE_STOCKS_MOVING ." 
                               AND tsm_id = ". intval($tsm_id) ." LIMIT 1");
      if($row  = $db_tsm->fetch()){
         
         $quantity      = $row['tsm_quantity'];
         $branch_from   = $row['tsm_branch_from'];
         $pro_id        = $row['tsm_pro_id'];
         $branch_to     = $row['tsm_branch_to'];
         $branch_to_name   = isset($arrayBranch[$branch_to])? $arrayBranch[$branch_to]['usb_name'] : '';
         $branch_from_name = isset($arrayBranch[$branch_from])? $arrayBranch[$branch_from]['usb_name'] : '';
         $branch_from_id      = intval($row['tsm_branch_from']);
         $branch_to_id        = intval($row['tsm_branch_to']);
         $quantity_parent  =  $row['tsm_quantity_parent'];
         $quantity_child  =  $row['tsm_quantity_child'];
         
         //@file_put_contents('../logs/move.txt', "\n" . ' 1: Select ok',FILE_APPEND);
         // lấy thông tin nhập hàng lần cối cùng của bên chuyển hàng để thêm vào bên nhập hàng
         $price_import  = 0;
         $price_export  = 0;
         $unit = 0;
         $db_stock      = new db_query("SELECT * FROM " . USER_STOCK . "
                                        WHERE uss_pro_id = " . intval($row['tsm_pro_id']) . " AND uss_use_parent_id = " . intval($admin_id) . "
                                        AND uss_branch_id = " . $branch_from_id . " AND uss_price_import > 0 AND uss_quantity > 0 
                                        ORDER BY uss_id DESC LIMIT 1");
         if($rstock  = $db_stock->fetch()){
            $price_import = intval($rstock['uss_price_import']);
            $price_export = intval($rstock['uss_price_out']);
            $unit         = intval($rstock['uss_unit']);
         }
         unset($db_stock);
         
         
         /*
            B1: cập nhật cho bên nhận xong
            B2: Trừ bên chuyển
         */
         
         // B1:
         // thêm vào bảng product nếu chưa có (có rồi thì thêm số lượng vào)
         // thêm mới nhập kho và recount lại giá vốn
         
         $success = 0;
         
         // kiểm tra xem có thuốc này không
         $db_pro = new db_query("SELECT * FROM " . USER_PRODUCTS . "
                                 WHERE usp_id = " . intval($pro_id) . " LIMIT 1");
         if($rpro = mysqli_fetch_assoc($db_pro->result)){
            
            // nếu chose_pro_id > 0 thì đích thị thuốc đó, = 0 thì tạo mới
            if($chose_pro_id > 0){
               
               // kiểm tra thuốc có tồn tại không, có thì ok
               $db_check_pro  = new db_query("SELECT * FROM " . USER_PRODUCTS . " 
                                              WHERE usp_id = " . intval($chose_pro_id) . "
                                              AND usp_use_parent_id = " . intval($admin_id) . "
                                              AND usp_branch_id = " . intval($branch_id) . " 
                                              AND usp_active < 2
                                              LIMIT 1");
               if($rcheck  = $db_check_pro->fetch()){
                  $array_pro  = $rcheck;
                  // nếu trạng thái là xóa thì khôi phục lại trươc khi update
                  if($rcheck['usp_active'] == 0){
                     $import_last   = $class_import_export->import_get_last_stocks(array('pro_id' => $chose_pro_id, 'quantity' => 1));
                  
                     $pr_im   = isset($import_last['uss_price_import'])? $import_last['uss_price_import'] : 0;
                     $pr_ex   = isset($import_last['uss_price_out'])? $import_last['uss_price_out'] : 0;
                     
                     $db_update  = new db_execute("UPDATE " . USER_PRODUCTS . "
                                                   SET usp_active = 1,usp_price_import = " . doubleval($pr_im) . ", usp_price = " . doubleval($pr_ex) . "
                                                   WHERE usp_id = " . $chose_pro_id);
                     unset($db_update);
                  }
                  
                  // cập nhật số lượng vào thuốc được chỉ định                  
                  $db_update  = new db_execute("UPDATE ". USER_PRODUCTS ." SET 
                                                usp_quantity = usp_quantity + " . intval($quantity) . "
                                                WHERE usp_id = " . intval($chose_pro_id));
                  unset($db_update);
                  $success = 1;
               }else{
                  
                  // không có ID thuốc như vậy thì báo lỗi
                  $arrayReturn['error'] = "Thuốc này không tồn tại, Vui lòng xem lại";
                  
               }
               unset($db_check_pro);
               
            }else{
               
               // tạo mới thuốc
               $data = array(
                  'pro_id' => intval($pro_id),
                  'price_import' => $price_import,
                  'price_export' => $price_export,
                  'quantity' => $quantity                    
               );
               $array_pro = $this->import_product_to_product($data);
               
               if(!empty($array_pro)) $success = 1;
               
            }
            
            
         }else{
            $arrayReturn['error'] = 'Thuốc cần chuyển kho không tồn tại';
         }
         unset($db_pro);
         
         // nếu thành công thì bắt đầu thêm nhập kho và thực hiện B2
         if($success == 1){
            // thêm mới nhập kho và tính recount     
            $arrayLast  = $this->import_get_last_stocks(array('pro_id' => $array_pro['usp_id'], 'quantity' => 1));
            $cogs = isset($arrayLast['uss_cogs'])? $arrayLast['uss_cogs'] : 0;       
            $db_import     = new db_execute_return();
            $oid = $db_import->db_execute("INSERT INTO " . USER_STOCK . " 
                                           (uss_date,uss_date_expires,uss_use_parent_id,uss_use_child_id,uss_price_import,uss_price_out,uss_quantity,uss_unit,uss_pro_id,uss_dat_id,uss_branch_id,uss_status,uss_quantity_unit_parent,uss_quantity_unit_child,uss_cogs,uss_note)
                                           VALUES(". time() .",0,". intval($admin_id) .",". intval($myuser->u_id) .",". doubleval($price_import) .",". doubleval($price_export) ."," . intval($quantity) .",". $array_pro['usp_unit'] .",". $array_pro['usp_id']. ",". $array_pro['usp_dat_id'] .",". intval($branch_id) .",1,". intval($quantity_parent) .",". intval($quantity_child) .",". doubleval($cogs) .",'Nhận hàng từ kho: ". $branch_from_name ."')");
            if($oid > 0){
               //@file_put_contents('../logs/move.txt', "\n" . ' 9: Thêm mới nhập kho cho bên nhận thành công: '. $oid ,FILE_APPEND);
               $this->import_recount_cogs(array('stocks_id' => $oid, 'pro_id' => $array_pro['usp_id']));
               $arrayReturn['status'] = 1;
            }
            
            // B2:
            
            // cập nhật trạng thái chuyển thành công của bên chuyển
            $db_update  = new db_execute("UPDATE temp_stocks_move SET 
                                          tsm_status = " . MOVE_STOCKS_ACCESS . ",
                                          tsm_date_access = " . time() . ",
                                          tsm_use_access = " . intval($myuser->u_id) . "
                                          WHERE tsm_id = " . intval($tsm_id));
            if($db_update->total >= 0){
               // trừ số lượng ở nơi chuyển
               $db_product =  new db_execute("UPDATE " . USER_PRODUCTS . " SET 
                                              usp_quantity = usp_quantity - " . intval($quantity) . "
                                              WHERE usp_id = " . intval($pro_id) . "
                                              AND usp_branch_id = " . intval($branch_from_id). "
                                              AND usp_use_parent_id = " . intval($admin_id));
               //@file_put_contents('../logs/move.txt', "\n" . ' 3: Updat noi chuyển: ' . $db_product->total ,FILE_APPEND);
               unset($db_product);
               // thêm bản ghi âm số lượng vào bảng nhập hàng của bên chuyển
               
               $db_stock_im   = new db_execute_return();
               $last_import_id   = $db_stock_im->db_execute(
                  "INSERT INTO ". USER_STOCK . "(uss_date,uss_use_parent_id,uss_use_child_id,uss_quantity,uss_unit,uss_pro_id,uss_dat_id,uss_branch_id,uss_status,uss_quantity_unit_parent,uss_quantity_unit_child,uss_note)
                  SELECT ".time().",uss_use_parent_id,uss_use_child_id,-". intval($quantity) .",uss_unit,uss_pro_id,uss_dat_id,uss_branch_id,1,uss_quantity_unit_parent,uss_quantity_unit_child,'Chuyển hàng sang kho: ". $branch_to_name ."'
                  FROM " . USER_STOCK . " WHERE uss_pro_id = " . intval($pro_id) . " AND uss_use_parent_id = " . intval($admin_id) . " AND uss_branch_id = " . intval($branch_from_id) . " ORDER BY uss_id DESC LIMIT 1");
               unset($db_stock_im);
            }
            unset($db_update);
         }
         
         $arrayReturn['status'] = $success;
         
      }else{
         $arrayReturn['error'] = 'Bạn không có quyền xác nhận chuyển hàng';
      }
      unset($db_tsm);
      
      return $arrayReturn;
   }
   
   /**
    * Hàm lấy bản ghi nhập cuối cùng của 1 sản phẩm thành công
    * param(
    *    'pro_id' => 
    * )
    */
   function import_get_last_stocks($data = array()){
      global $admin_id, $myuser, $branch_id;
      
      if(empty($data)) return array();
      $pro_id  = isset($data['pro_id'])? intval($data['pro_id']) : 0;
      $quantity   = isset($data['quantity'])? intval($data['quantity']) : 0;
      $no_insert   = isset($data['no_insert'])? intval($data['no_insert']) : 1;
      $check_price_import   = isset($data['check_price_import'])? intval($data['check_price_import']) : 0;
      
      if($pro_id <= 0) return array();
      
      $arrayReturn   = array();
      $sql_w         = ' AND uss_status = 1';
      if($quantity == 1){
         $sql_w = " AND uss_status > 0 AND uss_quantity > 0";
      }
      $db_last = new db_query("SELECT * FROM " . USER_STOCK . "
                               WHERE uss_use_parent_id =" . intval($admin_id) . "
                               AND uss_branch_id = " . intval($branch_id) . "
                               AND uss_pro_id = " . intval($pro_id) . $sql_w . "
                               ORDER BY uss_date DESC
                               LIMIT 1");
      if($row  = mysqli_fetch_assoc($db_last->result)){
         $arrayReturn = $row;
      }else{
         
         $rdata = array(
            "date" 					=> time()	//ngày tạo
				,"date_expires"		=>	0	//ngày hết hạn
				,"use_parent_id"		=> $admin_id	//id admin
				,"use_child_id"		=> $myuser->u_id	//id người nhập
				,"price_import"		=> 0 //giá nhập
				,"price_out"			=> 0 //giá bán
				,"quantity"				=> 0 //số lượng
				,"unit"					=> 0 //đơn vị
				,"pro_id"						=> $pro_id //id sản phẩm
				,"dat_id"						=> 0 //id sản phẩm sàn
				,"branch_id"					=> $branch_id //id chi nhánh
				,"status"						=> 1 // trạng thái
				,"quantity_unit_parent"		=> 0 //đơn vị tính theo dạng hộp
				,"quantity_unit_child"		=> 0 //
         );
         
         foreach($rdata as $key => $val){
   		    $$key = $val;	
   		}
         
         if($no_insert == 1){
            $db_ex = new db_execute("INSERT INTO " . USER_STOCK . "(uss_date,uss_date_expires,uss_use_parent_id,uss_use_child_id,uss_price_import,uss_price_out,uss_quantity,uss_unit,uss_pro_id,uss_dat_id,uss_branch_id,uss_quantity_unit_parent,uss_quantity_unit_child,uss_cogs,uss_status)
      										 VALUES(" . intval($date) . "," . intval($date_expires) . "," . intval($use_parent_id) . "," . intval($use_child_id) . "," . doubleval($price_import) . "," . doubleval($price_out) . ",0,0,". intval($pro_id) .",0,". intval($branch_id) ."," . intval($quantity_unit_parent) . "," . intval($quantity_unit_child) . ",0,1)"); 
            unset($db_ex);
         }
         
         $arrayReturn   = $this->import_get_last_stocks(array('pro_id' => $pro_id));
         return $arrayReturn;
         
      }
      unset($db_last);
      
      return $arrayReturn;
   }
   
   function update_cogs_product($pro_id = 0, $cogs = 0){
      //file_put_contents('../logs/a.txt', $pro_id, FILE_APPEND);
      global $admin_id;
      if($pro_id > 0){
         //file_put_contents('../logs/a.txt', "UPDATE " . USER_PRODUCTS . " SET usp_cogs = " . doubleval($cogs) . " WHERE usp_id = " . intval($pro_id) . " AND usp_use_parent_id = " . intval($admin_id), FILE_APPEND);
         $db_ex   = new db_execute("UPDATE " . USER_PRODUCTS . " SET usp_cogs = " . doubleval($cogs) . " WHERE usp_id = " . intval($pro_id) . " AND usp_use_parent_id = " . intval($admin_id));
         unset($db_ex);
      }
   }
   
   /**
    * hàm tính toán lại giá vốn của id hiện tại
    * + tính giá vốn của id hiện tại
    * + tính giá vốn của id > id hiện tại (cùng 1 sản phẩm)
    * + cập nhật giá vốn vào bảng bán đối với những id bị thay đổi
    * param(
    *    'stocks_id' => 
    *    'pro_id' => 
    * )
    */
   function import_recount_cogs($data = array()){
      global $admin_id, $myuser, $branch_id;
      
      $stocks_id  = isset($data['stocks_id'])? intval($data['stocks_id']) : 0;
      $pro_id     = isset($data['pro_id'])? intval($data['pro_id']) : 0;
      if($stocks_id <= 0 || $pro_id <= 0) return 0;
      
      $total_sold = 0;
      
      // b1:
      // lấy bản ghi nhỏ hơn gần bản ghi này nhất để tính lại giá vốn
      $array1  = array();
      $array2  = array();
      $db_min  = new db_query("SELECT * FROM " . USER_STOCK . "
                               WHERE uss_use_parent_id = " . intval($admin_id) ."
                               AND uss_branch_id = " . intval($branch_id) . "
                               AND uss_pro_id = " . intval($pro_id) . "
                               AND uss_status = 1
                               AND uss_id <= " . intval($stocks_id) ."
                               ORDER BY uss_id DESC LIMIT 2");
      while($rmin = mysqli_fetch_assoc($db_min->result)){
         
         if($rmin['uss_id'] == $stocks_id){
            $array1 = $rmin; 
         }else{
            $array2 = $rmin;
         }
         
         
      }
      unset($db_min);
      
      // nếu chỉ có nguyên array1 mà không có array2 thì cập nhật giá vốn vào bảng bán hàng với gia nhập hiện tại
      if(empty($array1) && !empty($array2)){
         $total_sold = $array2['uss_new_sold'];
         // cập nhật toàn bộ bảng bán hàng có id uso_uss_id = stocks_id
         $db_update_sale   = new db_execute("UPDATE " . USER_SALE . " SET 
                                             uso_cogs = 0
                                             WHERE uso_uss_id = " . intval($stocks_id));
         unset($db_update_sale);
         $array1 = $array2;
         //file_put_contents('../logs/a.txt', $pro_id, FILE_APPEND);
         
      }else if(!empty($array1) && empty($array2)){
         
         $array2 = $array1;
         $total_sold = $array1['uss_quantity'];
         //file_put_contents('../logs/a.cfn', $pro_id . ': ' . $total_sold . ' --- ' . json_encode($array1) . "\n", FILE_APPEND);
         //@file_put_contents('../logs/a.cfn', '8: ' . $total_sold . ' ' . __LINE__ . "\n", FILE_APPEND);
         if($array1['uss_cogs'] == 0){
            $db_stock   = new db_execute("UPDATE " . USER_STOCK . " SET uss_cogs = uss_price_import,uss_new_sold = uss_quantity WHERE uss_id =".$array1['uss_id']);
            unset($db_stock);
         }
         //file_put_contents('../logs/a.txt', $array1['uss_price_import'], FILE_APPEND);
         $this->update_cogs_product($pro_id, $array1['uss_price_import']);
         
         // cập nhật toàn bộ bảng bán hàng có id uso_uss_id = stocks_id 
         $db_update_sale   = new db_execute("UPDATE " . USER_SALE . " SET 
                                             uso_cogs =" . $array1['uss_price_import'] . "
                                             WHERE uso_uss_id = " . intval($stocks_id));
         unset($db_update_sale);
         $array1['uss_cogs'] = $array1['uss_price_import'];
         $array1['uss_new_sold'] = $total_sold;
         
      }else if(!empty($array1) && !empty($array2)){
         
         $total_sold = ($array1['uss_quantity'] + $array2['uss_new_sold']);
         //@file_put_contents('../logs/b.cfn', $pro_id . ': ' . $total_sold . "\n", FILE_APPEND);
         $price_cogs = 0;
         if($total_sold > 0){         
            $price_cogs = (($array1['uss_quantity'] * $array1['uss_price_import']) + ($array2['uss_new_sold'] * $array2['uss_cogs'])) / $total_sold;
         }
         $cogs = format_number($price_cogs,2);
         //$content = $array1['uss_quantity'] .' ' .$array1['uss_price_import'] . ' -- ' . $array2['uss_quantity'] . ' ' . $array2['uss_price_import'];
         //$this->log($content);
         $db_update  = new db_execute("UPDATE " . USER_STOCK . " SET
                                       uss_cogs = " . doubleval($cogs) . ",uss_new_sold = ". intval($total_sold) . " WHERE uss_id = " . intval($stocks_id));
         unset($db_update);
         $this->log($cogs);
         $this->update_cogs_product($pro_id, $cogs);
         
         // cập nhật toàn bộ bảng bán hàng có id uso_uss_id = stocks_id
         $db_update_sale   = new db_execute("UPDATE " . USER_SALE . " SET 
                                             uso_cogs =" . doubleval($cogs) . "
                                             WHERE uso_uss_id = " . intval($stocks_id));
         unset($db_update_sale);
         $array1['uss_cogs'] = $cogs;
         $array1['uss_new_sold'] = $total_sold;
      }
      
      // b2:
      // cập nhật lại toàn bộ bản ghi trong nhập kho > id hiện tại để tính giá vốn lại
      
      
      $array_old  = $array1; // gắn bản ghi cũ = bản ghi hiện tại 
      // nếu array1 sửa thông tin số lượng nhập và giá nhập về 0 hết (tương tự hủy bỏ bản ghi thì set array_old = array2(bản ghi trước đó))      
      if((isset($array1['uss_quantity']) && $array1['uss_quantity'] == 0) && ((isset($array2['uss_quantity']) && $array2['uss_quantity'] > 0) && (isset($array2['uss_price_import']) && $array2['uss_price_import'] > 0))){
         $array_old = $array2;
      }
                                        
      $db_stocks  = new db_query("SELECT * FROM " . USER_STOCK . "
                                  WHERE uss_id > ". intval($stocks_id) . "
                                  AND uss_use_parent_id = " . intval($admin_id) . "
                                  AND uss_branch_id = " . intval($branch_id) . "
                                  AND uss_pro_id = " . intval($pro_id) . "
                                  AND uss_cogs > 0
                                  AND uss_status = 1
                                  ORDER BY uss_id ASC");
      $i = 0;
      while($rstock  = mysqli_fetch_assoc($db_stocks->result)){
         $array_new  = $rstock;
         $st_id      = $rstock['uss_id'];
         
         $total_sold +=  $rstock['uss_quantity'];
         
         $price_cogs = (($array_old['uss_new_sold'] * $array_old['uss_cogs']) + ($array_new['uss_quantity'] * $array_new['uss_price_import'])) / ($array_new['uss_quantity'] + $array_old['uss_new_sold']);
         $cogs = format_number($price_cogs,2);
         
         if($i == 0){
            //$t = $array_old['uss_new_sold'] .'*'. $array_old['uss_cogs'] . '|'. $array_new['uss_quantity'] .'*'. $array_new['uss_price_import'] .' / ' . $array_new['uss_quantity'] . '+'. $array_old['uss_new_sold'] .'----'.'sold: ' . $total_sold . ' | cogs:' . $cogs . ' | '. $array_old['uss_sold'] .'-'.$array_old['uss_cogs'] . ' | ' . $array_new['uss_quantity'].'-'.$array_new['uss_price_import'];
            //$this->log($t);
         }
         //@file_put_contents('../logs/a.cfn', '9: ' . $total_sold . ' ' . __LINE__ . "\n", FILE_APPEND);
         $db_update  = new db_execute("UPDATE " . USER_STOCK . " SET
                                       uss_cogs = " . doubleval($cogs) . ",uss_new_sold = ". intval($total_sold) ." WHERE uss_id = " . intval($st_id));
         unset($db_update);
         
         $this->update_cogs_product($pro_id, $cogs);
         
         // cập nhật toàn bộ bảng bán hàng có id uso_uss_id = stocks_id
         $db_update_sale   = new db_execute("UPDATE " . USER_SALE . " SET 
                                             uso_cogs =" . doubleval($cogs) . "
                                             WHERE uso_uss_id = " . intval($st_id));
         unset($db_update_sale);
         
         // cập nhật xong gắn đè lại array_old = array_new để tiếp tục vòng mới
         $array_new['uss_cogs'] = $cogs;
         $array_new['uss_new_sold'] = $total_sold;
         $array_old = $array_new;
         $i++;
      }
      unset($db_stocks);
                             
   }
   
   function log($content){
      //file_put_contents('../logs/a.txt', $content);
   }
   /**
    * Hàm tạo cache theo ngày cho nhà thuốc
    */
   function createUserCache($parent_id,$branch_id,$date){
      
      // nếu thông số không có thì báo lỗi
   	if($parent_id <= 0 || $branch_id <= 0 || $date <= 0) return 0;
      
      $total_import  = 0;
      $total_export  = 0;
      $total_profit  = 0;
      
      $table_stock   = 'user_stock';
      $table_order   = 'user_orders';
      // kiểm tra xem có phải user trả phí không
      $db_user = new db_query("SELECT * FROM users WHERE use_id = " . intval($parent_id) . " LIMIT 1");
      if($row  = mysqli_fetch_assoc($db_user->result)){
         if($row['use_payment'] == 1){
            $table_stock   = 'user_stock_' . $row['use_id'];
            $table_order   = 'user_orders_' . $row['use_id'];
         }
      }
      unset($db_user);
      
      
      // thời gian where là thời gian đầu ngày và cuối ngày
      $time_f  = convertDateTime(date('d/m/Y', $date), '00:00:00');
      $time_e  = convertDateTime(date('d/m/Y', $date), '23:59:59');
      //echo $time_f . ' - ' .$time_e;
      // tính tổng tiền nhập trong ngày
      
      $db_import  = new db_query("SELECT SUM(uss_quantity * uss_price_import) AS total_import FROM " . $table_stock . "
                                  WHERE uss_date >= " . intval($time_f) . " AND uss_date <= " . intval($time_e) . "
                                  AND uss_branch_id = " . intval($branch_id) . " AND uss_use_parent_id = " . intval($parent_id));
      if($rim     = mysqli_fetch_assoc($db_import->result)){
         $total_import  = $rim['total_import'];
      }
      unset($db_import);
      
      // tính tổng tiền bán trong ngày
      $db_export  = new db_query("SELECT SUM(uso_quantity * uso_price_out) AS total_export FROM " . $table_order . "
                                  WHERE uso_date >= " .intval($time_f) . " AND uso_date <= " . intval($time_e) . "
                                  AND uso_branch_id = " . intval($branch_id) . " AND uso_use_parent_id = " . intval($parent_id));
      if($rim     = mysqli_fetch_assoc($db_export->result)){
         $total_export  = $rim['total_export'];
      }
      unset($db_export);
      
      // tính tổng tiền lãi trong ngày
      $db_export  = new db_query("SELECT SUM(uso_quantity * uso_price_out - uso_quantity * uss_cogs) AS total_profit 
                                  FROM " . $table_order . "
                                  INNER JOIN ". $table_stock ." ON uso_uss_id = uss_id
                                  WHERE uso_date >= " . intval($time_f) . " AND uso_date <= " . intval($time_e) ."
                                  AND uso_branch_id = " . intval($branch_id) . " AND uso_use_parent_id = " . intval($parent_id));
      if($rim     = mysqli_fetch_assoc($db_export->result)){
         $total_profit  = $rim['total_profit'];
      }
      unset($db_export);
      
      // cập nhật bảng user_cache
      
      $db_ex   = new db_execute("INSERT INTO user_cache(usc_branch_id,usc_parent_id,usc_date,usc_stock,usc_order,usc_profit)
                                 VALUES(". intval($branch_id) .",". intval($parent_id) .",". intval($time_f) .",". doubleval($total_import) .",". doubleval($total_export) .",". doubleval($total_profit) .")");
      if($db_ex->total > 0){
         return 1;
      }
      
      return 0;
   }
   
   /**
    * Hàm tính thống kê user
    * 
    * $array(
    *    'time' =>
    *    'branch_id' =>
    *    'admin_id' =>
    * )
    */
   function statistic_user($data = array()){
      global $myredis;
      $time = isset($data['time'])? intval($data['time']) : 0;
      $branch_id  = isset($data['branch_id'])? intval($data['branch_id']) : 0;
      $parent_id  = isset($data['admin_id'])? intval($data['admin_id']) : 0;
      
      $status  = 0;
      if($time <= 0 || $branch_id <= 0 || $parent_id <= 0) return $status;
      
      // các biến cần thống kê
      $total_money_import  = 0;
      $total_product  = 0;
      $total_product_new  = 0;
      $total_money_sold  = 0;
      $total_money_sale  = 0;
      $total_money_profit  = 0;
      $total_product_sale  = 0;
      $time_fday  = $time;
      $time_eday  = $time + 86399;
      $time_cron  = time();
      
      $db_user    = new db_query("SELECT * FROM users WHERE use_id = " . intval($parent_id) . " LIMIT 1");
      if($row  = mysqli_fetch_assoc($db_user->result)){
         if($row['use_parent_id'] != $parent_id) return $status;
         
         $table_stock   = 'user_stock';
         $table_product = 'user_products';
         $table_orders  = 'user_orders';
         if($row['use_payment'] == 1){
            $table_stock   = 'user_stock_' . $parent_id;
            $table_product = 'user_products_' . $parent_id;
            $table_orders  = 'user_orders_' . $parent_id;
         }
         
         // sản phẩm mới
         $key_product_new  = 'new_product:'. $time . ':' . $branch_id .":". $parent_id;
         $total_product_new = intval($myredis->get($key_product_new));
         
         // tính tổng tiền nhập hàng trong ngày
         $db_stock   = new db_query("SELECT SUM(uss_quantity*uss_price_import) AS total_money_import
                                     FROM " . $table_stock . "
                                     WHERE uss_use_parent_id = " . intval($parent_id) . "
                                     AND uss_branch_id = " . intval($branch_id) . "
                                     AND uss_date >= " . intval($time_fday) . " AND uss_date <= ". intval($time_eday) ."
                                     AND uss_status = 1");
         if($rstock  = mysqli_fetch_assoc($db_stock->result)){
            $total_money_import  = $rstock['total_money_import'];
         }
         unset($db_stock);
         
         // tính tổng sản phẩm trong ngày
         $db_pro   = new db_query("SELECT COUNT(usp_id) AS total_product, SUM(usp_quantity*usp_cogs) AS total_money_sold
                                     FROM " . $table_product . "
                                     WHERE usp_use_parent_id = " . intval($parent_id) . "
                                     AND usp_branch_id = " . intval($branch_id) . "
                                     AND usp_active = 1");
         if($rpro  = mysqli_fetch_assoc($db_pro->result)){
            $total_product  = $rpro['total_product'];
            $total_money_sold  = $rpro['total_money_sold'];
         }
         unset($db_pro);
         
         // tính tiền bán và tiền lãi trong ngày
         $db_sale = new db_query("SELECT SUM(uso_quantity*uso_price_out) AS total_money_sale, SUM(uso_quantity*uss_cogs) AS total_cogs 
                                  FROM " . $table_orders . "
                                  LEFT JOIN " . $table_stock . " ON uss_id = uso_uss_id
                                  WHERE uso_use_parent_id = " . intval($parent_id) . "
                                  AND uso_branch_id = " . intval($branch_id) . "
                                  AND uso_date >= " . intval($time_fday) . " AND uso_date <= ". intval($time_eday) . "
                                  AND uso_status = 1");
         if($rsale   = mysqli_fetch_assoc($db_sale->result)){
            $total_money_sale = $rsale['total_money_sale'];
            $total_money_profit  = $total_money_sale - $rsale['total_cogs'];
         }
         unset($db_sale);
         
         
         $db_statistic  = new db_execute("REPLACE INTO statistic(sta_use_id,sta_branch_id,sta_time,sta_time_cron,sta_total_money_import,sta_total_product,sta_total_product_new,sta_money_sold,sta_total_money_sale,sta_total_money_profit,sta_total_product_sale)
            VALUES(". intval($parent_id) .",". intval($branch_id) .",". intval($time_fday) .",". intval($time_cron) .",". doubleval($total_money_import) .",". intval($total_product) .",". intval($total_product_new) .",". doubleval($total_money_sold) .",". doubleval($total_money_sale) .",". doubleval($total_money_profit) .",". doubleval($total_product_sale) .")",
            __FILE__ . " LINE: " . __LINE__, DB_NOTIFICATION);
         unset($db_statistic);
         
         return 1;
      }
      unset($db_user);
      
   }
   
   

   /**
    * Function kiểm kho
    * 
    * array(
    *    'pid' =>
    *    'quantity' =>
    * )
    * 
    * return 0 || 1
    */
   function save_inventory($data = array()){
      global $admin_id, $branch_id, $myuser;
      
      $pro_id  = isset($data['pid'])? intval($data['pid']) : 0;
      $quantity   = isset($data['quantity'])? intval($data['quantity']) : 0;
      $price_import   = isset($data['price_import'])? doubleval($data['price_import']) : 0;
      $price_out   = isset($data['price_out'])? doubleval($data['price_out']) : 0;
      
      if($pro_id <= 0) return 0;
      
      // cập nhật số tồn trong bảng product
      $db_pro  = new db_query("SELECT * FROM " . USER_PRODUCTS . " 
                               WHERE usp_id = " . intval($pro_id) . "
                               AND usp_use_parent_id = " . intval($admin_id) . "
                               AND usp_branch_id = " . intval($branch_id) . "
                               LIMIT 1");
      if($row  = $db_pro->fetch()){
         
         $stocks_id  = 0;
         // select bảng nhập hàng để trừ dần
         $db_stock   = new db_query("SELECT * FROM " . USER_STOCK . "
                                     WHERE uss_pro_id = " . intval($pro_id) . "
                                     AND uss_use_parent_id = " . intval($admin_id) . "
                                     AND uss_branch_id = " . intval($branch_id) . "
                                     AND uss_status = 1
                                     ORDER BY uss_id DESC LIMIT 1");
         if($rstock    = $db_stock->fetch()){
            
            $stocks_id  = $rstock['uss_id'];
            $note_inventory   = '';
            //@file_put_contents('../logs/a.cfn', '5: ' . $quantity . ' ' . __LINE__ . "\n", FILE_APPEND);
            //@file_put_contents('../logs/a.cfn', '6.1: q:' . $quantity . ' - usp:' . $row['usp_quantity']. ' - uss:' . $rstock['uss_quantity'] . ' ' . __LINE__ . "\n", FILE_APPEND);
            if($quantity < $row['usp_quantity']){
               $new_quantity  = ($rstock['uss_quantity'] - ($row['usp_quantity'] - $quantity));
               $chenhlech  = ($row['usp_quantity'] - $quantity);
               $note_inventory = ' làm Giảm từ ' . $rstock['uss_quantity'] . ' Xuống ' . ($rstock['uss_quantity'] - $chenhlech);
               //@file_put_contents('../logs/a.cfn', '5: ' . $new_quantity . ' ' . __LINE__ . "\n", FILE_APPEND);
            }else if($quantity > $row['usp_quantity']){
               if($row['usp_quantity'] > 0){                  
                  $chenhlech  = ($quantity - $row['usp_quantity']);
                  $new_quantity  = ($row['usp_quantity']) + (intval($quantity) - $rstock['uss_quantity']);
                  $note_inventory = ' làm Tăng từ ' . $rstock['uss_quantity'] . ' lên ' . ($chenhlech + $rstock['uss_quantity']);
               }else{
                  $new_quantity = $quantity;
                  $chenhlech = 0;
                  $note_inventory = ' Không thay đổi';
               }
            }else{
               $new_quantity = $quantity;
               $chenhlech = 0;
               $note_inventory = ' Không thay đổi';
            }
            //@file_put_contents('../logs/a.cfn', '6: ' . $new_quantity . ' ' . __LINE__ . "\n", FILE_APPEND);
            $file_stock_update   = '';
            if($price_import > 0){
               $file_stock_update = ",uss_price_import = " . doubleval($price_import) . ",uss_cogs = ". doubleval($price_import);
            }
            if($price_out > 0){
               $file_stock_update .= ",uss_price_out = ". doubleval($price_out);
            }
            
            //@file_put_contents('../logs/a.cfn', "UPDATE " . USER_STOCK . " SET uss_quantity = " . $new_quantity . ",
//                                    uss_new_sold = ". intval($new_quantity) . $file_stock_update .",
//                                    uss_note = '". $myuser->useField['use_fullname'] . " <b>Kiểm kho:</b> ( Tổng kho cũ:<b>". $row['usp_quantity'] ."</b>, Tổng kho mới: <b>". $quantity ."</b>) lúc: " . date('H:i - d/m/Y') .", ". $note_inventory ."'
//                                    WHERE uss_id = " . intval($stocks_id) . ' ' . __LINE__ . "\n", FILE_APPEND);
            $db_s = new db_execute("UPDATE " . USER_STOCK . " SET uss_quantity = " . intval($new_quantity) . ",
                                    uss_new_sold = ". intval($new_quantity) . $file_stock_update .",
                                    uss_note = '". $myuser->useField['use_fullname'] . " <b>Kiểm kho:</b> ( Tổng kho cũ:<b>". $row['usp_quantity'] ."</b>, Tổng kho mới: <b>". $quantity ."</b>) lúc: " . date('H:i - d/m/Y') .", ". $note_inventory ."'
                                    WHERE uss_id = " . intval($stocks_id));
            unset($db_s);
                         
            // ghi log hành động
             $data_action  = array(
               'file' => json_encode(debug_backtrace()),
               'action' => 'edit',
               'des' => 'Cập nhật kiểm kho số lượng từ <b>' . $row['usp_quantity'] . '</b> sang <b>' . $quantity . '</b>' . "\n" . ' Ảnh hưởng đến nhập kho id <b>' . $stocks_id . '</b> Số lượng từ <b>' . $rstock['uss_quantity'] . '</b> thành <b>' . $new_quantity . '</b>',
               'pro_id' => $pro_id
             );
   
             log_action($data_action);
            
            $this->import_recount_cogs(array('stocks_id' => $stocks_id,'pro_id' => $pro_id));
         }else{
            // nếu không có bản ghi nào (có thể toàn bản ghi chỉ xem thì thêm mới 1 bản ghi để cho sửa)
            $db_i   = new db_execute_return();
            $oid = $db_i->db_execute("INSERT INTO " . USER_STOCK . " 
                                       (uss_date,uss_use_parent_id,uss_use_child_id,uss_price_import,uss_price_out,uss_quantity,uss_unit,uss_pro_id,uss_dat_id,uss_branch_id,uss_status,uss_quantity_unit_parent,uss_quantity_unit_child)
                                       VALUES(". time() .",". intval($admin_id) .",". intval($myuser->u_id) .",0,0,0,". intval($row['usp_unit']) .",". intval($row['usp_id']). ",". $row['usp_dat_id'] .",". intval($branch_id) .",1,0,". intval($row['usp_specifi']) .")");
            return $this->save_inventory($data);
         }
         unset($db_stock);
         
         // cập nhật kho
         $field_update  = '';
         if($price_out > 0) $field_update = ", usp_price = " . doubleval($price_out);
         //@file_put_contents('../logs/a.cfn', '7: ' . $quantity . ' ' . __LINE__ . "\n", FILE_APPEND);
         $db_up   = new db_execute("UPDATE " . USER_PRODUCTS . " SET usp_quantity = " . intval($quantity) . $field_update . "
                                    WHERE usp_id = " . intval($pro_id));
         unset($db_up);
         
         
         $this->recheck_stock(array('pro_id' => $pro_id, 'quantity' => $quantity, 'stocks_id' => $stocks_id));
         
         return 1;
      }else{
         return 0;
      }
      unset($db_pro);
   }
   
   
   function recheck_stock($data = array()){
      global $admin_id, $branch_id, $myuser;
      $limit   = 10000;
      $total_sale = 0;
      $total_import  = 0;
      $pro_id  = isset($data['pro_id'])? intval($data['pro_id']) : 0;
      $quantity  = isset($data['quantity'])? intval($data['quantity']) : 0;
      $stocks_id  = isset($data['stocks_id'])? intval($data['stocks_id']) : 0;
      
      if($pro_id > 0 && $quantity >= 0 && $stocks_id > 0){
         
         // lấy ra tổng số lượng lịch sử bán
         $db_sale = new db_query("SELECT SUM(uso_quantity) AS total_sale FROM " . USER_SALE . "
                                  WHERE uso_use_parent_id = " . intval($admin_id) . "
                                  AND uso_branch_id = " . intval($branch_id) ."
                                  AND uso_pro_id  = " . intval($pro_id) . "
                                  AND uso_status = 1 AND uso_view = 0
                                  ");
         if($row  = $db_sale->fetch()){
            $total_sale  =$row['total_sale'];
         }
         unset($db_sale);
         
         // tính lại số lượng nhập của lần nhập gần nhất
         
         $db_sum = new db_query("SELECT SUM(uss_quantity) AS total_import FROM " . USER_STOCK . "
                                  WHERE uss_use_parent_id = " . intval($admin_id) . "
                                  AND uss_branch_id = " . intval($branch_id) ."
                                  AND uss_pro_id = " . intval($pro_id) . "
                                  AND uss_status = 1 
                                  AND uss_id NOT IN (". intval($stocks_id) .")");
         if($rowsum = $db_sum->fetch()){
            $uss_quantity = ($quantity + $total_sale) - $rowsum['total_import'];
            
            //@file_put_contents('../logs/a.cfn', '10: ' . $uss_quantity . ' ' . __LINE__ . "\n", FILE_APPEND);
            $db_upstock = new db_execute("UPDATE " . USER_STOCK . " SET uss_quantity = " . intval($uss_quantity) . "
                                          WHERE uss_id = " . intval($stocks_id));
            unset($db_upstock);
            
            $this->import_recount_cogs(array('stocks_id' => $stocks_id, 'pro_id' => $pro_id));
         }
         unset($db_sum);
         

         
      }
   }
   
   
   /**
    * Hàm thêm sản phẩm vào gọi hàng
    * param(
    *    'pro_id' =>
    * )
    * return array
    */
   function call_product($data = array()){
      global $admin_id, $branch_id, $myuser,$pathRoot,$array_unit;
      
      $arrayReturn   = array(
         'status' => 0,
         'error' => ''
      );
      
      $pro_id  = isset($data['pro_id'])? intval($data['pro_id']) : 0;
      if($pro_id <= 0){
         $arrayReturn['error'] = 'Không có thuốc chuyển kho';
         return $arrayReturn;
      }
      
      // check quyền user có đc chuyển hàng không
      if(!$myuser->checkRight(USER_RIGHT_CALL_PRODUCT)){
         $arrayReturn['error'] = 'Bạn không có quyền gọi hàng';
         return $arrayReturn;
      }
      
      // kiểm tra thuốc còn hàng không
      $db_pro  = new db_query("SELECT * FROM " . USER_PRODUCTS . "
                               WHERE usp_id = " . intval($pro_id) . " AND usp_use_parent_id = ". intval($admin_id) ."
                               LIMIT 1");
      if($row  = mysqli_fetch_assoc($db_pro->result)){
         
         // kiểm tra trong bảng có chưa có thềm cộng thêm, chưa thì thêm mới
         $db_check = new db_query("SELECT * FROM orders_products 
                                   WHERE orp_use_parent_id = " . intval($admin_id) . "
                                   AND orp_branch_id = " . intval($branch_id) . "
                                   AND orp_pro_id = " . intval($pro_id) . "
                                   AND orp_status = 0
                                   LIMIT 1");
         if($rcheck  = mysqli_fetch_assoc($db_check->result)){
            $db_update  = new db_execute("UPDATE orders_products SET
                                          orp_quantity_parent = orp_quantity_parent + 1,
                                          orp_quantity = orp_quantity_parent * orp_quantity_child
                                          WHERE orp_id = " . $rcheck['orp_id']);
            if($db_update->total > 0){
               $arrayReturn['status'] = 1;
               $arrayReturn['oid'] = $rcheck['orp_id'];
               $arrayReturn['url'] = URL_WEB . $pathRoot . 'call_product.php';
            }
            unset($db_update);
            
         }else{
            // thêm mới vào bảng order product
            $db_insert  = new db_execute_return();         
            $oid = $db_insert->db_execute("
               INSERT INTO orders_products (orp_use_parent_id,orp_branch_id,orp_pro_id,orp_quantity_parent,orp_quantity_child,orp_quantity,orp_date,orp_unit_import,orp_unit,orp_dat_id,orp_use_child_id)
               SELECT ". intval($admin_id) .",". intval($branch_id) .",". intval($pro_id) .",1,".intval($row['usp_specifi']). ",". intval($row['usp_specifi']). ",". time() .",". intval($row['usp_unit_import']) .",". intval($row['usp_unit']) .",". intval($row['usp_dat_id']) .",". intval($myuser->u_id) ." FROM ". USER_PRODUCTS ." WHERE usp_id = " . intval($pro_id));
            unset($db_insert);
            
            if($oid > 0){
               $arrayReturn['status'] = 1;
               $arrayReturn['oid'] = $oid;
               $arrayReturn['url'] = URL_WEB . $pathRoot . 'call_product.php';
               
               $unit = (isset($array_unit[$row['usp_unit']]) && $row['usp_unit'] > 0)? $array_unit[$row['usp_unit']] : 'đơn vị bán';
               $unit_import = (isset($array_unit[$row['usp_unit_import']]) && $row['usp_unit_import'] > 0)? $array_unit[$row['usp_unit_import']] : 'đơn vị nhập';
               $quycach = intval($row['usp_specifi']);
               $unit_name  = ($unit != '')? '<span class="item_unit">Đơn vị: '. $unit . ( ($unit_import != '' && $quycach > 1)? ' ('.$unit_import . ' ' . $quycach . ' ' . $unit.')' : '' ) .'</span>': '';
               $specifi    = intval($row['usp_specifi']);
               $quantity   = $row['usp_specifi'] * 1;
                                           
               $arrayReturn['html'] = '<tr class="item_order" id="item_order_'. $oid .'" data-oid="'. $oid .'">
                                             <td width="20" class="text_c"><span class="item_order_del" onclick="delete_item_order(this)">x</span></td>
                                             <td>
                                                <div class="stocks_name">
                                                   <span class="item_order_name">'. $row['usp_pro_name'] .'</span>
                                                   '. $unit_name . '
                                                </div>
                                             </td>
                                             <td width="40" class="text_c bnone">
                                                <p><input autocomplete="off" maxlength="8" name="quantity_parent['. $oid .']" onblur="setUpdate(this); setTotalPrice();" title="Số lượng '. $unit_import .'" data-type="quantity_parent" class="tooltip number item_order_quantity quantity_parent type_number text_c" id="item_order_quantity_parent_'. $oid .'" type="tel" data-vl="1" value="1" /></p>
                                             </td>
                                             <td width="6" class="bnone">x</td>
                                             <td width="40" class="text_c bnone">
                                                <p><input autocomplete="off" maxlength="8" name="quantity_child['. $oid .']" onblur="addClass_text(this);  setUpdate(this); setTotalPrice();" title="Số lượng '. $unit .' / 1 '. $unit_import .'" data-type="quantity_child" onfocus="removeClass_text(this)" class="tooltip number item_order_quantity quantity_child type_number text_c '. (($specifi > 0)? 'text_text' : '') .'" id="item_order_quantity_child_'. $oid .'" type="tel" data-vl="1" value="'. format_number($specifi) .'" /></p>
                                             </td>
                                             <td width="6" class="bnone">=</td>
                                             <td class="text_r bnone" width="20">
                                                <b class="price" id="quantity_'. $oid .'">'. $quantity .'</b>
                                             </td>
                                             <td width="30" class="text_c"><i class="item_unit ww">('. $unit .')</i></td>
                                          </tr>';
            }
         }
         unset($db_check);
            
         
         return $arrayReturn;
      }else{
         $arrayReturn['error'] = 'Thuốc không tồn tại ở chi nhánh hiện tại';
         return $arrayReturn;
      }
      unset($db_pro);
      
   }
   
   
   /**
    * Hàm cập nhật thay đổi số lượng trong khi gọi hàng
    * param{
      array(
         'orp_id' => id của bảng gọi hàng
         'value' => giá trị cần thay đổi
         'type' => tên trường cần cập nhật
      )
    }
    
    return 0 || 1
    */
   function call_update_product($data = array()){
      global $admin_id, $branch_id, $array_unit, $myuser;
      $child_id   = $myuser->u_id;
      
      $status  = 0;
      $orp_id  = isset($data['orp_id'])? intval($data['orp_id']) : 0;
      $value      = isset($data['value'])? intval($data['value']) : 0;
      $type       = isset($data['type'])? $data['type'] : '';
      
      //$this->log($tsm_id . $value . $type);
      // nếu 1 trong 3 tham số trên không thỏa mãn thì return 0
      if($orp_id <= 0 || $value <= 0 || $type == '') return $status;
      
      // kiểm tra trong stocks có phải của mình không, không phải thì báo không có quyền
      $db_check   = new db_query("SELECT * FROM orders_products 
                                  WHERE orp_id = " . intval($orp_id) . "
                                  LIMIT 1");
      if($row  = mysqli_fetch_assoc($db_check->result)){
         if($row['orp_branch_id'] != $branch_id || $row['orp_use_parent_id'] != $admin_id){
            return 0;
         }
         
         // đã check là của mình thì bắt đầu cập nhật
         $field_update  = '';
         switch($type){
            case 'quantity_parent':
               $toal_quantity = $row['orp_quantity_child'] * $value;
               $field_update = " orp_quantity =" . $toal_quantity .",orp_quantity_parent=". $value;
               break;
            case 'quantity_child':
               $toal_quantity = $row['orp_quantity_parent'] * $value;
               $field_update = " orp_quantity =" . $toal_quantity .",orp_quantity_child=". $value;
               break;
         }
         
         // cập nhật thay đổi
         $db_ex   = new db_execute("UPDATE orders_products SET " . $field_update . " WHERE orp_id = " . intval($orp_id));
         unset($db_ex);
         $status = 1;
      }
      unset($db_check);
      
      return $status;
   }
   
   
   /**
    * Xóa gọi hàng
    */
   function call_product_delete($data = array()){
      global $admin_id, $branch_id, $myuser;
      
      $arrayReturn   = array(
         'status' => 0,
         'error' => ''
      );
      
      $orp_id  = isset($data['orp_id'])? intval($data['orp_id']) : 0;      
      if($orp_id <= 0) $arrayReturn['error'] = 'Thông tin không đúng';
      
      // check xem có phải mình tạo ra không
      $db_check   = new db_query("SELECT * FROM orders_products 
                                   WHERE orp_use_parent_id = " . intval($admin_id) . "
                                   AND orp_branch_id = " . intval($branch_id) . "
                                   AND orp_id = " . intval($orp_id) . "
                                   LIMIT 1");
      if($row  = mysqli_fetch_assoc($db_check->result)){
         
         // xóa
         $db_del  = new db_execute("DELETE FROM orders_products WHERE orp_id = " . intval($orp_id));
         if($db_del->total > 0){
            $arrayReturn['status'] = 1;
         }
         unset($db_del);
      }else{
         $arrayReturn['error'] = 'Bạn không có quyển xóa bản ghi này';
      }
      unset($db_check);
      
      return $arrayReturn;
   }
   
   
   /**
    * Hàm ghi nhận trạng thái thành công của gọi hàng
    * param
    * array(
    *    'orp_id' => id của bảng chuyển hang
    *    'quantity_parent' => 
    *    'quantity_child' =>
    * )
    * return 0 || 1
    */
   function call_product_save($data = array()){
      global $admin_id, $branch_id, $myuser;
      $child_id   = $myuser->u_id;
      $status  = 0;
      
      $orp_id  = isset($data['orp_id'])? intval($data['orp_id']) : 0;
      $quantity_parent  = isset($data['quantity_parent'])? intval($data['quantity_parent']) : 0;
      $quantity_child  = isset($data['quantity_child'])? intval($data['quantity_child']) : 0;
      $orp_code  = isset($data['orp_code'])? intval($data['orp_code']) : 0;
      
      if($orp_id <= 0 || $quantity_parent <= 0 || $quantity_child <= 0) return 0;
      
      // kiểm tra trong stocks có phải của mình không, không phải thì báo không có quyền
      $db_check   = new db_query("SELECT * FROM orders_products
                                  WHERE orp_id = " . intval($orp_id) . "
                                  LIMIT 1");
      if($row  = mysqli_fetch_assoc($db_check->result)){
         if($row['orp_use_parent_id'] != $admin_id || $row['orp_branch_id'] != $branch_id){
            return 0;
         }
         
         // mặc định giá vốn bằng giá nhập
         $toal_quantity = $quantity_parent * $quantity_child;
                  
         // đúng là của mình thì được cập nhật         
         $db_ex   = new db_execute("UPDATE orders_products SET 
                                    orp_quantity = ". intval($toal_quantity) .",
                                    orp_quantity_parent = ". intval($quantity_parent) . ",
                                    orp_quantity_child = ". intval($quantity_child) . ",
                                    orp_status = 1,
                                    orp_date = ". time() .",
                                    orp_branch_id = ". intval($branch_id) .",
                                    orp_code = ". intval($orp_code) ."
                                    WHERE orp_id = " . intval($orp_id));                                    
         unset($db_ex);
         
         $status = 1;
         
      }
      unset($db_check);
      
      return $status;
   }
   
   function save_orders_provider($data = array()){
      $user_id = isset($data['use_id'])? intval($data['use_id']) : 0;
      $provider_id   = isset($data['provider_id'])? intval($data['provider_id']) : 0;
      $code    = isset($data['code'])? intval($data['code']) : 0;
      $branch_id  = isset($data['branch_id'])? intval($data['branch_id']) : 0;
      
      if($user_id > 0 && $provider_id > 0){
         $db_provider   = new db_execute("INSERT INTO orders_provider (orp_use_id,orp_provider_id,orp_date,orp_code, orp_branch_id)
                                          VALUES (". $user_id .",". $provider_id .",". time() .",". $code .",". $branch_id .")");
         if($db_provider->total > 0) return 1;
         unset($db_provider);
      }
      return 0;
   }
   
   
   /**
    * Hàm cập nhật và thêm mới giá tiền báo giá của nhà cung cấp
    * data = array(
    *    'id' =>
    *    'price' =>
    * )
    */
   function price_list_update_price($data = array()){
      global$admin_id, $branch_id, $myuser;
      
      $id = isset($data['id'])? intval($data['id']) : 0;
      $price = isset($data['price'])? doubleval($data['price']) : 0;
      
      if($id <= 0){
         return 0;
      }
      
      $db_list = new db_query("SELECT * FROM orders_price_list 
                               WHERE opl_provider_id = " . intval($admin_id) . "
                               AND opl_orp_id = " . intval($id) ."
                               LIMIT 1");
                               
      if($rlist   = $db_list->fetch()){
         $db_up   = new db_execute("UPDATE orders_price_list SET opl_price = " . doubleval($price) . "
                                    WHERE opl_provider_id = " . intval($admin_id) . " AND opl_orp_id = " . intval($id));
         unset($db_up);
         return 1;
      }else{
         /**
          * Đoạn này có thể xảy ra trường hợp 1 thằng nào đó chọn id orp_id để gửi báo giá (nhưng tạm thời không sao vì vẫn chỉ gửi thôi cũng chưa làm gì đc
          * )
          */
         
         // lấy thông tin của sản phẩm gọi hàng thêm vào bảng báo giá
         $db_call = new db_query("SELECT * FROM orders_products
                                  WHERE orp_id = " . intval($id) . " LIMIT 1");
         if($rcall   = $db_call->fetch()){
            
            $db_in   = new db_execute("INSERT INTO orders_price_list (opl_provider_id,opl_orp_id,opl_orp_code,opl_date,opl_price,opl_pro_id,opl_dat_id)
                                       VALUES(". intval($admin_id) .",". intval($id) .",". intval($rcall['orp_code']) .",". time() .",". doubleval($price) .",". intval($rcall['orp_pro_id']) .",". intval($rcall['orp_dat_id']) .")");
            if($db_in->total > 0){
               return 1;
            }else{
               return 0;
            }
         }else{
            return 0;
         }
         unset($db_call);
         
      }
      unset($db_list);
   }
   
   
   /**
    * Tiến hành đặt hàng với từng nhà cung cấp
    * $array(
    *    'pro_id' =>
    *    'code' =>     
    *    'provider_id' =>
    * )
    * 
    * return 0 || 1
    */
   function order_call_product($data = array()){
      global $admin_id, $branch_id, $myuser, $notification,$arrayBranch, $pathRoot;
      
      $pro_id  = isset($data['pro_id'])? intval($data['pro_id']) : 0;
      $provider_id   = isset($data['provider_id'])? intval($data['provider_id']) : 0;
      $code   = isset($data['code'])? intval($data['code']) : 0;
      
      if($pro_id <= 0 || $provider_id <= 0 || $code <= 0) return 0;
      
      // lấy thông tin đặt hàng của sản phẩm 
      $db_order   = new db_query("SELECT * FROM orders_products 
                                  WHERE orp_use_parent_id = " . intval($admin_id) . "
                                  AND orp_branch_id = " . intval($branch_id) . "
                                  AND orp_pro_id = " . intval($pro_id) . "
                                  AND orp_status = 1
                                  LIMIT 1");
      if($row  = $db_order->fetch()){
         
         // tiến hành lưu đặt hàng của từng thuốc vs từng nhà cung cấp và gửi notification
         $db_check   = new db_query("SELECT * FROM orders_provider_detail
                                        WHERE opd_use_id = " . intval($admin_id) . "
                                        AND opd_branch_id = " . intval($branch_id) . "
                                        AND opd_provider_id = " . intval($provider_id) . "
                                        AND opd_code = " . intval($code) . "
                                        AND opd_pro_id = ". intval($pro_id) ." LIMIT 1");
         if($rcheck  = $db_check->fetch()){
            if($rcheck['opd_status'] == 0){
               $db_update  = new db_execute("UPDATE orders_provider_detail SET opd_status = 1 
                                             WHERE opd_id = " . intval($rcheck['opd_id']));
               unset($db_update);
            }
            
            return 1;
         }else{
            // chưa có thì thêm mới rồi gửi notifi
            $db_in   = new db_execute("INSERT INTO orders_provider_detail (opd_pro_id,opd_code,opd_use_id,opd_branch_id,opd_provider_id,opd_date,opd_orp_id)
                                       VALUES(". intval($pro_id) .",". intval($code) .",". intval($admin_id) .",". intval($branch_id) .",". intval($provider_id) .",". time() .",". intval($row['orp_id']) .")");
            if($db_in->total > 0){
               // send notification
               
               $des  = 'Nhà thuốc: <b>'. $arrayBranch[$branch_id]['usb_name'] .'</b> Địa chỉ: <b>'. $arrayBranch[$branch_id]['usb_address'] .'</b> đặt hàng thuốc (Mã đơn: <b>'. show_code($code) .'</b>).';
               $arrayMes   = array(
                  'title' => $myuser->useField['use_fullname'] .' đặt hàng cho đơn thuốc (Mã đơn: '. show_code($code) .')'
                  ,'description' => $des
                  ,'action' => NOTI_ACTION_ADD_CALL_PRODUCT
                  ,'url' => URL_WEB .  $pathRoot .'price_list.php?detail=1&code=' . $code
                  ,'date' => time()
                  ,'use_id' => $admin_id
                  ,'parent_id' => $provider_id
               );
               
               $arrayUser  = array($provider_id);
               $notification->send($arrayMes, $arrayUser,$provider_id);
               return 1;
            }else{
               return 0;
            }
            unset($db_in);
         }
         unset($db_check);
         
         return 1;
      }
      unset($db_order);
      return 0;
      
   }
   
   
   
}