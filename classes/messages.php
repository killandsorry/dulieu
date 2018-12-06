<?
require_once("CurlClient.php");
/**
 * Class tạo thông báo message
 * 
 * AIzaSyBILPRtIoTCU33AYEOQkBi-VzcIN8fmYHM
 */
define('MESSAGE_ROW', 50000); // số row trong 1 bảng message
define("GOOGLE_API_KEY",'AIzaSyBILPRtIoTCU33AYEOQkBi-VzcIN8fmYHM');

define("MESSAGE_NUMBER_TABLE_USER",50); //số lượng bảng user_mesage chia theo parent_id
define('CHAT_SMS_NAME', 'vchat_sms'); //user gửi sms
define('CHAT_SMS_PASS', 'vchat@vnp'); //password gửi sms

define("NOTI_ACTION_COMMEMT", 1); // comment
define("NOTI_ACTION_CLOSE_BOOK",2); // yêu cầu chốt sổ
define("NOTI_ACTION_ADD_PRODUCT", 3); // thêm mới sản phẩm cần đc kiểm kho
define("NOTI_ACTION_ADD_CALL_PRODUCT", 4); // thêm mới sản phẩm cần đc kiểm kho

define('SMS_TYPE_QC', 1); // tin nhắn quảng cáo
define('SMS_TYPE_CSKH', 2); // loai tin nhan cham soc khach hang
define('SMS_TYPE_BRANDNAME', 3); // loai tin nhan brand name cskh
define("SMS_ROOT_URL", 'http://api.speedsms.vn/index.php');
define('ACCESS_TOKENT', 'tLXW5b7bVZU3RbQBgMd6CWqBqKPKuR_K'); // access token sms

class messages{
	
   // hàm send notifi
	function send($arrayMessage = array(),$arrayUserId = array(), $parent_id = 0, $send_gcm = 1){
	   global $domain, $pathRoot;
      $default_arrayMessage = array("title" 				=> ""
											,"teaser" 			=> ""
											,"description" 	=> ""
											,"url" 				=> ""
											,"date" 				=> 0
											,"create_by" 		=> 0
											,"status" 			=> 0
											,"use_id" 			=> 0
											,"parent_id" 		=> 0
                                 ,'action'         => 0
											);
     
		/*
         - lưu vào bảng messages_0
         - lấy được id lưu vào bảng messages_id
         - xong thì thêm vào bảng messages_user_0 những user được nhận thông báo
      */      
		$arr_message = merge($default_arrayMessage, $arrayMessage);
      // lấy id cuối cùng để biết nó sẽ thêm vào bảng nào
      $max_id  = 0;
      $db_id   = new db_query("SELECT MAX(mes_id) AS id FROM messages_id LIMIT 1",__FILE__,DB_NOTIFICATION);
      if($rmax = mysqli_fetch_assoc($db_id->result)){
         $max_id = intval($rmax['id']);
      }
      unset($db_id);
      
      // tên bảng message_ sẽ đc thêm vào
      $table_message = $this->getTableMessage($max_id+1);
      //file_put_contents('../logs/a.txt', $table_message);
      
      $db_ex   = new db_execute_return();
      $last_id = $db_ex->db_execute("INSERT INTO " . $table_message . "(
         mes_id,mes_title,mes_teaser,mes_description,mes_url,mes_date,mes_create_by,mes_status,mes_use_id,mes_parent_id,mes_action
      )
      VALUES(
         ". ($max_id+1) .",
         '". replaceMQ($arr_message['title']) ."',
         '". replaceMQ($arr_message['teaser']) ."',
         '". replaceMQ($arr_message['description']) ."',
         '". replaceMQ($arr_message['url']) ."',
         ". time() .",
         ". intval($arr_message['create_by']) .",
         0,
         ". intval($arr_message['use_id']) .",
         ". intval($arr_message['parent_id']) .",
         ". intval($arr_message['action']) ."        
      )",__FILE__,DB_NOTIFICATION);
      
      $last_id = ($max_id + 1);
      if($last_id > 0){
         // thêm vào bảng message_id
         $db_mes  = new db_execute("INSERT INTO messages_id(mes_id) VALUES(". $last_id .")",__FILE__,DB_NOTIFICATION);
         unset($db_mes);
         
         $arraySql 	= 	array();
         
         // table message_user
         if(!empty($arrayUserId)){
            foreach($arrayUserId as $to_id){
               $table_message_user  = $this->getTableUser($to_id);
               $arraySql[$table_message_user][] = "(". $last_id .",". $arrayMessage['use_id'] .",". $to_id .",". time() .", 0)"; 
            }
         }
         //nếu tồn tại sql
         if(!empty($arraySql)){
   			foreach($arraySql as $table => $rows){
   			   
   				$db_isnert  = new db_execute("INSERT INTO " . $table . "(meu_mes_id,meu_from_id,meu_to_id,meu_date,meu_status)
               										VALUES" . implode(",", $rows) . ";",__FILE__,DB_NOTIFICATION);
               
               unset($db_isnert);
   			}
        	}
         
         if($send_gcm == 1){
            // lấy thông tin register của user
            $db_gcm  = new db_query("SELECT * FROM user_gcm WHERE usg_use_id = " . intval($parent_id), __FILE__, DB_NOTIFICATION);
            while($rgcm  = mysqli_fetch_assoc($db_gcm->result)){
               if($rgcm['usg_register_id'] != ''){
                  $message_data  = array(
                     'id' => $rgcm['usg_register_id'],
                     'body' => removeHTML(replaceMQ($arr_message['description'])),
                     'title' => removeHTML(replaceMQ($arr_message['title'])),
                     'url' => replaceMQ($arr_message['url'])
                  );
                  $send = $this->gcm($message_data);
                  $send = json_decode($send, 1);
                  if(isset($send['success']) && $send['success'] == 0){
                     $db_ex   = new db_execute("DELETE FROM user_gcm WHERE usg_id = " . $rgcm['usg_id'], __FILE__, DB_NOTIFICATION);
                     unset($db_ex);
                  }
               }
            }
            unset($db_gcm);
         }
         
         return 1;
      }else{
         return 0;
      }
	}
	
	function getTableUser($user_id = 0){
		return "messages_user_" . intval(intval($user_id) % MESSAGE_NUMBER_TABLE_USER);
	}
	
	function getTableMessage($record_id = 0){
		return "messages_" . intval(intval($record_id) / MESSAGE_ROW);
	}
   
   /**
    * Hàm get count notifi 
    */
	function getCount_noti($user = 0){
      if($user <= 0) return 0;
      
      $table_noti  = $this->getTableUser($user);
      $db_count = new db_query("SELECT COUNT(meu_mes_id) AS count FROM " . $table_noti . "
                               WHERE meu_to_id = " . intval($user). " 
                               AND meu_status = 0", __FILE__, DB_NOTIFICATION);
      if($row   = mysqli_fetch_assoc($db_count->result)){
         return $row['count'];
      }
      unset($db_count);
      return 0;
	}
   
   /**
    * Hàm lấy notification
    */
   function getNotification($user = 0){
      if($user <= 0) return 0;
      
      $array_noti = array();
      $table_noti = $this->getTableUser($user);
      $db_noti = new db_query("SELECT * FROM " . $table_noti . " 
                                 WHERE meu_to_id = " . intval($user) . " 
                                 ORDER BY meu_date DESC LIMIT 20", __FILE__, DB_NOTIFICATION);
      while($row = mysqli_fetch_assoc($db_noti->result)){
         $db_mes  = new db_query("SELECT * FROM " . $this->getTableMessage($row['meu_mes_id']) . " WHERE mes_id = " . $row['meu_mes_id'] . " LIMIT 1", __FILE__, DB_NOTIFICATION);
         if($r = mysqli_fetch_assoc($db_mes->result)){
            $data = array_merge($row, $r);
            $array_noti[$row['meu_mes_id']] = $data;
         }
         unset($db_mes);
      }
      unset($db_noti);
      
      return $array_noti;
   }
   
   /**
    * Hàm update trạng thái đã đọc notifi
    * array = array(
    *    id => 0,
    *    type => all (nếu == all là update tất cả)
    * )
    */
   function notification_update_readed($array = array()){
      global   $myuser;
      $not_id  = isset($array['id'])? intval($array['id']) : 0;
      $type    = isset($array['type'])? $array['type'] : '';
      $user_id    = isset($array['user_id'])? intval($array['user_id']) : 0;
      
      if($type == 'all'){
         $table   = $this->getTableUser($myuser->u_id);
         $db_noti    = new db_execute("UPDATE " . $table . " SET meu_status = 1 
                                       WHERE meu_to_id = " . $myuser->u_id, __FILE__, DB_NOTIFICATION);
         if($db_noti->total >=  0) return 1;
      }else{
         if($not_id <= 0){
            return 0;
         }
         $table   = $this->getTableUser($myuser->u_id);
         $db_check   = new db_query("SELECT * FROM " . $table . " 
                                    WHERE meu_to_id = " . $myuser->u_id . "
                                    AND meu_mes_id = " . intval($not_id) . " LIMIT 1", __FILE__, DB_NOTIFICATION);
         if($rcheck  = mysqli_fetch_assoc($db_check->result)){
            $db_ex   = new db_execute("UPDATE " . $table . " SET meu_status = 1
                                          WHERE meu_to_id = " . $myuser->u_id . "
                                          AND meu_mes_id = " . intval($not_id), __FILE__, DB_NOTIFICATION);
            if($db_ex->total >= 0) return 1;
         }else{
            return 0;
         }
         unset($db_check);
      }
      
      return 0;
   }
   
   /**
    * Hàm add redis để ghi nhận số sản phẩm notification được thêm mới và cảnh báo kiểm kho
    */
   function notification_add_product(){
      global $myredis, $myuser, $admin_id, $branch_id;
      if($admin_id <= 0) return 0;
      
      $key_noti   = 'notification:' . $admin_id . ":" . NOTI_ACTION_ADD_PRODUCT;
      $myredis->incr($key_noti);
      
      return 1;
   }    
   
   
   /**
    * Set key redis statistic
    */
   function setKeyRedis(){
      global $myredis, $myuser, $admin_id,$branch_id;
      if($admin_id <= 0) return 0;
      $time    = mktime(0,0,0,date('m'), date('d'), date('Y'));
      
      $key_noti   = 'statistic:'. $time . ':' . $branch_id .":". $admin_id;
      $myredis->incr($key_noti); 
      
      return 1;
   }
   
   /**
    * Set redis sản phẩm mới trong ngày
    */
   function setNewProductInDay(){
      global $myredis, $myuser, $admin_id,$branch_id;
      if($admin_id <= 0) return 0;
      $time    = mktime(0,0,0,date('m'), date('d'), date('Y'));
      
      $key_noti   = 'new_product:'. $time . ':' . $branch_id .":". $admin_id;
      $myredis->incr($key_noti); 
      
      return 1;
   }
   
   /**
    * send notification
    * 
    * $data = array(
       'f_name' => 'Ten nguoi gui',
       't_id' => 'id cua nguoi nhận',
       't_name' => 'tên người nhận',
       'time' => 'thời gian gửi',
       'url' => 'url khi click vào thông báo sẽ ra link (link có hoặc không)'
       'text' => 'noi dung thông bao'
     );

    * 
    */
   function gcm($messageData){
		$headers = array("Content-Type:" . "application/json", "Authorization:" . "key=" . GOOGLE_API_KEY);
		
      
      $data  = array(
         'to' => $messageData['id'],
         "priority" => "high",
         'notification' => array(
            'body' => removeHTML($messageData['body'])
            ,'title' => removeHTML($messageData['title'])
         ),
         'data' => array(
            'url' => $messageData['url']
         )   
      );
      
		$myCurl = new CurlClient(5);
		$myCurl->setOpt(CURLOPT_HTTPHEADER,$headers);
		$result = $myCurl->postJson('https://android.googleapis.com/gcm/send',json_encode($data));
		return $result;
	}

	function apn($deviceToken, $data, $alert,$test=0){
		if ($deviceToken != '') {
			$ctx = stream_context_create();
			// stream_context_set_option($ctx, 'ssl', 'cafile', 'entrust_2048_ca.cer');
			if($test == 1){
				stream_context_set_option($ctx, 'ssl', 'local_cert', '../sslkey/vchat_dev_aps.pem');
			}else{
				stream_context_set_option($ctx, 'ssl', 'local_cert', '../sslkey/vchat_aps.pem');
			}
			//stream_context_set_option($ctx, 'ssl', 'passphrase', '123456');

			// Open a connection to the APNS server
			if($test == 1){
				$fp = stream_socket_client(
					'ssl://gateway.sandbox.push.apple.com:2195', $err,
					$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
			}else{
				$fp = stream_socket_client(
				'ssl://gateway.push.apple.com:2195', $err,
				$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
			}


			if (!$fp)
				exit(json_encode(array('results' => array(), 'status' => 400)));

			// Create the payload body
			$body['aps'] = array(
				'alert' => $alert,
				'sound' => 'default'
				);
			$body['data'] = $data;

			// Encode the payload as JSON
			$payload = json_encode($body);

			// Build the binary notification
			$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

			// Send it to the server
			$result = fwrite($fp, $msg, strlen($msg));

			if (!$result)
				return 0;

			// Close the connection to the server
			fclose($fp);
			return $result;
		} else
				return 0;
	}
   
   
   
   // send sms
   function send_sms_cong($array = array()){
		$url	= 'http://sms.boss-auto.org/restful/insert_sms';

		// default array gửi sms
		$arrayDefault	= array(
			"username" => CHAT_SMS_NAME,
	      "password" => md5(CHAT_SMS_PASS),
	      'send_vb' => 0,
	      'ck_service' => 1
		);

		// check nếu không đủ điều kiện thì thoát luôn
		if(empty($array) || (!isset($array['sms']) && !isset($array['sms']))) return 0;

		// ghép lại thành mảng để post đi
		$datatopost = array_merge($arrayDefault, $array);

		// post curl
		$ini = curl_init($url);
	   curl_setopt($ini, CURLOPT_HEADER, false);
	   curl_setopt($ini, CURLOPT_SSL_VERIFYPEER, FALSE);
	   curl_setopt($ini, CURLOPT_SSL_VERIFYHOST, FALSE);
	   curl_setopt($ini, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	   curl_setopt($ini, CURLOPT_RETURNTRANSFER, true);
	   curl_setopt($ini, CURLOPT_FOLLOWLOCATION, 1);
	   curl_setopt($ini, CURLOPT_POSTFIELDS, $datatopost);
		curl_setopt($ini, CURLOPT_CONNECTTIMEOUT , 30);
		curl_setopt($ini, CURLOPT_TIMEOUT, 40); //timeout in seconds
	   $result  = curl_exec($ini);
	   unset($ini);
	   return $result;
	}
   
   
   
   /**
    * Hàm gửi notifi và send gcm khi yêu cầu chốt sổ
    */
   function send_close_book($data = array()){
      $from_id    = isset($data['from_id'])? intval($data['from_id']) : 0;
      $admin_id   = isset($data['admin_id'])? intval($data['admin_id']) : 0;
      $action     = isset($data['action'])? intval($data['action']) : 0;
      $des        = isset($data['des'])? $data['des'] : '';
      $send_gcm   = isset($data['send_gcm'])? intval($data['send_gcm']) : 0;
      $title      = isset($data['title'])? $data['title'] : '';
      $url        = isset($data['url'])? $data['url'] : '';
   }
   
   // send sms
   

    

    public function getUserInfo() {
        $url = SMS_ROOT_URL . '/user/info';
        $headers = array('Accept: application/json');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERPWD, ACCESS_TOKENT.':x');

        $results = curl_exec($ch);

        if(curl_errno($ch)) {
            return null;
        }
        else {
            curl_close($ch);
        }
        return json_decode($results, true);
    }

    public function sendSMS($to, $smsContent, $smsType, $brandName) {
        if (!is_array($to) || empty($to) || empty($smsContent))
            return null;

        $type = SMS_TYPE_CSKH;
        if (!empty($smsType))
            $type = $smsType;

        if ($type < 1 && $type > 3)
            return null;

        if ($type == 3 && empty($brandName))
            return null;

        if (strlen($brandName) > 11)
            return null;

        $json = json_encode(array('to' => $to, 'content' => $smsContent, 'sms_type' => $type, 'brandname' => $brandName));

        $headers = array('Content-type: application/json');

        $url = SMS_ROOT_URL . '/sms/send';
        $http = curl_init($url);
        curl_setopt($http, CURLOPT_HEADER, false);
        curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($http, CURLOPT_POSTFIELDS, $json);
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_VERBOSE, 0);
        curl_setopt($http, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($http, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($http, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($http, CURLOPT_USERPWD, ACCESS_TOKENT.':x');
        $result = curl_exec($http);
        if(curl_errno($http))
        {
            return null;
        }
        else
        {
            curl_close($http);
            return json_decode($result, true);
        }
    }

    public function getSMSStatus($tranId) {
        $url = SMS_ROOT_URL . '/sms/status/'.$tranId;
        $headers = array('Accept: application/json');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERPWD, ACCESS_TOKENT.':x');

        $result = curl_exec($ch);
        if(curl_errno($ch))
        {
            return null;
        }
        else
        {
            curl_close($ch);
            return json_decode($result, true);
        }
    }
}