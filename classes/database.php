<?
include("config.php");	

if(!isset($_SERVER["SERVER_NAME"])){
	$_SERVER["SERVER_NAME"] = "cmd";
	$_SERVER["REMOTE_ADDR"] = "127.0.0.1";
	$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__) . "/../";
}

/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
/*                                                  CLASS                                                              */
/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
/**
 * Class db_init
 * Class khoi tao ket noi database
 */ 
class db_init{
    /** Ten Server */
	var $server;   
	/** Ten User */
    var $username;   
    /** Mat khau */
	var $passworddb; 
    /** Ten CSDL */
	var $database;    
 	var $cookie_server 							= '';
         
    /*********************************************************************************************************/
	/**
	 * db_init::db_init()
	 * Ham khoi tao class
	 * @return
	 */
	function __construct($using_db = ""){
 		// Khai bao Server localhost day
		$this->server	 					= MYSQL_HOST;
		$this->username 					= MYSQL_USER;
		$this->database					= DB_MASTER;
		$this->passworddb					= MYSQL_PASSWORD; //matj khau server ape
		
		if($_SERVER['SERVER_NAME'] == 'localhost'){
			$this->cookie_server			= '/'; //cau hinh server luu cookie
		}else{
			$this->cookie_server			= ''; //cau hinh server luu cookie
		}
	}
	
    /*********************************************************************************************************/
	/**
	 * db_init::log()
	 * Ham ghi log
	 * @param mixed $filename : ten file log
	 * @param mixed $content  : noi dung log
	 * @return
	 */
	function log($filename, $content){
		
		$log_path     =   $_SERVER["DOCUMENT_ROOT"] . "/logs/";
		$handle       =   @fopen($log_path . $filename . ".cfn", "a");
		//Neu handle chua co mo thêm ../
		if (!$handle) $handle = @fopen($log_path . $filename . ".cfn", "a");
		//Neu ko mo dc lan 2 thi exit luon
		if (!$handle) exit("Error save logs " . $log_path);
		fwrite($handle, date("d/m/Y h:i:s A") . " " . @$_SERVER["REQUEST_URI"] . "\n" . $content . "\n");	
		fclose($handle); 	
			
	}
    
    /*********************************************************************************************************/
 	/**
 	 * db_init::debug_query()
 	 * Ham print query vào 1 file log de kiem tra loi
 	 * @param string $query : cau query
 	 * @param string $file_line_query : noi dung loi
 	 * @return
 	 */
 	function debug_query($query, $file_line_query){ 		
 		//neu localhost thi luon save query vào file con de kiem tra
 		if(@$_SERVER["SERVER_NAME"] == "localhost"){
 			$this->log("query",$file_line_query . " \n " . $query);
 		}
 		
 	}
    
    /*********************************************************************************************************/
	/**
	 * db_init::__destruct()
	 * Ham huy tu dong chay khi unset class
	 * @return
	 */
	function __destruct(){
		unset($this->server);
		unset($this->username);
		unset($this->passworddb);
		unset($this->database);
	}
    
    
}

/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
/*                                                  CLASS                                                              */
/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
/**
 * Class db_query
 * Class thuc hien 1 truy van
 */ 
class db_query{
	/** Ket qua cua cau truy van */
	var $result;               
    /** ket noi */
	var $links;        
    /** Thoi gian nhieu nhat 1 cau query duoc thuc hien */                

	var $time_slow_log = 0.001;     //
     
    /*********************************************************************************************************/ 
	/**
	 * db_query::db_query()
	 * 
	 * @param mixed $query : cau truy van
	 * @param string $file_line_query : loi se ghi
	 * @return
	 */
	function __construct($query, $file_line_query = "", $using_db = ""){
		global $query_analyze_array;
		if (!isset($query_analyze_array)) $query_analyze_array = array();
		$dbinit       = new db_init($using_db);		
		//Khai bao connect
		$this->links  = @mysqli_connect($dbinit->server, $dbinit->username, $dbinit->passworddb, $dbinit->database);
		//Neu khong ket noi duoc
		
		if(!$this->links){			
			//ghi ra log loi query
			$path        = $_SERVER['DOCUMENT_ROOT'] . "/logs/";
			$filename    = "errorconect.cfn";
			$url         = $file_line_query;
			$str         = "File : " . $file_line_query . " ";
			$str        .= "IP:" . $_SERVER['REMOTE_ADDR'] . " Not connect DB: host: " . $dbinit->server . ", User : " . $dbinit->username . chr(13);
			$str         = "" . chr(13) . chr(13) . $str;			
			$dbinit->log("errorconect", $str); 					
			exit("Error save log: $path ");
		}		
		$db_select    = mysqli_select_db($this->links,$dbinit->database);		
		$time_start   = $this->microtime_float();
				//hàm này để bắt log xem từ file nào dòng nào gọi đến xử lý 
		$arrTemp = @debug_backtrace();
		
		//lấy ra file gọi class xử lý
		$file_call = isset($arrTemp[0]["file"]) ? $arrTemp[0]["file"] : '';
		//lấy ra dòng gọi class
		$file_line = isset($arrTemp[0]["line"]) ? $arrTemp[0]["line"] : '';
		
		//@mysqli_query($this->links,"SET NAMES 'utf8'");
		@mysqli_set_charset($this->links, "utf8");
		$this->result = @mysqli_query($this->links, $query . " \n" . "/* " . @$_SERVER['REMOTE_ADDR'] . " - " . @base64_encode(@$_SERVER['REQUEST_URI'] . " - F:" . $file_call . " - L:" . $file_line) . " */ ");
		

		
		$time_end     = $this->microtime_float();
		$time         = $time_end - $time_start;
		//neu thoi gian thuc hien query lon hon hoac bang 0.05 thi ghi log lai.
		if ($time >= $this->time_slow_log){
		
			//lấy ra các tham số query từ file nào dòng nào hàm nào hay class nào
			$arrTemp = @debug_backtrace();
			if(isset($arrTemp[0]) && is_array($arrTemp[0])){
				$arrTemp[0]["time"] 		= $time;
				$arrTemp[0]["query"] 	= $query;
				$arrQueryInfo				= $arrTemp[0];
				if(isset($arrQueryInfo["object"])) unset($arrQueryInfo["object"]);
				// Dump thêm server gây chậm để có thể xử lý
				$arrQueryInfo["host"] = $dbinit->server;
					 $arrQueryInfo["type"] = $dbinit->database;
				$query_analyze_array[] = $arrQueryInfo;
				unset($arrQueryInfo);
				
			}
			unset($arrTemp);
		
			//Ghi log o file
			$path    = $_SERVER['DOCUMENT_ROOT'] . "/logs/";
			//Ghi log o file
			$str     = "File : " . $file_line_query . "\n";
			$str    .= "Query time : " . number_format($time,10,".",",") . "\n";
			$str    .= "IP:" . $_SERVER['REMOTE_ADDR'] . $query . chr(13);
			$dbinit->log("slow_sql", $str); 
		
		}
		
		
		//Neu query ko co ket qua -> dump log
		if (!$this->result){
			//ghi ra log loi query
			$path    = $_SERVER['DOCUMENT_ROOT'] . "/logs/";
			$error   = @mysqli_error($this->links);
			@mysqli_close($this->links);	
		 	$dbinit->log("error_sql", $error . "\n" . $query);
			die( $error . " " . $query);
		}
		
		//ghi query ra log de kiem tra
		$dbinit->debug_query($query, $file_line_query);
		unset($dbinit);
	}
    
    /*********************************************************************************************************/
	/**
	 * db_query::resultArray()
	 * Ham lay ket qua
	 * @return array $arrayReturn : Mang
	 */
	function resultArray($field_id = ""){
		$arrayReturn = array();
		while($row = mysqli_fetch_assoc($this->result)){
			if($field_id != ""){
				$arrayReturn[$row[$field_id]] = $row;
			}else{
				$arrayReturn[] = $row;	
			}
			
		}
		return $arrayReturn;
	}
   
   /**
    * Ham thay the mysqli_fetch_accoss
    */
    function fetch(){
      return mysqli_fetch_assoc($this->result);
    }
    
    /*********************************************************************************************************/
	/**
	 * db_query::close()
	 * Ham dong ket noi
	 * @return
	 */
	function close(){
		@mysqli_free_result($this->result); 
		if ($this->links){ 
			@mysqli_close($this->links);		
		}
	}
	
    /*********************************************************************************************************/
	/**
	 * db_query::microtime_float()
	 * Ham tinh thoi gian(miligiay)
	 * @return float $return
	 */
	function microtime_float(){
	 list($usec, $sec) = explode(" ", microtime());
	 return ((float)$usec + (float)$sec);
	}	
}
//End class db_query


/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
/*                                                  CLASS                                                              */
/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
/**
 * Class db_execute
 * Class thuc thi 1 query
 */
class db_execute{
    /** ket noi */
	var $links; 
    /** so dong bi anh huong */
	var $total = 0;    

    /*********************************************************************************************************/
	/**
	 * db_execute::db_execute()
	 * Thuc hien 1 cau query
	 * @param mixed $query : Cau query
	 * @param string $file_line_query : Ghi loi
	 * @return
	 */
	function __construct($query, $file_line_query = "", $using_db = ""){
      global $myuser;
		$dbinit       = new db_init($using_db);
        $this->links  = @mysqli_connect($dbinit->server, $dbinit->username, $dbinit->passworddb);
		@mysqli_select_db($this->links,$dbinit->database);
		//@mysqli_query($this->links,"SET NAMES 'utf8'");
		@mysqli_set_charset($this->links, "utf8");
		@mysqli_query($this->links,$query);
      
      
      if(isset($myuser) && $myuser->useField['use_payment'] == 1){
         $file = '../logs/log_ex_' . date('d-m-Y') . '.cfn';
         $content = "User_id: " . $myuser->u_id . ': ' . date('H:i'). ': ' . $file_line_query . "\n" . $query . "\n\n";
         @file_put_contents($file, $content, FILE_APPEND);
      }
      
		//kiem tra thanh cong hay chua
		$this->total = @mysqli_affected_rows($this->links);
		
		//neu ket qua query thuc thi khong thanh cong tru truong hop insert ignore
		if($this->total < 0 && strpos($query, "IGNORE") === false ){
			$error = @mysqli_error($this->links);
			@mysqli_close($this->links);	
            //ghi log
			$dbinit->log("error_sql", $file_line_query . " " . $error . "\n" . $query); 	
		}
		@mysqli_close($this->links);
		
		//ghi query ra log de kiem tra
		$dbinit->debug_query($query, $file_line_query);
		unset($dbinit);
	}
}



/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
/*                                                  CLASS                                                              */
/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
/**
 * Class db_count
 * Class dem so ket qua cua cau query
 */
class db_count{
	/** so luong ket qua */
    var $total;
    
	/*********************************************************************************************************/
    /**
	 * db_count::db_count()
	 * 
	 * @param string $sql : Cau lenh sql
	 * @return int so ket qua
	 */
	function __construct($sql, $use_db = ''){
      
		$db_ex    = new db_query($sql, __FILE__, $use_db);
        
		if( $row = mysqli_fetch_assoc($db_ex->result)){
			$this->total = intval($row["count"]);
		}else{
			$this->total = 0;
		}
		$db_ex->close();
		unset($db_ex);
		return $this->total;
	}
}


/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
/*                                                  CLASS                                                              */
/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
/**
 * Class db_execute_return
 * Class thuc hien 1 cau query,co tra ve ket qua la id cuoi cung dc insert.
 */
class db_execute_return{
	/** ket noi*/
	var $links;    
    /** ket qua*/
	var $result;  
    
    /*********************************************************************************************************/ 
	/**
	 * db_execute_return::db_execute()
	 * 
	 * @param string $query : cau truy van
	 * @param string $file_line_query : Loi se ghi
	 * @return int :ID duoc them vao cuoi cung.
	 */
	function db_execute($query, $file_line_query = "",$using_db = ""){
		
		$dbinit       =   new db_init($using_db);
		$this->links  =   @mysqli_connect($dbinit->server, $dbinit->username, $dbinit->passworddb);
		@mysqli_select_db($this->links,$dbinit->database);
		
		
		//@mysqli_query($this->links,"SET NAMES 'utf8'");
		@mysqli_set_charset($this->links, "utf8");
		@mysqli_query($this->links,$query);
		
		$total        =   @mysqli_affected_rows($this->links);
      
		//neu ket qua khong thanh cong và khong phai là insert ignore
		if($total < 0 && strpos($query, "IGNORE") === false ){
				
			$error = @mysqli_error($this->links);
			@mysqli_close($this->links);	
			 
			$dbinit->log("error_sql", $file_line_query . " " . $error . "\n" . $query); 	
		}		
		
		$last_id      =   0;
		$this->result = @mysqli_query($this->links,"select LAST_INSERT_ID() as last_id");
		
		if($row = @mysqli_fetch_array($this->result)){
			$last_id = $row["last_id"];
		}
		
		@mysqli_close($this->links); 
		
		//ghi query ra log de kiem tra
		$dbinit->debug_query($query, $file_line_query);
		//huy bien
		unset($dbinit);
		return $last_id;
	}
}

/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/


function myInclude($filename){
   foreach($GLOBALS as $key => $val){
      global $$key;
   }
	return include $filename;

}

?>