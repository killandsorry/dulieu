<?
////////////////////////////////////////////////
// Ban khong thay doi cac dong sau:
function send_mailer($to,$title,$content, $FromName = "",$id_error=""){
	global $con_gmail_name;
	global $con_gmail_pass;
	global $con_gmail_subject;
	global $con_admin_email;
	//*	
	$header  = 'MIME-Version: 1.0' . "\r\n";
	$header .= 'Content-type: text/html; charset=utf-8' . "\r\n";
	$header .= 'From: admin@luongcao.com <admin@luongcao.com>' . "\r\n";
	//*/	
	$class_path = dirname(__FILE__);
	if(file_exists(str_replace("functions","classes",$class_path) . "/mailer/class.phpmailer.php")){
		require_once(str_replace("functions","classes",$class_path) . "/mailer/class.phpmailer.php");
	}
	
	$mail_server	=	"";
	$user_name		=	"";
	$password		=	"";
	
	
	//Lấy account mail có lần gửi ít nhất	
		
	$mail_server 	= "";
	$user_name		= "";
	$password		= "";
	
	$mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch

	$mail->IsSMTP(); // telling the class to use SMTP
	
	try {
	  $mail->Host       = "123.30.208.146"; // SMTP server
	  //$mail->SMTPDebug  = false;                     // enables SMTP debug information (for testing)
	  $mail->SMTPAuth   = false;                  // enable SMTP authentication
	  $mail->Port       = 6500;                    // set the SMTP port for the GMAIL server
	  $mail->CharSet	  = "utf-8";
	  $mail->Username   = "transaction.luongcao.com"; // SMTP account username
	  $mail->Password   = "hy4yr8fhryf45Oey";        // SMTP account password
	  $mail->AddAddress($to);
	  
	  $mail->SetFrom('noreply@luongcao.com', 'LuongCao');
	  $mail->Subject 		= $title;
	  $mail->AltBody 		= removeHTML($content); // optional - MsgHTML will create an alternate automatically
	  $mail->MsgHTML($content);
	  if(!$mail->Send()){
	  		return true;
	  }else{
	  		return false;
	  }
	  
	  $db_log	= new db_init();
	  $db_log->log('log_send_email', $content);
	  unset($db_log);
	  
	} catch (phpmailerException $e) {
	  echo $e->errorMessage(); //Pretty error messages from PHPMailer
	  $db_log	= new db_init();
	  $db_log->log('log_send_email', $e->errorMessage());
	  unset($db_log);
	} catch (Exception $e) {
	  echo $e->getMessage(); //Boring error messages from anything else!
	  $db_log	= new db_init();
	  $db_log->log('log_send_email', $e->errorMessage());
	  unset($db_log);
	}
	return;
}

//require_once("../classes/database.php");
//send_mailer("dinhtoan1905@gmail.com","chu de gui di","Cộng hòa xã hội chủ nghĩa Việt Nam <b>Xin chào các bạn</b><br><br>Cúc cu xin chào các bạn");

function generate_content($content,$array = array()){
	foreach($array as $key=>$value){
		$content = str_replace("{#" . $key . "#}",$value,$content);
	}
	return $content;
}

function send_mailer_spam($to,$title,$content, $FromEmail = "", $FromName = 'LươngCao (chuyên trang tuyển dụng)',$id_error=""){
	global $con_gmail_name;
	global $con_gmail_pass;
	global $con_gmail_subject;
	global $con_admin_email;
	//*	
	$header  = 'MIME-Version: 1.0' . "\r\n";
	$header .= 'Content-type: text/html; charset=utf-8' . "\r\n";
	$header .= 'From: ' . $FromEmail . ' <' . $FromEmail . '>' . "\r\n";
	//*/	
	$class_path = dirname(__FILE__);
	if(file_exists(str_replace("functions","classes",$class_path) . "/mailer/class.phpmailer.php")){
		require_once(str_replace("functions","classes",$class_path) . "/mailer/class.phpmailer.php");
	}
	
	$mail_server	=	"";
	$user_name		=	"";
	$password		=	"";
	
	$mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch

	$mail->IsSMTP(); // telling the class to use SMTP
	
	try {
	  $mail->Host       = "123.30.208.146"; // SMTP server
	  //$mail->SMTPDebug  = false;                     // enables SMTP debug information (for testing)
	  $mail->SMTPAuth   = false;                  // enable SMTP authentication
	  $mail->Port       = 6500;                    // set the SMTP port for the GMAIL server
	  $mail->CharSet	  = "utf-8";
	  $mail->Username   = "transaction.luongcao.com"; // SMTP account username
	  $mail->Password   = "hy4yr8fhryf45Oey";        // SMTP account password
	  $mail->AddAddress($to);
	  
	  $mail->SetFrom('noreply@luongcao.com', 'Tuyển dụng Lương Cao');
	  //$mail->SetFrom('noreply@mailvatgia.com', '');
	  //echo $to;
	  $mail->Subject 		= $title;
	  $mail->AltBody 		= removeHTML($content); // optional - MsgHTML will create an alternate automatically
	  $mail->MsgHTML($content);
	  if($mail->Send()){
	  		return true;
	  }else{
	  		return false;
	  }
	} catch (phpmailerException $e) {
	  echo $e->errorMessage(); //Pretty error messages from PHPMailer
	} catch (Exception $e) {
	  echo $e->getMessage(); //Boring error messages from anything else!
	}
	//Lấy account mail có lần gửi ít nhất	
}
?>