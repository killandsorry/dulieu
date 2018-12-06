<?
class userStatistic{
	
	var $uid = 0;
	var $arrayFeldValue = array();
	var $is_insert = false;
	var $arrayNew	= array();
	var $arrayUpdate = array();
	
	function __construct($use_id){
		$this->uid = intval($use_id);
		$this->getRow();
	}
	
	/**
	 * lay ra thong tin co san trong db
	 */
	function getRow(){
		if($this->uid <= 0) return;
		global $branch_id;
		$db_select = new db_query("SELECT * 
											FROM users_statistic 
											WHERE ust_use_id = " . $this->uid . " AND ust_branch_id = " . intval($branch_id) . "  
											LIMIT 1");
		if($row = mysqli_fetch_assoc($db_select->result)){
			$this->arrayFeldValue			=	$row;	
		}else{
			$this->arrayNew["ust_use_id"] 		= $this->uid;
			$this->arrayNew["ust_last_update"] 	= time();
			$this->arrayNew["ust_branch_id"] 	= $branch_id;
			$this->is_insert 							=	true;
		}
	}
	
	/**
	 * Ham so sanh va tinh lai du lieu neu can update
	 */
 	function setReCount($field_name, $value){
 		$current_value = isset($this->arrayFeldValue[$field_name]) ? $this->arrayFeldValue[$field_name] : 0;
 		//neu gia tri trong db khac voi gia tri tinh duoc thi them vao array de cap nhat
 		if($current_value != $value){
 			$this->arrayNew[$field_name] = intval($value);
 			$this->arrayUpdate[$field_name] = "" . $field_name . "=" . intval($value);
 		}
 	}
 	
 	/**
 	 * ham luu tru thong tin ham nay xu ly cuoi cung
 	 */
	function save(){
		if($this->uid <= 0) return;
		global $branch_id;
		if($this->is_insert){
			$arrayField = array_keys($this->arrayNew);
			$db_ex = new db_execute("INSERT IGNORE INTO users_statistic(" . implode(",",$arrayField) . ") VALUES(" . implode(",",$this->arrayNew) . ")");
			unset($db_ex);
		}elseif(!empty($this->arrayUpdate)){
			$this->arrayUpdate["ust_last_update"] = "ust_last_update=" . time();
			$sql = "UPDATE users_statistic SET " . implode(",", $this->arrayUpdate) . " WHERE ust_use_id = " . $this->uid . " AND ust_branch_id = " . intval($branch_id);
			//echo $sql;
			$db_ex = new db_execute($sql);
			unset($db_ex);
		}
	}
	
}