<?

function checkSlowFunction($file,$line,$function_name){
	$function_name1 = $function_name . "_time";	
	global $function_analyze_array,$$function_name1;
	if (!isset($function_analyze_array)) $function_analyze_array = array();
	if(!isset($$function_name1)){
		$$function_name1 = microtime_float();	
	}else{
		$time = microtime_float() - $$function_name1;
		$arr = array("file" => $file,"line" => $line,"function" => $function_name,"time" => $time);
		$function_analyze_array[$function_name][] = $arr;				
		
	} $time = microtime_float();	
	$arr = array("file" => $file,"line" => $line,"function" => $function_name,"time" => $time);
	
}
function detectSpecialWord($string){
	$string 			= mb_strtolower($string,'UTF-8');
	$arrayString 	= explode(" ",$string);
	$arrayNew 		= array();
	foreach($arrayString as $word){
 		if(preg_match("/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/i",$word,$matches)) continue;
 		if(strlen($word) <= 3) continue;
		$arrayNew[$word] = BuildTrigrams($word);
	}
	return $arrayNew;
}
function crateUrlToken($arrayParam = array(),$return = 1){
	$arrayParam["time"] = time();
	$string = "token=" . tokenEncode(json_encode($arrayParam)); 
	if($return) $string .= "&urlreturn=" . createUrlReturn($_SERVER["REQUEST_URI"]);
	return $string;
}
function getUrlReturn($default_url = ""){
	$urlreturn = base64_url_decode(getValue("urlreturn","str","GET",base64_url_encode($default_url)));
	return $urlreturn;
}
function createUrlReturn($url, $ignore_from_url = 0){
	if($ignore_from_url){
		$urlreturn = base64_url_encode($url);
	}else{
		$urlreturn = getValue("urlreturn","str","GET",base64_url_encode($url));	
	}
	return $urlreturn;
}
function decodeUrlToken(){
	$token = getValue("token","str","GET","");
	if($token == "") return;
	$token = json_decode(tokenDecode($token),true);
	if(is_array($token)){
		$time = isset($token["time"]) ? $token["time"] : 0;
		if((time()-$time) > (3600*6)) exit("Loi token");
		foreach($token as $key => $val) $_GET[$key] = $val;
	}
}
define("KEY_TOKEN","bcb88b7e103a0cd8b22263051cef08bc55abe029fdebae5e1d456e2ffb2a00a3");
function tokenEncode($plaintext){
	$key = "fdsljfldsjlfjlsdjlf";
	$checksum = md5($plaintext . "|" . $key);
	$arr = array("checksum" => $checksum,"plaintext" => $plaintext);
	return base64_url_encode(json_encode($arr));
}

function tokenDecode($ciphertext){
	$arr = json_decode(base64_url_decode($ciphertext),true);
	$key = "fdsljfldsjlfjlsdjlf";
	$checksum = isset($arr["checksum"]) ? $arr["checksum"] : '';
	$plaintext = isset($arr["plaintext"]) ? $arr["plaintext"] : '';
	if($checksum == md5($plaintext . "|" . $key)){
		return $plaintext;
	}else{
		return '';
	}
}
/**
 * Ham kich hoat user thanh toan tien
 */
function activeUserPayment($user_id,$admin_id = 0,$expire = 0){
	$user_id = intval($user_id);
	$total_record = 0;
	//tạo bảng mới riêng cho user đó
	$db_ex = new db_execute("CREATE TABLE IF NOT EXISTS user_products_" . $user_id . " LIKE user_products;");
	$total_record += $db_ex->total;
	unset($db_ex);
	//đẩy dữ liệu sang bảng mới
	$db_ex = new db_execute("INSERT IGNORE INTO user_products_" . $user_id . " SELECT * FROM user_products WHERE usp_use_parent_id = " . $user_id .";");
	$total_record += $db_ex->total;
	unset($db_ex);
	$db_ex = new db_execute("CREATE TABLE IF NOT EXISTS user_orders_" . $user_id . " LIKE user_orders;");
	$total_record += $db_ex->total;
	unset($db_ex);
	$db_ex = new db_execute("INSERT IGNORE INTO user_orders_" . $user_id . " SELECT * FROM user_orders WHERE uso_use_parent_id = " . $user_id .";");
	$total_record += $db_ex->total;
	unset($db_ex);
	$db_ex = new db_execute("CREATE TABLE IF NOT EXISTS user_stock_" . $user_id . " LIKE user_stock;");
	$total_record += $db_ex->total;
	unset($db_ex);
	$db_ex = new db_execute("INSERT IGNORE INTO user_stock_" . $user_id . " SELECT * FROM user_stock WHERE uss_use_parent_id = " . $user_id .";");
	$total_record += $db_ex->total;
	unset($db_ex);
	$db_ex = new db_execute("INSERT IGNORE INTO logs_active_payment(lap_admin_id,lap_use_parent_id,lap_date,lap_expire)
									 VALUES(" . $admin_id . "," . $user_id . "," . time() . "," . $expire . ")");
	$total_record += $db_ex->total;
	unset($db_ex);
	return $total_record;
}

function createTriggerHistory($table_name, $field_branch_id, $field_parent_id, $field_child_id, $field_primary_key,$field_dat_id = ""){
	//lay danh sach cot cua table
	$db_select = new db_query("DESCRIBE " . $table_name);
	$arrayField = array();
	while($row = $db_select->fetch()){
		$arrayField[] = $row["Field"];
	}
	unset($db_select);
	if(!in_array($field_branch_id,$arrayField)) return;
	if(!in_array($field_parent_id,$arrayField)) return;
	if(!in_array($field_child_id,$arrayField)) return;
	if(!in_array($field_primary_key,$arrayField)) return;
	foreach($arrayField as $key => $val) $arrayField[$key] = "CONCAT('" . $val . "::'," . $table_name . "." . $val . ")";
	//khai bao tren trigger neu co roi thi xoa di tao moi
	$trigger_name = "update_" . $table_name;
	$db_ex = new db_execute("DROP TRIGGER IF EXISTS " . $trigger_name);
	unset($db_ex);
	$sql_field = "hit_date,hit_table,hit_action,hit_branch_id,hit_parent_id,hit_child_id,hit_key_id,hit_data";
	$sql_dat_id = "";
	if($field_dat_id != ""){
		$sql_field .= ",hit_dat_id";
		$sql_dat_id = "," . $table_name . "." . $field_dat_id . "";
	}
	$db_ex = new db_execute("CREATE TRIGGER " . $trigger_name . " 
									 AFTER UPDATE ON " . $table_name . " FOR EACH ROW
								    INSERT IGNORE INTO user_history(" . $sql_field . ") 
									 SELECT UNIX_TIMESTAMP(NOW()),'" . $table_name . "','UPDATE'," . $table_name . "." . $field_branch_id . "," . $table_name . "." . $field_parent_id . "," . $table_name . "." . $field_child_id . "," . $table_name . "." . $field_primary_key . ",CONCAT_WS('{:}'," . implode(",",$arrayField) . ")" . $sql_dat_id . "
								    FROM " . $table_name . " WHERE " . $table_name . "." . $field_primary_key . " = NEW." . $field_primary_key . "");
	unset($db_ex);
	$trigger_name = "delete_" . $table_name;
	$db_ex = new db_execute("DROP TRIGGER IF EXISTS " . $trigger_name);
	unset($db_ex);
	$db_ex = new db_execute("CREATE TRIGGER " . $trigger_name . " 
									 BEFORE DELETE ON " . $table_name . " FOR EACH ROW
								    INSERT IGNORE INTO user_history(" . $sql_field . ") 
									 SELECT UNIX_TIMESTAMP(NOW()),'" . $table_name . "','DELETE'," . $table_name . "." . $field_branch_id . "," . $table_name . "." . $field_parent_id . "," . $table_name . "." . $field_child_id . "," . $table_name . "." . $field_primary_key . ",CONCAT_WS('{:}'," . implode(",",$arrayField) . ")" . $sql_dat_id . "
								    FROM " . $table_name . " WHERE " . $table_name . "." . $field_primary_key . " = OLD." . $field_primary_key . "");
	unset($db_ex);
}

function dropAllTrigger(){
	$db_select = new db_query("SHOW TRIGGERS");
	$arrayField = array();
	while($row = $db_select->fetch()){
		if(trim($row["Trigger"]) != ''){
			$db_ex = new db_execute("DROP TRIGGER IF EXISTS " . $row["Trigger"]);
			unset($db_ex);
		}
	}
	unset($db_select);
}

function decodeLogs($data){
	$data = explode('{:}',$data);
	$arrayReturn = array();
	foreach($data as $field){
		$row = explode("::",$field);
		$arrayReturn[trim($row[0])] = isset($row[1]) ? trim($row[1]) : '';
	}
	return $arrayReturn;
}

function showValLog($field,$val){
	if((strpos($field,"date") !== false) && is_numeric($val)) return showDate($val);
	if((strpos($field,"barcode") !== false) && is_numeric($val)) return $val;
	if((strpos($field,"_id") !== false) && is_numeric($val)) return $val;
	if((strpos($field,"code") !== false) && is_numeric($val)) return $val;
	if($val == "DELETE") return '<b style="color:red">' . $val . '</b>';
	if(is_numeric($val))  return format_number($val);
	return $val;
}

function logsAddEditData($dat_id,$new_name,$pro_id,$unit_parent,$unit,$admin_id,$use_id,$specifi = 1){
	$db_ex = new db_execute("INSERT IGNORE INTO datas_logs(datl_dat_id,datl_pro_id,datl_date,datl_unit_parent,datl_unit,datl_new_name,datl_parent_id,datl_child_id,datl_specifi)
									 VALUES(" . intval($dat_id) . "," . intval($pro_id) . "," . time() . "," . intval($unit_parent) . "," . intval($unit) . ",'" . replaceMQ($new_name) . "'," . intval($admin_id) . "," . intval($use_id) . ",". intval($specifi) .")");
 	unset($db_ex);
}
function showUnit($unit_id, $use_space = 0){
	global $array_unit;
	$unit_id = intval($unit_id);
	$unit_name = isset($array_unit[$unit_id]) ? $array_unit[$unit_id] : '';
	if($use_space){
		$current_len = mb_strlen($unit_name,'UTF-8');
		for($i = 0; $i < (6-$current_len); $i++){
			$unit_name .= "&nbsp;";
		}
	}
 	return $unit_name;
}
function showDate($date,$show_date_expires = 0){
	//nếu thời gian quá 1 năm thì hiển thị cả năm
   if($show_date_expires == 1){
      return (date('d/m/Y', $date));
   }
	if(date("Y",$date) < date("Y")){
		return ('<i style="font-size: 11px; color: #27ae60;">'. date('H:i', $date) .'</i> - ' . date('d/m/Y', $date));
	}else{
		return ('<i style="font-size: 11px; color: #27ae60;">'. date('H:i', $date) .'</i> - ' . date('d/m', $date));;
	}
}

function show_code($code){
   return substr($code + 1000000, 1);
}

function show_status_move($status = 0){
   $html = '';
   switch($status){
      case 0:
         $html = '<span class="status_btn bg_rez">Chưa chuyển</span>';
         break;
      case 1:
         $html = '<span class="status_btn bg_mov">Đang chuyển</span>';
         break;
      case 2:
         $html = '<span class="status_btn bg_acc">Đã nhận</span>';
         break;
      case 3:
         $html = '<span class="status_btn bg_can">Đã hủy</span>';
         break;
   }
   
   return $html;
}

function show_status_close_book($status = 0){
   
}

/**
 * create me barcode
 */
function creatMeBarcode($char){
    $old = 1000000000000;
    if(detected_keyword($char)){
        $new = $old + $char;
    }else{
        $new = $old .''. $char;
    }
    return $new;
}

/**
 * check search me barcode
 */
function checkMeBarcode($key){
    $old = 1000000000000;
    if(detected_keyword($key)){
        $firstCode     = substr($key, 0, 5) * 1;

        if($firstCode % 10000 == 0){
            return 1; // truong me_barcode
        }else{
            return 0; // truong barcode
        }
    }else{
        $a = explode($old,$key);
        if(count($a) > 1){
            return 2;
        }else{
            return 100;
        }
    }
}
/**
 * Hàm cập nhật giá nhập của bảng bán hàng khi giá nhập = 0
 * thay đổi giá nhập từ 0 => >0 trong danh sách sản phẩm thì gọi hàm này
 * array = array(
 *    pro_id => id thuoocs,
 *    price => gia nhaapj
 * )
 */
function update_price_import_sale($array = array()){
   global $admin_id;
   $pro_id  = isset($array['pro_id'])? intval($array['pro_id']) : 0;
   $price   = isset($array['price'])? intval($array['price']) : 0;
   
   if(($pro_id) <= 0) return 0;
   if(($price) <= 0) return 0;
   
   $db_update    = new db_execute("UPDATE " . USER_SALE . "
                                    SET uso_price_import = uso_quantity * " . $price . " 
                                    WHERE uso_pro_id = " . intval($pro_id) . "
                                    AND uso_use_parent_id = " . intval($admin_id) . "
                                    AND uso_price_import = 0");
   if($db_update->total > 0){
      return 1;
   }else{
      return 0;
   }
}
/* Check data_id
 * */
function check_data_id($data_id){
	$sql_datas = "SELECT * FROM datas WHERE dat_id = " . (int)$data_id . " LIMIT 1";
	$db_query_data_by_id = new db_query($sql_datas); unset($sql_datas);
	$row_datas = mysqli_fetch_assoc($db_query_data_by_id->result);
	return $row_datas;
}
/* */
/* lay ra tat ca ban ghi trong bang user stock
 * */
function getAllStockStatus0(){
	global $admin_id, $branch_id, $myuser, $array_unit;
	if(intval($admin_id) == 0 || intval($branch_id) == 0){
		return 0;
	}
	$db_count_stock_continue        = new db_count("SELECT COUNT(*) as count FROM ".USER_STOCK."
                                                                            WHERE uss_use_parent_id = " . $admin_id . "
                                                                            AND uss_use_child_id = " .$myuser->u_id."
                                                                            AND uss_status = 0
                                                                            AND uss_trash = 1
                                                                            AND uss_branch_id = " . intval($branch_id));
	$count                          = $db_count_stock_continue->total; unset($db_count_stock_continue);
	if ( $count > 0 ){
		$db_query_stock_continue    = new db_query("SELECT * FROM ".USER_STOCK." LEFT JOIN ".USER_PRODUCTS."
                                                        ON uss_pro_id = usp_id
                                                        WHERE uss_use_parent_id = " . $admin_id . "
                                                        AND uss_use_child_id = " .$myuser->u_id."
                                                        AND uss_status = 0
                                                        AND uss_trash = 1
                                                        AND uss_branch_id = " . intval($branch_id) ."
                                                        ORDER BY uss_id DESC");
		$arr                            = array();
		while ( $data_row_stock = mysqli_fetch_assoc( $db_query_stock_continue->result ) )
		{
			if( isset($array_unit[$data_row_stock['uss_unit']]) ){
				$data_row_stock['unit'] = $array_unit[$data_row_stock['uss_unit']];
			}else{
				$data_row_stock['unit'] = 'Đơn vị';
			}
            if( isset($array_unit[$data_row_stock['usp_unit_import']]) ){
                $data_row_stock['unit_import'] = $array_unit[$data_row_stock['usp_unit_import']];
            }else{
                $data_row_stock['unit_import'] = 'Đơn vị';
            }
			$date_limit = '';
			if($data_row_stock['uss_date_expires'] > 0){
				$date_limit = date('d/m/Y',$data_row_stock['uss_date_expires']);
			}
			$data_row_stock['date_limit'] = $date_limit;
			$arr[] = $data_row_stock;
		}unset($db_query_stock_by_use_id);
		return $arr;
	}
	return 0;
}
/* */
/* lay ra thong tin cua san pham dc nhap theo 1 array product
 * */
function getRowStock($data_product = array(),$opt = 0){
	if(empty($data_product)){
        return false;
    }
	// kiem tra xem trong bang user stock xem san pham da ton tai chua -> co roi thi tang so luong -> chua co thi them moi
	$db_count_user_stock = new db_count("SELECT COUNT(*) as count FROM " . USER_STOCK . "
                                                            WHERE uss_use_parent_id = " . $data_product['usp_use_parent_id'] . "
                                                            AND uss_use_child_id = " .$data_product['usp_use_child_id']."
                                                            AND uss_pro_id = " . $data_product['usp_id'] . "
                                                            AND uss_status = 0
                                                            AND uss_trash = 1
                                                            AND uss_branch_id = " . $data_product['usp_branch_id']);
	$count_user_stock = $db_count_user_stock->total;
	unset($db_count_user_stock);
	if ($count_user_stock > 0) {
		if($opt == 0){
			$sql_update_quantity = "UPDATE " . USER_STOCK . " SET uss_quantity_unit_parent = uss_quantity_unit_parent + 1,
                                                                    uss_quantity = uss_quantity_unit_parent * uss_quantity_unit_child,
                                                                    uss_sold = uss_sold + uss_quantity_unit_child
                                                WHERE uss_pro_id = " . $data_product['usp_id'] . "
                                                AND uss_use_parent_id = " . $data_product['usp_use_parent_id'] . "
                                                 AND uss_use_child_id = " .$data_product['usp_use_child_id']."
                                                AND uss_status = 0
                                                AND uss_trash = 1
                                                AND uss_branch_id = " . $data_product['usp_branch_id'];
			$user_stock_execute = new db_execute($sql_update_quantity);
			unset($user_stock_execute);
		}
	} else {
		// lay ra gia nhap cua san pham voi lan nhap gan nhaat
		$db_price_import = new db_query("SELECT uss_price_import FROM " . USER_STOCK . "
                                                            WHERE uss_use_parent_id = " . $data_product['usp_use_parent_id'] . "
                                                            AND uss_use_child_id = " .$data_product['usp_use_child_id']."
                                                            AND uss_pro_id = " . $data_product['usp_id'] . "
                                                            AND uss_branch_id = " . $data_product['usp_branch_id'] . "
                                                            ORDER BY uss_id DESC LIMIT 1");
		$price_import = mysqli_fetch_assoc($db_price_import->result); unset($db_price_import);
		if(empty($price_import['uss_price_import'])){
			$import = 0;
		}else{
			$import = $price_import['uss_price_import'];
		}
		// luu tam thoi vao bang user stock voi gia tri mac dinh cua san pham o bang datas va bang user products
		$sql_user_stock = "INSERT INTO " . USER_STOCK . " (
                                                                        uss_date,
                                                                        uss_use_parent_id,
                                                                        uss_use_child_id,
                                                                        uss_price_import,
                                                                        uss_price_out,
                                                                        uss_quantity,
                                                                        uss_unit,
                                                                        uss_pro_id,
                                                                        uss_dat_id,
                                                                        uss_branch_id,
                                                                        uss_sold,
                                                                        uss_bill_name,
                                                                        uss_bill_code,
                                                                        uss_status,
                                                                        uss_trash,
                                                                        uss_quantity_unit_parent,
                                                                        uss_quantity_unit_child,
                                                                        uss_date_expires
                                                                      ) VALUES (
                                                                        " . time() . ",
                                                                        " . $data_product['usp_use_parent_id'] . ",
                                                                        " . $data_product['usp_use_child_id'] . ",
                                                                        " . $import . ",
                                                                        " . $data_product['usp_price'] . ",
                                                                        " . $data_product['usp_specifi'] . ",
                                                                        " . $data_product['usp_unit'] . ",
                                                                        " . $data_product['usp_id'] . ",
                                                                        " . $data_product['usp_dat_id'] . ",
                                                                        " . $data_product['usp_branch_id'] . ",
                                                                        " . $data_product['usp_quantity'] . " + 1,
                                                                        '',
                                                                        0,
                                                                        0,
                                                                        1,
                                                                        1,
                                                                        " . $data_product['usp_specifi'] . ",
                                                                        0
                                                                      )";
		$user_stock_execute_return = new db_execute_return();
		$user_stock_execute_return->db_execute($sql_user_stock);
		unset($user_stock_execute_return);
	}
	$data_stock = getAllStockStatus0();
	return $data_stock;
}

/* lay ra thong tin product hay id product dua vao $option
 * */
function updateProduct_by_datId($data_id, $option = 0){
	global $admin_id, $branch_id, $myuser;
	if(intval($admin_id) == 0 || intval($branch_id) == 0){
		return 0;
	}
	// lấy ra thông tin chung của sản phẩm trong bảng datas
//	$sql_datas = "SELECT * FROM datas WHERE dat_id = " . (int)$data_id . " LIMIT 1";
//	$db_query_data_by_id = new db_query($sql_datas);
	$row_datas = check_data_id($data_id);
	if($row_datas) {
		// kiem tra xem bang user product da co san pham do chua
		// chua co thi thuc hien them san pham do vao bang user product
		// kiem tra xem san pham da co trong bang pro chua neu co roi thi lay ra id product
		$sql_get_id_product = "SELECT * FROM " . USER_PRODUCTS . "
                                          WHERE usp_dat_id = " . intval($row_datas['dat_id']) . "
                                          AND usp_use_parent_id = " . intval($admin_id) . "
                                          AND usp_use_child_id = " . $myuser->u_id."
                                          AND usp_branch_id = " . intval($branch_id);
		$db_query_id_product = new db_query($sql_get_id_product);
		if ($row_pro = mysqli_fetch_assoc($db_query_id_product->result)) {
			$row_id_pro = $row_pro;
			//$pro_id = $row_id_pro['usp_id'];
		} else {
			// thuc hien luu san pham vao bang user products
			$sql_insert_user_products = "INSERT INTO " . USER_PRODUCTS . " (
                                                                        usp_dat_id,
                                                                        usp_pro_name,
                                                                        usp_barcode,
                                                                        usp_unit,
                                                                        usp_unit_import,
                                                                        usp_specifi,
                                                                        usp_branch_id,
                                                                        usp_use_parent_id,
                                                                        usp_use_child_id,
                                                                        usp_quantity,
                                                                        usp_price,
                                                                        usp_active,
                                                                        usp_me_barcode
                                                                        )
                                                                        VALUES (
                                                                        " . $row_datas['dat_id'] . ",
                                                                        '" . $row_datas['dat_name'] . "',
                                                                        '" . $row_datas['dat_barcode'] . "',
                                                                        " . $row_datas['dat_unit'] . ",
                                                                        " . $row_datas['dat_unit_import'] . ",
                                                                        " . $row_datas['dat_specifi'] . ",
                                                                        " . $branch_id . ",
                                                                        " . $admin_id . ",
                                                                        " . $myuser->u_id . ",
                                                                        0,
                                                                        0,
                                                                        1,
                                                                        '".$row_datas['dat_me_barcode']."'
                                                                        )";
			$user_pro_execute_return = new db_execute_return();
			$pro_id = $user_pro_execute_return->db_execute($sql_insert_user_products);
			if($pro_id){
				$db_query_product_by_pro_id = new db_query("SELECT * FROM " . USER_PRODUCTS . "
															WHERE usp_id = " . intval($pro_id) . "
															AND usp_use_parent_id = " . intval($admin_id) . "
															AND usp_use_child_id = " . intval($myuser->u_id) . "
															AND usp_branch_id = " . intval($branch_id) . " LIMIT 1");
				$row_id_pro = mysqli_fetch_assoc($db_query_product_by_pro_id->result);
				unset($db_query_product_by_pro_id);
			}
			unset($user_pro_execute_return);
		}
		unset($db_query_id_product);

		if($option == 0){
			return $row_id_pro['usp_id'];
		}else{
			return $row_id_pro;
		}
	}else{
		return 0;
	}

}
// color quantity
function notifyQuantity($quantity){
	$color = '';
	if(intval($quantity) < 0){
		$color = ' qtt_-0';
	}
	if(intval($quantity) == 0){
		$color = ' qtt_0';
	}
	return $color;
}
function getHeightImg($thumb_width, $src_width, $src_height){
	if($src_width <= 0) return $thumb_width;
	return round(($thumb_width * $src_height) / $src_width);
}
function getImageThumb($filename, $maxWidth = 0, $maxHeight = 0){
	if($maxWidth < 50) $maxWidth = 50;
	if($maxHeight < 100) $maxHeight = 100;
	if($filename == "") return "/images/no_photo_x_small.gif";
	return "/pictures/thumb/" . $maxWidth . "x" . $maxHeight . "/" . getPathDateTimeImg($filename);
}
function getPathDateTimeImg($filename, $pre_path = ""){
	$filename = @end(explode("/", $filename));
	$time = intval(preg_replace("/[^0-9]/i","",$filename));
	if($time > time()) $time = time();
	return $pre_path . date("Y/m/", $time) . $filename;
}
function base64_url_encode($input){
	return strtr(base64_encode($input), '+/=', '_,-');
}
function base64_url_decode($input) {
	return base64_decode(strtr($input, '_,-', '+/='));
}
function get_type_categories(){
	$array_value 		= array("thuoc" => translate_text("Thuốc")//
										,"news" => translate_text("Tin tức bài viết")
										,"sale" => translate_text("Thuốc bán")
								);
	return $array_value;
}

function get_type_cat(){
	$array_value 		= array("blog" => translate_text("Blog")//
										,"help" => translate_text("Hướng dẫn")
								);
	return $array_value;
}

function removeSpecialChar($string){
	 //$string = removeAccent($string);
	 $string  =  (preg_replace("/[^A-Za-z0-9àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđĐÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÈÉẸẺẼÊỀẾỆỂỄÌÍỊỈĨÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÙÚỤỦŨƯỪỨỰỬỮỲÝỴỶỸĐĐ]/i","-",$string)); // khong dau
	 $string = preg_replace('/-+/', '-', $string);
	 $string = str_replace('-', ' ', $string);
	 return trim($string);
}
function updateInfoFromVatgia($record_id,$pro_vg_id, $pro_name = ""){
	$rest_url_pub            	= "http://slave.vatgia.com/authorize/api/api.php";
	//Basic Authentication
	$rest_user_name_pub			= "id.vatgia.com";
	$rest_password_pub			= 'b#2nb9o$hh9&blfdsfs';
	$rest_access_key_id_pub	 	= "id.vatgia.com";
	$rest_share_key_pub		 	= "fdsjfldsaj;l2wrjlewjrlfldsfds";

	$myRS = new Request_Security( $rest_access_key_id_pub, $rest_share_key_pub, $rest_url_pub);
	$myRS	->Set_Digest_Authentication($rest_user_name_pub, $rest_password_pub);
	$myRS ->domain = "id.vatgia.com";
	$myRS->debug = false;
	$myRS->call("getProductDetail", array("iPro" => $pro_vg_id , "tskt" => 1));
	$rs 		= $myRS->Post_Data("json", 1, 1);
	if(isset($rs["getProductDetail"][0]["info"])){
		$rs = $rs["getProductDetail"][0]["info"];
		global $pro_production,$pro_assign,$pro_formula,$pro_dangbaoche,$pro_donggoi,$pro_cou_id,$pro_update_info,$pro_name;
		foreach($rs as $key => $arr){
			$label = mb_strtolower($arr["label"],"UTF-8");
			switch($label){
				case "hãng sản xuất":
					$pro_production = removeLink($arr["value"]);
				break;
				case "chức năng":
					$pro_assign = removeLink($arr["value"]);
				break;
				case "thành phần":
					$pro_formula = removeLink($arr["value"]);
				break;
				case "dạng bào chế":
					$pro_dangbaoche = removeLink($arr["value"]);
				break;
				case "quy cách đóng gói":
					$pro_donggoi = removeLink($arr["value"]);
				break;
				case "xuất xứ":
					$pro_cou_id = getCountryId(removeLink($arr["value"]));
				break;
			}
		}//foreach($rs as $key => $arr)
		$pro_update_info = 1;
		$myform = new generate_form();
		$myform->add("pro_production","pro_production",0,1,"");
		$myform->add("pro_assign","pro_assign",0,1,"");
		$myform->add("pro_formula","pro_formula",0,1,"");
		$myform->add("pro_dangbaoche","pro_dangbaoche",0,1,"");
		$myform->add("pro_donggoi","pro_donggoi",0,1,"");
		$myform->add("pro_cou_id","pro_cou_id",1,1,0);
		$myform->add("pro_update_info","pro_update_info",1,1,0);
		$myform->add_Field_Seach("pro_search", array(
																  "pro_name" => 1,
																  "pro_assign" => 1,
																  "pro_formula" => 1
																	));
		$myform->addTable("products");
		if($myform->checkdata() == ""){
			$myform->removeHTML(0);
			$db_ex = new db_execute($myform->generate_update_SQL("pro_id", $record_id));
			unset($db_ex);
			generateTagsProduct($record_id, $pro_production . ","
											 . $pro_assign . ","
											 . $pro_name . ","
											 );
		}

	}//end if
}
function getCountryId($cou_name){
	$db_select = new db_query("SELECT cou_id FROM countries WHERE cou_name = '" . $cou_name . "' LIMIT 1");
	if($row = mysqli_fetch_assoc($db_select->result)){
		return $row["cou_id"];
	}else{
		$db_ex = new db_execute_return();
		return $db_ex->db_execute("INSERT INTO countries(cou_name) VALUES('" . replaceMQ($cou_name) . "')");
	}
}
function getCountryName($cou_id){
	$db_select = new db_query("SELECT cou_name FROM countries WHERE cou_id = " . intval($cou_id) . " LIMIT 1");
	if($row = mysqli_fetch_assoc($db_select->result)){
		return $row["cou_name"];
	}
}
function generateTagsProduct($pro_id, $text){
	echo $text . '<hr>';
	$pro_id 	  = intval($pro_id);
	if($pro_id <= 0) return 0;
	$text		  = removeHTML($text);
	$text		  = str_replace(array(".",":","<br>"), ",", $text);
	$arrayTemp = explode(",", $text);
	$arraySql = array();
	foreach($arrayTemp as $key => $value){
		$value = removeSpecialChar($value);
		$len	 = mb_strlen($value, "UTF-8");
		$value = mb_strtolower($value, "UTF-8");
		if($value != "" && $len > 5 && $len < 50){
			$md5		= md5($value);
			$db_select = new db_query("SELECT tag_id FROM tags WHERE tag_md5='" . $md5 . "' LIMIT 1");
			if($row = mysqli_fetch_assoc($db_select->result)){

				$arraySql[] = "(" . $pro_id . "," . $row["tag_id"] . ")";
			}else{
				$db_ex = new db_execute_return();
				$value				 = str_replace("br ","", $value);
				$tag_name_noaccent = removeAccent($value);
				echo $value . '<hr>';
				$tag_id = $db_ex->db_execute("INSERT IGNORE INTO tags(tag_name,tag_name_noaccent,tag_md5,tag_trigrams,tag_len) VALUES('" . replaceMQ($value) . "','" . $tag_name_noaccent . "','" . $md5 . "','" . BuildTrigrams($tag_name_noaccent) . "'," . strlen($tag_name_noaccent) . ")");
				unset($db_ex);
				if($tag_id > 0){
					$arraySql[] = "(" . $pro_id . "," . $tag_id . ")";
				}
			}
			unset($db_select);

		} //end if
	}//end foreach
	if(count($arraySql) > 0){
		$db_ex = new db_execute("INSERT IGNORE INTO tags_products(tap_pro_id,tap_tag_id) VALUES" . implode(",", $arraySql));
		unset($db_ex);
	}
	return 1;
}

function get_type_product(){
	$array_value 		= array(  0 => translate_text("Chọn dạng bào chế")//
										,1 => translate_text("Viên nang")//
										,2 => translate_text("Viên nén")
										,3 => translate_text("Lọ")
										,4 => translate_text("Bôi ngoài da")
										,5 => translate_text("Pha nước uống")
										,6 => translate_text("Viên bao phim")
								);
	return $array_value;
}
function get_coutries(){
	$db_query = new db_query("SELECT cou_id,cou_name FROM countries ORDER BY cou_order DESC");
	$array_value 		= array(  0 => translate_text("Chọn quốc gia")	);
	while($row = mysqli_fetch_assoc($db_query->result)){
		$array_value[$row["cou_id"]] = $row["cou_name"];
	}
	return $array_value;
}
function get_group_type(){
	$array_value 		= array(  "" => translate_text("Chọn nhóm thuốc")
										,1 => translate_text("Nhóm thuốc điều trị")//
										,2 => translate_text("Nhóm thuốc bổ")
										,4 => translate_text("Thực phẩm chức năng")
										,8 => translate_text("Nhóm kháng sinh")
								);
	return $array_value;
}
/**
 * Check trình duyệt IE
 * @return [type] [description]
 */
function check_IE(){
	if(preg_match('/MSIE/i',@$_SERVER['HTTP_USER_AGENT'])){
		return true;
	}
	else{
		return false;
	}
}
//Get : Tìm việc nhanh
// Get data tại mục tìm việc theo trình độ
function get_array_trinhdo(){
    $array_value        = array(0 => "Bất kỳ",
                                1 => "Lao động phổ thông",
                                2 => "Chứng chỉ",
                                3 => "Trung học",
                                4 => "Trung cấp",
                                5 => "Cao đẳng",
                                6 => "Đại học",
                                7 => "Cao học",
                                8 => "Không khai báo"
                                );

    return $array_value;

}
// Get data tại mục tìm việc theo hình thức làm việc
function get_array_hinhthuc(){
    $array_value        = array(0 => "Bất kỳ",
                                1 => "Nhân viên chính thức",
                                2 => "Nhân viên thời vụ",
                                3 => "Bán thời gian",
                                4 => "Làm thêm ngoài giờ",
                                5 => "Thực tập và dự án"
                                );
    return $array_value;

}

// Get data tại mục tìm việc theo mức lương
function get_array_luong(){
    $array_value        = array(0 => "Bất kỳ",
                                1 => "1 - 2 triệu",
                                2 => "2 - 3 triệu",
                                3 => "3 - 4 triệu",
                                4 => "4 - 5 triệu",
                                5 => "5 - 8 triệu",
                                6 => "Trên 8 triệu",
                                7 => "Thỏa thuận"
                                );
    return $array_value;

}
// End tìm việc nhanh
function breakKey($content, $title, $tag=0){
	$title 			= mb_strtolower($title,"UTF-8");
	$array 			= explode(".",$content);
	$contentReturn = '';
	$arrayKey 		= explode(" ", $title);
	$arraySort 		= array();
	$arResult 		= array();
	foreach($array as $key=>$value){
		$value 				= mb_strtolower($value,"UTF-8");
		$arrayCau 			= explode(" ",$value);
		$result 	 			= array_intersect($arrayKey, $arrayCau);
		$arraySort[$key] 	= count($result);
		$arResult[$key]	= $result;
	}
	arsort($arraySort);
	$i=0;
	foreach($arraySort as $key=>$value){
		$i++;
		if(isset($array[$key]) && isset($arResult[$key])){
			$contentReturn = $contentReturn . replaceTag(cut_string($array[$key], 200), $arResult[$key]) . '. ';
		}
		if($i==3) break;
	}
	unset($result);
	unset($arraySort);
	unset($arResult);
	if($tag==1) $this->text .= strip_tags($contentReturn);
	$contentReturn = str_replace("</b>"," ",$contentReturn);

	return $contentReturn;
}
function replaceTag($content,$array=array()){

	if(count($array)>0){
		foreach($array as $key=>$value){
			$value = trim($value);
			if($value!=''){
				//echo $value. chr(13);
				//$content = @preg_replace("#" . $value . "#Usi","<b>$0</b>",$content);
				//echo $content;
			}
		}
	}
	return $content;
}
function array_currency(){
	$arrReturn	= array(0 => "USD", 1 => "EUR");
	return $arrReturn;
}

function array_language(){
	$db_language	= new db_query("SELECT * FROM languages ORDER BY lang_id ASC");
	$arrReturn		= array();
	while($row = mysqli_fetch_array($db_language->result)){
		$arrReturn[$row["lang_id"]] = array($row["lang_code"], $row["lang_name"]);
	}
	return $arrReturn;
}

function array_length_of_stay_tour(){
	$arrReturn	= array (1 => "1 " . tdt("day"),
								2 => "2 - 5 " . tdt("days"),
								3 => "6 - 9 " . tdt("days"),
								4 => "10 - 16 " . tdt("days"),
								5 => "17 " . tdt("and_more_days"),
								);
	return $arrReturn;
}

function array_star_rating_hotel(){
	$arrReturn	= array (2 => "2 " . tdt("stars"),
								3 => "3 " . tdt("stars"),
								4 => "4 " . tdt("stars"),
								5 => "5 " . tdt("stars"),
								);
	return $arrReturn;
}

function array_service(){
	$arrReturn	= array (1 => tdt("Air_ticket"),
								2 => tdt("Train_ticket"),
								3 => tdt("Visa"),
								4 => tdt("Car_for_rent"),
								);
	return $arrReturn;
}

function callback($buffer){
	$str		= array(chr(9));
	$buffer	= str_replace($str, "", $buffer);
	$buffer	= str_replace("  ", " ", $buffer);
	$buffer	= str_replace("  ", " ", $buffer);
	$buffer	= str_replace("  ", " ", $buffer);
	$buffer	= str_replace("  ", " ", $buffer);
	$buffer	= str_replace("  ", " ", $buffer);
	$buffer	= str_replace("  ", " ", $buffer);
	return $buffer;
}

function responseData($data){
	$etag 	= hash("sha256",$data . json_encode($_POST));
	saveLog1("log_cache.cfn", $data);
	header("Last-Modified: ".gmdate("D, d M Y H:i:s", 112233456)." GMT");
	header("Etag: " . $etag);
	header("Accept-Ranges: bytes");
	//header('Content-Type: application/json');
	if (trim(@$_SERVER['HTTP_IF_NONE_MATCH']) == $etag) {
		 saveLog1("header.cfn", @$_SERVER['HTTP_IF_NONE_MATCH'] ."\n" . @$_SERVER['HTTP_IF_MODIFIED_SINCE']);
	    header("HTTP/1.1 304 Not Modified");
	    exit;
	}
	echo $data;
	flush();
	ob_end_flush();
	exit();
}

function saveLog1($filename, $content){

	$log_path     =   $_SERVER["DOCUMENT_ROOT"] . "/logs/";
	$handle       =   @fopen($log_path . $filename . ".cfn", "a");
	//Neu handle chua co mo thêm ../
	if (!$handle) $handle = @fopen($log_path . $filename . ".cfn", "a");
	//Neu ko mo dc lan 2 thi exit luon
	if (!$handle) exit();
	fwrite($handle, date("d/m/Y h:i:s A") . " " . @$_SERVER["REQUEST_URI"] . "\n" . $content . "\n");
	fclose($handle);

}

function check_email_address($email) {
	//First, we check that there's one @ symbol, and that the lengths are right
	if(!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)){
		//Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
		return false;
	}
	//Split it into sections to make life easier
	$email_array = explode("@", $email);
	$local_array = explode(".", $email_array[0]);
	for($i = 0; $i < sizeof($local_array); $i++){
		if(!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])){
			return false;
		}
	}
	if(!ereg("^\[?[0-9\.]+\]?$", $email_array[1])){
	//Check if domain is IP. If not, it should be valid domain name
		$domain_array = explode(".", $email_array[1]);
		if(sizeof($domain_array) < 2){
			return false; // Not enough parts to domain
		}
		for($i = 0; $i < sizeof($domain_array); $i++){
			if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])){
				return false;
			}
		}
	}
	return true;
}

function check_session_security($security_code){
	$return = 1;
	if(!isset($_SESSION["session_security_code"])) $_SESSION["session_security_code"] = generate_security_code();
	if($security_code != $_SESSION["session_security_code"]){
		$return = 0;
	}
	// Reset lại session security code
	$_SESSION["session_security_code"] = generate_security_code();
	return $return;
}

function count_online(){
	$visited_timeout		= 10 * 60;
	$last_visited_time	= time();
	//Kiem tra co session_id hay ko, neu co
	if(session_id() != ""){
		$db_exec	= new db_execute("REPLACE INTO active_users(au_session_id, au_last_visit) VALUES('" . session_id() . "', " . $last_visited_time . ")");
		unset($db_exec);
	}
	// Delete timeout
	$db_exec	= new db_execute("DELETE FROM active_users WHERE au_last_visit < " . ($last_visited_time - $visited_timeout));
	unset($db_exec);
	// Select Count
	$db_count= new db_query("SELECT count(*) AS count FROM active_users");
	$row		= mysqli_fetch_array($db_count->result);
	unset($db_count);
	// Return value
	return $row["count"];
}

function count_visited(){
	$db_count	= new db_query("SELECT vis_counter FROM visited");
	$row = mysqli_fetch_array($db_count->result);
	unset($db_count);
	return $row["vis_counter"];
}

function cut_string($str, $length, $char=" ..."){
	//Nếu chuỗi cần cắt nhỏ hơn $length thì return luôn
	$strlen	= mb_strlen($str, "UTF-8");
	if($strlen <= $length) return $str;

	//Cắt chiều dài chuỗi $str tới đoạn cần lấy
	$substr	= mb_substr($str, 0, $length, "UTF-8");
	if(mb_substr($str, $length, 1, "UTF-8") == " ") return $substr . $char;

	//Xác định dấu " " cuối cùng trong chuỗi $substr vừa cắt
	$strPoint= mb_strrpos($substr, " ", "UTF-8");

	//Return string
	if($strPoint < $length - 20) return $substr . $char;
	else return mb_substr($substr, 0, $strPoint, "UTF-8") . $char;
}

function format_number($number, $edit=0, $intval = 0){
	if($intval) $number = intval($number);
	if($edit == 0){
		$return	= number_format($number, 2, ".", ".");
		if(intval(substr($return, -2, 2)) == 0) $return = number_format($number, 0, ".", ".");
		elseif(intval(substr($return, -1, 1)) == 0) $return = number_format($number, 1, ".", ".");
		return $return;
	}
	else{
		$return	= number_format($number, 2, ".", "");
		if(intval(substr($return, -2, 2)) == 0) $return = number_format($number, 0, ".", "");
		return $return;
	}
}

function parse_type_number($vl = 0){
   if($vl == '') return '';
   if($vl <= 0) return 0;
   $new_vl  = str_replace(',', '', $vl);
   return intval($new_vl);
}

function format_currency($value = ""){
	$str		=	$value;
	if($value != ""){
		$str		=	number_format($value,0,"",",");
	}
	return $str;
}

function generate_array_variable($variable){
	$list			= tdt($variable);
	$arrTemp		= explode("{-break-}", $list);
	$arrReturn	= array();
	for($i=0; $i<count($arrTemp); $i++) $arrReturn[$i] = trim($arrTemp[$i]);
	return $arrReturn;
}

function generate_security_code(){
	$code	= rand(1000, 9999);
	return $code;
}

function generate_sort($type, $sort, $current_sort, $image_path){
	if($type == "asc"){
		$title = tdt("Tang_dan");
		if($sort != $current_sort) $image_sort = "sortasc.gif";
		else $image_sort = "sortasc_selected.gif";
	}
	else{
		$title = tdt("Giam_dan");
		if($sort != $current_sort) $image_sort = "sortdesc.gif";
		else $image_sort = "sortdesc_selected.gif";
	}
	return '<a title="' . $title . '" href="' . getURL(0,0,1,1,"sort") . '&sort=' . $sort . '"><img border="0" src="' . $image_path . $image_sort . '" style="margin-top:3px" /></a>';
}

function generate_sql_length_of_stay_tour($key){
	$arrSQL	= array (1 => " AND tou_length = 1 ",
							2 => " AND tou_length >= 2 AND tou_length <= 5 ",
							3 => " AND tou_length >= 6 AND tou_length <= 9 ",
							4 => " AND tou_length >= 10 AND tou_length <= 16 ",
							5 => " AND tou_length >= 17 ",
							);
	if(isset($arrSQL[$key])) return $arrSQL[$key];
	else return "";
}

function generate_title_url_tour($arrCou, $arrTs, $nData, $nTab){
	global $lang_path;
	$strReturn	= '<a href="' . generate_module_url("Search_tour") . ';country=' . $arrCou[0] . '">' . $arrCou[1] . '</a> &raquo; ';
	$strReturn .= '<a href="' . generate_module_url("Search_tour") . ';country=' . $arrCou[0] . '&travel_style=' . $arrTs[0] . '">' . $arrTs[1] . '</a> &raquo; ';
	$strReturn .= '<a href="' . generate_detail_tour_url($arrCou[1], $arrTs[1], $nData) . '">' . $nData . '</a> &raquo; ';
	$strReturn .= '<span>' . $nTab . '</span>';
	return $strReturn;
}

function generate_title_url_hotel($arrCou, $arrCity, $nData, $nTab){
	global $lang_path;
	$strReturn	= '<a href="' . generate_module_url("Search_hotel") . ';country=' . $arrCou[0] . '">' . $arrCou[1] . '</a> &raquo; ';
	$strReturn .= '<a href="' . generate_module_url("Search_hotel") . ';country=' . $arrCou[0] . '&city=' . $arrCity[0] . '">' . $arrCity[1] . '</a> &raquo; ';
	$strReturn .= '<a href="' . generate_detail_hotel_url($arrCou[1], $arrCity[1], $nData) . '">' . $nData . '</a> &raquo; ';
	$strReturn .= '<span>' . $nTab . '</span>';
	return $strReturn;
}

function getURL($serverName=0, $scriptName=0, $fileName=1, $queryString=1, $varDenied=''){
	$url	 = '';
	$slash = '/';
	if($scriptName != 0)$slash	= "";
	if($serverName != 0){
		if(isset($_SERVER['SERVER_NAME'])){
			$url .= 'http://' . $_SERVER['SERVER_NAME'];
			if(isset($_SERVER['SERVER_PORT'])) $url .= ":" . $_SERVER['SERVER_PORT'];
			$url .= $slash;
		}
	}
	if($scriptName != 0){
		if(isset($_SERVER['SCRIPT_NAME']))	$url .= substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/') + 1);
	}
	if($fileName	!= 0){
		if(isset($_SERVER['SCRIPT_NAME']))	$url .= substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], '/') + 1);
	}
	if($queryString!= 0){
		$url .= '?';
		reset($_GET);
		$i = 0;
		if($varDenied != ''){
			$arrVarDenied = explode('|', $varDenied);
			while(list($k, $v) = each($_GET)){
				if(array_search($k, $arrVarDenied) === false){
					$i++;
					if($i > 1) $url .= '&' . $k . '=' . @urlencode($v);
					else $url .= $k . '=' . @urlencode($v);
				}
			}
		}
		else{
			while(list($k, $v) = each($_GET)){
				$i++;
				if($i > 1) $url .= '&' . $k . '=' . @urlencode($v);
				else $url .= $k . '=' . @urlencode($v);
			}
		}
	}
	$url = str_replace('"', '&quot;', strval($url));
	return $url;
}

function getValue($value_name, $data_type = "int", $method = "GET", $default_value = 0, $advance = 0){
	$value = $default_value;
	switch($method){
		case "GET": if(isset($_GET[$value_name])) $value = $_GET[$value_name]; break;
		case "POST": if(isset($_POST[$value_name])) $value = $_POST[$value_name]; break;
		case "COOKIE": if(isset($_COOKIE[$value_name])) $value = $_COOKIE[$value_name]; break;
		case "SESSION": if(isset($_SESSION[$value_name])) $value = $_SESSION[$value_name]; break;
		default: if(isset($_GET[$value_name])) $value = $_GET[$value_name]; break;
	}
	/**
	 * Edit 26/03/2014
	 * - Sửa lại để không dính lỗi trên PHP 5.4 với hàm strval khi get arr
	 */
	$data_type = trim(strtolower($data_type));
	switch($data_type)
	{
		case 'int':
         $value = str_replace('.', '', $value);
			$returnValue = intval($value);
			break;
		case 'str':
			$returnValue = strval($value);
			break;
		case 'flo':
			$returnValue = floatval($value);
			break;
		case 'dbl':
			$returnValue = doubleval($value);
			break;
		case 'arr':
			$returnValue = $value;
			break;
		default:
			//Nếu mặc định ko truyền data_type thì là kiểu int
			$returnValue = intval($value);
			break;
	}
	//Check xem có cần format giá trị trả về hay không??
	if($advance != 0 && is_string($returnValue)){
		switch($advance){
			case 1:
				$returnValue = replaceMQ($returnValue);
				break;
			case 2:
				$returnValue = htmlspecialbo($returnValue);
				break;
		}
	}
	//Do số quá lớn nên phải kiểm tra trước khi trả về giá trị
	if(($data_type != "str") && !is_array($returnValue) && (strval($returnValue) == "INF")) return 0;
	return $returnValue;
	/*
     $valueArray	= array("int" => intval($value), "str" => trim(strval($value)), "flo" => floatval($value), "dbl" => doubleval($value), "arr" => $value);
     foreach($valueArray as $key => $returnValue){
         if($data_type == $key){
             if($advance != 0){
                 switch($advance){
                     case 1:
                         $returnValue = replaceMQ($returnValue);
                         break;
                     case 2:
                         $returnValue = htmlspecialbo($returnValue);
                         break;
                 }
             }
             //Do số quá lớn nên phải kiểm tra trước khi trả về giá trị
             if((strval($returnValue) == "INF") && ($data_type != "str")) return 0;
             return $returnValue;
             break;
         }
     }
     return (intval($value));
    */
}

function get_server_name(){
	$server = $_SERVER['SERVER_NAME'];
	if(strpos($server, "asiaqueentour.com") !== false) return "http://www.asiaqueentour.com";
	else return "http://" . $server . ":" . $_SERVER['SERVER_PORT'];
}

function htmlspecialbo($str){
	$arrDenied	= array('<', '>', '\"', '"');
	$arrReplace	= array('&lt;', '&gt;', '&quot;', '&quot;');
	$str = str_replace($arrDenied, $arrReplace, $str);
	return $str;
}

function javascript_writer($str){
	$mytextencode = "";
	for ($i=0;$i<strlen($str);$i++){
		$mytextencode .= ord(substr($str,$i,1)) . ",";
	}
	if ($mytextencode!="") $mytextencode .= "32";
	return "<script language='javascript'>document.write(String.fromCharCode(" . $mytextencode . "));</script>";
}

function lang_path(){
	global $lang_id;
	global $array_lang;
	global $con_root_path;
	$default_lang = 1;
	$path	= ($lang_id == $default_lang) ? $con_root_path : $con_root_path . $array_lang[$lang_id][0] . "/";
	return $path;
}

function microtime_float(){
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}

function random(){
	$rand_value = "";
	$rand_value.= rand(1000,9999);
	$rand_value.= chr(rand(65,90));
	$rand_value.= rand(1000,9999);
	$rand_value.= chr(rand(97,122));
	$rand_value.= rand(1000,9999);
	$rand_value.= chr(rand(97,122));
	$rand_value.= rand(1000,9999);
	return $rand_value;
}

function redirect($url){
	$url	= htmlspecialbo($url);
	echo '<script type="text/javascript">window.location.href = "' . $url . '";</script>';
	exit();
}

function removeAccent($mystring){
	$marTViet=array(
		// Chữ thường
		"à","á","ạ","ả","ã","â","ầ","ấ","ậ","ẩ","ẫ","ă","ằ","ắ","ặ","ẳ","ẵ",
		"è","é","ẹ","ẻ","ẽ","ê","ề","ế","ệ","ể","ễ",
		"ì","í","ị","ỉ","ĩ",
		"ò","ó","ọ","ỏ","õ","ô","ồ","ố","ộ","ổ","ỗ","ơ","ờ","ớ","ợ","ở","ỡ",
		"ù","ú","ụ","ủ","ũ","ư","ừ","ứ","ự","ử","ữ",
		"ỳ","ý","ỵ","ỷ","ỹ",
		"đ","Đ","'",
		// Chữ hoa
		"À","Á","Ạ","Ả","Ã","Â","Ầ","Ấ","Ậ","Ẩ","Ẫ","Ă","Ằ","Ắ","Ặ","Ẳ","Ẵ",
		"È","É","Ẹ","Ẻ","Ẽ","Ê","Ề","Ế","Ệ","Ể","Ễ",
		"Ì","Í","Ị","Ỉ","Ĩ",
		"Ò","Ó","Ọ","Ỏ","Õ","Ô","Ồ","Ố","Ộ","Ổ","Ỗ","Ơ","Ờ","Ớ","Ợ","Ở","Ỡ",
		"Ù","Ú","Ụ","Ủ","Ũ","Ư","Ừ","Ứ","Ự","Ử","Ữ",
		"Ỳ","Ý","Ỵ","Ỷ","Ỹ",
		"Đ","Đ","'"
		);
	$marKoDau=array(
		/// Chữ thường
		"a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a",
		"e","e","e","e","e","e","e","e","e","e","e",
		"i","i","i","i","i",
		"o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o",
		"u","u","u","u","u","u","u","u","u","u","u",
		"y","y","y","y","y",
		"d","D","",
		//Chữ hoa
		"A","A","A","A","A","A","A","A","A","A","A","A","A","A","A","A","A",
		"E","E","E","E","E","E","E","E","E","E","E",
		"I","I","I","I","I",
		"O","O","O","O","O","O","O","O","O","O","O","O","O","O","O","O","O",
		"U","U","U","U","U","U","U","U","U","U","U",
		"Y","Y","Y","Y","Y",
		"D","D","",
		);
	return str_replace($marTViet, $marKoDau, $mystring);
}

function removeHTML($string){
	$string = preg_replace ('/<script.*?\>.*?<\/script>/si', ' ', $string);
	$string = preg_replace ('/<style.*?\>.*?<\/style>/si', ' ', $string);
	$string = preg_replace ('/<.*?\>/si', ' ', $string);
	$string = str_replace ('&nbsp;', ' ', $string);
	$string = mb_convert_encoding($string, "UTF-8", "UTF-8");
	$string = str_replace (array(chr(9),chr(10),chr(13)), ' ', $string);
	for($i = 0; $i <= 5; $i++) $string = str_replace ('  ', ' ', $string);
	return $string;
}

function removeLink($string){
	$string = preg_replace ('/<a.*?\>/si', '', $string);
	$string = preg_replace ('/<\/a>/si', '', $string);
	return $string;
}

function replaceFCK($string, $type=0){
	$array_fck	= array ("&Agrave;", "&Aacute;", "&Acirc;", "&Atilde;", "&Egrave;", "&Eacute;", "&Ecirc;", "&Igrave;", "&Iacute;", "&Icirc;",
								"&Iuml;", "&ETH;", "&Ograve;", "&Oacute;", "&Ocirc;", "&Otilde;", "&Ugrave;", "&Uacute;", "&Yacute;", "&agrave;",
								"&aacute;", "&acirc;", "&atilde;", "&egrave;", "&eacute;", "&ecirc;", "&igrave;", "&iacute;", "&ograve;", "&oacute;",
								"&ocirc;", "&otilde;", "&ugrave;", "&uacute;", "&ucirc;", "&yacute;",
								);
	$array_text	= array ("À", "Á", "Â", "Ã", "È", "É", "Ê", "Ì", "Í", "Î",
								"Ï", "Ð", "Ò", "Ó", "Ô", "Õ", "Ù", "Ú", "Ý", "à",
								"á", "â", "ã", "è", "é", "ê", "ì", "í", "ò", "ó",
								"ô", "õ", "ù", "ú", "û", "ý",
								);
	if($type == 1) $string = str_replace($array_fck, $array_text, $string);
	else $string = str_replace($array_text, $array_fck, $string);
	return $string;
}

function replaceJS($text){
	$arr_str = array("\'", "'", '"', "&#39", "&#39;", chr(10), chr(13), "\n");
	$arr_rep = array(" ", " ", '&quot;', " ", " ", " ", " ");
	$text		= str_replace($arr_str, $arr_rep, $text);
	$text		= str_replace("    ", " ", $text);
	$text		= str_replace("   ", " ", $text);
	$text		= str_replace("  ", " ", $text);
	return $text;
}

function replace_keyword_search($keyword, $lower=1){
	if($lower == 1) $keyword	= mb_strtolower($keyword, "UTF-8");
	$keyword	= replaceMQ($keyword);
	$arrRep	= array("'", '"', "-", "+", "=", "*", "?", "/", "!", "~", "#", "@", "%", "$", "^", "&", "(", ")", ";", ":", "\\", ".", ",", "[", "]", "{", "}", "‘", "’", '“', '”');
	$keyword	= str_replace($arrRep, " ", $keyword);
	$keyword	= str_replace("  ", " ", $keyword);
	$keyword	= str_replace("  ", " ", $keyword);
	return $keyword;
}

function replaceMQ($text){
	$text	= str_replace("\'", "'", $text);
	$text	= str_replace("'", "''", $text);
	return $text;
}

function remove_magic_quote($str){
	$str = str_replace("\'", "'", $str);
	$str = str_replace("\&quot;", "&quot;", $str);
	$str = str_replace("\\\\", "\\", $str);
	return $str;
}

function tdt($variable){
	global $lang_display;
	if (isset($lang_display[$variable])){
		if (trim($lang_display[$variable]) == ""){
			return "#" . $variable . "#";
		}
		else{
			$arrStr	= array("\\\\'", '\"');
			$arrRep	= array("\\'", '"');
			return str_replace($arrStr, $arrRep, $lang_display[$variable]);
		}
	}
	else{
		return "_@" . $variable . "@_";
	}
}

function generate_star($value = 1, $width = 19){
	$value	=	intval($value);
	$width	=	intval($width);
	$str	=	'';
	$str	.=	'<span class="rateStar" style="background: url(\'/themes/v1/images/rating-star-'	.	$width	.	'.png\') no-repeat scroll 0 0 transparent; display: inline-block; height: '	.	$width	.	'px; width: '	.	($value*$width)	.	'px; background-position: 0px '	.	($value - 1)*(-$width)	.	'px;"></span>';
	return $str;
}

function navbar($iCit = 0, $iDis = 0, $iLoc = 0, $iHot = 0, $type = 0){
	$raquote = "&raquo;";

	$menu = new menu();
	$str = $menu->getAllPrentCity($iCit, $type, $raquote);
	unset($menu);
	if($iDis > 0){
		$db_select	=	new db_query("SELECT cou_id,cou_name
												FROM	countries
												WHERE	cou_id = "	.	$iCit);
		$row_city	=	mysqli_fetch_assoc($db_select->result);
		unset($db_select);
		$db_select	=	new db_query("SELECT dis_name
												FROM	districts
												WHERE	dis_id = "	.	$iDis);
		$row_dis	=	mysqli_fetch_assoc($db_select->result);
		unset($db_select);
		$str	.=	' <i class="icons navicon"></i><a class="parentlink" href="' . ($type == 2 ? url_city_location($row_city) : url_city($row_city, $iDis, $type)) . '">' . $row_dis['dis_name'] . '</a> ';
	}
	if($iLoc > 0){
		$db_select = new db_query("SELECT loc_id,loc_name
											FROM locations
											WHERE loc_id = " . $iLoc);
		if($row = mysqli_fetch_assoc($db_select->result)){
			$str .= '<i class="icons navicon"></i> ' . $row["loc_name"];
		}
		unset($db_select);
	}
	if($iHot > 0){
		$db_select	=	new db_query("SELECT hot_name
												FROM	hotels
												WHERE	hot_id = "	.	$iHot);
		$row			=	mysqli_fetch_assoc($db_select->result);
		$str			.=	' <i class="icons navicon"></i>'	.	translate("Khách sạn")	.	" " . $row["hot_name"];
		unset($db_select);
	}
	return $str;
}
/**
 * Ham get danh sach quan/huyen cua city
 */
function generate_list_district($iCit = 0, $type = 0){
	$iCit	=	intval($iCit);
	$strReturn	=	'';
	$db_select = new db_query ("SELECT cou_id,cou_parent_id,cou_name
										 FROM countries " .
										" WHERE cou_id = " . $iCit);
	$row_city	=	mysqli_fetch_assoc($db_select->result);
	unset($db_select);
	$db_select	=	new db_query("SELECT dis_id,dis_name
											FROM	districts
											WHERE	dis_city_id = "	.	$iCit);
	if(mysqli_num_rows($db_select->result) > 0){
		$strReturn	.=	'<table width="100%" celspacing="5"><tr><td class="hr" colspan="3">Quận huyện</td></tr><tr>';
		$i=0;
		while($row	=	mysqli_fetch_assoc($db_select->result)){
			$i++;
			$url_dis	=	url_city($row_city, $row['dis_id'], $type);
			$strReturn	.=	'<td><a href="'	.	$url_dis	.	'" title="' . translate("Xem khách sạn ở") . ' ' . $row['dis_name'] . '">' . $row['dis_name'] . '</a></td>';
			if($i%3 == 0) $strReturn	.=	'</tr><tr' . (($i>3) ? ' class="more noneshow"' : '') .'>';
		}
		$strReturn	.=	'</tr>';
		if(mysqli_num_rows($db_select->result) > 4) $strReturn	.= '<tr><td colspan="3"><a class="showmore linksmallhotel" href="javascript:;">Xem thêm</a><span class="spriteS iconSM"></span></td></tr>';
		$strReturn	.=	'</table>';
	}
	unset($db_select);
	return $strReturn;
}
/**
 * Ham list location hien thi vao tab theo location type
 */
function generate_list_locations($iCit = 0, $iDis = 0){
	global $path_root;
	$iCit			=	intval($iCit);
	$iDis			=	intval($iDis);
	$sql			=	"";
	if($iDis > 0){
		$sql	.=	" AND loc_district_id = " . $iDis;
	}else{
		$sql	.=	" AND loc_city_id = "	.	$iCit;
	}
	$strReturn	=	'';
	$db_select	=	new db_query("SELECT	cou_id,cou_name
											FROM	countries
											WHERE	cou_id = "	.	$iCit);
	$row_city	=	mysqli_fetch_assoc($db_select->result);
	unset($db_select);
	$strReturn	.= generate_list_locations_demo($iCit, $iDis);
	$strReturn	.=	'<div id="tablistLoc" class="more noneshow listLoc border_large border_color">';
	$strReturn	.=	'<ul class="ultabs">';
	$arr_type	=	array();
	$db_select	=	new db_query("SELECT lot_id,lot_name
											FROM	locations_type
											ORDER BY lot_order");
	while($row = mysqli_fetch_assoc($db_select->result)){
		$arr_type[$row['lot_id']]	=	$row['lot_name'];
	}
	unset($db_select);
	foreach($arr_type as $key=>$value){
		$strReturn	.=	'<li><a href="#tab-' . $key . '"><span>' . $value . '</span></a></li>';
	}
	$strReturn	.=	'</ul>';
	foreach($arr_type as $key=>$value){
		$strReturn	.=	'<div id="tab-' . $key . '">';
		$db_select	=	new db_query("SELECT loc_id,loc_name,loc_district_id
												FROM	locations
												WHERE	loc_type = "	.	intval($key)	.	$sql);
		if(mysqli_num_rows($db_select->result) > 0){
			$strReturn	.=	'<table cellspacing="5" width="100%"><tr>';
			$i = 0;
			while($row = mysqli_fetch_assoc($db_select->result)){
				$i++;
				$strReturn	.=	'<td><a href="' . $path_root . 'filter.php?module=hotel&iCit=' . $iCit . '&iDis=' . $row['loc_district_id'] . '&iLoc=' . $row['loc_id'] . '"" title="' . translate("Khách sạn ở gần") . ' ' . $row['loc_name'] . '">' . $row['loc_name'] . '</a></td>';
				if($i%6 == 0) $strReturn	.=	"</tr><tr>";
			}
			$strReturn	.=	'</tr></table>';
		}else{
			$strReturn	.=	'<p style="font-size: 12px;">Không có địa danh nào.</p>';
		}
		unset($db_select);
		$strReturn	.=	'</div>';
	}
	$strReturn	.=	'</div>';
	return $strReturn;
}
/**
 * Ham lay 6 location uu tien dua ra hien thi
 */
function generate_list_locations_demo($iCit = 0, $iDis = 0){
	global $path_root;
	$iCit			=	intval($iCit);
	$iDis			=	intval($iDis);
	$sql			=	"";
	if($iDis > 0){
		$sql	.=	"loc_district_id = " . $iDis;
	}else{
		$sql	.=	"loc_city_id = "	.	$iCit;
	}
	$strReturn	=	'';
	$db_select	=	new db_query("SELECT	cou_id,cou_name
											FROM	countries
											WHERE	cou_id = "	.	$iCit);
	$row_city	=	mysqli_fetch_assoc($db_select->result);
	unset($db_select);
	$db_select	=	new db_query("SELECT loc_id,loc_name,loc_district_id
											FROM	locations
											WHERE	" . $sql . " ORDER BY loc_priority DESC");
	$total		=	mysqli_num_rows($db_select->result);
	unset($db_select);
	$db_select	=	new db_query("SELECT loc_id,loc_name,loc_district_id
											FROM	locations
											WHERE	" . $sql . " ORDER BY loc_priority DESC LIMIT 4");
	if(mysqli_num_rows($db_select->result) > 0){
		$strReturn	.=	'<table width="100%" celspacing="5"><tr><td class="hr" colspan="3">Địa danh</td></tr><tr>';
		$i = 0;
		while($row = mysqli_fetch_assoc($db_select->result)){
			$i++;
			$strReturn	.=	'<td valign="top"><a href="' . $path_root . 'filter.php?module=hotel&iCit=' . $iCit . '&iDis=' . $row['loc_district_id'] . '&iLoc=' . $row['loc_id'] . '" title="' . translate("Khách sạn ở gần") . ' ' . $row['loc_name'] . '">' . $row['loc_name'] . '</a></td>';
			if($i%2 == 0) $strReturn	.=	'</tr><tr' . (($i>3) ? ' class="more noneshow"' : '') .'>';
		}
		$strReturn		.=	'</tr>';
		if($total > 4) $strReturn	.=	'<tr><td colspan="3"><a class="showmore linksmallhotel" href="javascript:;">Xem thêm</a><span class="spriteS iconSM"></span></td></tr>';
		$strReturn	.=	'</table>';
		unset($db_select);
	}
	return $strReturn;
}
function getValueBound($string_bound = ""){
	$string  =  trim(preg_replace("/[^0-9.,]/i"," ",$string_bound));
	$fou		=	explode(",", $string);
	$arr_return = array();
	for($i = 0; $i<=3; $i++){
		$arr_return[$i] = isset($fou[$i]) ? doubleval($fou[$i]) : 0;
	}
	return $arr_return;
}
function decodeParam(){
	$string 	 = getValue("p", "str", "GET", "");
	if($string != ""){
		$myDefine = new generate_define();
		$string 	 = $myDefine->fSdecode($string);
		$string	 = json_decode($string, true);
		if($string != null){
			if(isset($_GET["p"])) unset($_GET["p"]);
			$_GET	= array_merge($_GET, $string);
		}
	}
}
function isIE6(){
	if(preg_match('/\bmsie 6/i', $_SERVER['HTTP_USER_AGENT'])){
		return true;
	}else{
		return false;
	}
}
function amount_booking($rom_id, $timefrom = 0, $timeto = 0, $num_room = 1){
	$rom_id		=	intval($rom_id);

	$amount		=	0;
	//Lay ra gia ngay thuong cua loai phong
	$db_select	=	new db_query("SELECT rom_price
											FROM	rooms
											WHERE	rom_id = "	.	$rom_id);
	$row			=	mysqli_fetch_assoc($db_select->result);
	unset($db_select);

	//Tinh tong so ngay de tinh tong so tien phai thanh toan
	$total_day_booking	=	(($timeto - $timefrom) / 86400);

	//Lay ra khoang gia dac biet voi khoang thoi gian tim kiem
	$db_getprice						=	new db_query("SELECT ropr_price
																	FROM	rooms_price
																	WHERE	ropr_time >= " . $timefrom . " AND ropr_time < " . $timeto . " AND ropr_rom_id = " . $rom_id);

	//Tong so ngay co gia dac biet
	$total_date_price_special		=	0;
	//Tong so tien cua nhung ngay co gia dac biet
	$total_amount_price_special	=	0;
	while($row_price = mysqli_fetch_assoc($db_getprice->result)){
		$total_date_price_special++;
		$total_amount_price_special += $row_price['ropr_price'];
	}
	unset($db_getprice);
	//Tinh tong so tien
	$amount	=	$num_room * ($total_amount_price_special + (($total_day_booking - $total_date_price_special) * $row['rom_price']));

	return $amount;
}

function lost_pwd_mail($email) {
	$link			=	"";
	$reset_pwd	=	"luongcaocao";
	$db_user		=	new db_query("SELECT use_id, use_password FROM users WHERE use_email = '" . $email . "'");
	if($user		=	mysqli_fetch_assoc($db_user->result)) {
		$checksum		=	md5($reset_pwd . "/" . $user['use_id'] . "/" . $user["use_password"]);
		$link		= 'home/reset_password.php?u=' . $user['use_id'] . '&validator=' . $checksum;
	}
	unset($db_user);

	$content 	= "";
	if($link == "") {
		$content .= "Xin chào, <br />";
		$content .= "Bạn hoặc ai đó vừa sử dụng địa chỉ email này để yêu cầu lấy lại mật khẩu trên trang luongcao <br />";
		$content .= "Tuy nhiên, địa chỉ email bạn cung cấp không tồn tại trong cơ sở dữ liệu của luongcao<br />";
		$content .= "Bạn vui lòng cung cấp chính xác địa chỉ email để nhận lại mật khẩu.<br />";
		$content .= "<br />Thân!<br />";
		$content .= "<span stype='color: #999'>luongcao - Chuyên trang tuyển dụng việc làm hấp dẫn, lương cao.</span>";
	} else {
		//$content .= "<div style='border:3px double #94c7ff; padding: 10px; line-height: 19px; color: #444'>";
		$content .= "Xin chào, <br />";
		$content .= "Bạn hoặc ai đó vừa sử dụng địa chỉ email này để yêu cầu lấy lại mật khẩu trên trang luongcao <br />";
		$content .= "Nếu chính xác là bạn thì mời xác nhận việc yêu cầu gửi lại mật khẩu theo đường link sau đây: <br />";
		$content .= "http://luongcao.com/" . $link;
		$content .= "<br /><br />Thân!<br />";
		$content .= "<span stype='color: #999'>luongcao - Chuyên trang tuyển dụng việc làm hấp dẫn, lương cao.</span>";
		//$content	.=	"</div>";
	}

	$send		= send_mailer_spam($email, "Xác nhận yêu cầu lấy lại mật khẩu trên trang luongcao", $content);

	if($send) {
		return 1;
	} else {
		return 0;
	}
}

function new_password_mail($email, $new_password) {
	$content	  =  "";
	$content  .=  "Mật khẩu mới của bạn là: <b>" . $new_password . "</b><br />";
	$content  .=  "Vui lòng đổi lại mật khẩu ngay khi đăng nhập thành công.<br />";
	$content  .= "<br /><br />Thân!<br />";
	$content  .= "<span stype='color: #999'>luongcao - Chuyên trang tuyển dụng việc làm hấp dẫn, lương cao.</span>";
	if(send_mailer_spam($email, "Mật khẩu mới trên trang luongcao", $content)) {
		return true;
	} else {
		return false;
	}
}

function register_success_mail($email, $uid) {
	$link 		=	"";
	$db_user		=	new db_query("SELECT use_id, use_password,use_email_active FROM users WHERE use_email = '" . $email . "'");
	if($user		=	mysqli_fetch_assoc($db_user->result)) {
		$checksum		=	md5($user['use_email_active'] . "/" . $user['use_id'] . "/" . $user["use_password"]);
		$link		= 'home/verify_acccount.php?u=' . $user['use_id'] . '&validator=' . $checksum;
	}
	unset($db_user);
	$content  = "";
	$content .= "Bạn đã đăng ký thành công tài khoản trên trang luongcao <br />";
	$content .= "Mặc định, tài khoản của bạn có thể sử dụng ngay các dịch vụ trên site của chúng tôi.<br />";
	$content .= "Tuy nhiên, để đăng tin tuyển dụng không giới hạn, bạn cần phải xác thực tài khoản của bạn theo đường dẫn sau:<br />";
	$content .= "http://luongcao.com/" . $link . "<br/>";
	$content .= "Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi<br />";
	$content .= "<br /><br />Thân!<br />";
	$content .= "<span stype='color: #999'>luongcao - Chuyên trang tuyển dụng việc làm hấp dẫn, lương cao.</span>";
	if(send_mailer_spam($email, "Xác thực tài khoản - Chào mừng bạn đến với luongcao", $content)) {
		return true;
	} else {
		return false;
	}
}

function save_candidate_info($let_mail, $let_city_id, $let_category_id) {
	$db_save	=	new db_execute_return();
	$lid		=	$db_save->db_execute("INSERT IGNORE INTO new_letter(let_mail, let_city_id, let_category_id) VALUES ('" . $let_mail . "', " . $let_city_id . ", " . $let_category_id . ")");
	unset($db_save);
	return $lid;
}

function get_navigate($array = array()){

	global $iData,$arrayCatNews,$arrayCatChild,$iCat;

	$iCat		= getValue("iCat");
	$iNew		= getValue("iData");
	$arrayRetun	=	array();
	$arrayQuery	=	array();

	$i	=	0;
	if($iCat > 0){
		$cat_parent	= 0;

		if(isset($arrayCatNews[$iCat])){
			$cat_parent	= $arrayCatNews[$iCat]['cat_parent_id'];
			if($cat_parent > 0 && isset($arrayCatChild[$cat_parent][$iCat])){
				$param = array("cat_id" => $cat_parent, 'cat_name' => $arrayCatNews[$cat_parent]['cat_name']);
				$link_category	=	rewrite_cat_news($param);
				$arrayRetun[$i]["name"]	= $arrayCatNews[$cat_parent]['cat_name'];
				$arrayRetun[$i]["link"]	= $link_category;
				$i++;
			}

			$param = array("cat_id" => $iCat, 'cat_name' => $arrayCatNews[$iCat]['cat_name']);
			$link_category	=	rewrite_cat_news($param);
			$arrayRetun[$i]["name"]	= $arrayCatNews[$iCat]['cat_name'];
			$arrayRetun[$i]["link"]	= $link_category;
			$i++;
		}
	}
	if($iData > 0){
		$db_cla	= new db_query(" SELECT *
										  FROM news
										  WHERE new_id = " . $iData, __FILE__ . " Line: " . __LINE__);
		if($rdata	= mysqli_fetch_assoc($db_cla->result)){
			$link		= rewrite_url_news($rdata);
			$arrayRetun[$i]["name"]	= html_entity_decode($rdata["new_title"], ENT_QUOTES, 'UTF-8');
			$arrayRetun[$i]["link"]	= $link;
		}
	}
	return $arrayRetun;
}

/**
 * Hàm trừ số lượng tồn kho và thêm vào bảng order
 * pro_id (id sản phẩm)
 * branch_id (chi nhánh)
 * user_parent_id (id admin)
 * quantity (số lượng trừ)
 */
function caculator_stock($oid_id = 0, $quantity = 0, $price_out = 0){
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
         file_put_contents('../logs/abcd.txt', "UPDATE " . USER_PRODUCTS . " SET usp_quantity = usp_quantity -" . intval($old_quantity) . ",usp_lats_update = ". time() ." 
                                    WHERE usp_id = " . intval($pro_id) . " AND usp_use_parent_id = " . intval($admin_id));
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
 * sửa thông tin hóa đơn bán hàng 
 */
function update_order($oid_id = 0, $quantity = 0, $price_out = 0){
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
         $sl = abs($sl);
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
      }else{
         $sl = abs($sl);
         // bắt đầu tính toán trừ số lượng
         $db_select = new db_query("SELECT * FROM ". USER_STOCK ." 
                                     WHERE uss_use_parent_id = " . intval($admin_id) . "
                                          AND uss_branch_id = ". intval($branch_id) . "
                                          AND uss_pro_id = " . intval($row_order['uso_pro_id']) . "
                                          AND uss_quantity > uss_sold                     
                                          ORDER BY uss_id DESC", __FILE__ ." Line: " . __LINE__);
         // set lại số lượng về số dương                                       
         
         while($row   = mysqli_fetch_assoc($db_select->result)){
            if($row['uss_sold'] + $sl <= $row['uss_quantity']){         
               // cộng số lượng vầo sold
               $db_update  = new db_execute("UPDATE ". USER_STOCK ." SET uss_sold = uss_sold + " . intval($sl) . "
                                             WHERE uss_id = " . $row["uss_id"] . " 
                                                      AND uss_use_parent_id = " . intval($admin_id));
               unset($db_update); 
               break;
            }else{
               $db_update  = new db_execute("UPDATE ". USER_STOCK ." SET uss_sold = " . intval($row['uss_quantity']) . "
                                             WHERE uss_id = " . $row["uss_id"] . " 
                                                      AND uss_use_parent_id = " . intval($admin_id));
               unset($db_update); 
               $sl   -= $row['uss_quantity'];
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

function detected_keyword($text = ''){
  return preg_match('/^-?[0-9]+$/', $text) ? 1 : 0;
}

// select thuoocs
function getInfoThuoc($id = 0, $table = ''){
   $arrayReturn   = array();
   $db_pro  = new db_query("SELECT * FROM " . $table . " WHERE usp_id = " . intval($id) . " LIMIT 1");
   if($row  = mysqli_fetch_assoc($db_pro->result)){
      $arrayReturn =  $row;
   }
   unset($db_pro);
   
   return $arrayReturn;
}
// merge multi level
function merge($a = array(), $b = array()){
	if(!is_array($a) || !is_array($b)) return array();

	foreach($a as $k => $vl){
		if(!is_array($vl) || !isset($b[$k])){
			if(!isset($b[$k])) $b[$k] = $a[$k];
		}else{
			$b[$k] = merge($a[$k], $b[$k]);
		}
	}

	return $b;
}

function format_login_phone($phone)
{

   $phone = str_replace('+84', '0', $phone);
   $phone  = str_replace(array(' ', '.', ',', '-'), '', $phone);
   $phone = preg_replace("/[^A-Za-z0-9 ]/", '', $phone);
   
   //Check xem có bắt đầu bằng số 0?
   if(substr($phone,0,1) == '0'){
      //09 thì là 10 số --- 01 thì là 11 số
      if(
      (substr($phone, 0, 2) == '09' && strlen($phone) == 10)
      || (substr($phone, 0, 2) == '01' && strlen($phone) == 11)
      )
      {
         return $phone;
      }
   }
   return false;
}

function search_user_product($table = '', $keyword = ''){
   global $admin_id, $branch_id;
   $data = array();
   if($table   == '') return $data;
   
   $sqlWhere  = '';
   if($keyword != ''){
      $sqlWhere = " AND usp_pro_name LIKE '%". replaceMQ($keyword) ."%'";
   }
   
   $db_products = new db_query("SELECT * FROM " . $table . " 
                                 WHERE usp_use_parent_id = " . intval($admin_id) . " 
                                       AND usp_branch_id = " . $branch_id . $sqlWhere . " 
                                       AND usp_active = 1
                                 ORDER BY usp_alias ASC LIMIT 50");
   while($row  = mysqli_fetch_assoc($db_products->result)){
      $data[$row['usp_id']] = $row;
   }
   unset($db_products);
   return $data;
}

function search_data_product($table = '', $keyword = '', $array_not_in = array()){
   $data = array();
   if($table == '') return $data;
   
   $sqlWhere  = "";
   if($keyword != ''){
      $sqlWhere = " AND dat_name LIKE '%". replaceMQ($keyword) ."%'";
   }
   if(!empty($array_not_in)){
      $sqlWhere   .= " AND dat_id NOT IN(". implode(',', $array_not_in) .") ";
   }
   
   $db_products = new db_query("SELECT * FROM datas
                                 WHERE 1 " . $sqlWhere . " AND dat_active = 1 LIMIT 50");
   while($row  = mysqli_fetch_assoc($db_products->result)){               
      $data[$row['dat_id']] = $row;
   }
   unset($db_products);
   
   return $data;
}


function deny_error($permission = 0){
   global $arrayUserRight, $admin_id;
   
   if($permission <= 0) return '';
   
   $userInfo   = array();
   $db_user = new db_query("SELECT * FROM users WHERE use_id = " . intval($admin_id) . " LIMIT 1");
   if($row     = mysqli_fetch_assoc($db_user->result)){
      $userInfo = $row;
   }else{
      return '';
   }
   unset($db_user);
   
   $html = '
      <div class="expires">
         <h3>Truy cập chức năng <b>'. $arrayUserRight[$permission]['name'] .'</b> bị từ chối: </h3>
         <p style="color: #f00;">
            Tài khoản của bạn không có quyền sử dụng chức năng này. Vui lòng liên hệ với: <br><b>'. $userInfo['use_fullname'] .'</b> Số ĐT: <b>'. $userInfo['use_phone'] .'</b><br>
            Để yêu cầu cấp quyền sử dụng tính năng: <b><i>('. $permission .') </i>'. $arrayUserRight[$permission]['name'] .'</b>
         </p>
         <br>Cảm ơn bạn đã tin tưởng và sử dụng phần mềm của chúng tôi!
      </div>
   ';
   
   return $html;
}

/**
 * Check os platform
 * 
 * 0 : desktop
 * 1 : andoird
 * 2 : winphone
 * 3 : ios
 */
function checkOS($u_agent = ''){
   $os = 0;
   if($u_agent == '') return 0;   
   $agent   = strtolower($u_agent);
   if(strpos($agent,"iphone") !== false || strpos($agent,"ipad")  !== false){
      $os = 3;
      return $os;
   }else if(strpos($agent,"windows phone")  !== false){
      $os = 2;
      return $os;
   }else if(strpos($agent,"android")  !== false){
      $os = 1;
      return $os;         
   }
   return $os;   
}


/**
 * 
 * Function generate function url filter sale list
 */
function generate_url_sale_list_filter($array_get = array(), $array_filter = array(), $key_filter = '', $url = ''){
 
   $new_url = $url;
   if(empty($array_filter) || $key_filter == '') return $new_url;
   
   $i = 0;
   foreach($array_filter as $field){
      if($field != $key_filter){
         unset($array_get[$field]);
         $i++;
         continue;
      } 
      
      if(!isset($array_get[$key_filter])){
         $array_get[$key_filter] = 'asc';      
      }else{
         if($array_get[$key_filter] == 'asc'){
            $array_get[$key_filter] = 'desc';
         }else{
            $array_get[$key_filter] = 'asc';
         }
      }
      $i++;
   }
   
   if(!empty($array_get)){
      $a = array();
      foreach($array_get as $k => $v){
         $a[] = $k . '=' . $v;
      }
      return  $url . '?' . implode('&', $a);
   }
   
   return $new_url;
}


/**
 * 
 * Function generate function url filter sale list
 */
function generate_url_sale_list_search($array_get = array(), $key_filter = '', $value = '', $url = ''){
 
   $new_url = $url;
   if($key_filter == '') return $new_url;
   
   switch($key_filter){
      case 'date':
         $date   = date('Y-m-d', $value);
         $array_get['start_fill_date'] = $date; 
         $array_get['end_fill_date'] = $date;
         break;
      case 'user':
         $array_get['user_id'] = $value;
         break;
   }
   
   if(!empty($array_get)){
      $a = array();
      foreach($array_get as $k => $v){
         $a[] = $k . '=' . $v;
      }
      return  $url . '?' . implode('&', $a);
   }
   
   return $new_url;
}

/**
 * Ham tinh thoi gian da qua ra phut
 */
function countTime($timein = 0){
	$timeCount		= time() - $timein;
	// nếu time > 30 ngày thì trả về số chính xác luôn
	if($timeCount > (30 * 86400)) return 'Lúc: ' . date('H:i - d/m/Y', $timein);

	$timeFday		= mktime(0, 0, 0, date('m', time()), date('d', time()), date('Y', time()));
	// neeus time < time đầu ngày thì hiển thị theo ngày

	// nếu time là trong ngày thì tính theo h hoặc phút
	if($timein > $timeFday){
		if($timeCount / 60 > 59){
			return round($timeCount / 3600) . ' giờ trước';
		}else{
			$phut	= round($timeCount / 60);
			if($phut < 5) return 'vài giây trước';
			return $phut . ' phút trước';
		}
	}else{
		if($timein < $timeFday && $timein >= ($timeFday - 86400)){
			return 'hôm qua';
		}else{
			return round($timeCount / 86400) . ' ngày trước';
		}
	}
}

/**
 * hàm tạo barcode
 * truyền vào 12 ký tự
 * return 13 ký tự
 */
function generate_me_barcode($ean){
   
   if(strlen($ean) != 12) return 0;
   $ean=(string)$ean;
   $even=true; $esum=0; $osum=0;
   for ($i=strlen($ean)-1;$i>=0;$i--){
      if ($even) $esum+=$ean[$i];	else $osum+=$ean[$i];
      $even=!$even;
   }
   $check_sum   = (10-((3*$esum+$osum)%10))%10;
   return $ean . $check_sum;
}

/**
 * hàm hiển thị barcde
 */
function show_barcode($barcode ,$mebarcode){
   if($barcode != '' && $barcode != 0) return $barcode;
   return $mebarcode;
}

/**
 * Hàm ghi log hành động
 * data = array(
 *    'file' =>
 *    'action' =>
 *    'des' =>
 * )
 */
function log_action($data = array()){
   global $admin_id, $branch_id, $myuser;
   
   $file    = isset($data['file'])? $data['file'] : '';
   $action  = isset($data['action'])? mb_strtolower($data['action']) : '';
   $des     = isset($data['des'])? '<b>'. $myuser->useField['use_fullname'] . '</b>' . "\n" . $data['des'] : '';
   $pro_id  = isset($data['pro_id'])? intval($data['pro_id']) : 0;
   
   $action_id = 0;
   switch($action){
      case 'add':
         $action_id = 1;
         break;
      case 'edit':
         $action_id = 2;
         break;
      case 'delete':
         $action_id = 3;
         break;
   }
   
   $type_login = $myuser->isTypeLogin();
   
   $db_log  = new db_execute("
      INSERT INTO logs(log_date,log_action,log_text,log_use_parent_id,log_use_child_id,log_branch_id,log_file,log_pro_id,log_type_login)
      VALUES(". time() .",". intval($action_id) .",'". replaceMQ($des) ."',". intval($admin_id) .",". intval($myuser->u_id) .",". intval($branch_id) .",'". replaceMQ($file) ."',". $pro_id.",". $type_login .")
   ", __FILE__, DB_NOTIFICATION);
   
   unset($db_log);
}

function show_action($ac = 0){
   $ac_name = '';
   switch($ac){
      case 1:
         $ac_name = '<span class="status_btn bg_acc">Thêm mới</span>';
         break;
      case 2:
         $ac_name = '<span class="status_btn bg_mov">Sửa</span>';
         break;
      case 3:
         $ac_name = '<span class="status_btn bg_can">Xóa</span>';
         break;
   }
   
   return $ac_name;
}

function show_type_login($t = 0){
   $type_name = '';
   switch($t){
      case 0:
         $type_name = '<span class="status_btn bg_acc">Thường</span>';
         break;
      case 1:
         $type_name = '<span class="status_btn bg_mov">Face login</span>';
         break;
      case 2:
         $type_name = '<span class="status_btn bg_can">Debug login</span>';
         break;
   }
   
   return $type_name;
}


/**
 * functin check unit
 * array(
 *    usp_unit_import =>
 *    usp_unit
 =>  * )
 */
function check_unit($data = array()){
   $unit_import = isset($data['usp_unit_import'])? intval($data['usp_unit_import']) : 0;
   $unit = isset($data['usp_unit'])? intval($data['usp_unit']) : 0;
   $specifi = isset($data['usp_specifi'])? intval($data['usp_specifi']) :  1;
   
   if($unit > 0 && $unit_import == 0) return 0;
   if($unit == 0) return 0;
   if($unit_import == 0 && $unit == 0) return 0;
   if($unit_import == 0) return 0;
   if($unit_import == $unit && $specifi > 1) return 0;
   
   return 1;
}

/**
 * Function start time on site
 */
function start_time_onsite(){
   global $admin_id, $myredis;
   $sday = strtotime('Today');
   $ss_time = isset($_SESSION['ss_time'])? $_SESSION['ss_time'] : '';
   if($ss_time > 0){
      $key_ss_time_start   = 'timeonsite:' . $sday . ':' . $admin_id . ':time_start:'. $ss_time;
      $myredis->set($key_ss_time_start, time());
   }
}

function end_time_onsite(){
   global $admin_id, $myredis;
   $sday = strtotime('Today');
   $ss_time = isset($_SESSION['ss_time'])? $_SESSION['ss_time'] : '';
   if($ss_time > 0){
      $key_ss_time_end   = 'timeonsite:' . $sday . ':' . $admin_id . ':time_end:'. $ss_time;
      $myredis->set($key_ss_time_end, time());
   }
}


function convert_utf82utf8($str = '',  $removeAccent = 0){
   $new_str = '';
   $marTViet=array(
		// Chữ thường
		"à","á","ạ","ả","ã","â","ầ","ấ","ậ","ẩ","ẫ","ă","ằ","ắ","ặ","ẳ","ẵ",
		"è","é","ẹ","ẻ","ẽ","ê","ề","ế","ệ","ể","ễ",
		"ì","í","ị","ỉ","ĩ",
		"ò","ó","ọ","ỏ","õ","ô","ồ","ố","ộ","ổ","ỗ","ơ","ờ","ớ","ợ","ở","ỡ",
		"ù","ú","ụ","ủ","ũ","ư","ừ","ứ","ự","ử","ữ",
		"ỳ","ý","ỵ","ỷ","ỹ",
		"đ","Đ",
		"À","Á","Ạ","Ả","Ã","Â","Ầ","Ấ","Ậ","Ẩ","Ẫ","Ă","Ằ","Ắ","Ặ","Ẳ","Ẵ",
		"È","É","Ẹ","Ẻ","Ẽ","Ê","Ề","Ế","Ệ","Ể","Ễ",
		"Ì","Í","Ị","Ỉ","Ĩ",
		"Ò","Ó","Ọ","Ỏ","Õ","Ô","Ồ","Ố","Ộ","Ổ","Ỗ","Ơ","Ờ","Ớ","Ợ","Ở","Ỡ",
		"Ù","Ú","Ụ","Ủ","Ũ","Ư","Ừ","Ứ","Ự","Ử","Ữ",
		"Ỳ","Ý","Ỵ","Ỷ","Ỹ",
		"Đ","Đ"
		);
	$arrTohop 	=	array( 0 => 'à', 1 => 'á', 2 => 'ạ', 3 => 'ả', 4 => 'ã', 5 => 'â', 6 => 'ầ', 7 => 'ấ', 8 => 'ậ', 9 => 'ẩ', 10 => 'ẫ', 11 => 'ă', 12 => 'ằ', 13 => 'ắ', 14 => 'ặ', 15 => 'ẳ', 16 => 'ẵ', 17 => 'è', 18 => 'é', 19 => 'ẹ', 20 => 'ẻ', 21 => 'ẽ', 22 => 'ê', 23 => 'ề', 24 => 'ế', 25 => 'ệ', 26 => 'ể', 27 => 'ễ', 28 => 'ì', 29 => 'í', 30 => 'ị', 31 => 'ỉ', 32 => 'ĩ', 33 => 'ò', 34 => 'ó', 35 => 'ọ', 36 => 'ỏ', 37 => 'õ', 38 => 'ô', 39 => 'ồ', 40 => 'ố', 41 => 'ộ', 42 => 'ổ', 43 => 'ỗ', 44 => 'ơ', 45 => 'ờ', 46 => 'ớ', 47 => 'ợ', 48 => 'ở', 49 => 'ỡ', 50 => 'ù', 51 => 'ú', 52 => 'ụ', 53 => 'ủ', 54 => 'ũ', 55 => 'ư', 56 => 'ừ', 57 => 'ứ', 58 => 'ự', 59 => 'ử', 60 => 'ữ', 61 => 'ỳ', 62 => 'ý', 63 => 'ỵ', 64 => 'ỷ', 65 => 'ỹ', 66 => 'đ', 67 => 'Đ', 68 => 'À', 69 => 'Á', 70 => 'Ạ', 71 => 'Ả', 72 => 'Ã', 73 => 'Â', 74 => 'Ầ', 75 => 'Ấ', 76 => 'Ậ', 77 => 'Ẩ', 78 => 'Ẫ', 79 => 'Ă', 80 => 'Ằ', 81 => 'Ắ', 82 => 'Ặ', 83 => 'Ẳ', 84 => 'Ẵ', 85 => 'È', 86 => 'É', 87 => 'Ẹ', 88 => 'Ẻ', 89 => 'Ẽ', 90 => 'Ê', 91 => 'Ề', 92 => 'Ế', 93 => 'Ệ', 94 => 'Ể', 95 => 'Ễ', 96 => 'Ì', 97 => 'Í', 98 => 'Ị', 99 => 'Ỉ', 100 => 'Ĩ', 101 => 'Ò', 102 => 'Ó', 103 => 'Ọ', 104 => 'Ỏ', 105 => 'Õ', 106 => 'Ô', 107 => 'Ồ', 108 => 'Ố', 109 => 'Ộ', 110 => 'Ổ', 111 => 'Ỗ', 112 => 'Ơ', 113 => 'Ờ', 114 => 'Ớ', 115 => 'Ợ', 116 => 'Ở', 117 => 'Ỡ', 118 => 'Ù', 119 => 'Ú', 120 => 'Ụ', 121 => 'Ủ', 122 => 'Ũ', 123 => 'Ư', 124 => 'Ừ', 125 => 'Ứ', 126 => 'Ự', 127 => 'Ử', 128 => 'Ữ', 129 => 'Ỳ', 130 => 'Ý', 131 => 'Ỵ', 132 => 'Ỷ', 133 => 'Ỹ', 134 => 'Đ', 135 => 'Đ' );
   if($str != ''){
      $new_str = str_replace($arrTohop, $marTViet, $str);
   }
   
   if($removeAccent == 1){
      $new_str = strtolower(removeAccent($new_str));
   }
   
   return $new_str;

}


function clean_string($text = ''){   
   $text = preg_replace("/[\r\n]+/", "\n", $text);   
   $text = str_replace("  ", " ", $text);
   $text = str_replace("  ", " ", $text);
   return $text;
}

function clean_text_error($text = '', $remove_accent = 0){
   $new_text   = '';
   $len  = mb_strlen($text, 'UTF-8');
   for($i = 0;$i<$len;$i++){
      $kt = mb_substr($text, $i, 1, 'UTF-8');
      $ord = ord($kt);
      
      if($ord != 204){
         $new_text .= $kt;
      }
   }
   
   if($remove_accent == 1){
      $new_text   = removeAccent($new_text);
      $new_text   = strtolower($new_text);
   }
   
   $new_text = str_replace('   ', ' ', $new_text);
   $new_text = str_replace('  ', ' ', $new_text);
   $new_text = str_replace('  ', ' ', $new_text);
   
   return $new_text;
}

function convert_number_to_words($number) {
 
   $hyphen      = ' ';
   $conjunction = ' ';
   $separator   = ' ';
   $negative    = 'âm ';
   $decimal     = ' phẩy ';
   $dictionary  = array(
      0                   => 'Không',
      1                   => 'Một',
      2                   => 'Hai',
      3                   => 'Ba',
      4                   => 'Bốn',
      5                   => 'Năm',
      6                   => 'Sáu',
      7                   => 'Bảy',
      8                   => 'Tám',
      9                   => 'Chín',
      10                  => 'Mười',
      11                  => 'Mười một',
      12                  => 'Mười hai',
      13                  => 'Mười ba',
      14                  => 'Mười bốn',
      15                  => 'Mười năm',
      16                  => 'Mười sáu',
      17                  => 'Mười bảy',
      18                  => 'Mười tám',
      19                  => 'Mười chín',
      20                  => 'Hai mươi',
      30                  => 'Ba mươi',
      40                  => 'Bốn mươi',
      50                  => 'Năm mươi',
      60                  => 'Sáu mươi',
      70                  => 'Bảy mươi',
      80                  => 'Tám mươi',
      90                  => 'Chín mươi',
      100                 => 'trăm',
      1000                => 'nghìn',
      1000000             => 'triệu',
      1000000000          => 'tỷ',
      1000000000000       => 'nghìn tỷ',
      1000000000000000    => 'nghìn triệu triệu',
      1000000000000000000 => 'tỷ tỷ'
   );
    
   if (!is_numeric($number)) {
      return false;
   }
    
   if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
   // overflow
   trigger_error(
   'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
   E_USER_WARNING
   );
   return false;
   }
    
   if ($number < 0) {
   return $negative . convert_number_to_words(abs($number));
   }
    
   $string = $fraction = null;
    
   if (strpos($number, '.') !== false) {
   list($number, $fraction) = explode('.', $number);
   }
    
   switch (true) {
   case $number < 21:
   $string = $dictionary[$number];
   break;
   case $number < 100:
   $tens   = ((int) ($number / 10)) * 10;
   $units  = $number % 10;
   $string = $dictionary[$tens];
   if ($units) {
   $string .= $hyphen . $dictionary[$units];
   }
   break;
   case $number < 1000:
   $hundreds  = $number / 100;
   $remainder = $number % 100;
   $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
   if ($remainder) {
   $string .= $conjunction . convert_number_to_words($remainder);
   }
   break;
   default:
   $baseUnit = pow(1000, floor(log($number, 1000)));
   $numBaseUnits = (int) ($number / $baseUnit);
   $remainder = $number % $baseUnit;
   $string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
   if ($remainder) {
   $string .= $remainder < 100 ? $conjunction : $separator;
   $string .= convert_number_to_words($remainder);
   }
   break;
   }
    
   if (null !== $fraction && is_numeric($fraction)) {
   $string .= $decimal;
   $words = array();
   foreach (str_split((string) $fraction) as $number) {
   $words[] = $dictionary[$number];
   }
   $string .= implode(' ', $words);
   }
 
   return $string;
}



?>