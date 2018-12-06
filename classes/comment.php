<?
/**
 * class send comment, get comment
 */ 

class comment{
   
   // 
   /**
    * function send comment
    * 
    * array(
    *    content =>
    *    code =>
    *    to_id =>
    * )
    * 
    * return 
    * 0 : không thành công;
    * 2 : Nội dung không có hoặc quá ngắn
    * 3 : Không có mã code
    * 1 : thành công
    */
   function send_comment($data = array()){
      
      
      global $admin_id, $branch_id, $myuser,$notification, $pathRoot;
      $content = isset($data['content'])? replaceMQ($data['content']) : '';
      $code = isset($data['code'])? intval($data['code']) : 0;
      $to_id = isset($data['to_id'])? intval($data['to_id']) : 0;
      $url  = isset($data['url'])? $data['url'] : '';
      $from_name  = isset($data['fname'])? replaceMQ($data['fname']) : '';
      
      
      if($content == '' || mb_strlen($content) < 1){
         return 2;
      }
      
      if($code <= 0) return 3;
      
      $common_id  = $admin_id + $to_id;
      
      $sql  = "INSERT INTO comment (com_content, com_date, com_use_id, com_common_id, com_code) VALUES
               ('". $content ."',". time() .",". $admin_id .",". $common_id .",". $code .")";
      $db_ex   = new db_execute($sql);
      if($db_ex->total > 0){
         
         $des  = 'Bạn có thêm 01 nội dung phản hồi mới của '. $from_name .' từ phần gọi hàng (Mã hóa đơn: ' . show_code($code) . ')';         
         $arrayMes   = array(
            'title' => $from_name . ' Gửi cho bạn bạn phản hồi mới từ mã hóa đơn ' . show_code($code)
            ,'description' => $des
            ,'action' => NOTI_ACTION_ADD_CALL_PRODUCT
            ,'url' => URL_WEB .  $pathRoot . $url
            ,'date' => time()
            ,'use_id' => $admin_id
            ,'parent_id' => $to_id
         );
         
         $arrayUser  = array($to_id);
         $notification->send($arrayMes, $arrayUser,$to_id);
         
         return 1;
          
      } 
      return 0;
      
   }
   
   /**
    * Function get comment
    * 
    * array(
    *    use_id => 
    *    to_id =>
    *    code =>
    * )
    */
   function get_comment($data = array()){
      $use_id = isset($data['use_id'])? intval($data['use_id']) : 0;
      $to_id = isset($data['to_id'])? intval($data['to_id']) : 0;
      $code    = isset($data['code'])? intval($data['code']) : 0;
      
      if($use_id <= 0 || $code <= 0) return 0;
      
      $common_id  = $use_id + $to_id;
      
      $db_comment = new db_query("SELECT * FROM comment
                                  WHERE com_code = " . intval($code) . "
                                  AND com_common_id = ". intval($common_id) . " 
                                  ORDER BY com_id ASC");
      $array_return   = $db_comment->resultArray('com_id');
      unset($db_comment);
      return $array_return;
   }
   
   
   
}