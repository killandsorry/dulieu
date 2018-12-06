<?
$dirname = dirname(__FILE__) . '/';
require_once $dirname.'../classes/database.php';
require_once $dirname.'../classes/user.php';

require_once $dirname.'../functions/functions.php';
require_once $dirname.'../functions/date_functions.php';

$myuser  = new user();
if($myuser->logged == 1){
   redirect('/soft/');
   exit();
}

$action  = getValue('myaction', 'str', 'POST', '');
$username   = getValue('uname', 'str', 'POST', '');
$pass       = getValue('upass', 'str', 'POST', '');
$errorMsg   = '';
if($action == 'login'){
   
   
   if($username == '' || $pass == ''){
      $errorMsg   = ('Chưa có tên tài khoản và mật khẩu');
   }else{
      $myuser  = new user($username, $pass);
      if($myuser->logged ==  1){
         
         $timesave = 365*86400;
         $myuser->savecookie($timesave);
         
         header('location:/soft/');
         exit();
      }else{
         $errorMsg = ('Tài khoản hoặc Mật khẩu không đúng');
      }
   }
}

?>
<!DOCTYPE HTML>
<head>
	<meta http-equiv="content-type" content="text/html, charset=utf-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Đăng nhập hệ thống</title>
   <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i&amp;subset=vietnamese" rel="stylesheet">
   <link rel="stylesheet" href="/asset/css/css_out.css" />
</head>
<body class="login">
   <div class="content_login">
      <form method="post" action="">
         <h1>Vào tài khoản của bạn</h1>
         <ul class="row">
             
            <li>
               Tên tài khoản:<br />
               <input type="text" value="<?=$username?>" placeholder="nhập tên tài khoản" id="uname" name="uname" />
            </li>
            <li>
               Mật khẩu:<br />
               <input type="password" value="<?=$pass?>" placeholder="nhập mật khẩu" id="upass" name="upass" />
            </li>                       
            <li>    
               <p class="error"><?=$errorMsg?></p>           
               <input type="submit" value="Đăng nhập" class="btn btn_login" />
               <input type="hidden" value="login" name="myaction" />
            </li>
            <li>
               <p class="question">Bạn chưa có tài khoản? <a href="/soft/register">Đăng ký tài khoản mới</a></p>
            </li>
         </ul>
      </form>
   </div>
</body>
</html>