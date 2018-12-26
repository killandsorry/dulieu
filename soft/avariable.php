<?

define("PATH_ASSET", '/asset/');
define('PATH_CSS', '/asset/css/');
define('PATH_JS', '/asset/js/');

// define type bill
define('TYPE_NH', 1); // nhập hàng
define('TYPE_KK', 2); // kiểm kho
define('TYPE_THNCC', 3); // trả hàng
define('TYPE_BH', 4); // bán hàng
define('TYPE_KT', 5); // khởi tạo sp mới
define('TYPE_KTH', 6); // khách trả hàng

define('TYPE_PAYMENT_TM', 101); // thanh toán tiền mặt: - tiền mặt tại két
define('TYPE_PAYMENT_CK', 102); // thanh toán chuyển khoản: - tiền trong thẻ ngân hàng

// bill type in table user_bill
define('STOCK_CARD_TYPE_IN', 1); // loại hóa đơn nhập
define('STOCK_CARD_TYPE_OUT', 0); // loại hóa đơn bán

// page size
define('PAGE_SIZE', 20); // số bản ghi / trang

// config page
$page_prefix      = "";
$normal_class     = "page";
$selected_class   = "page_current";
$previous         = "<";
$next             = ">";
$first            = "<<";
$last             = ">>";
$break_type       = 2;//"1 => << < 1 2 [3] 4 5 > >>", "2 => < 1 2 [3] 4 5 >", "3 => 1 2 [3] 4 5", "4 => < >"
$pageurl          = $_SERVER["REQUEST_URI"];
$tmpUrl           = explode("&page=", $pageurl);
$pageurl          = $tmpUrl[0];
if (strpos($pageurl, '?') === false) $pageurl .= '?';
$current_page     = isset($tmpUrl[1]) ? intval($tmpUrl[1]) : 1;


$web_sortname  = 'QLBT';
$web_fullname  = 'Phần mềm nhà thuốc'; 
$current_url   = getURL(1,1,1,0);
$requestUri    = isset($_SERVER['REDIRECT_URL'])? $_SERVER['REDIRECT_URL'] : '';

$version_up       = rand(11111,99999);


$path_css   = PATH_CSS . 'common.css';
$arrayJs    = array(
   'jquery.js', 'common.js', 'shortcut.js', 'zebra_datepicker.min.js'
);

//<link href="https://fonts.googleapis.com/css?family=Roboto+Mono:300,300i,400,400i,500,700&amp;subset=vietnamese" rel="stylesheet">
$css        = '<link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i&amp;subset=vietnamese" rel="stylesheet">';
$css        .= '<link href="'. $path_css .'?v='. $version_up .'" type="text/css" rel="stylesheet" />';
$css        .= '<link href="'. PATH_ASSET . 'data/font/flaticon.css?v='. $version_up .'" type="text/css" rel="stylesheet" />';
$css        .= '<link href="'. PATH_ASSET .'css/zebra_datepicker.min.css?v='. $version_up .'" type="text/css" rel="stylesheet" />';

$js         = '';
foreach($arrayJs as $file){
   $js .= '<script src="'. PATH_JS . $file .'?v='. $version_up .'" type="text/javascript"></script>';
}




// array pay method
$arrayPayMethod = array(
   TYPE_PAYMENT_TM => 'Tiền mặt',
   TYPE_PAYMENT_CK => 'Chuyển khoản'
);

// đơn vị tính
$array_unit	= array(
   0 => '[ Đơn vị ]'
   ,1=>'Lọ'
   ,2=>'Hộp'
   ,3=>'Tuýp'
   ,4=>'Túi'
   ,6=>'Thùng'
   ,7=>'Kiện'
   ,8=>'Chai'
   ,9=>'Cọc'
   ,10=>'Gói'
   ,12=>'Vỉ'
   ,14=>'Bịch'
   ,15=>'Chiếc'
   ,16=>'Dây'
   ,17=>'Hộp to'
   ,18=>'Cái'
   ,20=>'Buộc'
   ,21=>'Cây'
   ,22=>'Viên'
   ,23=>'Cuộn'
   ,24=>'Ống'
   ,25=>'Miếng'
   ,26=>'Gram'
   ,27=>'Kg'
);
$array_unit_text	= array(
   'don vi' => 0
   ,'lo' => 1
   ,'hop' => 2
   ,'tuyp' => 3
   ,'tui' => 4
   ,'thung' => 6
   ,'kien' => 7
   ,'chai' => 8
   ,'coc' => 9
   ,'goi' => 10
   ,'vi' => 12
   ,'bich' => 14
   ,'chiec' => 15
   ,'day' => 16
   ,'hop to' => 17
   ,'cai' => 18
   ,'buoc' => 20
   ,'cay' => 21
   ,'vien' => 22
   ,'cuon' => 23
   ,'ong' => 24
   ,'mieng' => 25
   ,'kg' => 27,
   'gram' => 26
);


// menu
$arrayMenu  = array(   
   'import' => array(
      'name' => 'Nhập',
      'url' => 'import/',
      'ic' => 'flaticon-import',
      'key' => 'b',
      'sub' => array(
         'add' => array(
            'url' => 'add',
            'title' => 'Hóa đơn nhập kho',
            'key' => 1
         ),
         'addexcell' => array(
            'url' => 'add-excell',
            'title' => 'Nhập từ file excell',
            'key' => 2
         ),
         'history' => array(
            'url' => 'list',
            'title' => 'Lịch sử nhập kho',
            'line' => 1,
            'key' => 3
         ),
         'provider' => array(
            'url' => 'provider',
            'title' => 'Nhà cung cấp',
            'line' => 1,
            'key' => 3
         ),
         'transfer_warehoue' => array(
            'url' => 'transfer-warehouse',
            'title' => 'Chuyển kho',
            'key' => 3
         ),
         'transfer_access' => array(
            'url' => 'transfer-access',
            'title' => 'Nhận chuyển kho',
            'key' => 3
         ),
         'transfer_list' => array(
            'url' => 'transfer-list',
            'title' => 'Lịch sử chuyển kho',
            'key' => 3
         )
      )
   ),
   'sale' => array(
      'name' => 'Bán',
      'url' => 'sale/',
      'ic' => 'flaticon-export',
      'key' => 'c',
      'sub' => array(
         'add' => array(
            'url' => 'add',
            'title' => 'Hóa đơn bán lẻ',
            'key' => 1
         ),
         'addmulti' => array(
            'url' => 'add?type=wls',
            'title' => 'Hóa đơn bán buôn',
            'line' => 1,
            'key' => 2
         ),
         'list' => array(
            'url' => 'list',
            'title' => 'Lịch sử bán',
            'key' => 3
         ),
         'list_bonus' => array(
            'url' => 'list-bonus',
            'title' => 'Hóa đơn đã giảm giá',
            'line' => 1,
            'key' => 4
         ),
         'customer' => array(
            'url' => 'customer',
            'title' => 'Danh sách khách hàng',
            'key' => 3
         ),
         'access_lock' => array(
            'url' => 'access-lock-book',
            'title' => 'Duyệt chốt sổ doanh số',
            'key' => 3
         )
      )
   ),
   'product' => array(
      'name' => 'Sản phẩm',
      'url' => 'product/',
      'ic' => 'flaticon-pill',
      'key' => 'c',
      'sub' => array(
         'cat' => array(
            'url' => 'category',
            'title' => 'Danh mục thuốc',
            'line' => 1,
            'key' => 1
         ),
         'list' => array(
            'url' => 'list',
            'title' => 'Danh sách thuốc',
            'key' => 2
         ),
         'list_sale' => array(
            'url' => 'list-sale',
            'title' => 'Danh sách thuốc doanh số',
            'key' => 3
         ),
         'combo' => array(
            'url' => 'combo',
            'title' => 'Tạo liều thuốc',
            'line' => 1,
            'key' => 4
         ),
         'check_warehouse' => array(
            'url' => 'check-warehouse',
            'title' => 'Tạo kiểm kho',
            'key' => 4
         ),
         'check_list' => array(
            'url' => 'check-list',
            'title' => 'Danh sách kiểm kho',
            'line' => 1,
            'key' => 4
         ),
         'expires' => array(
            'url' => 'product-expires',
            'title' => 'Thuốc sắp hết hạn sử dụng',
            'key' => 4
         ),
         'product_warning' => array(
            'url' => 'product-warning',
            'title' => 'Thuốc sắp hết hàng',
            'line' => 1,
            'key' => 4
         ),
         'print' => array(
            'url' => 'product-print',
            'title' => 'In mã vạch thuốc',
            'key' => 4
         )
      )
   ),
   'setting' => array(
      'name' => 'Cài đặt',
      'url' => 'setting/',
      'ic' => 'flaticon-settings',
      'key' => 'a',
      'sub' => array(
         'warning' => array(
            'url' => 'warning',
            'title' => 'Cảnh báo thuốc hết hàng',
            'key' => 1
         ),
         'owner' => array(
            'url' => 'owner',
            'title' => 'Đơn vị sử dụng hệ thống',
            'key' => 2
         ),
         'calendar' => array(
            'url' => 'calendar',
            'title' => 'Nhắc lịch',
            'key' => 3
         ),
         'price_table' => array(
            'url' => 'price-table',
            'title' => 'Bảng giá bán buôn',
            'key' => 4
         )
      )
   
   ),
   'manager' => array(
      'name' => 'Quản lý',
      'url' => 'manager/',
      'ic' => 'flaticon-share',
      'key' => 'd',
      'sub' => array(
         'branch' => array(
            'url' => 'branch',
            'title' => 'Cửa hàng thuốc',
            'key' => 1
         ),
         'branch_set' => array(
            'url' => 'branch-set',
            'title' => 'Chọn cửa hàng làm việc',
            'key' => 2,
            'line' => 1
         ),
         'history' => array(
            'url' => 'history',
            'title' => 'Lịch sử hành động',
            'key' => 3
         ),
         'staff' => array(
            'url' => 'staff',
            'title' => 'Quản lý nhân viên',
            'key' => 4,
            'line' => 1
         ),
         'money' => array(
            'url' => 'money-add',
            'title' => 'Phiếu thu/chi',
            'key' => 5
         ),
         'money_list' => array(
            'url' => 'money-list',
            'title' => 'Danh sách thu/chi',
            'key' => 6,
            'line' => 1
         ),
         'money-remain' => array(
            'url' => 'price-remain',
            'title' => 'Tiền thừa chốt doanh số',
            'key' => 7
         )
      )
   ),
   'staff' => array(
      'name' => 'Nhân viên',
      'url' => 'staff/',
      'ic' => 'flaticon-businessman',
      'key' => 'e',
      'sub' => array(
         'salesstaff' => array(
            'url' => 'sales-staff',
            'title' => 'Doanh số nhân viên',
            'key' => 1
         ),
         'send_lock_book' => array(
            'url' => 'send-lock-book',
            'title' => 'Chốt doanh số',
            'line' => 1,
            'key' => 2
         ),
         'list_lock_book' => array(
            'url' => 'list-lock-book',
            'title' => 'Lịch sử chốt doanh số',
            'key' => 3
         )
      )
   ),
   'report' => array(
      'name' => 'Báo cáo',
      'url' => 'report/',
      'ic' => 'flaticon-profit',
      'key' => 'f',
      'sub' => array(
         'salesday' => array(
            'url' => 'sales-day',
            'title' => 'Doanh số theo ngày',
            'key' => 1
         ),
         'salesmonth' => array(
            'url' => 'sales-month',
            'title' => 'Doanh số theo tháng',
            'key' => 1
         ),
         'salesstaff' => array(
            'url' => 'sales-staff',
            'title' => 'Doanh số theo nhân viên',
            'key' => 2
         ),
         'salesbranch' => array(
            'url' => 'sales-branch',
            'title' => 'Doanh số theo cửa hàng',
            'line' => 1,
            'key' => 3
         ),
         'profitsaler' => array(
            'url' => 'profit-saler',
            'title' => 'Thuốc có doanh số cao nhất',
            'key' => 4
         ),
         'quantitysale' => array(
            'url' => 'quantity-saler',
            'title' => 'Thuốc bán được nhiều nhất',
            'key' => 5
         ),
         'smallsaler' => array(
            'url' => 'small-saler',
            'title' => 'Thuốc bán chậm nhất',
            'key' => 6
         ),
         'deficitsale' => array(
            'url' => 'deficit-saler',
            'title' => 'Thuốc bán lỗ',
            'key' => 7,
            'line' => 1
         ),
         'survivebranch' => array(
            'url' => 'survive-branch',
            'title' => 'Tồn hàng theo cửa hàng',
            'key' => 8
         ),
         'survive' => array(
            'url' => 'survive-all',
            'title' => 'Tồn tất cả cửa hàng',
            'key' => 9,
            'line' => 1
         ),
         'index' => array(
            'url' => 'index-import-sale',
            'title' => 'Chỉ số nhập / xuất',
            'key' => 10
         ),
         'caculator' => array(
            'url' => 'cal',
            'title' => 'Ước tính lãi',
            'key' => 10
         ),
         'indexall' => array(
            'url' => 'index-all',
            'title' => 'Chỉ số cửa hàng',
            'key' => 10
         ),
         'report3' => array(
            'url' => 'report-month',
            'title' => 'Báo cáo quý',
            'key' => 10
         ),
         'reportall' => array(
            'url' => 'report-year',
            'title' => 'Báo cáo năm',
            'key' => 10
         )
      )
   ),
   'question' => array(
      'name' => 'Hướng dẫn',
      'url' => 'question/',
      'ic' => 'flaticon-conversation',
      'key' => 'g',
      'sub' => array(
         'help' => array(
            'url' => 'help',
            'title' => 'Hướng dẫn sử dụng phần mềm',
            'key' => 1
         ),
         'pay' => array(
            'url' => 'pay',
            'title' => 'Mua gói sử dụng',
            'key' => 2
         )
      )
   )
   
);

$currentUrl = $_SERVER['REQUEST_URI'];
$arrayUrl   = explode('/', $currentUrl);

$file_title   = 'Hệ thống quản lý nhà thuốc';
if(isset($arrayUrl[3]) && $arrayUrl[3] != ''){
   $arrendfile  = explode('?', $arrayUrl[3]);
   $endFile = isset($arrendfile[0])? $arrendfile[0] : '';
   if($endFile != ''){
      foreach($arrayMenu as $mk => $mvalue){
         if($mk == $arrayUrl[2]){
            foreach($mvalue['sub'] as $subvalue){
               if($endFile == $subvalue['url']){
                  $file_title = $subvalue['title'];
                  break;
               }
            }
         }
      }      
   }   
}

