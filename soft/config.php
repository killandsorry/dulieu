<?
$dirname = dirname(__FILE__) . '/';

require_once $dirname.'../classes/database.php';
require_once $dirname.'../classes/user.php';

require_once $dirname.'../classes/kill_search.php';
require_once $dirname.'../classes/kill_product.php';
require_once $dirname.'../classes/kill_warehouse.php';
require_once $dirname.'../classes/kill_sale.php';
require_once $dirname.'../classes/kill_provider.php';

require_once $dirname.'../functions/functions.php';
require_once $dirname.'../functions/date_functions.php';


$killSearch       = new killSearch();
$killProduct      = new killProduct();
$killWareHouse    = new killWareHouse();
$killSale         = new killSale();
$killProvider     = new killProvider();

// check user
$myuser  = new user();
if($myuser->logged != 1){
   redirect(ROOT . 'login');
   exit();
}

$child_id      = $myuser->u_id;       
$admin_id      = ($myuser->useField['use_parent_id'] > 0)? $myuser->useField['use_parent_id'] : $child_id;
$branch_id     = 0;

// lấy thông tin chi nhánh
$arrayBranch   = array();
$db_branch = new db_query("SELECT * FROM branch WHERE bra_use_id = " . intval($admin_id) . " AND bra_active = 1");
while($rowbranch = $db_branch->fetch()){
   $arrayBranch[$rowbranch['bra_id']] = $rowbranch;
}
unset($db_branch);

// fix branch hiện tại của user
$branch_id  = getValue('branch_id', 'int', 'COOKIE', 0);

// branch do mình quản lý
$myBranch   = array();
if($myuser->u_id != $admin_id){
   $user_branch   = $myuser->useField['use_branch'];   
   
   if($user_branch != ''){
      $user_branch = json_decode(base64_decode($user_branch), 1);         
      if(count($user_branch) == 1){
         $branch_id = $user_branch[0];
         $myBranch[$branch_id] = isset($arrayBranch[$branch_id])? $arrayBranch[$branch_id] : array();
      }else{
         foreach($user_branch as $brid){
            if(isset($arrayBranch[$brid])){
               $myBranch[$brid] = isset($arrayBranch[$brid])? $arrayBranch[$brid] : array();
            }
         }
         
         $arrBranch_reveser   = array_flip($user_branch);
         if(!isset($arrBranch_reveser[$branch_id])) $branch_id = 0;
      }      
   }
}else{
   $myBranch = $arrayBranch;
   if(count($arrayBranch) == 1){
      $arrkey  = array_keys($arrayBranch);
      $branch_id = $arrkey[0];
   }else{
      if(!isset($arrayBranch[$branch_id])) $branch_id = 0;
   }
}

//bắt chọn chi nhánh trước khi thực hiện
/*
if($branch_id <= 0){
   if(!isset($module) || $module != 'branch'){
      header('location:'. $pathRoot .'branch.php?g=1');
      exit();
   }
}
*/

// check thông tin branch để lấy thông tin bảng
if($branch_id > 0){
   if(isset($myBranch[$branch_id])){
      if($myBranch[$branch_id]['bra_master'] == 1){
         define('TABLE_PRODUCT', 'user_product_' . intval($admin_id));
         define('TABLE_WAREHOUSE', 'user_warehouse_' . intval($admin_id));
         define('TABLE_ORDER', 'user_order_' . intval($admin_id));
         define('TABLE_PROVIDER', 'user_provider_' . intval($admin_id));
         define('TABLE_STOCK_CARD', 'user_stock_card_' . intval($admin_id));
      }else{
         define('TABLE_PRODUCT', 'user_product_' . intval($admin_id) . '_' . intval($branch_id));
         define('TABLE_WAREHOUSE', 'user_warehouse_' . intval($admin_id) . '_' . intval($branch_id));
         define('TABLE_ORDER', 'user_order_' . intval($admin_id) . '_' . intval($branch_id));
         define('TABLE_PROVIDER', 'user_provider_' . intval($admin_id) . '_' . intval($branch_id));
         define('TABLE_STOCK_CARD', 'user_stock_card_' . intval($admin_id) . '_' . intval($branch_id));
      }
   }
}

// define
define('ADMIN_ID', $admin_id);
define('CHILD_ID', $child_id);
define('BRANCH_ID', $branch_id);


require_once 'avariable.php';