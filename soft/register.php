<?
$dirname = dirname(__FILE__) . '/';
require_once $dirname.'../classes/database.php';
require_once $dirname.'../classes/user.php';
require_once $dirname.'../classes/generate_form.php';

require_once $dirname.'../functions/functions.php';
require_once $dirname.'../functions/date_functions.php';

$action  = getValue('myaction', 'str', 'POST', '');
$errMsg	= '';
$use_create	= time();
$use_expires   = $use_create + (15*86400);
$use_security	= rand(111111, 999999);
$new_pass		= '';
$use_active = 1;
$use_type   = 1;
$use_is_admin  = 1;
$myform	= new generate_form();
$myform->add('use_login', 'uname', 0, 0, '', 1, 'Bạn chưa nhập tài khoản', 1, 'Tài khoản đã tồn tại');
$myform->add('use_pass', 'new_pass', 0, 1, '', 1, 'Bạn chưa nhập mật khẩu', 0, '');
$myform->add('use_phone', 'uphone', 0, 0, '', 1, 'Bạn chưa nhập số điện thoại', 1, 'Số điện thoại đã tồn tại');
$myform->add('use_fullname', 'ufullname', 0, 0, '', 1, 'Bạn chưa nhập tên đầy đủ', 0, '');
$myform->add('use_security', 'use_security', 1, 1, '', 0, '', 0, '');
$myform->add('use_create', 'use_create', 1, 1, '', 0, '', 0, '');
$myform->add('use_expires', 'use_expires', 1, 1, '', 0, '', 0, '');
$myform->add('use_active', 'use_active', 1, 1, '', 0, '', 0, '');
$myform->add('use_is_admin', 'use_is_admin', 1, 1, '', 0, '', 0, '');
$myform->addTable('users');

$ufullname  = getValue('ufullname', 'str', 'POST', '');
$uphone  = getValue('uphone', 'str', 'POST', '');
$usepass	= getValue('upass', 'str', 'POST', '');
$reusepass	= getValue('urepass', 'str', 'POST', '');
$usename	= getValue('uname', 'str', 'POST', '');

if($action == 'login'){
   
   if(!format_login_phone($uphone)){
      $errMsg .= '• Số điện thoại bạn nhập không đúng định dạng<br>';
   }
   
   if(strlen($usename) != mb_strlen($usename, 'UTF-8')){
      $errMsg .= '• Tên tài khoản không được viết có dấu<br>';
   }
   
   if(strpos($usename, ' ') !== false){
      $errMsg .= '• Tên tài khoản không được chứa dấu cách<br>';
   }
   
   if($usepass == '' || $reusepass == ''){
   	$errMsg .= '• Bạn chưa nhập mật khẩu';
   }else{
   	if($usepass != $reusepass){
   		$errMsg .= '• 02 ô mật khẩu không trùng khớp';
   	}
      
      if(strlen($usepass) < 6){
         $errMsg .= '• Mật khẩu phải có ít nhất 06 ký tự';   	  
   	}
   }
   
   $new_pass	= md5($usepass . $use_security);
   
   
   
   $errMsg .= $myform->checkdata();
   
   if($errMsg == ''){
      $db_ex	= new db_execute_return();
      $uid     = $db_ex->db_execute($myform->generate_insert_SQL());
      //echo $uid;
      if($uid > 0){
         $db_branch   = new db_execute("INSERT INTO branch(bra_use_id, bra_name, bra_date_create)
                                        VALUES(". $uid .",'Chi nhánh 01',". time() .")");
         unset($db_branch);
         
         $myuser  = new user($usename, $usepass);
         if($myuser->logged == 1){
            $timesave = 365*86400;
            $myuser->savecookie($timesave);
            
            header('location:/soft/');
            exit();
         }
      }
   }
   
}

?>
<!DOCTYPE HTML>
<head>
	<meta http-equiv="content-type" content="text/html, charset=utf-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Đăng ký mới tài khoản</title>
   <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i&amp;subset=vietnamese" rel="stylesheet">
   <link rel="stylesheet" href="/asset/css/css_out.css" />
</head>
<body class="login">
   <div class="content_register">
      <form method="post" action="">
         <h1>Đăng ký tài khoản mới</h1>
         <ul class="row">
            <li>
               Họ tên đầy đủ:<br />
               <input type="text" value="<?=$ufullname?>" placeholder="nhập họ tên" id="ufullname" name="ufullname" />
            </li> 
            <li>
               Tên tài khoản:<br />
               <input type="text" value="<?=$usename?>" placeholder="nhập tên tài khoản" id="uname" name="uname" />
               <i>(Tài khoản viết liền, không dấu, ít nhất 6 ký tự)</i>
            </li>
            <li>
               Mật khẩu:<br />
               <input type="password" value="<?=$usepass?>" placeholder="nhập mật khẩu" id="upass" name="upass" />
               <i>(Mật khẩu viết liền, ít nhất 6 ký tự)</i>
            </li>  
            <li>
               Nhập lại mật khẩu:<br />
               <input type="password" value="<?=$reusepass?>" placeholder="nhập lại mật khẩu giống ô mật khẩu" id="urepass" name="urepass" />
               <i>(Nhập lại mật khẩu giống ô Mật khẩu)</i>
            </li> 
            <li>
               Số điện thoại:<br />
               <input type="text" value="<?=$uphone?>" placeholder="nhập số điện thoại" id="uphone" name="uphone" />
               <i>(Vui lòng nhập đúng số điện thoại để xác minh tài khoản)</i>
            </li>                    
            <li>    
               <p class="error"><?=$errMsg?></p>           
               <input type="submit" value="Đăng ký" class="btn btn_login" />
               <input type="hidden" value="login" name="myaction" />
            </li>
            <li>
               <p class="question">Bạn đã có tài khoản? <a href="/soft/login">Đăng nhập tài khoản</a></p>
            </li>
         </ul>
      </form>
   </div>
</body>
</html>