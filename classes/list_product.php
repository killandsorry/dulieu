<?php
/**
 * Created by PhpStorm.
 * User: Nguyễn Thanh Huy
 * Date: 24/03/2016
 * Time: 11:56 AM
 * Base
 */
class base
{
    protected $global_branch_id, $array_unit, $class_import_export, $device;

    public $id;
    public $branch_id;
    public $use_id;
    public $admim_id;

    public function __construct(){
        global $admin_id, $myuser, $branch_id, $array_unit, $class_import_export,$is_android;
        $this->admim_id = $admin_id;
        $this->use_id = $myuser->u_id;
        $this->global_branch_id = $branch_id;
        $this->array_unit = $array_unit;
        $this->class_import_export = $class_import_export;
        $this->device = $is_android;
    }

    /*
     * Set id
     * */
    public function setId($id){
        $this->id = (int)$id;
    }

    /*
     * Set branch
     * */
    public function setBranch($id_branch){
            $this->branch_id = (int)$id_branch;
    }

    /*
     * Check id
     * */
    public function check_id(){
        if($this->id <= 0) return false;
        return 1;
    }

    /*
     * check branch
     * */
    public function check_branch(){
        if($this->branch_id <= 0) $this->branch_id = $this->global_branch_id;
    }

    /*
     * Check exit column in table
     * */
    public function CheckExitColumn($table,$col){
        if(trim($col) == '') return array();
        $db_column= new db_query("SHOW COLUMNS FROM " . $table . " LIKE '" . trim($col) . "'");
        //$result = mysqli_query();
        $exists = mysqli_fetch_assoc($db_column->result);
        unset($db_column);
        if(!empty($exists)){
            return $exists;
        }
        return array();
    }
}
?>
<?php
/**
 * Created by PhpStorm.
 * User: Nguyễn Thanh Huy
 * Date: 22/03/2016
 * Time: 3:28 CH
 * List product
 */
class list_product extends base
{
    public $active_edit;
    public $keyword;
    public $active = 1;
    public $searchOrNoSearch;


    /*
     * Set status search
     * */
    public function setSearchOrNoSearch($status){
        $this->searchOrNoSearch = (int)$status;
    }
    /*
     * Set pro dat active edit
     * */
    public function setActiveEdit($active_edit){
        $this->active_edit = (int)$active_edit;
    }

    /*
     * Set keyword search
     * */
    public function setKeyWord($key){
        $this->keyword = $key;
    }

    /*
     * Set keyword search
     * */
    public function setUsp_Active($ac){
        $this->active = (int)$ac;
    }

    /*
     * Check keyword null
     * */
    public function checkKeyWord(){
        if(trim($this->keyword) == '') return false;
        return 1;
    }

    /*
     * Check product id
     * @success : return array result
     * @error : return array null
     * */
    public function checkProId(){
        if($this->check_id()){
            $this->check_branch();
            $sql_check_id_product = "SELECT * FROM ".USER_PRODUCTS."
                            WHERE usp_id = " . $this->id . "
                            AND usp_branch_id = " . $this->branch_id . "
                            AND usp_use_parent_id = " . $this->admim_id . " LIMIT 1";
            $db_query_check_id_product = new db_query($sql_check_id_product);
            return mysqli_fetch_assoc($db_query_check_id_product->result);
        }
        return array();
    }
    /*
     * Update into user product
     * $array_product = array(
     *      'ten_truong' => 'gia tri'
     * )
     * */
    public function updateUserProductByProId($array_product = array()){
  		  global $myuser, $admin_id, $arrayBranch, $array_unit, $class_import_export;
        if($this->check_id()){
            if(empty($array_product)) return array();
            $row_product = $this->checkProId();
            $des_logs   = '';
            if(!empty($row_product)){
                $array_update    = array(); // mảng tên trường và value khi update vào bảng user_products
                $array_datas     = array(); // mảng tên trường và value khi update vào bảng datas
                $new_name        = '';
                $unit_parent     = 0;
                $unit            = 0;
                $specifi         = 0;
                $set_log_delete  = '';
                $usp_sale_money  = 0;
                $active          = 1;
                $usp_wholesale   = 0;
                foreach($array_product as $column => $value){
                    $check_col = $this->CheckExitColumn(USER_PRODUCTS,$column); // check tên trường tồn tại trong bang user_product
                    if(empty($check_col)) return array();
                    if(strstr($check_col['Type'],'int') !== false){ // check kiểu dữ liệu của trường
                        if(trim($check_col['Field']) != 'usp_active'){ // kiểm tra tên trường . nếu khác trường usp_active thì giá trị phải lớn hơn 0
                            if((int)$value <= 0) return array();
                        }
                    }
                    if(strstr($check_col['Type'],'varchar') !== false){ // check kiểu dữ liệu của trường
                        if(trim($value) == '') return array();
                        $value = $value;
                    }
                    $array_update[] = $column . " = '" . $value ."'";
                    switch($column){
                     case 'usp_pro_name':
                        $array_datas[] = "dat_name = '" . $value . "'";
                        $new_name      = $value;
                        $des_logs   .= "\n Sửa tên thuốc từ: <b>". $row_product['usp_pro_name'] ."</b> sang <b>". $value ."</b>";
                        break;
                     case 'usp_unit_import':
                        $array_datas[] = "dat_unit_import = " . $value;
                        $unit_parent   = $value;
                        $des_logs   .= "\n Sửa đơn vị nhập từ: <b>". $array_unit[$row_product['usp_unit_import']] ."</b> sang <b>". $array_unit[$value] ."</b>";
                        break;
                     case 'usp_unit':
                        $array_datas[] = "dat_unit = " . $value;
                        $unit          = $value;
                        $des_logs   .= "\n Sửa đơn vị bán từ: <b>". $array_unit[$row_product['usp_unit']] ."</b> sang <b>". $array_unit[$value] ."</b>";
                        break;
                     case 'usp_specifi':
                        $array_datas[] = "dat_specifi = " . $value;
                        $specifi       = $value;
                        $des_logs   .= "\n Sửa đơn vị bán từ: <b>". $row_product['usp_specifi'] ."</b> sang <b>". $value ."</b>";
                        break;
                     case 'usp_sale_money':
                        $usp_sale_money       = $value;
                        $des_logs   .= "\n Sửa tiền hoa hồng từ: <b>". $row_product['usp_sale_money'] ."</b> sang <b>". $value ."</b>";
                        break;
                     case 'usp_wholesale':
                        $usp_wholesale       = $value;
                        $des_logs   .= "\n Cập nhật giá bán buôn: <b>". $value ."</b>";
                        break;
                     case 'usp_active':
                        if($value == 0){
                           $active  = 0;
                           $set_log_delete = ',usp_last_delete = '. time();
                           $des_logs   .= "\n Chuyển sản phẩm sang ngừng bán";
                        }else{
                           $set_log_delete = ',usp_last_delete = '. time();
                           $des_logs   .= "\n Khôi phục sản phẩm để bán";
                        }
                        break;
                   }
                }
                //gán thông tin sản phẩm vào chính người sửa luôn
					 $array_update[] = "usp_use_child_id = " . $this->use_id;
                
                $str_col_val = implode(', ',$array_update);
                $sql_update = "UPDATE " . USER_PRODUCTS . " SET ". $str_col_val . $set_log_delete . "
                            WHERE usp_id = " . $this->id . "
                            AND usp_branch_id = " . $this->branch_id . "
                            AND usp_use_parent_id = " . $this->admim_id;
                //kiem tra xem co quyen edit khong
                if(isset($_SERVER) && ($_SERVER['REMOTE_ADDR'] == '14.177.216.14' || $_SERVER['SERVER_NAME'] == 'localhost')){
                  file_put_contents('../logs/logsupdate.cfn', $sql_update);
                }
                $db_ex = new db_execute($sql_update);
                
                // ghi logs                
                logsAddEditData($row_product['usp_dat_id'],$new_name,$row_product['usp_id'],$unit_parent,$unit,$admin_id,$myuser->u_id, $specifi);
                
                // ghi log hành động
                $data_action  = array(
                  'file' => json_encode(debug_backtrace()),
                  'action' => 'edit',
                  'des' => $des_logs,
                  'pro_id' => $this->id
                );

                log_action($data_action);
                
                // nếu chưa active dùng chung thì update luôn vào bảng data tên và đơn vị tính
                $dat_id  = $row_product['usp_dat_id'];            
                if($row_product['usp_dat_active'] == 0 && !empty($array_datas)){
                    $str_col_data   = implode(', ',$array_datas);
                    $db_update_datas  = new db_execute("UPDATE datas SET " . $str_col_data . " WHERE dat_id = " . $dat_id);
                    unset($db_update_datas);
                }
                
                // kiểm tra để cập nhật cho tất cả các sản phẩm của các chi nhánh cùng người chủ này quản lý cũng thay đổi luôn
                /*
                if(count($arrayBranch) > 1){
                  $db_update  = new db_execute("UPDATE " . USER_PRODUCTS . " SET " . $str_col_val . "
                                                WHERE usp_use_parent_id = " . $this->admim_id . "
                                                AND usp_id <> " . $this->id . " AND usp_dat_id = " . $dat_id);
                  unset($db_update);
                }
                */
                
                // nếu khách hàng thay đổi đơn vị bán thì
                //- xóa lịch sử nhập kho
                //- reset số tồn kho = 0
                if(($row_product['usp_unit'] > 0 && $unit > 0 && $unit != $row_product['usp_unit']) || $active == 0){
                  $this->delete_reset_stock(array('pro_id' => $this->id));
                }
                
                //nếu là khôi phục thì update lại thông tin giá nhập giá bán
                if($active == 1){
                  $import_last   = $class_import_export->import_get_last_stocks(array('pro_id' => $this->id, 'quantity' => 1));
                  
                  $pr_im   = isset($import_last['uss_price_import'])? $import_last['uss_price_import'] : 0;
                  $pr_ex   = isset($import_last['uss_price_out'])? $import_last['uss_price_out'] : 0;
                  
                  $db_update  = new db_execute("UPDATE " . USER_PRODUCTS . "
                                                SET usp_price_import = " . doubleval($pr_im) . ", usp_price = " . doubleval($pr_ex) . "
                                                WHERE usp_id = " . $this->id);
                  unset($db_update);
                }
                 
                return $this->checkProId();
            }
        }
        return array();
    }
   
   /**
    * Function delete history import stock , reset tồn kho = 0 
    */      
   function delete_reset_stock($data = array()){
      $pro_id  = isset($data['pro_id'])? intval($data['pro_id']) : 0;
      if($pro_id <= 0) return 0;
      
      $db_stock   = new db_execute("UPDATE " . USER_STOCK . " SET uss_status = 2 
                                    WHERE uss_branch_id = " . $this->branch_id . "
                                    AND uss_use_parent_id = " . $this->admim_id . "
                                    AND uss_pro_id = " . $pro_id);
      unset($db_stock);
      
      $db_sale   = new db_execute("UPDATE " . USER_SALE . " SET uso_view = 1 
                                    WHERE uso_branch_id = " . $this->branch_id . "
                                    AND uso_use_parent_id = " . $this->admim_id . "
                                    AND uso_pro_id = " . $pro_id);
      unset($db_sale);
      
      $db_update  = new db_execute("UPDATE " . USER_PRODUCTS . "
                                    SET usp_quantity = 0 WHERE usp_id = " . $pro_id);
      unset($db_update);
      
   }
   
    /*
     * Search product list
     * */
    public function searchProduct(){
        if($this->checkKeyWord() === false && $this->searchOrNoSearch == 0) return array();
        $this->check_branch();
        $andWhere = '';
        if($this->active == 0) $andWhere .= ' AND usp_active = 0';
        else $andWhere .= ' AND usp_active = 1';
        if($this->checkKeyWord() && $this->searchOrNoSearch == 0){
            if($this->class_import_export->detect_barcode($this->keyword)){
                if($this->class_import_export->detect_me_barcode($this->keyword)) $andWhere .= " AND usp_me_barcode LIKE '%".$this->keyword."%'";
                else $andWhere .= " AND usp_barcode LIKE '%".$this->keyword."%'";
            }else{
                $andWhere .= " AND usp_pro_name LIKE '%".$this->keyword."%'";
            }
        }
        $sql_search = "SELECT * FROM ".USER_PRODUCTS."
                        WHERE usp_branch_id = " . $this->branch_id . "
                        AND usp_use_child_id = ".$this->use_id."
                        AND usp_use_parent_id = " . $this->admim_id .$andWhere;
        $db_query = new db_query($sql_search);
        return $db_query->resultArray();
    }

    /*
     * Return result search product
     * */
    public function resultSearch(){
        return $this->returnHTML($this->searchProduct());
    }

    /*
     * Return HTML
     * */
    public function returnHTML($arr = array()){
        if(empty($arr)) return '';
        global $pathRoot;
        $i = 1;
        $html = '';
        foreach($arr as $row){
            $u = 'Đơn vị';
            if(isset($this->array_unit[$row['usp_unit']])){
                $u = $this->array_unit[$row['usp_unit']];
            }
            $list_unit_import = '';
            $list_unit = '';
            foreach($this->array_unit as $k => $unit){
                $sel = '';
                if($k == $row['usp_unit_import'])
                    $sel = 'selected';
                $list_unit_import .= '<option value="'.$k.'" '.$sel.'>'.$unit.'</option>';
            }
            foreach($this->array_unit as $k => $unit){
                $sel = '';
                if($k == $row['usp_unit'])
                    $sel = 'selected';
                $list_unit .= '<option value="'.$k.'" '.$sel.'>'.$unit.'</option>';
            }
            $ac = '';
            if($row['usp_active'] > 0){
                $b_or_nb = 'Ngừng bán';
            }else{
                $b_or_nb = 'Bán';
                $ac = 'active';
            }
            if($row['usp_barcode'] != '') $code = $row['usp_barcode'];
            else $code = $row['usp_me_barcode'];

            $html .= '<tr class="item_order" id="'.$row['usp_id'].'">';
                $html .= '<td class="ta-c">';
                    $html .= $i;
                $html .= '</td>';
                $html .= '<td class="usp_name pos-r">';
                    $html .= '<a title="lịch sử nhập hàng" class="history" href="'.$pathRoot.'stock_list.php?pro_id='.$row['usp_id'].'"> </a>';
                    $html .= '<span class="code_quantity_price_out" onclick="choose_edit(this)">&nbsp;'.$row['usp_pro_name'].'</span>';
                    $html .= '<div class="pos-a pro_quantity edit_item item_price hidden-element">';
                        $html .= '<table cellspacing="0" cellpadding="0" width="100%">';
                            $html .= '<tr>';
                                $html .= '<td style="white-space: nowrap">Tên sản phẩm:</td>';
                                $html .= '<td>';
                                    $html .= '<input type="text" maxlength="50" data-col="usp_pro_name" title="Tên thuốc" class="tooltip def pd-t-2 pd-l-5" value="'.$row['usp_pro_name'].'">';
                                $html .= '</td>';
                                $html .= '</tr>';
                            $html .= '<tr class="not-button">';
                                $html .= '<td style="white-space: nowrap">';
                                    $html .= '<span class="not-cancel pd-5" onclick="close_choose_edit(this)">Hủy bỏ</span>';
                                $html .= '</td>';
                                $html .= '<td>';
                                    $html .= '<span class="not-continue pd-5 pd-l-10 pd-r-10" onclick="edit_product(this)">Tiếp tục</span>';
                                $html .= '</td>';
                            $html .= '</tr>';
                        $html .= '</table>';
                    $html .= '</div>';
                $html .= '</td>';
                $html .= '<td class="pos-r ta-c quantity '.notifyQuantity($row['usp_quantity']).'">';
                    $html .= '<span>'.format_number($row['usp_quantity']).'</span>';
                $html .= '</td>';
                $html .= '<td class="ta-c pos-r dtv">';
                    $html .= '<span class="code_quantity_price_out" onclick="choose_edit(this)">'.$u.'</span>';
                    $html .= '<div class="pos-a pro_quantity edit_item item_price hidden-element">';
                        $html .= '<table cellspacing="0" cellpadding="0" width="100%">';
                            $html .= '<tr>';
                                $html .= '<td style="white-space: nowrap">Đơn vị nhập:&nbsp; <span class="icon_help" data-key="unit_import" onclick="show_help(this)"></span></td>';
                                $html .= '<td>';
                                    $html .= '<select name="usp_unit_import" data-col="usp_unit_import" class="ta-c def unit" style="width: 100%">'.$list_unit_import.'</select>';
                                $html .= '</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td style="white-space: nowrap">Đơn vị bán:&nbsp; <span class="icon_help" data-key="unit_export" onclick="show_help(this)"></span></td>';
                                $html .= '<td>';
                                    $html .= '<select class="ta-c def unit" data-col="usp_unit" name="usp_unit" style="width: 100%">'.$list_unit.'</select>';
                                $html .= '</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td style="white-space: nowrap">Quy cách:&nbsp; <span class="icon_help" data-key="specifi" onclick="show_help(this)"></span></td>';
                                $html .= '<td>';
                                    $html .= '<input name="usp_specifi" maxlength="10" data-col="usp_specifi" class="tooltip def ta-c" title="Quy cách" type="tel" value="'.$row['usp_specifi'].'" style="width:100%;">';
                                $html .= '</td>';
                            $html .= '</tr>';
                            $html .= '<tr class="not-button">';
                                $html .= '<td style="white-space: nowrap">';
                                    $html .= '<span class="not-cancel pd-5" onclick="close_choose_edit(this)">Hủy bỏ</span>';
                                $html .= '</td>';
                                $html .= '<td>';
                                    $html .= '<span data-col="usp_unit" class="not-continue pd-5 pd-l-10 pd-r-10" onclick="edit_product(this)">Tiếp tục</span>';
                                $html .= '</td>';
                            $html .= '</tr>';
                        $html .= '</table>';
                    $html .= '</div>';
                $html .= '</td>';
                $html .= '<td class="ta-r pos-r">';
                    $html .= '<b class="price">'.number_format($row['usp_price'],0,'.','.').'</b>';
                $html .= '</td>';
                $html .= '<td class="ta-c"><a class="usp_active" href="'.$pathRoot.'add_stocks.php?pro_id='.$row['usp_id'].'">Nhập kho</a></td>';
                $html .= '<td class="ta-c">';
                    $html .= '<span class="usp_active '. $ac .'" onclick="active_to_not_active(this)" >' . $b_or_nb . '</span>';
                $html .= '</td>';
                $html .= '<td class="ta-c pos-r code">';
                    $html .= '<span class="code_quantity_price_out" onclick="choose_edit(this)">'.$code .'</span>';
                    $html .= '<div class="pos-a pro_quantity edit_item item_price hidden-element">';
                        $html .= '<table cellspacing="0" cellpadding="0" width="100%">';
                            $html .= '<tr>';
                                $html .= '<td style="white-space: nowrap">Mã vạch:</td>';
                                $html .= '<td>';
                                    $html .= '<input type="text" maxlength="20" data-col="usp_barcode" title="Mã vạch" class="tooltip def pro_val pd-t-2 ta-c" value="'. $code .'">';
                                $html .= '</td>';
                            $html .= '</tr>';
                            $html .= '<tr class="not-button">';
                                $html .= '<td style="white-space: nowrap">';
                                    $html .= '<span class="not-cancel pd-5" onclick="close_choose_edit(this)">Hủy bỏ</span>';
                                $html .= '</td>';
                                $html .= '<td>';
                                    $html .= '<span data-col="usp_barcode" class="not-continue pd-5 pd-l-10 pd-r-10" onclick="edit_product(this)">Tiếp tục</span>';
                                $html .= '</td>';
                            $html .= '</tr>';
                        $html .= '</table>';
                    $html .= '</div>';
                $html .= '</td>';
            $html .= '</tr>';
            if($this->device == 0){
                if($i >= 15){
                    break;
                }
            }
            $i++;
        }
        return $html;
    }

}// end class list_product