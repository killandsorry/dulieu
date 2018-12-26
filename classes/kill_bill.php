<?
/**
 * Class process add bill
 */
class killBill{
   
   function __construct(){
      
   }
   
   /**
    * @input: array()
    * : usb_bill_number
    * : usb_bill_type
    * : usb_total_money
    * : usb_total_coc
    * : usb_provider_id
    * : usb_customer_id
    * : usb_paid
    * : usb_pay_remain
    * : usb_discount_percent
    * : usb_discount_money
    * : usb_vat
    * : usb_vat_number
    * : usb_pay_method
    * 
    * return array(
    *    code => 500 || 200
    *    error => ''
    * )
    */
   
   function insert($data = array()){
      
      $result  = array(
         'code' => 500,
         'error' => ''
      );
      
      $usb_bill_number        = isset($data['usb_bill_number'])? intval($data['usb_bill_number']) : date('ymdHi');
      $usb_bill_type          = isset($data['usb_bill_type'])? intval($data['usb_bill_type']) : 0;
      $usb_total_money        = isset($data['usb_total_money'])? doubleval($data['usb_total_money']) : 0;
      $usb_total_coc          = isset($data['usb_total_coc'])? doubleval($data['usb_total_coc']) : 0;
      $usb_provider_id        = isset($data['usb_provider_id'])? intval($data['usb_provider_id']) : 0;
      $usb_customer_id        = isset($data['usb_customer_id'])? intval($data['usb_customer_id']) : 0;
      $usb_paid               = isset($data['usb_paid'])? doubleval($data['usb_paid']) : 0;
      $usb_pay_remain         = isset($data['usb_pay_remain'])? doubleval($data['usb_pay_remain']) : 0;
      $usb_discount_percent   = isset($data['usb_discount_percent'])? floatval($data['usb_discount_percent']) : 0;
      $usb_discount_money     = isset($data['usb_discount_money'])? doubleval($data['usb_discount_money']) : 0;
      $usb_vat                = isset($data['usb_vat'])? floatval($data['usb_vat']) : 0;
      $usb_vat_number         = isset($data['usb_vat_number'])? $data['usb_vat_number'] : '';
      $usb_pay_method         = isset($data['usb_pay_method'])? intval($data['usb_pay_method']) : 0;
      
      if($usb_bill_number > 0 && $usb_total_money > 0){
         $db_ex   = new db_execute_return();
         
         $query   = "
            INSERT IGNORE INTO " . TABLE_BILL . "
            (usb_bill_number,usb_bill_type,usb_total_money,usb_total_coc,
            usb_branch_id,usb_parent_id,usb_child_id,usb_date,
            usb_provider_id,usb_customer_id,usb_paid,usb_pay_remain,
            usb_discount_percent,usb_discount_money,usb_vat,usb_vat_number,
            usb_pay_method)
            VALUES
            (". $usb_bill_number .",". $usb_bill_type .",". doubleval($usb_total_money) .",". doubleval($usb_total_coc) .",
            ". BRANCH_ID .",". ADMIN_ID .",". CHILD_ID .",". time() .",
            ". $usb_provider_id .",". $usb_customer_id .",". doubleval($usb_paid) .",". doubleval($usb_pay_remain) .",
            ". floatval($usb_discount_percent) .",". doubleval($usb_discount_money) .",". floatval($usb_vat) .",'". replaceMQ($usb_vat_number) ."',
            ". $usb_pay_method .")
         ";
         
         $usb_id  = $db_ex->db_execute($query);
         
         if($usb_id > 0){
            $result['code'] = 200;
         }else{
            $result['error'] = 'Thêm mới bill không thành công';
         }
      }else{
         $result['error'] = 'Thông tin hóa đơn và tổng tiền không đúng';
      }
      
      return $result;
   }
   
   /**
    * Function get count record bill
    * return total count 
    */
   function count(){
      
   }
   
   /**
    * Delete bill
    * @input: array()
    * :bill_code
    * :bill_type
    * 
    * @return : array()
    * :code 
    * :data => array(id)
    * :error
    */
   function deleteBill($data = array()){
      $bill_code     = isset($data['bill_code'])? intval($data['bill_code']) : 0;
      $bill_type     = isset($data['bill_type'])? $data['bill_type'] : STOCK_CARD_TYPE_IN;
      
      $result  = array(
         'code' => 500,
         'data' => array(),
         'error' => ''
      );
      
      
      
      return $result;
   }
   
   
   /**
    * @input array()
    * :bill_id
    * :bill_type
    * :page
    * :start_date
    * :end_date
    * :provider_id
    * 
    * Return array(
    *    code => 500 || 200
    *    data => array() // tất cả các dòng dữ liệu sản phẩm thuộc bill đó
    *    total => array(ttm_import, ttm_paid, ttm_remain) // tổng tiền, đã trả, còn lại
    *    count => int() // tổng số sản phẩm trong bill
    *    error => '' // lỗi nếu có
    * )
    */
   function getListBill($data = array()){
      
      $response   = array();
      $result     = array(
         'code'   => 500,
         'data'   => array(),
         'total'  => array(),
         'count'  => 0,
         'error'  => ''
      );
      
      $bill_id    = isset($data['bill_id'])? intval($data['bill_id']) : 0;
      $bill_type  = isset($data['bill_type'])? $data['bill_type'] : STOCK_CARD_TYPE_IN;
      $page       = isset($data['page'])? intval($data['page']) : 1;
      $start_date = isset($data['start_date'])? intval($data['start_date']) : str_totime('Today');
      $end_date   = isset($data['end_date'])? intval($data['end_date']) : (str_totime('Today') + 86399);
      $provider_id   = isset($data['provider_id'])? intval($data['provider_id']) : 0;
      
      // query where total money
      $sql_count  = "";
      
      // query where normal
      $sql_where  = "";
      
      if($bill_id > 0){
         $sql_where  .= " AND usb_bill_number = " . intval($bill_id) . " LIMIT 1";
         $sql_count  .= " AND usb_bill_number = " . intval($bill_id) . " LIMIT 1";
      }else{
         // loại hóa đơn (nhập, bán)
         $sql_where .= " AND usb_bill_type = " . intval($bill_type);
         $sql_count .= " AND usb_bill_type = " . intval($bill_type);
         
         // chọn ngày tháng
         if($start_date > 0 && $end_date > 0){
            $sql_where  .= " AND usb_date BETWEEN " . intval($start_date) . " AND " . intval($end_date);
            $sql_count .= " AND usb_date BETWEEN " . intval($start_date) . " AND " . intval($end_date);
         }
         
         if($provider_id > 0){
            $sql_where .= " AND usb_provider_id = " . intval($provider_id);
            $sql_count .= " AND usb_provider_id = " . intval($provider_id);
         }
         
         // phân trang
         if($page > 0){
            $sql_where  .= " ORDER BY usb_id DESC LIMIT " . (($page - 1)*PAGE_SIZE) . "," . PAGE_SIZE;
         }
      }
      
      // select money
      $db_money = new db_query("SELECT 
                                 SUM(usb_total_money) AS ttm_import,
                                 SUM(usb_paid) AS ttm_paid,
                                 SUM(usb_pay_remain) AS ttm_remain
                                 FROM " . TABLE_BILL . "
                                 WHERE 1 " . $sql_count, __FILE__ . " LINE: " . __LINE__);
      if($rtotal  = $db_money->fetch()){
         $result['total'] = $rtotal;
      }
      unset($db_money);
      
      // select count
      $db_count = new db_query("SELECT 
                                 COUNT(*) AS count
                                 FROM " . TABLE_BILL . "
                                 WHERE 1 " . $sql_count, __FILE__ . " LINE: " . __LINE__);
      if($rcount  = $db_count->fetch()){
         $result['count'] = $rcount['count'];
      }
      unset($db_count);
      
      
      // select sql
      $db_bill = new db_query("SELECT * FROM " . TABLE_BILL . "
                                 WHERE 1 " . $sql_where
                                 , __FILE__ . " LINE: " . __LINE__);
      while($row  = $db_bill->fetch()){
         $response[$row['usb_id']] = $row;
      }
      unset($db_bill);
      
      //
      if(!empty($response)){
         $result['code']   = 200;
         $result['data']   = $response;
      }else{
         $result['error']  = 'Không có dữ liệu';
      }
      return $result;                                 
   }    
   
   /**
    * @input array()
    * @: bill_code
    * @: bill_type // loại hóa đơn STOCK_CARD_TYPE IN || STOCK_CARD_TYPE_OUT
    * @: stock_id // id trong bảng stock_card
    * 
    * Return: array(
    *    'code' => 500,
         'data' => array(
            'bill' => array(), // thông tin bill
            'stock' => array() // chi tiết các sản phẩm trong bảng stock
         ),
         'error' => ''
      )
    * 
    */
   function getDetailBill($data = array()){
      $bill_code = isset($data['bill_code'])? intval($data['bill_code']) : 0;
      $bill_type = isset($data['bill_type'])? intval($data['bill_type']) : STOCK_CARD_TYPE_IN;
      $stock_id  = isset($data['stock_id'])? intval($data['stock_id']) : 0; 
      
      $result  = array(
         'code' => 500,
         'data' => array(
            'bill' => array(), // thông tin bill
            'stock' => array() // chi tiết các sản phẩm trong bảng stock
         ),
         'error' => ''
      );
      
      // template data
      $dataBill      = array();
      $dataProduct   = array();
      
      if($bill_code <= 0){
         $result['error'] = 'Không có số hóa đơn';
      }else{
         
         // lấy thông tin bảng bill
         $db_bill = new db_query("SELECT * FROM " . TABLE_BILL . "
                                    WHERE usb_bill_number = " . intval($bill_code) . "
                                    AND usb_bill_type = " . $bill_type . " LIMIT 1");
         if($rbill   = $db_bill->fetch()){
            
            $dataBill   = $rbill;
         }
         unset($db_bill);
         
         // điều kiện lấy trong bảng stock_card
         $sqlWhere_stock_card    = '';
         $limit_stock_card       = '';
         if($stock_id > 0){
            $sqlWhere_stock_card = " AND usc_id = " . intval($stock_id);
            $limit_stock_card    = " LIMIT 1";            
         } 
         
         // lấy thông tin sản phẩm trong bill
         $db_stock = new db_query("SELECT * FROM " . TABLE_STOCK_CARD . "
                                  WHERE usc_bill_code = " . intval($bill_code) . " 
                                  AND usc_card_type = " . $bill_type . 
                                  $sqlWhere_stock_card . 
                                  $limit_stock_card);
         while($row  = $db_stock->fetch()){
            
            $db_product = new db_query("SELECT usp_pro_name, usp_unit, usp_unit_import, usp_packing
                                          FROM " . TABLE_PRODUCT . " 
                                          WHERE usp_id = " . $row['usc_usp_id'] . " LIMIT 1");
            if($rpro    = $db_product->fetch()){
               $row['usp_pro_name']    = $rpro['usp_pro_name'];
               $row['usp_unit_import'] = $rpro['usp_unit_import'];
               $row['usp_unit']        = $rpro['usp_unit'];
               $row['usp_packing']     = $rpro['usp_packing'];
               $row['usc_number_in']   =  ($rpro['usp_packing'] > 0)? floor($row['usc_number_in'] / $rpro['usp_packing']) : $row['usc_number_in'];
               $row['usc_price']       = ($row['usc_number_in'] > 0)? floor($row['usc_total_money'] / $row['usc_number_in']) : $row['usc_total_money'];
            }
            unset($db_product);
            
            $dataProduct[$row['usc_id']] = $row;
            
         }  
         unset($db_stock);      
         
         if(!empty($dataBill) && !empty($dataProduct)){
            $result['code']            = 200;
            $result['data']['bill']    = $dataBill;
            $result['data']['stock']   = $dataProduct;
         }else{
            $result['error']           = 'Thông tin số hóa đơn không khớp';
         }                         
      }
      
      return $result;
   }
   
   /**
    * Get bill by code not detail bill
    * @input : array()
    * :bill_code
    * :bill_type
    * 
    * @return: array()
    * :code
    * :data
    * :error
    */
   function getBillByBillCode($data = array()){
      $result  = array(
         'code' => 500,
         'data' => array(),
         'error' => ''
      );
      $bill_code  = isset($data['bill_code'])? intval($data['bill_code']) : 0;
      $bill_type  = isset($data['bill_type'])? intval($data['bill_type']) : STOCK_CARD_TYPE_IN;
      
      if($bill_code > 0){
         $db_data = new db_query("SELECT * FROM " . TABLE_BILL . "
                                    WHERE usb_bill_number = " . $bill_code . "
                                    AND usb_bill_type = " . $bill_type . " LIMIT 1");
         if($row  = $db_data->fetch()){
            $result['data'] = $row;
            $result['code'] = 200;
         }else{
            $result['error'] = 'Không có thông tin';
         }
         unset($db_data);
      }else{
         $result['error']  = "Không có thông tin";
      }
      
      return $result;
   }
   
   
   /**
    * updateData
    * @input: array()
    * :bill_code
    * :bill_type
    * 
    * return array()
    * :code
    * :error
    */
   function updateData($data = array()){
      global $killStockCard;
      $bill_code  = isset($data['bill_code'])? intval($data['bill_code']) : 0;
      $bill_type  = isset($data['bill_type'])? intval($data['bill_type']) : STOCK_CARD_TYPE_IN;
      
      $result     = array(
         'code'   => 500,
         'error'  => ''
      );
      
      if($bill_code > 0){
         $data_stock = $killStockCard->getTotalMoneyBill($data);
         
         if(isset($data_stock['code']) && $data_stock['code'] == 200){
            
            $total_money_bill = isset($data_stock['data']['total'])? $data_stock['data']['total'] : 0;
            
            $db_data = $this->getBillByBillCode($data);
            if($db_data['code']  == 200){
               $total_paid       = $db_data['data']['usb_paid'];
               $total_remain     = $total_money_bill - $total_paid;
               $db_bill = new db_execute("UPDATE " . TABLE_BILL . " SET 
                                          usb_total_money = " . $total_money_bill . ",
                                          usb_pay_remain = " . $total_remain . "
                                          WHERE usb_bill_number = " . $bill_code . " AND usb_bill_type = " . $bill_type);
               unset($db_bill);
               $result['code'] = 200;
            }else{
               $result['error'] = 'Thông tin bill không có';
            }
               
         }else{
            $result['error']  = 'Không có dữ liệu';
         }
         unset($db_stock);
      }else{
         $result['error']  = 'Không có bill';
      }
      
      return $result;
   }
}