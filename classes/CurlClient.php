<?php
if(!defined('DEBUG_BACKTRACE_IGNORE_ARGS')) define('DEBUG_BACKTRACE_IGNORE_ARGS',2);
define("API_SECRET_VCHAT","JREWLREW32432S#FDS34%%^&");
/**
* CURL client PHP
* @author <dinhtoan1905@gmail.com>
*/

/**
 * Client Interface
 *
 * All clients must implement this interface
 *
 * The 4 http functions just need to return the raw data from the API
 */
interface ClientInterface {

    function get( $url, array $data = array() );
    function post( $url, array $data = array() );
    function put( $url, array $data = array() );
    function delete( $url, array $data = array() );

}

/**
 * Curl Client
 *
 * Uses curl to access the API
 */
class CurlClient implements ClientInterface {

		/**
		* Curl Resource
		*
		* @var curl resource
		*/
		protected $curl = null;
		/**
		* Thoi gian bat dau xu ly curl
		*/
		protected $start_generate_time   = 0;
		protected $timeOutConnect			= 2; //thoi gian mac dinh timeout la 1 giay
		protected $messageToIdVG			= array();
		protected $arrayStructMessage		= array("subject" => ""
															 ,"content" => ""
															 ,"data"		=> ""
															 ,"users"	=> ""
															 );
	 	protected $httpResponseCode		= 0;

		/**
		* Constructor
		*
		* Initializes the curl object
		*/
		function __construct($timeOut = 1){
		  $this->timeOutConnect = $timeOut;
		  $this->initializeCurl();
		}

		/**
		* SET authen digest
		*
		* @param string $username ten tk dang nhap authen
		* @param string $password mat khau
		* @access public
		*/

		function setAuthenDigest($username, $password){
			curl_setopt($this->curl, CURLOPT_USERPWD, $username . ":" . $password);
			curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
		}
		
		function setAuthenBasic($username, $password){
			curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			curl_setopt($this->curl, CURLOPT_USERPWD, $username . ":" . $password);
			//curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
		}

		/**
		* GET
		*
		* @param string $url URL to send get request to
		* @param array $data GET data
		* @return Response
		* @access public
		*/
		public function get( $url, array $data = array() ){
		  $this->httpResponseCode = 0;
		  curl_setopt( $this->curl, CURLOPT_CUSTOMREQUEST, 'GET' );
		  if(count($data) > 0){
		  		curl_setopt( $this->curl, CURLOPT_URL, sprintf( "%s?%s", $url, http_build_query( $data ) ) );
		  }else{
		  		curl_setopt( $this->curl, CURLOPT_URL, $url);
		  }
		  $data = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		  $data = array_shift($data);
		  return $this->fetch($data);
		}
      
      function setOpt($opt, $value){
			curl_setopt($this->curl, $opt, $value);
		}
      
      public function postJson( $url, $data_json = '' ) {
		  curl_setopt( $this->curl, CURLOPT_CUSTOMREQUEST, 'POST' );
		  curl_setopt( $this->curl, CURLOPT_URL, $url );
		  curl_setopt( $this->curl, CURLOPT_POSTFIELDS, $data_json );
		  $data = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		  $data = array_shift($data);
		  return $this->fetch($data);
		}
      
		/**
		* POST
		*
		* @param string $url URL to send post request to
		* @param array $data POST data
		* @return Response
		* @access public
		*/
		public function post( $url, array $data = array() ) {
			$this->httpResponseCode = 0;
		  curl_setopt( $this->curl, CURLOPT_CUSTOMREQUEST, 'POST' );
		  curl_setopt( $this->curl, CURLOPT_URL, $url );
		  curl_setopt( $this->curl, CURLOPT_POSTFIELDS, http_build_query( $data ) );
		  $data = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		  $data = array_shift($data);
		  return $this->fetch($data);
		}

		/**
		* POST
		*
		* @param string $url URL to send message to fb
		* @param array $data POST data {
		*	"subject": Tiêu đề của tin,
		*    "content": nội dung tin,
		*    "data": dữ liệu đi kèm tin (là mảng/dictionary),
		*    "link": link của tin,
		*    "users": "Các user_id nhận được thông báo, ngăn cách bởi ; hoặc ,",
		*    "link": "Link của tin, được sử dụng trong một số protocol như Facebook ...",
		}
		* @return Response
		* @access public
		*/
		public function addMessageFaceBook($subject, $content, $data, $link, $users) {
			$this->messageToIdVG["subject"]	=	$subject;
			$this->messageToIdVG["content"]	=	$content;
			$this->messageToIdVG["data"]		=	$data;
			$this->messageToIdVG["link"]		=	$link;
			$this->messageToIdVG["users"]		=	$users;
			$this->messageToIdVG["messengers"]["fbpns"]	= array();
		}

		/**
		* POST to id.vatgia.com
		*/
		public function SendMessage() {
		  $data_string = json_encode( $this->messageToIdVG );
		  echo $data_string .'<hr>';
		  curl_setopt( $this->curl, CURLOPT_CUSTOMREQUEST, 'POST' );
		  curl_setopt( $this->curl, CURLOPT_URL, "http://services.vnpid.com/api/notification/message/" );
		  curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
																	    'Content-Type: application/json',
																	    'Content-Length: ' . strlen($data_string))
																	);
		  curl_setopt( $this->curl, CURLOPT_POSTFIELDS, $data_string );
		  $data = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		  $data = array_shift($data);
		  return $this->fetch($data);
		}
		
		function sendToVchat($event, $arrayChannel, $arrayData){
			$array = array(
							"event" => $event,
							"data"  => $arrayData,
							"channels" => $arrayChannel
					 	 );
			$data 		= base64_url_encode(json_encode($array));
			$checksum 	= md5($data . "|" . API_SECRET_VCHAT);					 	 
			return $this->post("http://vchat.vn/api/send.php",array("data" => $data,"checksum" => $checksum));
		}


		/**
		* POST
		*
		* @param string $phone_number so dien thoai hoac array so dien thoai
		* @param array $content noi dung SMS
		* @return Response
		* @access public
		*/
		public function sendSMSToPhone($phone_number, $content, $is_viber = 0) {

			// ERP#21997 => Chuyển sang cổng SMS Bảo Kim của a Đăng, sẽ tự logic gửi theo erp
			return $this->sendSMSToPhoneByBaoKim($phone_number, $content);

			$str_phone = '';
			if(is_array($phone_number)){
				foreach($phone_number as $key => $phone){
					$phone = preg_replace("/[^0-9]/","",$phone);
					if(strlen($phone) > 9 && strlen($phone) < 13){
						$str_phone .= $phone . ";";
					}
				}
			}else{
				$phone_number = preg_replace("/[^0-9]/","",$phone_number);
				if(strlen($phone_number) > 9 && strlen($phone_number) < 13){
					$str_phone = $phone_number;
				}
			}

			if($str_phone == '') return 0;

			$data_send_sms = array(
									    "phone_number" => $str_phone,
									    "sms" => $content,
									    "username" => SMS_USERNAME,
									    "password" => md5(SMS_PASSWORD)
									);
			//neu su dung viber thi them tham so vao
			if($is_viber == 1){
				$data_send_sms["send_vb"]	= 1;
			}
			return $this->post(SMS_URL_API, $data_send_sms);
		}

		function sendSMSToPhoneByBaoKim($phone_number, $content) {

			$str_phone = '';
			if(is_array($phone_number)){
				foreach($phone_number as $key => $phone){
					$phone = preg_replace("/[^0-9]/","",$phone);
					if(strlen($phone) > 9 && strlen($phone) < 13){
						$str_phone .= $phone . ";";
					}
				}
			}else{
				$phone_number = preg_replace("/[^0-9]/","",$phone_number);
				if(strlen($phone_number) > 9 && strlen($phone_number) < 13){
					$str_phone = $phone_number;
				}
			}

			if($str_phone == '') return 0;


			$data_send_sms = array(
									    "phone" 		=> $str_phone,
									    "content" 		=> $content,
									    "branch_name"	=> "VatGia.com"
									);
			$smsBaokimURL 				= "/services/smsmt_api/send";
			$ref_signature				= $this->makeSMSBaoKimSignature('POST',$smsBaokimURL, array(), $data_send_sms,SMS_BAOKIM_KEY);
			// $ref_signature				= "";
			$requestSMSBaoKimApiURL = 'http://sms.x.baokim.vn'.$smsBaokimURL.'?signature='.$ref_signature;

			$curl = curl_init($requestSMSBaoKimApiURL);

			curl_setopt_array($curl, array(
				CURLOPT_HEADER				=>false,
				CURLOPT_POST				=>true,
				CURLINFO_HEADER_OUT		=>true,
				CURLOPT_HTTP_VERSION		=>CURL_HTTP_VERSION_1_0,
				CURLOPT_HTTPAUTH			=>CURLAUTH_DIGEST,
				CURLOPT_USERPWD			=>SMS_BAOKIM_USERNAME.':'.SMS_BAOKIM_PASSWORD,
				CURLOPT_TIMEOUT			=>30,
				CURLOPT_RETURNTRANSFER	=>true,
				CURLOPT_POSTFIELDS		=>$data_send_sms
			));

			// Lấy Data
			$data = curl_exec($curl);
			return $data;

		}

		function makeSMSBaoKimSignature($method, $url, $getArgs=array(), $postArgs=array(), $priKeyFile){
			if(strpos($url,'?') !== false)
			{
				list($url,$get) = explode('?', $url);
				parse_str($get, $get);
				$getArgs=array_merge($get, $getArgs);
			}

			ksort($getArgs);
			ksort($postArgs);
			$method=strtoupper($method);

			$data = $method.'&'.urlencode($url).'&'.urlencode(http_build_query($getArgs)).'&'.urlencode(http_build_query($postArgs));

			$priKey = openssl_get_privatekey($priKeyFile);

			openssl_sign($data, $signature, $priKey, OPENSSL_ALGO_SHA1);

			return urlencode(base64_encode($signature));
		}

		/**
		* PUT
		*
		* @param string $url URL to send put request to
		* @param array $data PUT data
		* @return Response
		* @access public
		*/
		public function put( $url, array $data = array()  ){
		  $this->httpResponseCode = 0;
		  curl_setopt( $this->curl, CURLOPT_CUSTOMREQUEST, 'PUT' );
		}

		/**
		* DELETE
		*
		* @param string $url URL to send delete request to
		* @param array $data DELETE data
		* @return Response
		* @access public
		*/
		public function delete( $url, array $data = array()  ){
		  $this->httpResponseCode = 0;
		  curl_setopt( $this->curl, CURLOPT_URL, sprintf( "%s?%s", $url, http_build_query( $data ) ) );
		  curl_setopt( $this->curl, CURLOPT_CUSTOMREQUEST, 'DELETE' );
		  $data = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		  $data = array_shift($data);
		  return $this->fetch($data);
		}

		/**
		* Initialize curl
		*
		* Sets initial parameters on the curl object
		*
		* @access protected
		*/
		protected function initializeCurl() {
			$this->httpResponseCode = 0;
			$this->start_generate_time = $this->microtime_float();
			$this->curl = curl_init();
			curl_setopt( $this->curl, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $this->curl, CURLOPT_SSL_VERIFYPEER, false );
			//thoi gian mac dinh cho phep connect
			curl_setopt( $this->curl, CURLOPT_TIMEOUT, $this->timeOutConnect);
			curl_setopt( $this->curl, CURLOPT_FOLLOWLOCATION, true);
		}

		/**
		* Fetch
		*
		* Execute the curl object
		*
		* @return StdClass
		* @access protected
		* @throws ApiException
		*/
		protected function fetch($debug_backtrace) {
		  $raw_response = curl_exec( $this->curl );
		  $error = curl_error( $this->curl );
		  $code 	= curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
		  $url 	= curl_getinfo($this->curl, CURLINFO_EFFECTIVE_URL );
		  $debug_backtrace["code"]		= $code;
		  $debug_backtrace["url"]		= $url;
		  $debug_backtrace["error"]	= $error;
		  $this->httpResponseCode		= $code;
		  if ( $error ) {
		      //bat dau ghi log loi vao file
				$this->saveLog("curlclient_error.cfn", $debug_backtrace);
		  }
		  //curl_close($this->curl);
		  //bat dau tinh thoi gian xu ly
		  $time_request = $this->microtime_float() - $this->start_generate_time;
		  //sau day tiep tuc gan lai thoi gian de xu ly tiep
		  $this->start_generate_time = $this->microtime_float();
		  //kiem tra xem time request co bi slow qua ko

		  return $raw_response;
		}

		/**
		 * Neu kiem tra xem http code tra ve bao nhieu thi goi ham nay
		 * ham nay chi tra ve gia tri sau khi thuc hien request
		 */
	 	function getHttpReponseCode(){
	 		return intval($this->httpResponseCode);
	 	}


		protected function saveLog($fileName, $arrayData){
	 		$dirname = dirname(__FILE__);
	 		$dirname	= 		str_replace('classes\restful',"", $dirname);
	 		$dirname	= 		str_replace('classes/restful',"", $dirname);
	 		$dirname .=		"ipstore/";
	 		@file_put_contents($dirname . $fileName,json_encode($arrayData) . "\n", FILE_APPEND);
	 	}

	 	/**
		 * Ham lay moc thoi gian de do toc do xu ly
		 */
		function microtime_float(){
		   list($usec, $sec) = explode(" ", microtime());
		   return ((float)$usec + (float)$sec);
		}

}