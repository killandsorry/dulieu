<?
define('REDIS_SLAVE_TIMEOUT_CONNECT', 1);
define('REDIS_MASTER_TIMEOUT_CONNECT', 2);
/**
 * Class redis
 * create 24/01/2013
 * dinhtoan1905
 */
class redis_store{

	var $redis			= false;
	var $redis_master = false;
	var $redis_online = false;
	var $master_host 	= "";
	var $slave_host	= "";
	var $online_host	= ''; //server check user online
	protected $redis_password = "";
	protected $time_load = 0;
	protected $list_check_online = array();
	/**
	 * Ham khoi tao
	 */
 	function __construct($temp_host = ""){
 		global $redis_analyze_list;
		if (!isset($redis_analyze_list)) $redis_analyze_list = "";

		$this->master_host		= "127.0.0.1";
		$this->online_host		= "127.0.0.1";//server check user online
		$this->redis_password	= "2l34l2fldjslflww3";
		$this->slave_host			= "127.0.0.1";
		//neu localhost thi connect chinh server tren may test
		if($_SERVER['SERVER_NAME'] == 'localhost'){
			$this->master_host		= "127.0.0.1";
			$this->slave_host			= "127.0.0.1";
			$this->redis_password	= "";
		}
		//neu su dung host de luu tam thoi ko can replication
		if($temp_host != ""){
			$this->master_host		= $temp_host;
			$this->slave_host			= $temp_host;
		}

 	}//end function

 	function getRedisHost($default_server="192.168.1.160"){

		//N?u dã có server trong Const r?i thì return luôn
		if (defined("REDIS_CLIENT_IP")) return REDIS_CLIENT_IP;

		if($_SERVER["SERVER_NAME"] == "vchat.vn" || $_SERVER["SERVER_NAME"] == "www.vchat.vn"){
			return "192.168.1.160";
		}

		// IP server => Weight (Tr?ng s? quy d?nh xem server nào ch?u t?i nhi?u hon)
		$array_server = array("192.168.1.142"	=> 1,
									 "192.168.1.143"	=> 1,
									 "192.168.1.145"	=> 1,
									 "192.168.1.148"	=> 1,
									 "192.168.1.149"	=> 1,
									 "192.168.1.162"	=> 1,
									 );

		//Loop array d? t?o array m?i, dàn d?u weight thành nh?ng ph?n t? d? l?y random
		$total_weight = 0;
		$new_array_server = array();

		//Bi?n array server thành 1 array ph?ng g?m n ph?n t?, n = t?ng tr?ng s?
		foreach ($array_server as $m_key => $m_value){
			//Loop t? 0 d?n weight d? tao array
			for ($i = 0; $i < $m_value; $i++){
				$total_weight++;
				$new_array_server[$total_weight] = $m_key;
			}
		}

		//N?u ko có server nào thì tr? v? server default
		if ($total_weight < 1) return $default_server;

		//B?t d?u l?y random
		$my_ape_client = rand(1, $total_weight);
		//Tr? v? server
		if (isset($new_array_server[$my_ape_client])){
			//N?u chua có Const SLAVE_SERVER_IP thì gán b?ng const
			if (!defined("REDIS_CLIENT_IP")) define("REDIS_CLIENT_IP", $new_array_server[$my_ape_client]);
			return $new_array_server[$my_ape_client];
		}
		else return $default_server;

	}//end function

	//Hàm tính time
	function microtime_float(){
	   list($usec, $sec) = explode(" ", microtime());
	   return ((float)$usec + (float)$sec);
	}

	/**
	 * check slow redis
	 */
 	protected function check_slow($start_time, $line){
 		$time_load = $this->microtime_float() - $start_time;
 		if(doubleval($time_load) > 1){
 			$this->log("redis_slow", $time_load . " Line: " . $line);
 		}
 	}

	/**
	 * Hàm ghi log
	 */
	function log($filename, $content){

		$log_path = str_replace("classes", "logs", dirname(__FILE__)) . "/";
		$handle = @fopen($log_path . $filename . ".cfn", "a");
		//N?u handle chua có m? thêm ../
		if (!$handle) $handle = @fopen($log_path . $filename . ".cfn", "a");

		@fwrite($handle, date("d/m/Y h:i:s A") . " " . @$_SERVER["REQUEST_URI"] . "\n" . $content . "\n");
		//fwrite($handle, date("d/m/Y h:i:s A") . "\n");
		@fclose($handle);

	}//end function

	/**
	 * Ham kiem tra xem key co ton tai hay ko
	 */
 	function exists($key){
 		$start_time = $this->microtime_float();
 		//neu connect khong thanh cong thi connect
	 	if(!$this->slave_connect()){
 			return (is_array($key)) ? array() : 0;
 		} //if(!$this->redis)

 		//neu khong phai la array thi return luon theo ham exists
 		if(!is_array($key)){
 			$return = $this->redis->exists($key);
 			$this->check_slow($start_time, __LINE__);
 			return $return;
 		}else{

		 	$arrayReturn = array();

 			foreach($key as $val){
 				$arrayReturn[$val] = $this->redis->exists($val);
 			}//end foreach
 			$this->check_slow($start_time, __LINE__);
 			return $arrayReturn;
 		}
 	}//end function

 	/**
    * Tự động tăng giá trị 1 key
    */
   function incr($key, $amount = 1)
   {
      //neu master chua connect thi connect
      if(!$this->master_connect())
      {
         return 0;
      } //if(!$this->redis_master)

      //goi ham incr tu redis
      return $this->redis_master->incr($key, $amount);
   }

 	function checkOnline($key){
 		$start_time = $this->microtime_float();
 		//neu connect khong thanh cong thi connect
	 	if(!$this->online_connect()){
 			return (is_array($key)) ? array() : 0;
 		} //if(!$this->redis)

 		//neu khong phai la array thi return luon theo ham exists
 		if(!is_array($key)){
 			$key_check = $key;
	 		if(is_numeric($key)){
	 			//neu la khach vang lai thi chia nho key ra de check
	 			if($key > CHAT_START_GUEST_ID){
	 				$key1 = $key % 100;
	 				$key2 = $key % 1000;
	 				$key_check = "online_guest:" . $key1 . ":" . $key2 . ":" . $key;
	 			}else{
		 			$key_check = "online:" . $key;
		 		}
	 		}
 			//neu user nay da check roi thi return luon
		 	if(isset($this->list_check_online[$key])) return $this->list_check_online[$key];

 			$return = intval($this->redis_online->exists($key_check));
 			$this->list_check_online[$key] = $return;
 			$this->check_slow($start_time, __LINE__);
 			return $return;
 		}else{

		 	$arrayReturn = array();

 			foreach($key as $val){
 				$key_check = $val;
		 		if(is_numeric($val)){
		 			//neu la khach vang lai thi chia nho key ra de check
		 			if($val > CHAT_START_GUEST_ID){
		 				$key1 = $val % 100;
		 				$key2 = $val % 1000;
		 				$key_check = "online_guest:" . $key1 . ":" . $key2 . ":" . $val;
		 			}else{
			 			$key_check = "online:" . $val;
			 		}
		 		}
 				if(isset($this->list_check_online[$val])){
 					$arrayReturn[$val] = $this->list_check_online[$val];
 				}else{
	 				$arrayReturn[$val] = intval($this->redis_online->exists($key_check));
	 				$this->list_check_online[$val] = $arrayReturn[$val];
 				}
 			}//end foreach
 			$this->check_slow($start_time, __LINE__);
 			return $arrayReturn;
 		}
 	}

 	/**
 	 * Ham sadd
 	 */
 	function sadd($key, $value){
 		//neu master chua connect thi connect
	 	if(!$this->master_connect()){
 			return 0;
 		} //if(!$this->redis_master)

 		//goi ham sadd tu redis
 		return $this->redis_master->sadd($key, $value);

 	}//end function

 	/**
 	 * Ham sadd
 	 */
 	function get($key){
 		//neu master chua connect thi connect
	 	if(!$this->slave_connect()){
 			return 0;
 		} //if(!$this->redis_master)

 		//goi ham sadd tu redis
 		return $this->redis->get($key);

 	}//end function

	function keys($key){
 		//neu master chua connect thi connect
	 	if(!$this->slave_connect()){
 			return 0;
 		} //if(!$this->redis_master)

 		//goi ham sadd tu redis
 		return $this->redis->keys($key);

 	}//end function

 	function delete($key){
 		//neu master chua connect thi connect
	 	if(!$this->master_connect()){
 			return 0;
 		} //if(!$this->redis_master)

 		//goi ham sadd tu redis
 		return $this->redis_master->delete($key);

 	}//end function

 	/**
 	 * Ham sadd
 	 */
 	function set($key, $value){
 		//neu master chua connect thi connect
	 	if(!$this->master_connect()){
 			return 0;
 		} //if(!$this->redis_master)

 		//goi ham sadd tu redis
 		return $this->redis_master->set($key, $value);

 	}//end function

 	function expire($key, $time)
   {
      if(!$this->master_connect())
      {
         return 0;
      } //if(!$this->redis_master)

      //goi ham sadd tu redis
      return $this->redis_master->expire($key, $time);
   }

 	/**
 	 * Ham get value cua 1 key
 	 * Parameters
	  * Key: key
	  * Return value
	  * An array of elements, the contents of the set.
 	 */
 	function sMembers($key){
 		//neu connect khong thanh cong thi connect
 		$start_time = $this->microtime_float();
	 	if(!$this->slave_connect()){
 			return (is_array($key)) ? array() : null;
 		} //if(!$this->redis)

 		$data  = $this->redis->sMembers($key);
 		$this->check_slow($start_time, __LINE__);
 		return $data;

 	}//end function

 	/**
 	 * Ham check xem key value ton tai hay ko
 	 */
 	function sIsMember($key, $value){
 		//neu connect khong thanh cong thi connect
	 	if(!$this->slave_connect()){
 			return 0;
 		} //if(!$this->redis)

 		return $this->redis->sIsMember($key, $value);

 	}//end function



 	/**
 	 * Ham publish du lieu vao redis
 	 */
 	function publish($channel, $string){
 		//neu o localhost thi post len dev server
 		if($_SERVER["SERVER_NAME"] == 'localhost'){
 			$arrayPost = array("channel" => $channel, "string" => $string);
 			$arrayPost = base64_url_encode(json_encode($arrayPost));
 			$ch = curl_init();
 			//http://dev.live.vchat.vn/api/dev.php
 			//http://localhost:9029/api/dev.php
			curl_setopt($ch, CURLOPT_URL,"http://dev.live.vchat.vn/api/dev.php");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "data=" . $arrayPost . "&type=publish");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			$server_output = curl_exec ($ch);
			curl_close ($ch);
			return $server_output;
 		}
 		$start_time = $this->microtime_float();
 		//neu master chua connect thi connect
	 	if(!$this->master_connect()){
 			return 0;
 		} //if(!$this->redis_master)

 		//goi ham publish tu redis
 		$return = $this->redis_master->publish($channel, $string);
 		$this->check_slow($start_time, __LINE__);
 		return $return;

 	}

 	/**
 	 *
 	 */
 	function lPush($key, $value){
 		//neu master chua connect thi connect
	 	if(!$this->master_connect()){
 			return 0;
 		} //if(!$this->redis_master)

 		//goi ham publish tu redis
 		return $this->redis_master->lPush($key, $value);
 	}
 	
 	function lPushx($key, $value){
 		//neu master chua connect thi connect
	 	if(!$this->master_connect()){
 			return 0;
 		} //if(!$this->redis_master)

 		//goi ham publish tu redis
 		return $this->redis_master->lPushx($key, $value);
 	}
 	
 	/**
 	 *
 	 */
 	function brPop($key, $time = 0){
 		//neu master chua connect thi connect
	 	if(!$this->master_connect()){
 			return 0;
 		} //if(!$this->redis_master)

 		//goi ham publish tu redis
 		return $this->redis_master->brPop($key, $time);
 	}
 	
 	function rPush($key, $value){
 		//neu master chua connect thi connect
	 	if(!$this->master_connect()){
 			return 0;
 		} //if(!$this->redis_master)

 		//goi ham publish tu redis
 		return $this->redis_master->rPush($key, $value);
 	}
 	
 	function rPushx($key, $value){
 		//neu master chua connect thi connect
	 	if(!$this->master_connect()){
 			return 0;
 		} //if(!$this->redis_master)

 		//goi ham publish tu redis
 		return $this->redis_master->rPushx($key, $value);
 	}

 	function lRem($key, $value, $ofset = 0){
 		//neu master chua connect thi connect
	 	if(!$this->master_connect()){
 			return 0;
 		} //if(!$this->redis_master)

 		//goi ham publish tu redis
 		return $this->redis_master->lRem($key, $value, $ofset);
 	}

 	/**
 	 * ham cap nhat va giu lai ket qua theo index tu $start den $end
 	 */
 	function lTrim($key, $start, $end){
 		//neu master chua connect thi connect
	 	if(!$this->master_connect()){
 			return 0;
 		} //if(!$this->redis_master)

 		//goi ham publish tu redis
 		return $this->redis_master->lTrim($key, $start, $end);
 	}

 	/**
 	 * Ham cap history chat cua user
 	 */
 	function updateHistoryUserChat($my_id, $to_id, $pro_id = 0){
 		//neu master chua connect thi connect
	 	if(!$this->master_connect()){
 			return 0;
 		} //if(!$this->redis_master)

		//chia nho cap luu history ra theo dang history_chat:level_1:my_id value la to_id
		$level_1 = $my_id % 1000;
		$arrayHistory = $this->lRange("history_chat:" . $level_1 . ":" . $my_id, 0, 20);

		if(is_array($arrayHistory) && $to_id != ""){
			$key_save = $to_id . ',' . $pro_id . ',' . time();
			$check_save = true;
			//lap gia tri de check xem user do ton tai chua neu ton tai roi thi them vao trong truong hop pro_id > 0

				foreach($arrayHistory as $key => $value){
					//neu ton tai user id do roi
					if(strpos("|" . $value, "|" . $to_id . ',') !== false && $value != $key_save){
						if($pro_id > 0){
							$this->lRem("history_chat:" . $level_1 . ":" . $my_id, $key_save, 0);
						}else{
							$check_save = false;
						}//if($pro_id > 0)
					}//end if
				}

			if(!in_array($key_save, $arrayHistory) && $check_save){
				//xoa het nhung user thu 20 tro di
				$this->lTrim("history_chat:" . $level_1 . ":" . $my_id, 0, 19);
				//echo "history_chat:" . $level_1 . ":" . $my_id;
				return $this->lPush("history_chat:" . $level_1 . ":" . $my_id, $key_save);
			}
		} //if(is_array($arrayHistory) && $to_id != "")


 	}//end function

 	function getInfo(){
 		//neu master chua connect thi connect
	 	if(!$this->master_connect()){
 			return 0;
 		} //if(!$this->redis_master)
 		return $this->redis_master->info();
 	}

 	function getHistoryUserChat($my_id){
 		$start_time = $this->microtime_float();
 		//neu master chua connect thi connect
	 	if(!$this->slave_connect()){
 			return 0;
 		} //if(!$this->redis_master)

 		//chia nho cap luu history ra theo dang history_chat:level_1:my_id value la to_id
		$level_1 = $my_id % 1000;
		$arrayHistory = $this->lRange("history_chat:" . $level_1 . ":" . $my_id, 0, 20);
		//$this->log("redis_dump", json_encode($arrayHistory));
		$this->check_slow($start_time, __LINE__);
		foreach($arrayHistory as $key => $value){
			$arr = explode(",",$value);
			$u_id = intval($arr[0]);
			$pro_id= isset($arr[1]) ? intval($arr[1]) : 0;
			$time	 = isset($arr[2]) ? intval($arr[2]) : 0;
			$arrayHistory[$key] = array("uid" => $u_id,"pro_id" => $pro_id, "time" => $time);
			unset($arr);
		}
		return $arrayHistory;
 	}

 	/**
 	 * ham lay ra ket qua theo index tu $start den $end
 	 */
 	function lRange($key, $start, $end){
 		//neu master chua connect thi connect
	 	if(!$this->slave_connect()){
 			return 0;
 		} //if(!$this->redis_master)

 		//goi ham publish tu redis
 		return @$this->redis->lRange($key, $start, $end);
 	}

 	/**
 	 * Truong hop ham nay duoc goi khi co lenh can tuong tac vao master
 	 */
 	protected function master_connect(){

 		if($this->redis_master == "error"){
 			return false;
 		}elseif($this->redis_master !== false){
 			return true;
 		}//end if
 		if(!class_exists("Redis")){
			$this->log("redis_error", "Redis not found extension");
			$this->redis_master = "error";
			return false;
		}
 		try
		{
			$host			 = $this->master_host;
			$this->redis_master = new Redis();
			$this->redis_master->pconnect($host, 6379, REDIS_MASTER_TIMEOUT_CONNECT);
			if($this->redis_password != ""){
				$this->redis_master->auth($this->redis_password); //mat khau connect toi server
			}
			return true;
		}
		catch (Exception $e)
		{
			$this->redis_master = "error";
			$this->log("redis_error_connect", $host . ":" . $e);
			return false;
		}
 	}//end function

 	/**
 	 * Ham nay duoc goi khi co lenh su dung den redis slave
 	 */
 	protected function slave_connect(){

 		if($this->redis == "error"){
 			return false;
 		}elseif($this->redis !== false){
 			return true;
 		}//end if
 		if(!class_exists("Redis")){
				$this->log("redis_error", "Redis not found extension");
				$this->redis = "error";
				return false;
		}
 		try
		{

			$host			 = $this->slave_host;
			$this->redis = new Redis();
			$this->redis->pconnect($host, 6379, REDIS_SLAVE_TIMEOUT_CONNECT);
			if($this->redis_password != ""){
				$this->redis->auth($this->redis_password); //mat khau connect toi server
			}
			return true;
		}
		catch (Exception $e)
		{
			$this->redis = "error";
			$this->log("redis_error_connect", $host . ":" . $e);
			return false;
		}
 	}//end function

 	/**
 	 * Ham nay duoc goi khi co lenh su dung den redis slave
 	 */
 	protected function online_connect(){

 		if($this->redis_online == "error"){
 			return false;
 		}elseif($this->redis_online !== false){
 			return true;
 		}//end if
 		if(!class_exists("Redis")){
				$this->log("redis_error", "Redis not found extension");
				$this->redis_online = "error";
				return false;
		}
 		try
		{

			$host			 = $this->online_host;
			$this->redis_online = new Redis();
			$this->redis_online->pconnect($host, 6379, REDIS_SLAVE_TIMEOUT_CONNECT);
			if($this->redis_password != ""){
				$this->redis_online->auth($this->redis_password); //mat khau connect toi server
			}
			return true;
		}
		catch (Exception $e)
		{
			$this->redis_online = "error";
			$this->log("redis_error_connect", $host . ":" . $e);
			return false;
		}
 	}//end function

}
/*
require_once("../functions/functions.php");
$redis = new redis_store();
print_r($redis->getInfo());
//for($i = 0; $i < 10; $i++){
//$redis->lPush("history:1:1", rand(1,20));
//}
//print_r($redis->lTrim("history:1:1", 0, 10));

//$a = ($redis->lRange('history:1:1', 0, 9));
//echo implode("<br>", $a);
//*/