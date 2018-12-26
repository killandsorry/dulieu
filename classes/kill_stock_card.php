<?

class killStockCard{
   
   function __construct(){
      
   }
   
   /**
    * Function addStockCard
    * input $_POST
    * 
    * return array(
    * 
    *    code => 200 || 500
    *    error => 'error'
    * ) 
    * 
    * Step process
    * 1: Insert table user_stock_card_
    * 2: Caculator coc, remain,
    * 3: Update table user_product_ remain, price_import, coc
    * 4: Insert table user_bill_
    * 5: Delete table user_warehouse_template (status = 0)
    * 6: Insert table user_provider_pay
    }
    */
   function add_stock_card($dataPost = array()){
      global $killWareHouseTemplate, $killProduct, $killBill, $killProvider; 
      
      $count_success = 0;
      $error         = '';
      
      $result  = array(
         'code' => 500,
         'error' => ''
      );
      
      // lấy các biến mặc định
      $provider_id         = isset($dataPost['provider_id'])? intval($dataPost['provider_id']) : 0;
      $total_amount        = isset($dataPost['total_pay'])? parse_type_number($dataPost['total_pay']) : 0;
      $total_paid          = isset($dataPost['total_payed'])? parse_type_number($dataPost['total_payed']) : 0;
      $total_pay_remain    = isset($dataPost['total_remain'])? parse_type_number($dataPost['total_remain']) : 0;
      $bill_code           = isset($dataPost['bill_code'])? intval($dataPost['bill_code']) : date('ymdHi');
      
      // array lưu tạm thông tin insert vào bảng stock_card
      $arrayTemplateStockCard       = array();
      
      // array template update product
      $arrayTemplateUpdateProduct   = array();
      
      // 1: gọi toàn bộ danh sách template đang nhập kho ra
      $dataTemplate     = $killWareHouseTemplate->get_insert_template();
      
      /** 
      ----------------------------------
      Không có dữ liệu thì thoát ra luôn
      ---------------------------------- 
      */
      if(empty($dataTemplate)){
         $result['error'] = 'Không có dữ liệu để thực hiện';
         return $result;
      }
                 
      
      /** 
      ----------------------------------
      Tiếp tục thực hiện khi có dữ liệu
      ---------------------------------- 
      */
      // 2: lặp trong đống dataTemplate check vs dataPost để xem dữ liệu có đúng không
      foreach($dataTemplate as $usw_id => $row){
         $quantity         = $row['usw_number_unit_parent']; // số lượng cha
         $packing          = $row['usw_number_unit_child']; // số lượng con 
         $price_import     = $row['usw_price_import'];
         
         $usc_usp_id       = $row['usw_usp_id']; // id sản phẩm
         $tem_number_in    = $quantity * $packing; // số lượng nhập
         $usc_unit         = $row['usw_unit']; // đơn vin tính bé nhất
         $usc_card_type    = STOCK_CARD_TYPE_IN; // phiếu nhập hàng
         $usc_total_money  = $price_import * $quantity; // tổng tiền nhập
         $tem_price_coc    = floor($price_import / $packing); // giá vốn = gia nhap / quy cách sản phẩm
         $usc_bill_code    = $bill_code;
         $usc_branch_id    = BRANCH_ID;
         $usc_parent_id    = ADMIN_ID;
         $usc_child_id     = CHILD_ID;
         $usc_time         = time();
         $usc_date_expires = $row['usw_date_expires'];
         $usc_lo           = $row['usw_lo'];
         $usc_bill_type    = TYPE_NH;
         $usc_note         = 'Hóa đơn nhập hàng ngày ' . date('d/m/Y');
         
         $usc_number_in    = $tem_number_in; // số lượng nhập
         $usc_price_coc    = $tem_price_coc; // góa vốn
         $usc_remain       = $usc_number_in; // số lượng tồn mặc định bằng số lượng nhập
         
         // 3: lấy thông tin tồn cuối của sản phẩm này rồi cộng thêm số lượng nhập => tồn
         $dataStock        = $this->get_remain($usc_usp_id);
         if($dataStock['code'] == 200){
            $remain        = intval($dataStock['remain']); // tồn cũ
            $coc           = intval($dataStock['coc']); // giá vốn cũ
            $usc_remain    = $usc_number_in + $remain;
            // giá vốn mới = (gia vốn mới * tổng số lượng mới) + (gia vốn cũ * tổng số lượng cũ) / (tổng số lượng mới + tổng số lượng cũ)
            $usc_price_coc = floor((($tem_price_coc * $usc_number_in) + ($remain * $coc)) / ($usc_remain));
         }
         
         // 4: thêm mới stock_card
         $data_stock_insert   = array(            
            'usc_usp_id'       => $usc_usp_id,
            'usc_unit'         => $usc_unit,
            'usc_card_type'    => $usc_card_type,
            'usc_total_money'  => $usc_total_money,
            'usc_bill_code'    => $usc_bill_code,
            'usc_branch_id'    => BRANCH_ID,
            'usc_parent_id'    => ADMIN_ID,
            'usc_child_id'     => CHILD_ID,
            'usc_time'         => $usc_time,
            'usc_date_expires' => $usc_date_expires,
            'usc_lo'           => $usc_lo,
            'usc_bill_type'    => $usc_bill_type,
            'usc_note'         => $usc_note,     
            'usc_number_in'    => $usc_number_in,
            'usc_price_coc'    => $usc_price_coc,
            'usc_remain'       => $usc_remain
         );
         
         /** Thêm từng sản phẩm vào bảng stock_card */
         $stock_id = $this->insert($data_stock_insert);
         
         
         /** Nếu thành công thì ngon */  
         if($stock_id > 0){            
            $arrayTemplateStockCard[$stock_id]  = $stock_id;
            $arrayTemplateUpdateProduct[$row['usw_usp_id']] = array(
               'usp_id'    => $row['usw_usp_id'],
               'remain'    => $usc_remain,
               'coc'       => $usc_price_coc,
               'price_import' => $price_import
            );
            $count_success++;
         }else{
            
            /** Chỉ 1 cái không thành công thì xóa hết các dòng đã thêm trước đó và thoát ra luôn */
            if(!empty($arrayTemplateStockCard)){
               foreach($arrayTemplateStockCard as $sid){
                  $this->delete_stock_id($sid);
               }
            }
            
            $result['error'] = 'Lỗi! ['. $usw_id .']';
            return $result;
         }            
         
      } // end foreach $dataTemplate
      
      //--------------------------------------------------//
      
      
      
      /**
         Mọi thứ đã ok tiến hành các bước cuối
      */
      // 5: nếu count success = count(dataTempate) => thành công
      
      
      if($count_success == count($dataTemplate)){
         
         // cập nhật tồn kho và giá nhập cho bảng product
         if(!empty($arrayTemplateUpdateProduct)){
            foreach($arrayTemplateUpdateProduct as $usp_id => $rowProduct){
               $killProduct->update_coc_remain($rowProduct);
            }
         }
                  
         // xóa bản ghi template
         foreach($dataTemplate as $usw_id => $temdata){
            $killWareHouseTemplate->delete_template($usw_id);   
         }
                     
         // insert vào bảng bill
         $dataBill   = array(
            'usb_bill_number'        => $bill_code,
            'usb_bill_type'          => STOCK_CARD_TYPE_IN,
            'usb_total_money'        => $total_amount,
            'usb_total_coc'          => 0,
            'usb_provider_id'        => $provider_id,
            'usb_customer_id'        => isset($dataPost['customer_id'])? intval($dataPost['customer_id']) : 0,
            'usb_paid'               => $total_paid,
            'usb_pay_remain'         => $total_pay_remain,
            'usb_discount_percent'   => 0,
            'usb_discount_money'     => 0,
            'usb_vat'                => 0,
            'usb_vat_number'         => '',
            'usb_pay_method'         => isset($dataPost['method_pay'])? intval($dataPost['method_pay']) : 0
         );
         
         $response   = $killBill->insert($dataBill);
         if(isset($response['code']) && $response['code'] == 200){
            $result['code']   = 200;
         }else{
            $result['error']   = 'Lỗi! Thêm bill không thành công';
         }
         // insert vào bảng công nợ nhà cung cấp         
         if($provider_id > 0 && $bill_code > 0){         
            $dataPayment   = array(
               'upp_provider_id'  => $provider_id,
               'upp_total_amount' => $total_amount,
               'upp_paid'         => $total_paid,
               'upp_pay_remain'   => $total_pay_remain,
               'upp_bill_code'    => $bill_code,
            );
            $pp =$killProvider->insertPayment($dataPayment);                        
         }    
      } // end check count_success == count(dataTemplate)
      
      return $result;
   }    
   
   /**
    * Function insert data to stock card
    */
   function insert($data = array()){
      $usc_usp_id          = isset($data['usc_usp_id'])? intval($data['usc_usp_id']) : 0;
      $usc_unit            = isset($data['usc_unit'])? intval($data['usc_unit']) : 0;
      $usc_card_type       = isset($data['usc_card_type'])? intval($data['usc_card_type']) : 0;
      $usc_total_money     = isset($data['usc_total_money'])? doubleval($data['usc_total_money']) : 0;
      $usc_bill_code       = isset($data['usc_bill_code'])? intval($data['usc_bill_code']) : date('ymdHi');
      $usc_branch_id       = BRANCH_ID;
      $usc_parent_id       = ADMIN_ID;
      $usc_child_id        = CHILD_ID;
      $usc_time            = time();
      $usc_date_expires    = isset($data['usc_date_expires'])? intval($data['usc_date_expires']) : 0;
      $usc_lo              = isset($data['usc_lo'])? $data['usc_lo'] : '';
      $usc_bill_type       = isset($data['usc_bill_type'])? intval($data['usc_bill_type']) : 0;
      $usc_note            = isset($data['usc_note'])? $data['usc_note'] : '';
      $usc_number_in       = isset($data['usc_number_in'])? intval($data['usc_number_in']) : 0;
      $usc_price_coc       = isset($data['usc_price_coc'])? doubleval($data['usc_price_coc']) : 0;
      $usc_remain          = isset($data['usc_remain'])? intval($data['usc_remain']) : 0;
      
      
      $query   = "
      INSERT IGNORE INTO ". TABLE_STOCK_CARD ."
         (usc_usp_id,usc_unit,usc_card_type,usc_total_money,usc_bill_code,
         usc_branch_id,usc_parent_id,usc_child_id,usc_time,usc_date_expires,
         usc_lo,usc_bill_type,usc_note,usc_number_in,
         usc_price_coc,usc_remain)
      VALUES
         (". $usc_usp_id .",". $usc_unit .",". $usc_card_type .",". $usc_total_money .",". $usc_bill_code .",
         ". $usc_branch_id .",". $usc_parent_id .",". $usc_child_id .",". $usc_time .",". $usc_date_expires .",
         '". replaceMQ($usc_lo) ."',". $usc_bill_type .",'". replaceMQ($usc_note) ."',". $usc_number_in .",
         ". $usc_price_coc .",". $usc_remain .")";
      
      $db_ex   = new db_execute_return();
      $stock_id   = $db_ex->db_execute($query);
      unset($db_ex);
      
      return $stock_id;
      
   }
   
   
   /**
    * 
    * Delete by stock_id
    */
   function delete_stock_id($stock_id = 0){
      $db_ex   = new db_execute("DELETE FROM " . TABLE_STOCK_CARD . "
                                 WHERE usc_id = " . intval($stock_id));
      unset($db_ex);                                 
   }    
   
   /**
    * get_remain:
    * @input : int
    * : usp_id
    * 
    * return quantity remain of product
    */
   function get_remain($usp_id = 0){
      $result  = array(
         'status' => '',
         'code'   => 500,
         'error'  => '',
         'remain' => 0,
         'coc'    => 0
      );
      
      if($usp_id <= 0){
         $result['error']  = 'Không có id sản phẩm';
      }else{
         $db_stock   = new db_query("SELECT usc_remain,usc_price_coc FROM " . TABLE_STOCK_CARD . "
                                       WHERE usc_usp_id = " . intval($usp_id) . "
                                       ORDER BY usc_id DESC LIMIT 1", __FILE__ . " Line: " . __LINE__);
         if($row     = $db_stock->fetch()){
            $result['remain'] = intval($row['usc_remain']);
            $result['coc']    = intval($row['usc_price_coc']);
            $result['code']   = 200;
         }         
         unset($db_stock);                                     
      }
      
      return $result;
   }
   
   /**
    * Quick edit quantity and price
    * @input: array()
    * : field
    * : old_value
    * : new_value
    * : stock_id
    * 
    * @return: array()
    * : code
    * : error
    * 
    * Process:
    * - lấy thông tin bắn vào
    * - lấy bản ghi stock_card
    *    + lấy tổng tiền,
    *    + lấy tổng số lượng nhập
    * - lấy thông tin sản phẩm
    *    + lấy thông tin quy cách, tồn
    * - tạo câu sql update
    * - recount lại số lượng tồn và giá bắt đầu từ id stock này
    */
   function quick_edit_import($data = array()){      
      global $killProduct, $killBill;
      
      $stock_id   = isset($data['stock_id'])? intval($data['stock_id']) : 0;
      $field      = isset($data['field'])? replaceMQ($data['field']) : '';
      $old_value  = isset($data['old_value'])? floatval($data['old_value']) : 0;
      $new_value  = isset($data['new_value'])? floatval($data['new_value']) : 0;
      
      
      $result  = array(
         'code' => 500,
         'error' => ''
      );
      
      
      
      // kiểm tra dữ liệu
      if($stock_id <= 0 || $field == ''){
         $result['error'] = 'Thông tin không đúng';
      }else{
         // check xem dòng dữ liệu này có hay không
         $dataStock  = $this->get_stock_by_id($stock_id);
         
         if($dataStock['code'] == 200){
            
            $bill_code        = isset($dataStock['data']['usc_bill_code'])? intval($dataStock['data']['usc_bill_code']) : 0;
            
            // id sản phẩm
            $usp_id           = isset($dataStock['data']['usc_usp_id'])? intval($dataStock['data']['usc_usp_id']) : 0;
            $dataProduct      = $killProduct->get_product_by_id($usp_id);
            $usp_packing      = isset($dataProduct['usp_packing'])? intval($dataProduct['usp_packing']) : 1;
            
            
            $usc_total_money  = isset($dataStock['data']['usc_total_money'])? floatval($dataStock['data']['usc_total_money']) : 0;
            $usc_price        = ($old_value > 0)? floor($usc_total_money / $old_value) : $usc_total_money; // tính giá tiền / 1 đơn vị
            $total_money      = $usc_total_money; // cập nhật tổng tiền sản phẩm
            
            $usc_number_old   = ($usp_packing > 0)? floor($dataStock['data']['usc_number_in'] / $usp_packing) : $dataStock['data']['usc_number_in'];
            
            // câu sql update sản phẩm
            $sqlUpdate     =  "";
            
            // cách thức tính lại số lượng và giá vốn, quantity => số lượng, price => giá vốn
            $reCountType   = 'quantity';  
            
            // tính tổng tiền
            switch($field){
               case 'number_in':
                  // nếu là thay đổi số lượng => 
                  // tổng tiền bằng số lượng mới * giá tiền cũ
                  $total_money = $usc_price * $new_value;
                  // số lương thực được quy về đơn vị bán nên phải * vs quy cách
                  $new_value     = $new_value * $usp_packing;
                  
                  $sqlUpdate = " usc_total_money = " . $total_money . ", usc_number_in = " .  $new_value; 
                  break;         
               case 'price_in':
                  $reCountType   = 'price'; // tính lại giá vốn
                  // nếu là thay đổi giá => 
                  // tổng tiền = số lượng cũ * giá mới
                  $total_money   = $usc_number_old * $new_value;  
                  $sqlUpdate = " usc_total_money = " . $total_money;                
                  break;
            }
            
            
                            
            // cập nhật bảng stock card
            $db_update  = new db_execute("UPDATE " . TABLE_STOCK_CARD . " SET " .
                                          $sqlUpdate ."
                                          WHERE usc_id = " . $stock_id);
            unset($db_update);                
            $result['code'] = 200;
            
            // cập nhật tổng tiền bảng bill
            $dataBillUpdate   = array(
               'bill_code'    => $bill_code,
               'bill_type'    => STOCK_CARD_TYPE_IN
            );
            $killBill->updateData($dataBillUpdate);
            
            // cập nhật xong và check xem có phải tính lại số lượng và giá vốn không
            if($reCountType != ''){
               $dataRecount   = array(
                  'stock_id'  => $stock_id,
                  'usp_id'    => $usp_id,
                  'recount_type'   => $reCountType
               );
               
               $dataResponseRecount = $this->reCountData($dataRecount);
            }
                                                  
         }else{
            $result['error'] = $dataStock['error'];
         }
      }
      
      return $result;
   }
   
   
   /**
    * Quick edit quantity and price
    * @input: array()
    * : field
    * : old_value
    * : new_value
    * : stock_id
    * 
    * @return: array()
    * : code
    * : error
    */
   function quick_edit_export($data = array()){
      global $killProduct;
      
      $stock_id   = isset($data['stock_id'])? intval($data['stock_id']) : 0;
      $field      = isset($data['field'])? replaceMQ($data['field']) : '';
      $old_value  = isset($data['old_value'])? floatval($data['old_value']) : 0;
      $new_value  = isset($data['new_value'])? floatval($data['new_value']) : 0;
      
      $field_name    = ''; // tên cột cần câp nhật
      $reCountType   = 'quantity'; // cách thức tính lại số lượng và giá vốn, quantity => số lượng, price => giá vốn 
      switch($field){
         case 'number_in':
            $field_name = 'usc_number_in';            
         case 'price_in':
            $field_name = 'usc_total_money';
            break;
      }
      
      $result  = array(
         'code' => 500,
         'error' => ''
      );
      
      // kiểm tra dữ liệu
      if($stock_id <= 0 || $field == ''){
         $result['error'] = 'Thông tin không đúng';
      }else{
         // check xem dòng dữ liệu này có hay không
         $dataStock  = $this->get_stock_by_id($stock_id);
         if($dataStock['code'] == 200){
            
            // id sản phẩm
            $usp_id  = isset($dataStock['data']['usc_usp_id'])? intval($dataStock['data']['usc_usp_id']) : 0;
            // thông tin sản phẩm
            $dataProduct   = $killProduct->get_product_by_id($usp_id);
            $usp_packing   = isset($dataProduct['usp_packing'])? intval($dataProduct['usp_packing']) : 1;
            
            // số lương thực được quy về đơn vị bán nên phải * vs quy cách
            $new_value     = $new_value * $usp_packing;                        
            // cập nhật
            $db_update  = new db_execute("UPDATE " . TABLE_STOCK_CARD . " SET " .
                                          $field_name ." = ". $new_value . "
                                          WHERE usc_id = " . $stock_id);
            unset($db_update);    
            
            $result['code'] == 200;
            
            // cập nhật xong và check xem có phải tính lại số lượng và giá vốn không
            if($reCountType != ''){
               $dataRecount   = array(
                  'stock_id'  => $stock_id,
                  'usp_id'    => $usp_id,
                  'recount_type'   => $reCountType
               );
               
               $dataResponseRecount = $this->reCountData($dataRecount);
            }
                                                  
         }else{
            $result['error'] = $dataStock['error'];
         }
      }
      
      return $result;
   }
   
   
   /**
    * Get total money bill
    * @input : array()
    * :bill_code
    * 
    * @return array()
    * :code
    * :error
    * :data
    */
   function getTotalMoneyBill($data = array()){
      $result  = array(
         'code' => 500,
         'data' => array(),
         'error' => '' 
      );
      
      $bill_code  = isset($data['bill_code'])? intval($data['bill_code']) : 0;
      if($bill_code > 0){
         $db_stock = new db_query("SELECT SUM(usc_total_money) AS total_money 
                                    FROM " . TABLE_STOCK_CARD . "
                                    WHERE usc_bill_code = " . $bill_code);
         if($row  = $db_stock->fetch()){
            $result['data']['total'] = $row['total_money'];
            $result['code'] = 200;
         }else{
            $result['error'] = 'Không có bill';
         }
         unset($db_stock);
      }else{
         $result['error'] = 'Không có bill';
      }
      
      return $result;
   }
   
   /**
    * reCountData
    * @input: array()
    * :stock_id => 
    * :usp_id =>
    * :recount_type
    * 
    * @return: array()
    * code => 200 || 500
    * error => 
    */
   function reCountData($data = array()){
      $result  = array(
         'code' => 500,
         'error' => ''
      );
      
      $stock_id      = isset($data['stock_id'])? intval($data['stock_id']) : 0;
      $recount_type  = isset($data['recount_type'])? replaceMQ($data['recount_type']) : 'quantity';
      $usp_id        = isset($data['usp_id'])? intval($data['usp_id']) : 0;
      
      if($stock_id <= 0 || $recount_type == '' || ($recount_type != 'quantity' && $recount_type != 'price')){
         $result['error'] = 'Thông tin không đúng';
      }else{
         // bắt đầu recount
         
         $f_remain         = 0; // tồn đầu
         $f_price_coc      = 0; // giá vốn đầu
         $f_total_money    = 0; // tổng tiền đầu
         // lấy stock nhỏ hơn id hiện tại gần nhất
         $db_first   = new db_query("SELECT * FROM " . TABLE_STOCK_CARD . "
                                       WHERE usc_usp_id = " . $usp_id . " AND usc_id < " . $stock_id . "
                                       ORDER BY usc_id DESC
                                       LIMIT 1");
         if($rfirst  = $db_first->fetch()){
            $f_remain         = $rfirst['usc_remain'];
            $f_price_coc      = $rfirst['usc_price_coc'];
            $f_total_money    = $rfirst['usc_total_money'];
         }  
         unset($db_first);                                     
         
         /** Tiếp từ id hiện tại đến hết */  
         $dataRow    = array(); // mảng dữ liệu                                              
         $db_stock   = new db_query("SELECT * FROM " . TABLE_STOCK_CARD . "
                                    WHERE usc_usp_id = " . $usp_id . " AND usc_id > " . ($stock_id - 1));
         while($row  = $db_stock->fetch()){
            $dataRow[$row['usc_id']] = $row;
         }  
         unset($db_stock);                  
         
         if(!empty($dataRow)){
            foreach($dataRow as $id => $stock_item){
               switch($stock_item['usc_card_type']){
                  case STOCK_CARD_TYPE_IN:
                     // giá vốn = 2 đợt giá + lại / tổng số lượng
                     $f_price_coc   = floor((($f_price_coc * $f_remain) + $stock_item['usc_total_money']) / ($f_remain + $stock_item['usc_number_in'])); //floor(($f_total_money + $stock_item['usc_total_money']) / ($f_remain + $stock_item['usc_number_in']));
                     $f_remain      +=  $stock_item['usc_number_in'];
                     $f_total_money = $stock_item['usc_total_money'];
                     
                     // set dữ liệu vào dataRoww
                     $dataRow[$id]['usc_remain']      = $f_remain;
                     $dataRow[$id]['usc_price_coc']   = $f_price_coc;
                     
                     break;
                  case STOCK_CARD_TYPE_OUT:
                     // TH bán thì chỉ cần cập nhật số tồn và giá vốn
                     $f_remain      -=  $stock_item['usc_number_out'];
                     $dataRow[$id]['usc_remain']      = $f_remain;
                     $dataRow[$id]['usc_price_coc']   = $f_price_coc;
                     break;
               }
            }
            
            // bắt đầu update dữ liệu
            foreach($dataRow as $id => $stock_item){
               $db_update  = new db_execute("UPDATE " . TABLE_STOCK_CARD . " SET
                                             usc_remain = " . $stock_item['usc_remain'] . ",
                                             usc_price_coc = " . $stock_item['usc_price_coc'] ."
                                             WHERE usc_id = " . $id
                                             );
               unset($db_update);
            }
            
            // update table product remain
            $db_pro  = new db_execute("UPDATE " . TABLE_PRODUCT . " SET
                                       usp_remain = " . $f_remain . "
                                       WHERE usp_id = " . $usp_id);
            unset($db_pro);                                       
         }                
      }
      
      return $result;
   }
   
   
   /**
    * Get stock_cart by id stock_id
    * @input : int
    * :stock_id
    * 
    * @return : array()
    * : code => trạng thái
    * : data => dòng dữ liệu
    * : error => lỗi nếu có
    */
   function get_stock_by_id($id = 0){
      $result  = array(
         'code' => 500,
         'data' => array(),
         'error' => ''
      );
      $stock_id   = intval($id);
      
      if($stock_id <= 0){
         $result['error'] = 'Thông tin không có';
      }else{
         $db_data    = new db_query("SELECT * FROM " . TABLE_STOCK_CARD . "
                                       WHERE usc_id = " . intval($stock_id) .
                                       " LIMIT 1");
         if($row     = $db_data->fetch()){
            $result['data'] = $row;
            $result['code'] = 200;
         }else{
            $result['error'] = 'Không có thông tin này';
         }   
         unset($db_data);                                   
      }
      
      return $result;
   }
}