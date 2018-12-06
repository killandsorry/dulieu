<?
/**
 * /right_field = intval(right / 31) (tức là mỗi cột chỉ lấy 30 giá trị)
 * 
 * 
 * use_right (1 -> 30) => value = pow(1->30)
 * use_right_1 (31 -> 60) => value = pow(right - 30)
 * use_right_2 (61 -> 90) => value = pow(right - 60)
 */

// array loại tài khoản
define('USER_TYPE_NORMAL', 2); // tài khoản thường
define('USER_TYPE_CTV', 4); // tài khoản cộng tác viên
define('USER_TYPE_NCC', 8); // tài khoản nhà cung cấp
define('USER_TYPE_ADMIN', 16); // tài khoản ADMIN
define('USER_TYPE_SALE', 32); // tài khoản sale

$array_user_type = array(
   USER_TYPE_CTV 		=> 'Chăm sóc',
   USER_TYPE_SALE		=> 'Kinh doanh',
   USER_TYPE_NCC 		=> 'N. Cung cấp',
   USER_TYPE_ADMIN 	=> 'Admin quản trị'
);

//khai báo array quyền của user
$arrayUserRight		= array();
define('USER_RIGHT_STOCK_ADD',1);
define('SYSTEM_SECRET_REIGHT','fdlsjfldslf' . date('today'));
$arrayUserRight[USER_RIGHT_STOCK_ADD] = array("right" => pow(2, USER_RIGHT_STOCK_ADD)
															,"name" => "Quản lý nhập thuốc"
                                             ,'default' => 0);
define('USER_RIGHT_STOCK_LIST',2);
$arrayUserRight[USER_RIGHT_STOCK_LIST] = array("right" => pow(2, USER_RIGHT_STOCK_LIST)
															,"name" => "Xem lịch sử nhập thuốc"
                                             ,'default' => 0);
define('USER_RIGHT_PRODUCT_LIST',3);
$arrayUserRight[USER_RIGHT_PRODUCT_LIST] = array("right" => pow(2, USER_RIGHT_PRODUCT_LIST)
															,"name" => "Xem danh sách thuốc"
                                             ,'default' => 0);
define('USER_RIGHT_MEMBER',4);
$arrayUserRight[USER_RIGHT_MEMBER] = array("right" => pow(2, USER_RIGHT_MEMBER)
															,"name" => "Quản lý thêm nhân viên"
                                             ,'default' => 0);
define('USER_RIGHT_SETTING',5);
$arrayUserRight[USER_RIGHT_SETTING] = array("right" => pow(2, USER_RIGHT_SETTING)
															,"name" => "Quản lý thiết lập"
                                             ,'default' => 0);
define('USER_RIGHT_BRANCH',6);
$arrayUserRight[USER_RIGHT_BRANCH] = array("right" => pow(2, USER_RIGHT_BRANCH)
															,"name" => "Quản lý chi nhánh"
                                             ,'default' => 0);
define('USER_RIGHT_SALE_ADD',7);
$arrayUserRight[USER_RIGHT_SALE_ADD] = array("right" => pow(2, USER_RIGHT_SALE_ADD)
															,"name" => "Tạo hóa đơn bán thuốc"
                                             ,'default' => 1);
define('USER_RIGHT_SALE_LIST',8);
$arrayUserRight[USER_RIGHT_SALE_LIST] = array("right" => pow(2, USER_RIGHT_SALE_LIST)
															,"name" => "Danh sách hóa đơn bán thuốc"
                                             ,'default' => 1);
define('USER_RIGHT_USER_STATISTIC',9);
$arrayUserRight[USER_RIGHT_USER_STATISTIC] = array("right" => pow(2, USER_RIGHT_USER_STATISTIC)
															,"name" => "Xem doanh số của chính nhân viên"
                                             ,'default' => 1);
define('USER_RIGHT_STATISTIC_HOME',10);
$arrayUserRight[USER_RIGHT_STATISTIC_HOME] = array("right" => pow(2, USER_RIGHT_STATISTIC_HOME)
															,"name" => "Xem thống kê chung"
                                             ,'default' => 0);  
define('USER_RIGHT_PRODUCT_WARNING',11);
$arrayUserRight[USER_RIGHT_PRODUCT_WARNING] = array("right" => pow(2, USER_RIGHT_PRODUCT_WARNING)
															,"name" => "Danh sách thuốc cảnh báo sắp hết hàng"
                                             ,'default' => 0); 
define('USER_RIGHT_STATISTIC_SALE',12);
$arrayUserRight[USER_RIGHT_STATISTIC_SALE] = array("right" => pow(2, USER_RIGHT_STATISTIC_SALE)
															,"name" => "Thống kê doanh số theo ngày"
                                             ,'default' => 0);  
define('USER_RIGHT_STATISTIC_MEMBER',13);
$arrayUserRight[USER_RIGHT_STATISTIC_MEMBER] = array("right" => pow(2, USER_RIGHT_STATISTIC_MEMBER)
															,"name" => "Thống kê doanh số theo nhân viên"
                                             ,'default' => 0); 
define('USER_RIGHT_STATISTIC_BRANCH',14);
$arrayUserRight[USER_RIGHT_STATISTIC_BRANCH] = array("right" => pow(2, USER_RIGHT_STATISTIC_BRANCH)
															,"name" => "Thống kê doanh số theo chi nhánh"
                                             ,'default' => 0);  
define('USER_RIGHT_HISTORY_CLOSE_BOOK',15);
$arrayUserRight[USER_RIGHT_HISTORY_CLOSE_BOOK] = array("right" => pow(2, USER_RIGHT_HISTORY_CLOSE_BOOK)
															,"name" => "Xem lịch sử gửi chốt doanh số"
                                             ,'default' => 1); 
define('USER_RIGHT_STATISTIC_PRO_EXPIRES',16);
$arrayUserRight[USER_RIGHT_STATISTIC_PRO_EXPIRES] = array("right" => pow(2, USER_RIGHT_STATISTIC_PRO_EXPIRES)
															,"name" => "Xem thuốc sắp hết hạn sử dụng"
                                             ,'default' => 0); 
define('USER_RIGHT_HISTORY_CLOSE_BOOK_ACCESS',17);
$arrayUserRight[USER_RIGHT_HISTORY_CLOSE_BOOK_ACCESS] = array("right" => pow(2, USER_RIGHT_HISTORY_CLOSE_BOOK_ACCESS)
															,"name" => "Duyệt yêu cầu chốt sổ"
                                             ,'default' => 0);
define('USER_RIGHT_CHECK_STOCKS',18);
$arrayUserRight[USER_RIGHT_CHECK_STOCKS] = array("right" => pow(2, USER_RIGHT_CHECK_STOCKS)
															,"name" => "Yêu cầu kiểm kho"
                                             ,'default' => 0);
define('USER_RIGHT_PROFIT',19);
$arrayUserRight[USER_RIGHT_PROFIT] = array("right" => pow(2, USER_RIGHT_PROFIT)
															,"name" => "Ước tính lãi bán hàng"
                                             ,'default' => 0);
define('USER_RIGHT_PRODUCT_ACTIVE',20);
$arrayUserRight[USER_RIGHT_PRODUCT_ACTIVE] = array("right" => pow(2, USER_RIGHT_PRODUCT_ACTIVE)
															,"name" => "Danh sách thuốc đã xóa"
                                             ,'default' => 0); 
define('USER_PRODUCT_SALE_MOST',21);
$arrayUserRight[USER_PRODUCT_SALE_MOST] = array("right" => pow(2, USER_PRODUCT_SALE_MOST)
															,"name" => "Danh sách thuốc bán chạy nhất"
                                             ,'default' => 0);
define('USER_PRODUCT_PROFIT_MOST',22);
$arrayUserRight[USER_PRODUCT_PROFIT_MOST] = array("right" => pow(2, USER_PRODUCT_PROFIT_MOST)
															,"name" => "Ước tính thuốc lãi nhiều nhất"
                                             ,'default' => 0);
define('USER_RIGHT_STOCKS_MOVE',23);
$arrayUserRight[USER_RIGHT_STOCKS_MOVE] = array("right" => pow(2, USER_RIGHT_STOCKS_MOVE)
															,"name" => "Chuyển kho hàng"
                                             ,'default' => 0);
define('USER_RIGHT_STOCKS_MOVE_ACCESS',24);
$arrayUserRight[USER_RIGHT_STOCKS_MOVE_ACCESS] = array("right" => pow(2, USER_RIGHT_STOCKS_MOVE_ACCESS)
															,"name" => "Nhận hàng chuyển kho"
                                             ,'default' => 0);
define('USER_RIGHT_STOCKS_MOVE_LIST',25);
$arrayUserRight[USER_RIGHT_STOCKS_MOVE_LIST] = array("right" => pow(2, USER_RIGHT_STOCKS_MOVE_LIST)
															,"name" => "Lịch sử chuyển kho"
                                             ,'default' => 0); 
define('USER_RIGHT_STOCKS_ACCESS',26);
$arrayUserRight[USER_RIGHT_STOCKS_ACCESS] = array("right" => pow(2, USER_RIGHT_STOCKS_ACCESS)
															,"name" => "Nhận chuyển kho"
                                             ,'default' => 0);
define('USER_RIGHT_LOGS',27);
$arrayUserRight[USER_RIGHT_LOGS] = array("right" => pow(2, USER_RIGHT_LOGS)
															,"name" => "Lịch sử hành động"
                                             ,'default' => 0);
define('USER_RIGHT_INDEX',28);
$arrayUserRight[USER_RIGHT_INDEX] = array("right" => pow(2, USER_RIGHT_INDEX)
															,"name" => "Chỉ số theo ngày"
                                             ,'default' => 0);                                             
define('USER_RIGHT_NEW_USER',29);
$arrayUserRight[USER_RIGHT_NEW_USER] = array("right" => pow(2, USER_RIGHT_NEW_USER)
															,"name" => "Người dùng đăng ký mới"
                                             ,'default' => 0);
define('USER_RIGHT_OLD_USER',29);
$arrayUserRight[USER_RIGHT_OLD_USER] = array("right" => pow(2, USER_RIGHT_OLD_USER)
															,"name" => "Người dùng sắp hết hạn"
                                             ,'default' => 0);                                                                                          
define('USER_RIGHT_ACTIVITI',30);
$arrayUserRight[USER_RIGHT_ACTIVITI] = array("right" => pow(2, USER_RIGHT_ACTIVITI)
															,"name" => "Người dùng sử dụng thường xuyên"
                                             ,'default' => 0); 
define('USER_RIGHT_CALL_PRODUCT',31);
$arrayUserRight[USER_RIGHT_CALL_PRODUCT] = array("right" => pow(2, USER_RIGHT_CALL_PRODUCT)
															,"name" => "Gọi hàng nhà cung cấp"
                                             ,'default' => 0);
                                            
define('USER_RIGHT_PRICE_LIST_PRODUCT',32);
$arrayUserRight[USER_RIGHT_PRICE_LIST_PRODUCT] = array("right" => pow(2, USER_RIGHT_PRICE_LIST_PRODUCT)
															,"name" => "Báo giá sản phẩm"
                                             ,'default' => 0);
define('USER_RIGHT_CALL_LIST_PRODUCT',33);
$arrayUserRight[USER_RIGHT_CALL_LIST_PRODUCT] = array("right" => pow(2, USER_RIGHT_CALL_LIST_PRODUCT)
															,"name" => "Danh sách gọi hàng"
                                             ,'default' => 0); 
define('USER_RIGHT_PRICE_LIST_PRODUCT_WAIT',34);
$arrayUserRight[USER_RIGHT_PRICE_LIST_PRODUCT_WAIT] = array("right" => pow(2, USER_RIGHT_PRICE_LIST_PRODUCT_WAIT)
															,"name" => "Đơn thuốc chờ báo giá"
                                             ,'default' => 0); 
define('USER_RIGHT_IMPORT_EXCEL',35);
$arrayUserRight[USER_RIGHT_IMPORT_EXCEL] = array("right" => pow(2, USER_RIGHT_IMPORT_EXCEL)
															,"name" => "Nhập kho từ file excel"
                                             ,'default' => 0); 
define('USER_RIGHT_SHOW_PRICE_IMPORT',36);
$arrayUserRight[USER_RIGHT_SHOW_PRICE_IMPORT] = array("right" => pow(2, USER_RIGHT_SHOW_PRICE_IMPORT)
															,"name" => "Quyền được xem giá nhập"
                                             ,'default' => 0);
define('USER_RIGHT_CUSTOMER',37);
$arrayUserRight[USER_RIGHT_CUSTOMER] = array("right" => pow(2, USER_RIGHT_CUSTOMER)
															,"name" => "Quản lý khách hàng"
                                             ,'default' => 0);   
define('USER_RIGHT_BONUS_LIST', 38);                       
$arrayUserRight[USER_RIGHT_BONUS_LIST] = array("right" => pow(2, USER_RIGHT_BONUS_LIST)
															,"name" => "Hóa đơn đã giảm giá"
                                             ,'default' => 0);                                                                                                                                                                                                                                                 
define('USER_RIGHT_SALE_MONEY', 39);                       
$arrayUserRight[USER_RIGHT_SALE_MONEY] = array("right" => pow(2, USER_RIGHT_SALE_MONEY)
															,"name" => "Thuốc bán theo doanh số"
                                             ,'default' => 0);                                             
define('USER_RIGHT_T_SALE_LIST', 40);                       
$arrayUserRight[USER_RIGHT_T_SALE_LIST] = array("right" => pow(2, USER_RIGHT_T_SALE_LIST)
															,"name" => "Quản lý nhân viên sale"
                                             ,'default' => 0);

define('USER_RIGHT_STATISTIC_SALE_MONEY', 41);                       
$arrayUserRight[USER_RIGHT_STATISTIC_SALE_MONEY] = array("right" => pow(2, USER_RIGHT_STATISTIC_SALE_MONEY)
															,"name" => "Thống kế thuốc bán theo doanh số"
                                             ,'default' => 0);
define('USER_RIGHT_T_SALE_SEND_ACTIVE', 42);                       
$arrayUserRight[USER_RIGHT_T_SALE_SEND_ACTIVE] = array("right" => pow(2, USER_RIGHT_T_SALE_SEND_ACTIVE)
															,"name" => "Gửi yêu cầu kích hoạt trả phí"
                                             ,'default' => 0);
define('USER_RIGHT_T_SALE_CUSTOMER', 43);                       
$arrayUserRight[USER_RIGHT_T_SALE_CUSTOMER] = array("right" => pow(2, USER_RIGHT_T_SALE_CUSTOMER)
															,"name" => "Quản lý khách hàng"
                                             ,'default' => 0);

define('USER_RIGHT_T_SALE_CUS_NEW', 44);                       
$arrayUserRight[USER_RIGHT_T_SALE_CUS_NEW] = array("right" => pow(2, USER_RIGHT_T_SALE_CUS_NEW)
															,"name" => "Khách hàng mới đăng ký"
                                             ,'default' => 0);
define('USER_RIGHT_T_SALE_CONNECT', 45);                       
$arrayUserRight[USER_RIGHT_T_SALE_CONNECT] = array("right" => pow(2, USER_RIGHT_T_SALE_CONNECT)
															,"name" => "Liên hệ khách mới"
                                             ,'default' => 0);  
define('USER_RIGHT_PRODUCT_AC_UN_ACTIVE', 46);                       
$arrayUserRight[USER_RIGHT_PRODUCT_AC_UN_ACTIVE] = array("right" => pow(2, USER_RIGHT_PRODUCT_AC_UN_ACTIVE)
															,"name" => "Xóa thuốc"
                                             ,'default' => 0); 
define('USER_RIGHT_BOX', 47);                       
$arrayUserRight[USER_RIGHT_BOX] = array("right" => pow(2, USER_RIGHT_BOX)
															,"name" => "Tủ thuốc - Danh mục thuốc"
                                             ,'default' => 1);      
define('USER_RIGHT_PROVIDER', 48);                       
$arrayUserRight[USER_RIGHT_PROVIDER] = array("right" => pow(2, USER_RIGHT_PROVIDER)
															,"name" => "Quản lý nhà cung cấp"
                                             ,'default' => 1);              
define('USER_RIGHT_LIA', 49);                       
$arrayUserRight[USER_RIGHT_LIA] = array("right" => pow(2, USER_RIGHT_LIA)
															,"name" => "Công nợ nhà cung cấp"
                                             ,'default' => 0); 
define('USER_RIGHT_BARCODE', 50);                       
$arrayUserRight[USER_RIGHT_BARCODE] = array("right" => pow(2, USER_RIGHT_BARCODE)
															,"name" => "In mã vạch"
                                             ,'default' => 0); 
define('USER_RIGHT_SETTING_COMBO', 51);                       
$arrayUserRight[USER_RIGHT_SETTING_COMBO] = array("right" => pow(2, USER_RIGHT_SETTING_COMBO)
															,"name" => "Tạo liều thuốc"
                                             ,'default' => 0);                                                            
                                             
                                                                             
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  

                                                                                            
                                                                                                                                        
                                                                                                                                                                                                                                                                           
                                             


                                                                                                                                     
foreach($arrayUserRight as $key=>$val){
	$val["key_right"]		 = md5(SYSTEM_SECRET_REIGHT . $val["right"]);
	$arrayUserRight[$key] = $val;
}													     
/**
*class user
*Developed by FinalStyle.com
*/
class user{
	var $logged = 0;
	var $login_name;
	var $use_name;
	var $use_address;
	var $password;
 	var $u_avatar;
	var $u_id = -1;
	var $level = 0;
	var $group_right = 0;
   var $u_parent_id = -1;
	var $user_right_name_array;
	var $user_right_quantity_array;
	var $use_security;
	var $use_admin = 0;
	var $use_phone;
	var $use_city;
	var $use_email;
	var $use_birthday;
	var $use_gender;
	var $use_email_active;
	var $useField = array();
	var $arrayAllStaff = array();//array chứa chủ cửa hàng va các nhân viên
	protected $key_sercurity_fake = "fdsljfldslr23l42l";
	protected $key_sercurity_dev = "fds23423gfdgfdtretertre";
	protected $isFakeLogin = false;
	protected $isDevLogin	= false;
	protected $guest_id = 0;
	/*
	init class
	login_name : ten truy cap
	password  : password (no hash)
	level: nhom user; 0: Normal; 1: Admin (default level = 0)
	*/
	function __construct($login_name="", $password="", $tcookie = 0){
		$checkcookie=0;
		$this->logged = 0;
		if ($login_name=="" && $tcookie == 0){
			if (isset($_COOKIE["fname"])) $login_name = $_COOKIE["fname"];
		}
		if ($password=="" && $tcookie == 0){
			if (isset($_COOKIE["fid"])) $password = $_COOKIE["fid"];
			$checkcookie=1;
		}
		else{
			//remove \' if gpc_magic_quote = on
			$password = str_replace("\'","'",$password);
		}
        
		if ($login_name=="" && $password=="") return;
		
		$db_user = new db_query("SELECT *
										 FROM users
										 WHERE use_login = '" . $this->removequote($login_name) . "' OR use_phone = '" . $this->removequote($login_name) . "'");
     		if ($row=mysqli_fetch_assoc($db_user->result)){
			//kiem tra password va use_active
            if($checkcookie==0)	$password=md5($password . $row["use_security"]);
            
			if (($password==$row["use_pass"] || $password== md5($this->key_sercurity_fake . $row["use_pass"]) || $password== md5($this->key_sercurity_dev . $row["use_pass"])) && $row["use_active"]==1) {

				//kiem tra xem neu la fake login thi gan vao bien de con check
				if($password== md5($this->key_sercurity_fake . $row["use_pass"])){
					$this->isFakeLogin = true;
				}

				//kiem tra xem neu la dev login thi gan vao bien de con check
				if($password== md5($this->key_sercurity_dev . $row["use_pass"])){
					$this->isDevLogin = true;
				}
			   
				$this->logged = 1;
				$this->login_name 	 	= $login_name;
				$this->use_name 			= $row["use_fullname"];
				$this->password 			= $password;
				$this->use_security	   = $row["use_security"];
				$this->u_id 				= intval($row["use_id"]);
            $this->use_is_admin 			= $row["use_is_admin"];
				$this->use_address		= $row["use_address"];
				$this->use_phone			= $row["use_phone"];
				$this->use_email			= $row["use_email"];
            $this->u_parent_id		= intval($row["use_parent_id"]);
				$this->useField  			= $row;
			}
		}
		unset($db_user);
		
      /*
		//get right list
		$db_right = new db_query("SELECT *
								  FROM user_right
								  ORDER BY ur_quantity DESC");
		$i=-1;
		while ($row=mysqli_fetch_array($db_right->result)){
			if ((intval($row["ur_code"]) & intval($this->group_right)) !=0){
				$i++;
				$this->user_right_name_array[$i] = $row["ur_variable"]; 
				$this->user_right_quantity_array[$i] = $row["ur_quantity"];
				//$this->user_right_list .= "[" . $row["ur_variable"] . "]";
			}
		}
      */
		//echo $this->user_right_list;
		
	}

	function isFakeLogin(){
		return $this->isFakeLogin;
	}

	function isDevLogin(){
		return $this->isDevLogin;
	}

	/**
	 * neu field_name bang rong thi tra ve array tat cac truong con neu khac rong thi tra ve thong tin truong do
	 */
	function getInfoStaff($use_id,$filed_name = ""){
		//nếu chưa select ra thông tin thì bắt đầu gọi ra
		if(empty($this->arrayAllStaff)){
			$db_select = new db_query("SELECT * FROM users WHERE use_parent_id = " . intval($this->u_parent_id) . " AND use_parent_id > 0");
			while($row = mysqli_fetch_assoc($db_select->result)){
				$this->arrayAllStaff[$row["use_id"]] = $row;
			}
			unset($db_select);
		}
		
		if($use_id == 0){
			return $this->arrayAllStaff;
		}else{
			if(isset($this->arrayAllStaff[$use_id])){
				return isset($this->arrayAllStaff[$use_id][$filed_name]) ? $this->arrayAllStaff[$use_id][$filed_name] : $this->arrayAllStaff[$use_id];
			}else{
				return array();
			}
		}
	}
	
	/**
	 * Ham kiem tra xem phai parent hay ko
	 */
 	function isParent(){
 		if($this->u_id == $this->u_parent_id){
 			return true;
 		}else{
 			return false;
 		}
 	}
	
   function checkRight($right, $user_id = 0){
		global $arrayUserRight, $admin_id;
		$right = intval($right);
		//nếu không tồn tại quyền thì return luôn
		if(!isset($arrayUserRight[$right])) return false;
		//nếu là user admin thì full quyền luôn
      
      if($user_id == 0){
         // check chính thằng đang đăng nhập
         if($this->u_parent_id == 0 || $this->u_parent_id == $this->u_id) return true;  
         //kiểm tra quyền xem có được thực thi hay không
         $key_right     = intval($right / 31);
         $field_right   = 'use_right';
         if($key_right > 0) $field_right .= '_' . $key_right;
         
         if($key_right <= 0){
            if((intval($arrayUserRight[$right]["right"]) & intval($this->useField['use_right'])) == intval($arrayUserRight[$right]["right"])){
      			return true;
      		}else{
      			return false;
      		}
         }else{
            $vl   = pow(2,$right - ($key_right * 30));
            if((intval($vl) & intval($this->useField[$field_right])) == intval($vl)){
      			return true;
      		}else{
      			return false;
      		}
         }
         
      }else{
         // admin check tài khoản con
         $db_member  = new db_query("SELECT * FROM users WHERE use_id = " . intval($user_id) . " AND use_parent_id = " . $admin_id . " LIMIT 1");
         if($rmember = mysqli_fetch_assoc($db_member->result)){
            //kiểm tra quyền xem có được thực thi hay không            
      		$key_right     = intval($right / 31);
            $field_right   = 'use_right';
            if($key_right > 0) $field_right .= '_' . $key_right;
            
            if($key_right <= 0){
               if((intval($arrayUserRight[$right]["right"]) & intval($rmember['use_right'])) == intval($arrayUserRight[$right]["right"])){
         			return true;
         		}else{
         			return false;
         		}
            }else{
               $vl   = pow(2,$right - ($key_right * 30));
               if((intval($vl) & intval($rmember[$field_right])) == intval($vl)){
         			return true;
         		}else{
         			return false;
         		}
            }
            
         }else{
            return false;
         }
         unset($db_member);
      }
		
	}
	
	/**
	 * Hàm tính tổng quyền của users
	 */
 	function getSumRight($array_key_right){
      $array_right   = array(
         'use_right' => 0,
         'use_right_1' => 0,
         'use_right_2' => 0
      ); 	 
    
 		if(!is_array($array_key_right)) return $array_right;
 		global $arrayUserRight;
 		$use_right = 0;
 		foreach($arrayUserRight as $key => $val){
 			$key_right = $val["key_right"];
 			if(in_array($key_right,$array_key_right)){
            
            $key_field     = intval($key / 31);
            $field_right   = 'use_right';
            if($key_field > 0) $field_right .= '_' . $key_field;
            
            if($key_field <= 0){
               $array_right[$field_right] += $val["right"];
            }else{
               $vl   = pow(2, $key - ($key_field * 30));
               $array_right[$field_right] += $vl;
            }
 			}
 		}
 		return $array_right;
 	}
   
	/**
	 * Ham get Guest ID
	 */
 	function guestId(){
 		$guest_id = getValue("guest_id", "int", "COOKIE", $this->guest_id);
 		if($guest_id <= 0){
 			$db_ex = new db_execute_return(); 
		 	$guest_id = $db_ex->db_execute("insert into users_guest() VALUES()");
		 	$this->guest_id = $guest_id;
		 	unset($db_ex);
 		}
 		setcookie("guest_id",$guest_id,time() + 86400*100,"/");
 		setcookie("guest_id",$guest_id,time() + 86400*100,"/", ".banthuoconline.com");
 		setcookie("guest_id",$guest_id,time() + 86400*100,"/", "www.banthuoconline.com");
 		return $guest_id;
 	}
	
	/* Ham face login */
	function face_login($mail = ''){
		$email	=	replaceMQ(trim($mail));
		$db_user = new db_query("SELECT use_id,use_login,use_email,use_address,use_phone,use_password,use_fullname, use_active,group_right,use_security,use_name,use_admin, use_avatar,use_city,use_birthday,use_gender,use_admin,use_supplier
										 FROM users, user_group
										 WHERE use_login = '" . $email . "' OR use_email = '" . $email . "'");
		if ($row=mysqli_fetch_array($db_user->result)){			
			$this->logged = 1;
			$this->login_name 	 	= $row['use_login'];
			$this->use_name 			= $row["use_fullname"];
			$this->password 			= $row['use_password'];
			$this->use_security	   = $row["use_security"];
			$this->u_id 				= intval($row["use_id"]);
			$this->group_right 	   = $row["group_right"];
			$this->use_admin 			= $row["use_admin"];
         $this->u_avatar         = $row["use_avatar"];
			$this->use_city 			= $row["use_city"];
			$this->use_address		= $row["use_address"];
			$this->use_phone			= $row["use_phone"];
			$this->use_city			= $row["use_city"];
			$this->use_email			= $row["use_email"];
			$this->use_birthday		= $row["use_birthday"];
			$this->use_gender			= $row["use_gender"];
			$this->useField  			= $row;
			setcookie("fname",$this->login_name,null,"/");
			setcookie("fid",$this->password,null,"/");		
		}
		unset($db_user);
	}
	
	function isAdmin(){
		$arg_list = func_get_args();
		$numargs  = count($arg_list);
		for($i = 0; $i < $numargs; $i++){
			$right_value = intval($arg_list[$i]);
			if($right_value != 0){
				if(($right_value & $this->use_supplier) == $right_value){
					return true;
				}
			}
		}
		if(!isset($this->useField["use_admin"])) return false;
		if($this->useField["use_admin"] == 1){
			return true;
		}else{
			return false;
		}
	}
	
	function isSupplier(){
		if(!isset($this->useField["use_supplier"])) return false;
		if($this->useField["use_supplier"] == 1){
			return true;
		}else{
			return false;
		}
	}
	
	/*
	Ham lay truong thong tin ra
	*/
	function row($field){
		if(isset($this->useField[$field])){
			return $this->useField[$field];
		}else{
			return '';
		}
	}
	/*
	save to cookie
	time : thoi gian save cookie, neu = 0 thi` save o cua so hien ha`nh
	*/
	function savecookie($time=0){
		if ($this->logged!=1) return false;	
		if ($time > 0){
            setcookie("fname",$this->login_name,time()+$time,"/");   
            setcookie("fid",$this->password,time()+$time,"/");
		}
		else{
			setcookie("fname",$this->login_name,null,"/");
			setcookie("fid",$this->password,null,"/");           	
		}
	}
	
	/**
	 * Function auto create account and fake login
	 * input: email
	 */
function create_account($email = '', $pass = ''){
		$mail	=	replaceMQ(htmlspecialchars($email));
		//kiểm tra đã có mail như này chưa
		$db_check	=	new db_query("SELECT * FROM users WHERE use_login = '". $mail ."'");
		if($row		=	mysqli_fetch_assoc($db_check->result)){
			//Nếu là user ứng viên thì update lên user nhà tuyển dụng
			if($row['use_type'] == 0){
				$db_up	=	new db_execute("UPDATE users SET use_type = 1 WHERE use_id = ".intval($row['use_id']));
				unset($db_up);
			}
			if($row['use_email_active'] != 1){
				$db_up	=	new db_execute("UPDATE users SET use_email_active = 1 WHERE use_id = ".intval($row['use_id']));
				unset($db_up);
			}
			$this->logged = 1;
			$this->login_name = $mail;
			$this->password	= $row['use_password'];
			$this->savecookie(100 * 86400);			
			return 1;
		}else{
			//tạo tài khoản bằng chính email đã nhận được 
			$db_ex	=	new db_execute_return();
			$newuse_id	=	$db_ex->db_execute("INSERT INTO users(use_login, use_name, use_fullname, use_active, use_password, use_type,use_email_active,use_email) 
												 VALUES('". $mail ."','". $mail ."','". $mail ."',1,'". md5($pass) ."',1,1,'". $mail . "')");
			if($newuse_id > 0){
				$this->logged		=	1;
				$this->login_name = $mail;
				$this->password 	= md5($pass);				
				$this->savecookie(100 * 86400);
				unset($db_ex);
				//Gửi email đến nhà tuyển dụng
				$content		=	'Chào '. $mail . ", <br>";
				$content		.=	"Hệ thống tuyển dụng việc làm vừa tạo 01 tài khoản đăng tin tuyển dụng cho bạn trên trang luongcao<br>";
				$content		.=	"Tài khoản: ". $mail . "<br>";
				$content		.=	"Mật khẩu: ". $pass . "<br>";
				$content		.=	"Bạn vui lòng <a href='http:luongcao.com/'>Đăng nhập</a> để thay đổi thông tin và quản lý tin tuyển dụng.<br>";
				$content		.=	'Thân ái,  <br><br>';
				$content		.=	"<hr>" . "<br>";
				$send			=	send_mailer($mail, 'Quản lý tin tuyển dụng trên trang luongcao', $content, 'BQT website tuyển dụng việc làm luongcao');
				
				//Lấy toàn bộ tin của email này Update lại toàn bộ tin đăng của user này thành có ID
				$db_cla	=	new db_query("SELECT cla_des_id AS cla_id 
													FROM classifields_description
													WHERE cla_email ='". $mail . "'");
				$arrayupdate = array();
				while($r_cla	=	mysqli_fetch_assoc($db_cla->result)){
					$arrayupdate[$r_cla['cla_id']] = $r_cla['cla_id'];
				}
				unset($db_cla);			
				if(count($arrayupdate) > 0){
					$db_upcla	=	new db_execute("UPDATE classifields SET cla_user_id=". $newuse_id . ' WHERE cla_id IN(' . implode(",", $arrayupdate) . ')');
					unset($db_upcla);
				}
				return 1;
			}else{
				return 0;
			}
		}
		unset($db_check);	
	}//end function
	
	
	/*
	Logout account
	*/
	function logout(){
      setcookie("fname","",null,"/");
		setcookie("fid","",null,"/");
		$this->logged=0;
	}
	//kiem tra password de thay doi email
	function check_password($password){
		$db_user = new db_query("SELECT use_password,use_security
										 FROM users, user_group
										 WHERE use_active=1 AND use_email = '" . $this->removequote($this->login_name) . "'");
		if ($row=mysqli_fetch_array($db_user->result)){
			$password=md5($password . $row["use_security"]);
			if($password==$row["use_password"]) return 1;
		}
		unset($db_user);
	}

	/*
	Remove quote
	*/
	function removequote($str){
		$temp = str_replace("\'","'",$str);
		$temp = str_replace("'","''",$temp);
		return $temp;
	}
	
	/*
	check_user_level: Kiem tra xem User co thuoc nhom Admin hay khong. Mac dinh User thuoc nhom Normal.
	table_name: ten bang (Ex; Users)
	data_field: ten truong trong bang (Ex; use_level)
	data_level_value: Gia tri cua use_level (0: Normal member; 1: Admin member)
	where_clause: Dieu kien them
	dump_query: In cau lenh ra man hinh. (0: No; 1: Yes)
	*/
	function check_user_level($table_name,$data_field,$data_level_value,$where_clause="",$dump_query=0){
		if ($this->logged!=1) return 0;
		$level = "SELECT " . $data_field . "
					  FROM " . $table_name . "
					  WHERE " . $data_field . "=" . intval($data_level_value) . " " . $where_clause;
		//Dum_query
		if ($dump_query==1) echo $level;
		//kiem tra query
		$db_check_level = new db_query($level);
		//Check record > 0
		if (mysqli_num_rows($db_check_level->result) > 0){
			unset($db_check_level);
			return 1;
		}
		else{
			unset($db_check_level);
			return 0;
		}
	}
	
	/*
	check_data_in_db : Kiem tra xem data hien thoi co phai thuoc user ko (check trong database)
	table_name : ten table
	data_id_field : Truong id vi du : new_id
	data_id_value : gia tri cua id vi du : 10
	user_id_field : ten truong user_id cua bang do vi du : new_userid, pro_userid....
	where_clause : cua query them va`o sau where vi du : new_approved = 1...
	dump_query : co hien thi query hay ko de debug loi. 0 : ko hien, 1: hien thi
	*/
	function check_data_in_db($table_name,$data_id_field,$data_id_value,$user_id_field,$where_clause="",$dump_query=0){
		if ($this->logged!=1) return 0;
		$my_query =  "SELECT " . $data_id_field . "
					  FROM " . $table_name . "
					  WHERE " . $data_id_field . "=" . $data_id_value . " AND " . $user_id_field . "=" . intval($this->u_id) . " " . $where_clause;

		//neu dump_query = 1 thi in ra ma`n hinh
		if ($dump_query==1) echo $my_query;
		
		//kiem tra query
		$db_check = new db_query($my_query);
		//neu ton tai record do thi` tra ve gia tri 1, neu ko thi` tra ve gia tri 0
		if (mysqli_num_rows($db_check->result) > 0){
			unset($db_check);
			return 1;
		}
		else{
			unset($db_check);
			return 0;
		}
	}
	
	/*
	check_data : kiem tra xem data co phai thuoc user_id khong (check trong luc fetch_array)
	user_id : gia tri user id để so sánh
	*/
	function check_data($user_id){
		if ($this->logged!=1) return 0;
		if ($this->u_id != $user_id) return 0;
		return 1;
	}
	
	/*
	change password : Sau khi change password phải dùng hàm save cookie. Su dung trong truong hop Change Profile
	*/
	function change_password($old_password,$new_password){
		
		//replace quote if gpc_magic_quote = on
		$old_password = str_replace("\'","'",$old_password);
		$new_password = str_replace("\'","'",$new_password);
		
		//chua login -> fail
		if ($this->logged!=1) return 0;
		//old password ko đúng -> fail
		if (md5($old_password . $this->use_security)!=$this->password) return 0;
		
		//change password
		$db_update = new db_execute("UPDATE users
									 SET use_password = '" . md5($new_password . $this->use_security). "'
									 WHERE use_id = " . intval($this->u_id));
		//reset password
		$this->password = md5($new_password . $this->use_security);
		return 1;
	}
	
	/* ghi thông tin của user vao cookei*/
	
	function setFakeLogin($login_name,$isDevLogin = false){
		if($login_name == "") return '';
		$email		=	replaceMQ($login_name);
		$db_select 	= new	db_query("SELECT use_login,use_email,use_password
										 FROM users
	 									 WHERE (use_login = '" . $login_name . "' OR use_email = '" . $login_name . "'  OR use_phone = '" . $login_name . "')
  										 LIMIT 1");
		if($row = mysqli_fetch_assoc($db_select->result)){
			$this->logged 		= 1;
			$this->login_name 	= $row["use_login"];
			$keyBefore			= ($isDevLogin) ? $this->key_sercurity_dev : $this->key_sercurity_fake;
			$this->password 	= md5($keyBefore . $row["use_password"]);
			$this->savecookie();
		}
		
	}

	function isTypeLogin(){
		if($this->isDevLogin){
			return 2;
		}elseif($this->isFakeLogin){
			return 1;
		}else{
			return 0;
		}
	}
	
	/*
	check user access
	*/
	
	function check_access($right_list,$id_value=0){
		$right_array = explode(",",$right_list);
		//lap trong right_list de tim quyen (right)
		//print_r($this->user_right_name_array);
		
		for ($i=0;$i<count($right_array);$i++){
			//neu user_right_name_array ma bang rong tuc la khong co quyen nao ca thi return 0
			if(!is_array($this->user_right_name_array)) return 0;
			//Tim thay quyen cua trong right list
			//if (strpos($this->user_right_list,$right_array[$i])!==false){
			//Tim trong array
			
			$key = array_search($right_array[$i], $this->user_right_name_array); 
			//co ton tai
		
			if ($key!==false){
				//eval global variable
				$vartemp = $right_array[$i];
				global $$vartemp;
				$temp = $$vartemp;
				//Kiem tra xem bien dc eval co ton tai khong
				if (!isset($temp)) { echo "<b>Variable " . $right_array["$i"] . " is undefined. </b><br>"; return 0;}
				
				//Neu co soluong va` action ko phai fullaccess thi` kiem tra so luong
				if ($this->user_right_quantity_array[$key]!=0 && $temp["action"]!="fullaccess" ){
					//gan query
					$sql = "SELECT count(*) as count
							FROM " . $temp["table_name"] . "
							WHERE " . $temp["user_id_field"] . "=" . $this->u_id . " ";
					//echo $sql;
					//neu action = change value them sql
					if ($temp["action"]=="changevalue") $sql.= " AND " . $temp["change_field"] . "= 1 ";
		
					//neu id them va`o khac 0 thi` loai bo id khoi cau lenh sql
					if ($id_value!=0) $sql.=" AND " . $temp["id_field"] . "<>" . $id_value;
					
					//Execute SQL
					$db_sum = new db_query($sql);
					$row = mysqli_fetch_array($db_sum->result);
					unset($db_sum);
					
					//Kiem tra count neu nho hon gia tri cho phep thi` return 1
					if ($row["count"] < $this->user_right_quantity_array[$key]) return 1;
					
				}
				else{
					return 1;
				}
			}
		}
		return 0;
	}
}
?>
<?
/*
defined right
Bao gom cac thong so sau :
right gom co :  insert : Them 1 ban ghi moi, 
				update : Sua chua ban ghi, 
				delete : Xoa ban ghi, 
				changevalue : Sua 1 column (field) na`o day trong ban ghi, vi du : hot, news, approver
				fullaccess : Admin 1 muc nao do
*/
$right_list = array("right_admin_catalogue");
/*
Defined right detail
*/
//Right admin user access module Blogs
$right_admin_catalogue = array("table_name"     =>  "",
						   		"id_field"       		=>  "",
						  		 	"user_id_field"  		=>  "",
						   		"change_field"			=>  "",
						   		"action"		    		=>  "fullaccess",
						   		"quantity"				=>  "",
						   		"description"			=>  "Admin module Catalogue",
						   		"name"					=>  "right_admin_catalogue");
?>