<?
define('CHAT_VATGIA_SECRET_JOIN_CHANNEL','flwo2solfhwogne02');
/**
 * dinhtoan1905
 * Class ket noi voi het hong socket io
 */
class SocketIOClient{
	
	var $arrayChannel = array();
	var $event			= array();
	var $check_id 		= 0;
	var $prifix			= "qlbt";
	
	/**
	 * Ham add channel
	 */
	 function addChannel($user_id){
	 	$this->arrayChannel[] 	= $user_id;
	 	$this->check_id			= $user_id;	
	 }
	 
	 /**
	  * Hàm add Event
	  */
  	 function addEvent($on, $function_callback){
		$this->event[$on]	= $function_callback;
  	 }
	
	/**
	 * Hàm kết nối server
	 */
	 function connect($data_join = ""){
 		$this->arrayData	= array(
										  "channel" 	=> $this->arrayChannel
		  								 ,"checksum"	=>	md5($this->check_id . "|" . CHAT_VATGIA_SECRET_JOIN_CHANNEL)
		  								 ,"check_id"	=> $this->check_id
										 ,"data" 		=> ""
										 );

		$this->arrayData["data"] = $data_join;
		
		$str_js = 'var RealtimeDataConnect ="data=' . rawurlencode(json_encode($this->arrayData)) . '&logged=1";';
		$str_js .= 'var socket = io.connect("' . $this->getServerConnectSocket() . '",{ query: RealtimeDataConnect });';
      $str_js .= 'var check_vgc_box_chat_loaded = false;';
 		$str_js .= 'socket.on("connect", function () {';
		foreach($this->event as $key => $val){
			$str_js .= 'socket.on("' . $key . '", function (raw) { ' . $val . '(raw.data); });';	
		}	
 		$str_js .= '});';
 		$str_js1  = '<script type="text/javascript" rsync src="/themes/js/socket.io.1.2.1.js"></script>';
 		$str_js1  .= '<script type="text/javascript">
								var debug_socket = setInterval(function(){
									if(typeof io != "undefined" && typeof io != undefined){
										'. $str_js .'
										clearInterval(debug_socket);
									}
								},1000);
							</script>';
 		return $str_js1;
	 }
	 
	 /**
	 * Ham can tai server socket
	 */
 	function getServerConnectSocket(){
 		//neu la ssl thi tra ve domain co ssl
 		$arrayDomainSocket = array("http://vc1.vnpgroup.net:8080"
			 									,"http://vc2.vnpgroup.net:8080"
		 										,"http://vc3.vnpgroup.net:8080"
			 									,"http://vc4.vnpgroup.net:8080"
			 									,"http://vc5.vnpgroup.net:8080"
			 									);
 		$key = array_rand($arrayDomainSocket);
		return $arrayDomainSocket[$key];
 	}
}