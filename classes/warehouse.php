<?
/**
 * Class xử lý xuất nhập kho và bán hàng
 * xử lý mọi hoạt động thay đổi giá bán số tồn 
 * tạo hóa đơn
 * đều thông qua class này hết
 */
class warehouse{
	/**
	 * Hàm khởi tạo
	 */
	function __construct(){
		
	}
	
	/**
	 * Hàm thêm vào kho hàng
	 */
 	function stockAdd($param = array()){
 		$paramStruct = array("date" 					=> time()	//ngày tạo
		 					,"date_expires"		=>	0	//ngày hết hạn
		 					,"use_parent_id"		=> 0	//id admin
		 					,"use_child_id"		=> 0	//id người nhập
		 					,"price_import"		=> 0 //giá nhập
		 					,"price_out"			=> 0 //giá bán
		 					,"quantity"				=> 0 //số lượng
		 					,"unit"					=> 0 //đơn vị
		 					,"pro_id"						=> 0 //id sản phẩm
		 					,"dat_id"						=> 0 //id sản phẩm sàn
		 					,"branch_id"					=> 0 //id chi nhánh
		 					,"status"						=> 0 // trạng thái
		 					,"quantity_unit_parent"		=> 0 //đơn vị tính theo dạng hộp
		 					,"quantity_unit_child"		=> 0 //
							 );
                      
      $data = array_merge($paramStruct, $param);
		//gán lại biến
		foreach($data as $key => $val){
		    $$key = $val;	
		}//foreach
      
      if($use_parent_id <= 0) $use_parent_id = CURRENT_ADMIN_ID;
      if($use_child_id <= 0) $use_child_id   = CURRENT_USER_CHILD_ID;
      if($branch_id <= 0) $branch_id         = CURRENT_BRANCH_ID;
		
      // kiểm tra trong bảng đã có sản phẩm này chưa
      // có rồi thì update số lượng và giá bán
      // chưa có thì thêm mới
      $db_check   = new db_query("SELECT * FROM " . USER_STOCK . "
                                  WHERE uss_pro_id = " . $pro_id . "
                                  AND uss_use_parent_id = " . $use_parent_id . " 
                                  AND uss_use_child_id = " . $use_child_id . "
                                  AND uss_status = 0 LIMIT 1");
      if($rcheck  = mysqli_fetch_assoc($db_check->result)){
         // thay đổi lái số lượng và giá nhập
         $this->stockUpdateItem();
      }else{
         // thêm mới vào bảng stocks
         $db_ex = new db_execute_return();
   		$record_id = $db_ex("INSERT INTO " . USER_STOCK . "(uss_date,uss_date_expires,uss_use_parent_id,uss_use_child_id,uss_price_import,uss_price_out,uss_quantity,uss_unit,uss_pro_id,uss_dat_id,uss_branch_id,uss_quantity_unit_parent,uss_quantity_unit_child,uss_cogs)
   										VALUES(" . intval($date) . "," . intval($date_expires) . "," . intval($use_parent_id) . "," . intval($use_child_id) . "," . doubleval($price_import) . "," . doubleval($price_out) . "," . intval($quantity_unit_parent) . "," . intval($quantity_unit_child) . "," . doubleval($cogs) . ")"); 
         unset($db_ex);
      }
      unset($db_check);
   		
		return $record_id;
		 
 	}
 	
 	/**
 	 * Hàm sửa kho hàng
 	 */
 	function stockEdit(){
 		
 	}
 	
 	/**
 	 * Hàm tính toán lại và nhập kho chính thức
 	 */
 	function stockReCount(){
 		
 	}
 	
 	/**
 	 * Hàm xóa kho hàng
 	 */
 	function stockDelete(){
 		
 	}
 	
 	/**
 	 * Hàm thêm Hóa đơn
 	 */
 	function saleAdd($param = array()){
 		
 		$paramStruct = array("date" 					=> time()	//ngày tạo
 									,"pro_id"						=> 0 //id sản phẩm
				 					,"dat_id"						=> 0 //id sản phẩm sàn
				 					,"branch_id"					=> 0 //id chi nhánh
				 					,"status"						=> 0 // trạng thái
				 					,"use_parent_id"		=> 0	//id admin
				 					,"use_child_id"		=> 0	//id người nhập
				 					,"price_out"			=> 0 //giá bán
				 					,"quantity"				=> 0 //số lượng
				 					,"total_money"			=> 0 //đơn vị
				 					,"record_id"			=> 0 //id cần cập nhật nếu có
									 );
	 	//gán lại biến
		foreach($paramStruct as $key => $val){
			global $$key;
			//nếu dữ liệu truyền vào thì gán vào biến global
			if(isset($param[$key])) $paramStruct[$key] = $param[$key];	
		}//foreach
		
		//nếu là thêm mới thì
		if($record_id <= 0){
			//lấy thông tin lần nhập hàng cuối cùng 
			$uso		=	$this->stockGetLastInfo($pro_id);
			$uss_id	= empty($uso) ? 0 : $uso["uso_id"];
			$cogs		= empty($uso) ? 0 : $uso["uss_cogs"];
			$db_ex = new db_execute_return();
			$record_id = $db_ex("INSERT INTO " . USER_SALE . "(uso_pro_id,uso_dat_id,uso_branch_id,uso_use_parent_id,uso_use_child_id,uso_date,uso_quantity,uso_price_out,uso_total_money,uso_uss_id,uso_cogs)
										VALUES(" . intval($paramStruct["pro_id"]) . "," . intval($dat_id) . "," . intval($branch_id) . "," . intval($use_child_id) . "," . intval($date) . "," . intval($quantity) . "," . doubleval($price_out) . "," . doubleval($total_money) . "," . $uss_id . "," . doubleval($cogs) . ")"); unset($db_ex);
		}else{//ngược lại thì cập nhật
			
		}//if($record_id <= 0){
 		
 		return $record_id;
 	}
 	
 	/**
 	 * Hàm sửa hóa đơn
 	 */
 	function saleEdit(){
 		
 	}
 	
 	/**
 	 * Hàm lấy info trong bảng stock
 	 */
 	function stockGetLastInfo($param = array()){
 		
 		$paramStruct = array("date" 					=> time()	//ngày tạo
 									,"pro_id"						=> 0 //id sản phẩm
				 					,"dat_id"						=> 0 //id sản phẩm sàn
				 					,"branch_id"					=> 0 //id chi nhánh
				 					,"use_parent_id"		=> 0	//id admin
				 					,"use_child_id"		=> 0	//id người nhập
									,"uss_id"				=> 0	//id bản ghi lần nhập nếu có
									 );
	   //gán lại biến
		foreach($paramStruct as $key => $val){
			global $$key;
			//nếu dữ liệu truyền vào thì gán vào biến global
			if(isset($param[$key])){
				//$$key = $param[$key];
				$paramStruct[$key]	=	$$key;	
			}	
			
		}//foreach
		
 		$sqlWhere = "uss_pro_id = " . intval($pro_id) . "";
 		if(intval($uss_id) > 0)	$sqlWhere = "uss_id = " . intval($uss_id) . "";
 		$db_select = new db_query("SELECT * FROM ". USER_STOCK ." WHERE " . $sqlWhere . " ORDER BY uss_id DESC LIMIT 1");
 		if($row = mysqli_fetch_assoc($db_select->result)){
 			return $row;
		//ngược lại nếu chưa có thì thêm mặc định vào
 		}else{
 			$uss_id = $this->stockAdd($paramStruct);
 			$param["uss_id"]	=	$uss_id;
 			if($uss_id > 0) return $this->stockGetLastInfo($param);
 		}
 		unset($db_select);
 		return array();
 	}
 	
   
 	
}
?>