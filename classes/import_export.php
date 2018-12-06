<?
/**
 * Nhập - xuất thuốc
 */
define("MOVE_STOCKS_ZERO", 0);
define("MOVE_STOCKS_MOVING", 1);
define("MOVE_STOCKS_ACCESS", 2);
define("MOVE_STOCKS_CANCEL", 3);

class import_export{
   
   
   function detect_barcode($keyword = ''){
      return preg_match('/^-?[0-9]+$/', $keyword) ? 1 : 0;
   }
   
   function  detect_me_barcode($keyword = ''){
      $firstCode     = substr($keyword, 0, 5) * 1;
      return ($firstCode % 10000 == 0)? 1 : 0;
   }
   
   
   /**
    * Hàm search keyword in product and data
    * keyword :
    * table_product:
    * return array
    */
   function search_keyword($data = array()){
      global $admin_id, $branch_id, $array_unit;
      $array_return = array();
      
      $keyword = isset($data['keyword'])? $data['keyword'] : '';
      $module  = isset($data['module'])? $data['module'] : '';
      
      $sql_keyword   = '';
      if($keyword != '') $sql_keyword  = " AND usp_pro_name LIKE'%". replaceMQ($keyword) ."%' ";
      
      $array_return['html'] = '<ul class="listProduct"><li>';
      $array_return['count'] = 0;
      
      $data = array();
      $idnot_in   = array();
      
      $sql_active = " AND usp_active = 1";
      if($module=='stock') $sql_active = '';
      $db_products = new db_query("SELECT * FROM " . USER_PRODUCTS . " 
                                    WHERE usp_use_parent_id = " . intval($admin_id) . " 
                                          AND usp_branch_id = " . $branch_id . $sql_keyword . $sql_active ."
                                    ORDER BY usp_alias ASC LIMIT 30");
      while($row  = mysqli_fetch_assoc($db_products->result)){
         $data[$row['usp_id']] = $row;
      }
      unset($db_products);
      
      if(!empty($data)){
         $array_return['html'] .= '<p class="sale_label">' . translate_text('Thuốc trong kho') . '</p>';
         foreach($data  as $dpid => $row){
            $idnot_in[] = $row['usp_dat_id'];
            $unit = '';
            if($row['usp_specifi'] > 0 && $row['usp_unit_import'] > 0 && $row['usp_unit'] > 0){
               $unit_import   = isset($array_unit[$row['usp_unit_import']])? $array_unit[$row['usp_unit_import']] : '';
               $unit_name     = isset($array_unit[$row['usp_unit']])? $array_unit[$row['usp_unit']] : '';
               $unit = '<i class="item_unit sunit">'. (($row['usp_specifi'] > 1)? $unit_import . ' '. $row['usp_specifi'] . ' ' . $unit_name : $unit_name) .'</i>';
            }
            $array_return['html'] .= '<p class="key_products" data-datid="'. $row['usp_dat_id'] .'" data-id="'. $row['usp_id'] .'" data-name="'. $row['usp_pro_name'] .'" data-price="'. $row['usp_price'] .'" data-quantity="'. $row['usp_quantity'] .'">                     
                                       <span class="product_name" onclick="addToOrder(this);">'. $row['usp_pro_name'] . $unit .'</span>
                                    </p>';   
            $array_return['count'] += 1;
         }
      }  
      
      // nếu trong kho có ít hơn 5 thuốc thì lấy thêm trong data
      if(count($data) < 6){
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
            $array_return['html'] .= '<p class="sale_label">' . translate_text('Bán và Yêu cầu nhập thuốc vào kho') . '</p>';
            foreach($data as $did => $row_d){       
               $unit = '';
               if($row_d['dat_specifi'] > 0 && $row_d['dat_unit_import'] > 0 && $row_d['dat_unit'] > 0){
                  $unit_import   = isset($array_unit[$row_d['dat_unit_import']])? $array_unit[$row_d['dat_unit_import']] : '';
                  $unit_name     = isset($array_unit[$row_d['dat_unit']])? $array_unit[$row_d['dat_unit']] : '';                  
                  $unit = '<i class="item_unit sunit">'. (($row_d['dat_specifi'] > 1)?  $unit_import .' ' . $row_d['dat_specifi'] . ' ' . $unit_name : $unit_name) .'</i>';
               }
               $array_return['html'] .= '<p class="key_products" data-datid="'. $row_d['dat_id'] .'"  data-name="'. $row_d['dat_name'] .'" data-price="0" data-quantity="0">                     
                                          <span class="product_name" onclick="addToOrder(this);">'. $row_d['dat_name'] . $unit .'</span>
                                       </p>';  
               $array_return['count'] += 1;
            }
         } 
         
      }
      
      $array_return['html'] .= '</li></ul>';
      
      if($array_return['count'] > 0) $array_return['status'] = 1;
      if($array_return['count'] == 0){
         $array_return['html'] = '<ul class="listProduct">
                                    <li>
                                       <div class="add_new_pro">
                                          <p>' . translate_text('Chúng tôi không tìm thấy thuốc nào như') . ' <b>'. $keyword .'</b>. <span onclick="add_product();" class="btn_yes"><i class="icon_add">+</i>' . translate_text('Thêm thuốc vào kho.') .'</span></p>
                                       </div>
                                    </li>
                                  </ul>';
      }
      
      return $array_return;
      
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
                                     WHERE uso_id = " . $sale_id . " LIMIT 1");
         if($row_sale  = mysqli_fetch_assoc($db_check->result)){
            // check xem có phải bản ghi của mình không, của mình mới đc xóa không thì thôi
            if($row_sale['uso_use_parent_id'] != $admin_id || $row_sale['uso_branch_id'] != $branch_id || $row_sale['uso_use_child_id'] != $child_id){
               $arrayReturn['error'] = 'Bạn không có quyền xóa thông tin bán hàng này';
            }
            
            // có quyền xóa rồi thì kiểm tra có còn thời hạn được xóa không (nếu đẫ chốt sổ thì không đc xóa)
            $time_check = 0;
            // lấy thông tin thời gian cuối cùng gửi tin chốt sổ
            $db_close   = new db_query("SELECT ucb_end_time_close FROM user_close_book
                                          WHERE ucb_user_id = " . intval($child_id) . " 
                                          AND ucb_parent_id = " . $admin_id . "
                                          ORDER BY ucb_id DESC LIMIT 1");
            if($row_check = mysqli_fetch_assoc($db_close->result)){
               $time_check = $row_check['ucb_end_time_close'];
            }
            unset($db_close);
            
            if($row_sale['uso_date'] <= $time_check){
               $arrayReturn['error'] = 'Dòng dữ liệu đã bị khóa bạn không có quyền xóa';
            }else{
               $pro_id     = $row_sale['uso_pro_id'];
               $quantity   = $row_sale['uso_quantity'];
               
               // thực hiện xóa bản ghi và trả lại số lượng cho bảng nhập kho               
               $db_del  = new db_execute("DELETE FROM " . USER_SALE . " WHERE uso_id = " . $sale_id);
               unset($db_del);
               
               // cập nhật trong danh sách product
               $db_update  = new db_execute("UPDATE " . USER_PRODUCTS . " 
                                             SET usp_quantity = usp_quantity + " . $quantity ."
                                             WHERE usp_id = " . $pro_id);
               unset($db_update);
               
               
               // bắt đầu tính toán trừ số lượng
               $db_select = new db_query("SELECT * FROM ". USER_STOCK ." 
                                           WHERE uss_use_parent_id = " . intval($admin_id) . "
                                                AND uss_branch_id = ". intval($branch_id) . "
                                                AND uss_pro_id = " . intval($pro_id) . "
                                                AND uss_sold >= 0
                                                ORDER BY uss_id ASC", __FILE__ ." Line: " . __LINE__);
               while($row   = mysqli_fetch_assoc($db_select->result)){
                  $dat_id     = $row['uss_dat_id'];
                  if(($row['uss_sold'] + $quantity) <=  $row['uss_quantity']){         
                     // trờ số lượng
                     $db_update  = new db_execute("UPDATE ". USER_STOCK ." SET uss_sold = uss_sold + " . intval($quantity) . "
                                                   WHERE uss_id = " . $row["uss_id"] . " 
                                                            AND uss_use_parent_id = " . intval($admin_id));
                     unset($db_update);     
                     break;
                  }else{
                     $quantity   = ($quantity + $row['uss_sold']) - $row['uss_quantity'];
                     $db_update  = new db_execute("UPDATE ". USER_STOCK ." SET uss_sold = uss_quantity
                                                   WHERE uss_id = " . $row["uss_id"] . " 
                                                            AND uss_use_parent_id = " . intval($admin_id));
                     unset($db_update);
                  }
               }
               unset($db_select);
               
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
      
      
      
      $pro_id        = isset($data['pro_id'])? intval($data['pro_id']) : 0;
      
      if($pro_id <= 0){
         $arrayReturn['error'] = 'Thông tin không đúng';
         return $arrayReturn; 
      }
      
      // kiểm tra thông tin sản phẩm có phải của admin không không phải thì báo không có quyền
      $db_products   = new db_query("  SELECT * FROM " . USER_PRODUCTS . "
                                       WHERE usp_id = " . intval($pro_id) . "
                                       AND usp_use_parent_id = " . intval($admin_id) . "
                                       AND usp_branch_id = " . $branch_id . "
                                       LIMIT 1");
      if($row  = mysqli_fetch_assoc($db_products->result)){
         // kiểm tra sản phẩm này có trong đơn hàng tạm chưa. chưa thì thêm mới không thì cộng thêm vào
         $db_order   = new db_query("SELECT * FROM " . USER_SALE . "
                                     WHERE uso_branch_id = " . intval($branch_id) . " 
                                     AND uso_use_parent_id = " . intval($admin_id) . " 
                                     AND uso_use_child_id = " . intval($myuser->u_id) . "
                                     AND uso_pro_id = " . intval($pro_id) . "
                                     AND uso_status = 0 LIMIT 1");
         if($row_order  = mysqli_fetch_assoc($db_order->result)){
            // có rồi thì chỉ cập nhật lại số lượng và giá tiền bán
            $quantity      = $row_order['uso_quantity'];
            $total_money   = ($quantity + 1) * $row_order['uso_price_out'];
                               
            $db_ex   = new db_execute("UPDATE " . USER_SALE . "
                                       SET uso_quantity = uso_quantity + 1,
                                       uso_total_money = " . $total_money . "
                                       WHERE uso_id = " . $row_order['uso_id']);
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
            $oid = $db_ex->db_execute("INSERT INTO " . USER_SALE . " 
                                          (uso_pro_id,uso_dat_id,uso_branch_id,uso_use_parent_id,uso_use_child_id,uso_date,uso_quantity,uso_price_out,uso_price_import,uso_total_money)
                                       VALUES(". $pro_id .",". $row['usp_dat_id'] .",". $branch_id .",". $admin_id .",". $myuser->u_id .",". time() .",1,". $pr .",0,". $pr .")");
            
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
                                                <p><input autocomplete="off" maxlength="8" name="quantity['. $oid .']" onblur="setUpdate(this); setTotalPrice();" title="Số lượng '. $unit .'" data-type="quantity" class="tooltip number item_order_quantity type_number text_c" id="item_order_quantity_'. $oid .'" type="tel" data-vl="1" value="1" /></p>
                                             </td>
                                             <td width="70" class="text_r">
                                                <p><input autocomplete="off" maxlength="10" name="price['. $oid .']" onblur="setUpdate(this); setTotalPrice();" title="Giá bán / 1 '. $unit .'" data-type="price_out" class="tooltip number item_order_price type_number text_r" id="item_order_price_'. $oid .'" type="tel" data-vl="'. $row['usp_price'] .'" data-update="'. (($row['usp_price'] == 0)? 1 : 0) .'" value="'. format_number($row['usp_price']) .'" /></p>
                                             </td>
                                             <td width="" class="text_r">
                                                <input type="tel" autocomplete="off" data-vl="'. $row['usp_price'] .'" maxlength="14" onblur="addClass_text(this); setUpdate(this); setTotalPrice();" data-type="price_total" onfocus="removeClass_text(this);" class="item_order_price_item number type_number text_text" id="item_order_price_item_'. $oid .'" value="'.format_number($row['usp_price']).'" />                                                
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
      //nhân viên nào chỉ sửa được của nhân viên đó
      $sqlWhereChild = " AND uso_use_child_id = " . $myuser->u_id . " ";
      //nếu là admin thì có quyên sửa mọi bản ghi của nhân viên
		if($myuser->isParent()) $sqlWhereChild = "";
		
      $db_check   = new db_query("SELECT * FROM " . USER_SALE . "
                                  WHERE uso_id = " . intval($order_id) . "
                                  AND uso_branch_id = " . intval($branch_id) . " 
                                  AND uso_use_parent_id = " . intval($admin_id) . " 
                                  " . $sqlWhereChild . "
                                  LIMIT 1");
      if($row = mysqli_fetch_assoc($db_check->result)){
         $total_money = $row[$field_caculator] * $value;
         $db_update  = new db_execute("UPDATE " . USER_SALE . " SET ". $field ." = " . intval($value) . ", uso_total_money = ". $total_money ." 
                                       WHERE uso_id = " . intval($order_id));
         if($db_update->total >= 0){
            
            if($update == 1 && $type == 'price_out'){
               $db_uppro   = new db_execute("UPDATE " . USER_PRODUCTS . " SET usp_price = " . intval($value) . "
                                                WHERE usp_id = " . intval($row['uso_pro_id']));
               unset($db_uppro);
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
   function sale_update_order_item($oid_id = 0, $quantity = 0, $price_out = 0){
      if($oid_id <= 0 || $quantity <= 0 || $price_out <= 0) return 0;
      global $myuser, $admin_id, $branch_id;
      $old_quantity  = $quantity;
      $moneyImport   = 0;
      $dat_id        = 0;
      $pro_id        = 0;
      //nhân viên nào chỉ sửa được của nhân viên đó
      $sqlWhereChild = " AND uso_use_child_id = " . $myuser->u_id . " ";
      //nếu là admin thì có quyên sửa mọi bản ghi của nhân viên
		if($myuser->isParent()) $sqlWhereChild = "";
      // lấy thông tin sản phẩm
      $db_order = new db_query("SELECT * FROM " . USER_SALE . " 
                                    WHERE uso_id = " . intval($oid_id) . "
                                    AND uso_branch_id = " . intval($branch_id) . "
                                    AND uso_use_parent_id = " . intval($admin_id) . $sqlWhereChild . " 
                                    LIMIT 1");
      if($row_order = mysqli_fetch_assoc($db_order->result)){
         $pro_id  = $row_order['uso_pro_id'];
         // update bảng user pro set quantity giảm đi);
         $db_sale   = new db_execute("UPDATE " . USER_SALE . " 
                                       SET uso_quantity = ". intval($old_quantity) . ",
                                       uso_price_out=". intval($price_out) . ",
                                       uso_total_money = ". intval($old_quantity*$price_out) ."
                                       WHERE uso_id = " . intval($oid_id));
         unset($db_sale);
         
         // update số lượng bảng product
         $sl = $row_order['uso_quantity'] - $old_quantity;
         $db_pro  = new db_execute("UPDATE " . USER_PRODUCTS . " SET usp_quantity = usp_quantity + (" . $sl . "),usp_lats_update = ". time() ." 
                                    WHERE usp_id = " . intval($pro_id) . " AND usp_use_parent_id = " . intval($admin_id));
         
         if($sl < 0){
            /*
             sl < 0 tức là số lượng thay đổi > số lượng cũ => select record có sold > 0 order by sold asc
             - sold - số lượng mới
            */
            $new_quantity  = intval(abs($sl));
            $db_select = new db_query("SELECT * FROM ". USER_STOCK ." 
                                       WHERE uss_use_parent_id = " . intval($admin_id) . "
                                       AND uss_branch_id = ". intval($branch_id) . "
                                       AND uss_pro_id = " . intval($row_order['uso_pro_id']) . "
                                       AND uss_sold > 0
                                       ORDER BY uss_sold ASC, uss_id ASC", __FILE__ ." Line: " . __LINE__);
            while($rstock = mysqli_fetch_assoc($db_select->result)){
               $sold = $rstock['uss_sold'];
               if($sold - $new_quantity >= 0){
                  // nếu số lượng còn lại trừ số lương mới vừa đủ hoặc thừa thì thoát luôn
                  $db_update  = new db_execute("UPDATE ". USER_STOCK ." SET uss_sold = uss_sold - " . $new_quantity . "
                                                WHERE uss_id = " . $rstock["uss_id"] . " 
                                                AND uss_use_parent_id = " . intval($admin_id));
                  unset($db_update);   
                  break;
               }else{
                  $new_quantity = $new_quantity - $rstock['uss_sold']; 
                  // nếu số lượng tồn trừ số lượng mới mà còn thiếu thì trừ tiếp của dòng dữ liệu khác
                  $db_update  = new db_execute("UPDATE ". USER_STOCK ." SET uss_sold = 0
                                                WHERE uss_id = " . $rstock["uss_id"] . " 
                                                AND uss_use_parent_id = " . intval($admin_id));
                  unset($db_update);                  
               }
            }
            unset($db_select);
         }else{
            /* 
               sl > 0 tức là số lượng thay đổi < số lượng cũ => select record sold < quantity order by sold desc 
               sold + thêm số lương đến khi bằng số lượng nhập
            */ 
            $db_select = new db_query("SELECT * FROM ". USER_STOCK ." 
                                       WHERE uss_use_parent_id = " . intval($admin_id) . "
                                       AND uss_branch_id = ". intval($branch_id) . "
                                       AND uss_pro_id = " . intval($row_order['uso_pro_id']) . "
                                       AND uss_sold < uss_quantity
                                       ORDER BY uss_sold DESC, uss_id DESC", __FILE__ ." Line: " . __LINE__);
            while($rstock  = mysqli_fetch_assoc($db_select->result)){
               if($rstock['uss_sold'] + $sl <= $rstock['uss_quantity']){
                  // nếu số tồn + số mới <= số lượng nhập thì vừa đủ ok, thực hiện và thoát
                  $db_update  = new db_execute("UPDATE ". USER_STOCK ." SET uss_sold = uss_sold + ". $sl . "
                                                WHERE uss_id = " . $rstock["uss_id"] . " 
                                                AND uss_use_parent_id = " . intval($admin_id));
                  unset($db_update);  
                  break;
               }else{
                  
                  $sl = $sl - ($rstock['uss_quantity'] - $rstock['uss_sold']);
                  // nếu số tồn + số mới > số nhập thì set số tồn = số nhập và tiến hành cộng tiếp vào recode tiếp theo
                  $db_update  = new db_execute("UPDATE ". USER_STOCK ." SET uss_sold = uss_quantity
                                                WHERE uss_id = " . $rstock["uss_id"] . " 
                                                AND uss_use_parent_id = " . intval($admin_id));
                  unset($db_update);  
               }
            }
            unset($db_select);
         }
      }
      
      // thêm vào bảng user order
      if($db_pro->total > 0){      
         return 1;
      }elseif($db_pro->total == 0){
         return 2;
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
   function sale_caculator_stock($oid_id = 0, $quantity = 0, $price_out = 0){
      if($oid_id <= 0 || $quantity <= 0 || $price_out <= 0) return 0;
      global $myuser, $admin_id, $branch_id;
      $old_quantity  = $quantity;
      $moneyImport   = 0;
      $dat_id        = 0;
      $pro_id        = 0;
      
      // lấy thông tin sản phẩm
      $db_order = new db_query("SELECT * FROM " . USER_SALE . " 
                                    WHERE uso_id = " . intval($oid_id) . "
                                    AND uso_branch_id = " . intval($branch_id) . "
                                    AND uso_use_parent_id = " . intval($admin_id). " 
                                    AND uso_use_child_id = " . intval($myuser->u_id) . "
                                    AND uso_status = 0 LIMIT 1");
      if($row_order = mysqli_fetch_assoc($db_order->result)){
         $pro_id  = $row_order['uso_pro_id'];
         // bắt đầu tính toán trừ số lượng
         $db_select = new db_query("SELECT * FROM ". USER_STOCK ." 
                                     WHERE uss_use_parent_id = " . intval($admin_id) . "
                                          AND uss_branch_id = ". intval($branch_id) . "
                                          AND uss_pro_id = " . intval($row_order['uso_pro_id']) . "
                                          AND uss_sold > 0
                                          ORDER BY uss_id ASC", __FILE__ ." Line: " . __LINE__);
         while($row   = mysqli_fetch_assoc($db_select->result)){
            $dat_id     = $row['uss_dat_id'];
            if($row['uss_sold'] >= $quantity){         
               // trờ số lượng
               $db_update  = new db_execute("UPDATE ". USER_STOCK ." SET uss_sold = uss_sold - " . intval($quantity) . "
                                             WHERE uss_id = " . $row["uss_id"] . " 
                                                      AND uss_use_parent_id = " . intval($admin_id));
               unset($db_update);         
               $moneyImport += $row['uss_price_import'] * $quantity;
               break;
            }else{
               $quantity   = $quantity - $row['uss_sold'];
               $db_update  = new db_execute("UPDATE ". USER_STOCK ." SET uss_sold = 0 
                                             WHERE uss_id = " . $row["uss_id"] . " 
                                                      AND uss_use_parent_id = " . intval($admin_id));
               unset($db_update);
               $moneyImport += $row['uss_price_import'] * $row['uss_sold'];
            }
         }
         unset($db_select);
      }
      
      // thêm vào bảng user order
      if($moneyImport >= 0){
         $total_money   = $old_quantity * $price_out;
         $db_order   = new db_execute("UPDATE " . USER_SALE . " 
                                       SET uso_quantity = ". intval($old_quantity) . ",
                                       uso_price_out=". intval($price_out) . ",
                                       uso_price_import =". intval($moneyImport) .",
                                       uso_total_money = ". intval($total_money) .",
                                       uso_status = 1 
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
   }
   
   /**
    * hàm thêm sản phẩm từ data sang product
    * array(
    *    dat_id => 
    * )
    * return pro_id
    */
    
   function import_data_to_product($data = array()){
      global   $admin_id, $branch_id, $myuser;
      $dat_id  = isset($data['dat_id'])? intval($data['dat_id']) : 0;
      
      if($dat_id <= 0) return 0;
      $pro_id  = 0; // mặc định product id
      $child_id   = $myuser->u_id;
      
      // lấy thông tin data lưu sang bảng product
      
      $sql = "INSERT INTO ". USER_PRODUCTS ."(usp_dat_id,usp_pro_name,usp_barcode,usp_me_barcode,usp_unit,usp_unit_import,usp_specifi,usp_branch_id,usp_use_parent_id,usp_use_child_id,usp_quantity,usp_price,usp_active,usp_lats_update,usp_date_expires,usp_dat_active) 
               SELECT dat_id,dat_name,dat_barcode,dat_me_barcode,dat_unit,dat_unit_import,dat_specifi,". $branch_id .",". $admin_id .",". $child_id .",0,dat_price_out,1,". time() .",0,dat_active FROM datas WHERE dat_id = ". $dat_id ;
      $db_ex   = new db_execute_return();
      $pro_id  = $db_ex->db_execute($sql);
      unset($db_ex);
      return $pro_id;
   }
   
   /**
    * Hàm thêm vào bảng stocks
    * $data = array(
    *    pro_id => 
    *    quantity =>  
    * )
    */
   function import_add_to_stocks($data = array()){
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
      
      
      
      $pro_id        = isset($data['pro_id'])? intval($data['pro_id']) : 0;
      
      if($pro_id <= 0){
         $arrayReturn['error'] = 'Thông tin không đúng';
         return $arrayReturn; 
      }
      
      // kiểm tra thông tin sản phẩm có phải của admin không không phải thì báo không có quyền
      $db_products   = new db_query("  SELECT * FROM " . USER_PRODUCTS . "
                                       WHERE usp_id = " . intval($pro_id) . "
                                       AND usp_use_parent_id = " . intval($admin_id) . "
                                       AND usp_branch_id = " . $branch_id . "
                                       LIMIT 1");
      if($row  = mysqli_fetch_assoc($db_products->result)){
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
                                       uss_quantity = " . $quantity . ",
                                       uss_sold = ". $quantity ."
                                       WHERE uss_id = " . $row_order['uss_id']);
            if($db_ex->total > 0){
               $arrayReturn['status'] = 1;
               $arrayReturn['oid'] = $row_order['uss_id'];
            }else{
               $arrayReturn['error'] = 'Không cập nhật thêm số lượng được';         
            }
            unset($db_ex);
         }else{
            // nếu đúng của mình thì thêm vào bảng order
            $db_ex   = new db_execute_return();
            $price_export   = ($row['usp_price'] > 0) ? $row['usp_price'] : 0;
            $price_import   = isset($data['price_import'])? $data['price_import'] : 0;
            
            // suggess giá nhập
            $db_import  = new db_query("SELECT * FROM " . USER_STOCK . "
                                        WHERE uss_branch_id = " . intval($branch_id) . " 
                                        AND uss_use_parent_id = " . intval($admin_id) . " 
                                        AND uss_pro_id = " . intval($pro_id) . "
                                        AND uss_status = 1 ORDER BY uss_id DESC LIMIT 1");
            if($rim  = mysqli_fetch_assoc($db_import->result)){
               $price_import  = $rim['uss_price_import'];
               $price_export  = ($rim['uss_price_out'] > 0)? $rim['uss_price_out'] : $price_export;
            }
            unset($db_import);
            
            $quantity   = $row['usp_specifi'] * 1;
            $specifi    = $row['usp_specifi'];
            
            // thêm vào bảng stocks
            $oid = $db_ex->db_execute("INSERT INTO " . USER_STOCK . " 
                                          (uss_date,uss_date_expires,uss_use_parent_id,uss_use_child_id,uss_price_import,uss_price_out,uss_quantity,uss_unit,uss_pro_id,uss_dat_id,uss_branch_id,uss_sold,uss_status,uss_quantity_unit_parent,uss_quantity_unit_child)
                                       VALUES(". time() .",0,". $admin_id .",". $myuser->u_id .",". $price_import .",". $price_export ."," . $quantity .",". $row['usp_unit'] .",". $row['usp_id']. ",". $row['usp_dat_id'] .",". $branch_id .",". $quantity .",0,1,". $specifi .")");
            
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
                                             
                                             <td width="70" class="text_r">
                                                <p><input autocomplete="off" maxlength="10" name="price_import['. $oid .']" onblur="setUpdate(this); setTotalPrice();" title="Giá nhập / 1 '. $unit .'" data-type="price_import" class="tooltip number item_order_price price_import type_number text_r" id="item_order_price_import_'. $oid .'" type="tel" data-vl="'. $price_import .'" data-update="'. (($price_import == 0)? 1 : 0) .'" value="'. format_number($price_import) .'" /></p>
                                             </td>
                                             <td width="70" class="text_r">
                                                <p><input autocomplete="off" maxlength="10" name="price_export['. $oid .']" onblur="setUpdate(this); setTotalPrice();" title="Giá bán / 1 '. $unit .'" data-type="price_export" class="tooltip number item_order_price price_out type_number text_r" id="item_order_price_export_'. $oid .'" type="tel" data-vl="'. $price_export .'" data-update="'. (($price_export == 0)? 1 : 0) .'" value="'. format_number($price_export) .'" /></p>
                                             </td>
                                             <td width="" class="text_r">
                                                <span class="ww price item_order_price_item" id="item_order_price_item_'. $oid .'">'. format_number($total_money) .'</span>
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
      unset($db_products);
      
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
                                  WHERE uss_id = " . $stocks_id . "
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
               $field_update = " uss_price_import =" . $value;
               break;
            case 'price_export':
               $field_update = " uss_price_out =" . $value;
               break;
         }
         
         // cập nhật thay đổi
         $db_ex   = new db_execute("UPDATE " . USER_STOCK . " SET " . $field_update . " WHERE uss_id = " . $stocks_id);
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
      $price_import  = isset($data['price_import'])? intval($data['price_import']) : 0;
      $price_export  = isset($data['price_export'])? intval($data['price_export']) : 0;
      
      if($stocks_id <= 0 || $quantity_parent <= 0 || $quantity_child <= 0 || $price_import < 0 || $price_export <= 0) return 0;
      
      // kiểm tra trong stocks có phải của mình không, không phải thì báo không có quyền
      $db_check   = new db_query("SELECT * FROM ". USER_STOCK . " 
                                  WHERE uss_id = " . $stocks_id . "
                                  LIMIT 1");
      if($row  = mysqli_fetch_assoc($db_check->result)){
         if($row['uss_use_parent_id'] != $admin_id || $row['uss_use_child_id'] != $child_id || $row['uss_branch_id'] != $branch_id){
            return 0;
         }
         
         // đúng là của mình thì được cập nhật
         $toal_quantity = $quantity_parent * $quantity_child;
         $db_ex   = new db_execute("UPDATE " . USER_STOCK . " SET 
                                    uss_quantity = ". $toal_quantity .",
                                    uss_quantity_unit_parent = ". $quantity_parent . ",
                                    uss_quantity_unit_child = ". $quantity_child . ",
                                    uss_price_import = ". $price_import . ",
                                    uss_price_out = ". $price_export .",
                                    uss_sold = ". $toal_quantity .",
                                    uss_status = 1
                                    WHERE uss_id = " . $stocks_id);                                    
         unset($db_ex);
         
         // cập nhật giá bán mới nhất cho bảng product
         $db_pro     = new db_execute("UPDATE " . USER_PRODUCTS . "
                                       SET usp_price = " . $price_export . ",
                                       usp_quantity = usp_quantity + " . $toal_quantity .",
                                       usp_active = 1
                                       WHERE usp_id = " . $row['uss_pro_id']);
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
      global $admin_id, $branch_id, $myuser;
      
      $pro_id  = 0;
      
      $pro_name   = isset($data['pro_name'])? trim($data['pro_name']) : '';
      $pro_unit   = isset($data['pro_unit'])? intval($data['pro_unit']) : 0;
      $pro_specifi   = isset($data['pro_specifi'])? intval($data['pro_specifi']) : 1;
      $pro_unit_import   = isset($data['pro_unit_import'])? intval($data['pro_unit_import']) : 0;
      $pro_barcode   = isset($data['pro_barcode'])? $data['pro_barcode'] : '';
      $pro_price   = isset($data['pro_price'])? intval(parse_type_number($data['pro_price'])) : 0;
      
      if($pro_name == '' || $pro_unit <= 0 || $pro_unit_import <= 0 || $pro_price <= 0) return 0;
      
      $db_datas   = new db_execute_return();
      $dat_id     = $db_datas->db_execute("INSERT INTO datas (dat_name,dat_barcode,dat_unit,dat_unit_import,dat_specifi, dat_use_parent_id, dat_price_out)
                                          VALUES('". replaceMQ($pro_name) ."','". replaceMQ($pro_barcode) ."',". $pro_unit .",". $pro_unit_import .",". $pro_specifi .",". $admin_id .",". $pro_price .")");
      if($dat_id > 0){
         // tạo barcode
         $mecode  = 100000000000 + $dat_id;
         $me_barcode = generate_me_barcode($mecode);
         
         $db_update  = new db_execute("UPDATE datas SET dat_me_barcode = '". $me_barcode ."' WHERE dat_id =" . $dat_id);
         unset($db_update);
         
         $pro_id  = $this->import_data_to_product(array('dat_id' => $dat_id));
         
         if($pro_id > 0) return $pro_id;
         
      }
      unset($db_datas);
      
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
      $price_import  = isset($data['price_import'])? intval(parse_type_number($data['price_import'])) : 0;
      $price_export  = isset($data['price_export'])? intval(parse_type_number($data['price_export'])) : 0;
      
      if($stocks_id <= 0 || $quantity_parent <= 0 || $quantity_child <= 0 || $price_import < 0 || $price_export <= 0) return $status;
      
      // kiểm tra trong stocks có phải của mình không, không phải thì báo không có quyền
      $db_check   = new db_query("SELECT * FROM ". USER_STOCK . " 
                                  WHERE uss_id = " . $stocks_id . "
                                  LIMIT 1");
      if($row  = mysqli_fetch_assoc($db_check->result)){
         if($row['uss_use_parent_id'] != $admin_id || $row['uss_branch_id'] != $branch_id){
            return 0;
         }
         
         
         
         // đã là của mình
         $total_quantity   = $quantity_parent * $quantity_child;
         $quantity_pro  = $total_quantity - $row['uss_quantity'];
         
         if($row['uss_quantity'] != $total_quantity || $row['uss_price_out'] != $price_export){
            //update product
            $db_pro  = new db_execute("UPDATE " . USER_PRODUCTS . " SET 
                                       usp_quantity = usp_quantity + " . $quantity_pro .",
                                       usp_price = " . $price_export . "
                                       WHERE usp_id = " . $row['uss_pro_id']);
            unset($db_pro);
         }
         
         if($row['uss_quantity'] != $total_quantity || $row['uss_price_out'] != $price_export || $row['uss_quantity_unit_parent'] != $quantity_parent || $row['uss_quantity_unit_child'] != $quantity_child || $row['uss_price_import'] != $price_import){
            // cập nhật kho hàng
            $sql  = "UPDATE " . USER_STOCK ." SET 
                                          uss_quantity = " . $total_quantity . ",
                                          uss_sold = ". $total_quantity . ",
                                          uss_price_out = " . $price_export . ",
                                          uss_price_import = " . $price_import . ",
                                          uss_quantity_unit_parent = " . $quantity_parent . ",
                                          uss_quantity_unit_child = " . $quantity_child ."
                                          WHERE uss_id = " . $stocks_id;
            //file_put_contents('../logs/a.txt', $sql);
            $db_stocks = new db_execute($sql);
            if($db_stocks->total > 0){
               $status = 1; 
            }
         }else{
            $status = 1;
         }
            
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
                                  WHERE uss_id = " . $stocks_id . " LIMIT 1");
      if($row  = mysqli_fetch_assoc($db_check->result)){
         
         $quantity   = $row['uss_quantity'];
         $pro_id     = $row['uss_pro_id'];
         if($row['uss_use_parent_id'] ==  $admin_id && $row['uss_branch_id'] == $branch_id && ($row['uss_use_child_id'] == $myuser->u_id || $myuser->u_parent_id == $admin_id)){
            $db_ex   = new db_execute("DELETE FROM " . USER_STOCK . " 
                                       WHERE uss_id = " . $stocks_id);
            unset($db_ex);
            
            //cập nhật số lượng
            $db_update  = new db_execute("UPDATE ". USER_PRODUCTS . "
                                          SET usp_quantity = usp_quantity - " . $quantity . " 
                                          WHERE usp_id = " . $pro_id);
            unset($db_update);
            
            $arrayReturn['status'] = 1;
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
      global $admin_id, $branch_id, $myuser,$pathRoot;
      
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
      $db_pro  = new db_query("SELECT* FROM " . USER_PRODUCTS . "
                               WHERE usp_id = " . $pro_id . " AND usp_use_parent_id = ". $admin_id ."
                               LIMIT 1");
      if($row  = mysqli_fetch_assoc($db_pro->result)){
         
         // kiểm tra trong bảng có chưa có thềm cộng thêm, chưa thì thêm mới
         $db_check = new db_query("SELECT * FROM temp_stocks_move 
                                   WHERE tsm_use_parent_id = " . $admin_id . "
                                   AND tsm_branch_from = " . $branch_id . "
                                   AND tsm_pro_id = " . $pro_id . "
                                   AND tsm_status =" . MOVE_STOCKS_ZERO . "
                                   LIMIT 1");
         if($rcheck  = mysqli_fetch_assoc($db_check->result)){
            $db_update  = new db_execute("UPDATE temp_stocks_move SET
                                          tsm_quantity_parent = tsm_quantity_parent + 1,
                                          tsm_quantity = tsm_quantity_parent * tsm_quantity_child
                                          WHERE tsm_id = " . $rcheck['tsm_id']);
            if($db_update->total > 0){
               $arrayReturn['status'] = 1;
               $arrayReturn['url'] = $pathRoot . 'stocks_move.php?st=move';
            }
            unset($db_update);
            
         }else{
            // thêm mới vào bảng temp_stocks
            $db_insert  = new db_execute_return();         
            $id = $db_insert->db_execute("
               INSERT INTO temp_stocks_move (tsm_use_parent_id,tsm_branch_from,tsm_pro_id,tsm_quantity_parent,tsm_quantity_child,tsm_quantity,tsm_date,tsm_use_move)
               SELECT ". $admin_id .",". $branch_id .",". $pro_id .",1,".$row['usp_specifi']. ",". $row['usp_specifi']. ",". time() .",". $myuser->u_id ." FROM ". USER_PRODUCTS ." WHERE usp_id = " . $pro_id);
            unset($db_insert);
            
            if($id > 0){
               $arrayReturn['status'] = 1;
               $arrayReturn['url'] = $pathRoot . 'stocks_move.php?st=move';
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
                                   WHERE tsm_use_parent_id = " . $admin_id . "
                                   AND tsm_branch_from = " . $branch_id . "
                                   AND tsm_id = " . $tsm_id . "
                                   LIMIT 1");
      if($row  = mysqli_fetch_assoc($db_check->result)){
         
         if($row['tsm_status'] == MOVE_STOCKS_ACCESS){
            $arrayReturn['error'] = 'Hàng đã được chuyển bạn không được xóa';
            return $arrayReturn;
         }
         // xóa
         $db_del  = new db_execute("DELETE FROM temp_stocks_move WHERE tsm_id = " . $tsm_id);
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
   
}