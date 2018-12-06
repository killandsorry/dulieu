<?

/**
 * 
 * Class liên quan đến forum
 */

// defined forum avariable
define('FORUM_TABLE_COMMENT', 10); // số bảng comment trong forum
define('FORUM_TABLE_FORUM_FOLOW', 10); // số bảng forum_folow
define('FORUM_TABLE_FORUM_USER_FOLOW', 10); // số bảng user folow forum_

class forum{
   
   
   /**
    *  Function create table comemnt
    * param: 
    * array(
    *    for_id => id
    * )
    * 
    * return : forum_comment_id
    */
   function create_table_comment($row = array()){
      $for_id  = isset($row['for_id'])? intval($row['for_id']) : 0;
      
      if($for_id <= 0) return 0;
      $table   = 'forum_comment_' . intval($for_id % FORUM_TABLE_COMMENT);
      return $table;
   }
   
   /**
    *  Function create table user folow
    * user folow forum nào
    * param: 
    * array(
    *    use_id => id
    * )
    * 
    * return : forum_user_folow_id
    */
   function create_table_user_folow($row = array()){
      $use_id  = isset($row['use_id'])? intval($row['use_id']) : 0;
      
      if($use_id <= 0) return 0;
      $table   = 'forum_user_folow_' . intval($use_id % FORUM_TABLE_FORUM_USER_FOLOW);
      return $table;
   }
   
   /**
    *  Function create table forum folow 
    *  (những ai folow forum để báo notifi)
    * param: 
    * array(
    *    for_id => id
    * )
    * 
    * return : forum_folow_id
    */
   function create_table_forum_folow($row = array()){
      $for_id  = isset($row['for_id'])? intval($row['for_id']) : 0;
      
      if($for_id <= 0) return 0;
      $table   = 'forum_folow_' . intval($for_id % FORUM_TABLE_FORUM_FOLOW);
      return $table;
   }
   
   /**
    * Function user folow forum
    * 
    * se thuc hien 2 viec
    * 1: insert table forum_flow
    * 2: insert table forum_user_folow
    * 
    * param:
    * array(
    *    use_id => 
    *    for_id =>
    * )
    * 
    * return success or error
    */
   function folow($row = array()){
      $use_id = isset($row['use_id'])? intval($row['use_id']) : 0;
      $for_id = isset($row['for_id'])? intval($row['for_id']) : 0;
      
      if($use_id <= 0 || $for_id <= 0) return 0;
      
      $table_user_folow = $this->create_table_user_folow(array('use_id' => $use_id));
      
      $table_forum_folow   = $this->create_table_forum_folow(array('for_id' => $for_id));
      
      // 1:
      $db_ex  = new db_execute("INSERT IGNORE INTO " . $table_user_folow . " (fuf_use_id,fuf_for_id)
                                VALUES(". intval($use_id) .",". intval($for_id) . ")", __FILE__ . " LINE: " . __LINE__,
                                DB_NOTIFICATION);
      unset($db_ex);
      
      // 2:      
      $db_ex  = new db_execute("INSERT IGNORE INTO " . $table_forum_folow . " (ffl_use_id,ffl_for_id)
                                VALUES(". intval($use_id) .",". intval($for_id) . ")", __FILE__ . " LINE: " . __LINE__,
                                DB_NOTIFICATION);
      unset($db_ex);
      
      // update folow table forum
      $db_forum   = new db_execute("UPDATE forum SET for_folowing = for_folowing + 1
                                    WHERE for_id = " . intval($for_id), __FILE__, DB_NOTIFICATION);
      unset($db_forum);
      
      return 1;
   }
   
   
   function setCountComment($row = array()){
      $for_id = isset($row['for_id'])? intval($row['for_id']) : 0;
      
      if($for_id <= 0) return 0;
      
      $table = 'forum';
      
      $db_ex  = new db_execute("UPDATE forum SET for_answer = for_answer + 1 
                                WHERE for_id = ". $for_id, __FILE__ . " LINE: " . __LINE__,
                                DB_NOTIFICATION);
      unset($db_ex);
      
      return 1;
   }
   
   /**
    * Function un_folow
    * (ngung theo doi forum)
    * 
    * se lam 2 viec
    * 1: xoa trong bang forum_folow
    * 2: xoa trong bang forum_user_folow
    * 
    * param
    * array(
    *    use_id => 
    *    for_id =>
    * )
    * 
    * return success || error
    */
    
    function un_folow($row = array()){
      $use_id = isset($row['use_id'])? intval($row['use_id']) : 0;
      $for_id = isset($row['for_id'])? intval($row['for_id']) : 0;
      
      if($use_id <= 0 || $for_id <= 0) return 0;
      
      $table_user_folow = $this->create_table_user_folow(array('use_id' => $use_id));
      
      $table_forum_folow   = $this->create_table_forum_folow(array('for_id' => $for_id));
      
      // 1:
      $db_ex  = new db_execute("DELETE FROM " . $table_user_folow . " 
                                WHERE fuf_use_id = " . intval($use_id) . " AND fuf_for_id = ". intval($for_id)
                                , __FILE__ . " LINE: " . __LINE__,
                                DB_NOTIFICATION);
      unset($db_ex);
      
      // 2:
      $db_ex  = new db_execute("DELETE FROM " . $table_forum_folow . "
                                WHERE ffl_use_id = ". intval($use_id) ." AND ffl_for_id = ". intval($use_id)
                                , __FILE__ . " LINE: " . __LINE__,
                                DB_NOTIFICATION);
      unset($db_ex);
      
      // update folow table forum
      $db_forum   = new db_execute("UPDATE forum SET for_folowing = for_folowing - 1
                                    WHERE for_id = " . intval($for_id), __FILE__, DB_NOTIFICATION);
      unset($db_forum);
      
      return 1;
   }
   
   
   /**
    * Check đã folow forum hay chưa
    * 
    * array(
    *    'for_id' => 
    *    'use_id' =>
    * )
    * 
    * return 0 : 1
    */
   function check_folow_forum($row = array()){
      $for_id  = isset($row['for_id'])? intval($row['for_id']): 0;
      $use_id  = isset($row['use_id'])? intval($row['use_id']): 0;
      
      if($for_id <= 0) return 0;
      
      $table_folow   = $this->create_table_user_folow(array('use_id' => $use_id));
      $db_check   = new db_query("SELECT * FROM " . $table_folow . "
                                  WHERE fuf_use_id = " . intval($use_id) . " AND fuf_for_id = " . intval($for_id) . " LIMIT 1",
                                  __FILE__, DB_NOTIFICATION);
      if($r = $db_check->fetch()){
         return 1;
      }
      unset($db_check);
      
      return 0;
      
   }
   
   /**
    * Function get user flow forum
    * (lay ra nhung ai folow forum)
    * 
    * param
    * array(
    *    for_id =>
    * )
    * 
    * return array
    */
    
   function get_folow_of_forum($row = array()){
      $for_id = isset($row['for_id'])? intval($row['for_id']) : 0;
      
      $array_return  = array();
      
      if($for_id <= 0) return $array_return;
      
      $table_folow_forum   = $this->create_table_forum_folow(array('for_id' => $for_id));
      $db_folow   = new db_query("SELECT * FROM " . $table_folow_forum . "
                                  WHERE ffl_for_id = " . intval($for_id),
                                  __FILE__ ,
                                  DB_NOTIFICATION);
      while($data = $db_folow->fetch()){
         $array_return[$for_id] = $data['ffl_use_id'];
      }
      unset($db_folow);
      
      return $array_return;
   }
   
   /**
    * Function get forum user folow
    * (lay nhung forum ma user folow)
    * 
    * param
    * array(
    *    use_id => 
    * )
    * 
    * return $array
    */
   function get_forum_user_folow($row = array()){
      $use_id = isset($row['use_id'])? intval($row['use_id']) : 0;
      
      $array_return  = array();
      
      if($use_id <= 0) return $array_return;
      
      $table_user_folow_forum   = $this->create_table_user_folow(array('use_id' => $for_id));
      $db_folow   = new db_query("SELECT * FROM " . $table_user_folow_forum . "
                                  WHERE fuf_use_id = " . intval($use_id),
                                  __FILE__ ,
                                  DB_NOTIFICATION);
      while($data = $db_folow->fetch()){
         $array_return[$use_id] = $data['fuf_for_id'];
      }
      unset($db_folow);
      
      return $array_return;
   }
   
   
   /**
    * Get count comment of forum
    * 
    * param
    * array(
    *    for_id =>
    * )
    * 
    * return count
    */
   function get_count_comment_forum ($row = array()){
      $for_id  = isset($row['for_id'])? intval($row['for_id']) : 0;
      
      if($for_id <= 0) return 0;
      $total   = 0;
      
      $table_comment = $this->create_table_comment(array('for_id' => $for_id));
      $db_q = new db_query("SELECT COUNT(*) AS count FROM " . $table_comment, __FILE__, DB_NOTIFICATION);
      if($rdata   = $db_q->fetch()){
         $total   = $rdata['count'];
      }
      unset($db_q);
      return $total;
   }
   
   
   /**
    * Function get comment of forum
    * (lay nhung comment cua forum theo trang và so luong)
    * 
    * param:
    * array(
    *    for_id => 
    *    page => 
    *    limit => 
    * )
    * 
    * return array
    */
   function get_comment_forum ($row = array()){
      
      $for_id = isset($row['for_id'])? intval($row['for_id']) : 0;
      $page = isset($row['page'])? intval($row['page']) : 1;
      $limit = isset($row['limit'])? intval($row['limit']) : 5;
      $last_comment  = isset($row['last'])? intval($row['last']) : 0;
      
      $array_return  = array();
      
      if($for_id <= 0) return $array_return;
      
      if($page <= 1) $page = 1;
      if($page > 2000) $page = 2000;
      $page = intval($page);
      
      if($limit <= 1) $limit = 1 ;
      if($limit > 100) $limit = 100;
      $limit = intval($limit);
      
      // bắt đầu lấy dữ liệu
      $table_comment = $this->create_table_comment(array('for_id' => $for_id));
      
      $sql_where  = " ORDER BY foc_id ASC ";
      if($last_comment == 1){
         $sql_where  = " ORDER BY foc_date DESC ";
      }
      
      // lấy thông tin bài đăng forum
      $db_forum   = new db_query("SELECT * FROM forum
                                  WHERE for_id = " . intval($for_id) . " LIMIT 1", __FILE__, DB_NOTIFICATION);
      if($rdata   = $db_forum->fetch()){
         $array_return['owner'] = $rdata;
         $user = new db_query("SELECT * FROM users WHERE use_id = " . $rdata['for_use_id'] . " LIMIT 1");
         if($ruser   = $user->fetch()){
            $array_return['owner']['user'] = $ruser;
         }
         unset($user);
      }else{
         return 0;
      }
      unset($db_forum);
      
      // lấy thông tin comment
      $db_comment = new db_query("SELECT * FROM " . $table_comment . " 
                                  WHERE foc_for_id = " . intval($for_id) . "
                                  AND foc_active = 1 ". $sql_where . "
                                  LIMIT ". (($page - 1) * $limit) . "," . $limit,
                                  __FILE__,
                                  DB_NOTIFICATION);
      while($rcomment   = $db_comment->fetch()){
         $array_return['comment'][$rcomment['foc_id']] = $rcomment;
         $user = new db_query("SELECT * FROM users WHERE use_id = " . $rcomment['foc_use_id'] . " LIMIT 1");
         if($ruser   = $user->fetch()){
            $array_return['comment'][$rcomment['foc_id']]['user'] = $ruser;
         }
         unset($user);
      }
      unset($db_comment);
      
      return $array_return;
   }
   
   
   /**
    * Count forum
    */
   function count_forum(){
      $db_count   = new db_query("SELECT COUNT(*) AS count FROM forum", __FILE__,DB_NOTIFICATION);
      if($rcount  = $db_count->fetch()){
         return $rcount['count'];
      }
      unset($db_count);
      
      return 0;
   }
   
   /**
    * Count forum user folow
    * 
    * array(
    *    use_id =>
    * )
    * 
    * return count
    */
   function count_forum_user_folow($row = array()){
      $use_id  = isset($row['use_id'])? intval($row['use_id']) : 0;
      
      if($use_id <= 0) return 0;
      
      $table_folow   = $this->create_table_user_folow(array('use_id' => $use_id));
      $db_count   = new db_query("SELECT COUNT(*) AS count FROM " . $table_folow, __FILE__,DB_NOTIFICATION);
      if($rcount  = $db_count->fetch()){
         return $rcount['count'];
      }
      unset($db_count);
      
      return 0;
   }
   
   /**
    * Function get forum new
    * 
    * lay bai viet
    * param
    * 
    * array(
    * 
    * )
    * 
    * return array
    */
   function get_forum($row = array()){
      $array_return  = array();
      
      $page = isset($row['page'])? intval($row['page']) : 1;
      $limit = isset($row['limit'])? intval($row['limit']) : 20;
      $type = isset($row['type'])? $row['type'] : 'new';
      $use_id  = isset($row['use_id'])? intval($row['use_id']) : 0;
      
      
      if($page <= 1) $page = 1;
      if($page > 2000) $page = 2000;
      $page = intval($page);
      
      if($limit <= 5) $limit = 5;
      if($limit > 100) $limit = 100;
      $limit = intval($limit);
      
      $array_forid   = array();
      if($type == 'folow'){
         
         $table_folow   = $this->create_table_user_folow(array('use_id' => $use_id));
         $db_folow   = new db_query("SELECT * FROM " . $table_folow . "
                                       WHERE fuf_use_id = " . intval($use_id) . "
                                       ORDER BY fuf_for_id DESC
                                       LIMIT " . ($page-1)*$limit . "," . $limit, __FILE__, DB_NOTIFICATION);
         while($rfolow  = $db_folow->fetch()){
            $array_forid[] = $rfolow['fuf_for_id'];
         }
         unset($db_folow);
         
         if(!empty($array_forid)){
            $db_forum   = new db_query("SELECT * FROM forum WHERE for_id IN (". implode(',', $array_forid) .") 
                                       ORDER BY find_in_set(for_id,'". implode(',', $array_forid) ."')",
                                        __FILE__, DB_NOTIFICATION);
         }else{
            return $array_return;
         }
      }else{
         $db_forum   = new db_query("SELECT * FROM forum 
                                     ORDER BY for_id DESC LIMIT " . (($page - 1) * $limit) . "," . $limit,
                                     __FILE__, DB_NOTIFICATION);
      }
      
      while($rfor = $db_forum->fetch()){
         
         $array_return[$rfor['for_id']] = $rfor;
         $user = new db_query("SELECT * FROM users WHERE use_id = " . $rfor['for_use_id'] . " LIMIT 1");
         if($ruser   = $user->fetch()){
            $array_return[$rfor['for_id']]['user'] = $ruser;
         }
         unset($user);
      }
      unset($db_forum);
      
      return $array_return;
   }
   
   
   /**
    * Check forum exits
    * 
    * param
    * array(
    *    'for_id' =>
    * )
    * retur 0 || 1
    */
   function check_forum_id($row = array()){
      $for_id  = isset($row['for_id'])? intval($row['for_id']) : 0;
      
      $db_check   = new db_query("SELECT * FROM forum WHERE for_id = " . intval($for_id) . " LIMIT 1", __FILE__, DB_NOTIFICATION);
      if($ro   = $db_check->fetch()){
         return $ro;
      }else{
         return 0;
      }
   }
   
   
   /**
    * Send notification when post
    * 
    * param
    * array(
    *    use_name =>
    *    for_title => 
    *    url => 
    * )
    * 
    */
   function send_notification_post($row = array()){
      global $notification;
      
      $use_name   = isset($row['use_name'])? $row['use_name'] : '';
      $for_title  = isset($row['for_title'])? $row['for_title'] : '';
      $url  = isset($row['url'])? $row['url'] : '';
      
      
      $text = $use_name . ' vừa đăng ' . $for_title . ' trong diễn đàn';
      $data_msg   = array(
         'title' => $use_name . ' vừa đăng trên diễn đàn'
         ,'description' => $text
         ,'url' => $url
         ,'action' => NOTI_ACTION_ADD_PRODUCT
         ,'date' => time()
         ,'use_id' => 0
      );
      
      $db_user = new db_query("SELECT * FROM user_gcm LIMIT 100", __FILE__, DB_NOTIFICATION);
      while($ruser   = $db_user->fetch()){
         
         // save notification
         $data_user   = array($ruser['usg_use_id']); 
         $s = $notification->send($data_msg, $data_user);
         
         
         // send gcm
         $message_data  = array(
            'id' => $ruser['usg_register_id'],
            'body' => $text,
            'title' => $use_name . ' vừa đăng trên diễn đàn',
            'url' => $url                     
         );
         $send = $notification->gcm($message_data);
         $send = json_decode($send);
         /*
         if($send->success == 0){
            $db_ex   = new db_execute("DELETE FROM user_gcm WHERE usg_id = " . $ruser['usg_id'], __FILE__, DB_NOTIFICATION);
            unset($db_ex);
         }
         */
         
      }
      unset($db_user);
      
      // send them cho cong tac vien
      $db_user = new db_query("SELECT * FROM users WHERE " . USER_TYPE_CTV ." & use_supplier = " . USER_TYPE_CTV);
      while($row  = $db_user->fetch()){
         
         $ctv_id  = $row['use_id'];
         $data_user   = array($ctv_id); 
         $s = $notification->send($data_msg, $data_user);
         
         // lấy thông tin register của user
         $db_gcm  = new db_query("SELECT * FROM user_gcm WHERE usg_use_id = " . intval($ctv_id), __FILE__, DB_NOTIFICATION);
         while($rgcm  = mysqli_fetch_assoc($db_gcm->result)){
            if($rgcm['usg_register_id'] != ''){
               $message_data  = array(
                  'id' => $ruser['usg_register_id'],
                  'body' => $text,
                  'title' => $use_name . ' vừa đăng trên diễn đàn',
                  'url' => $url                     
               );
               $send = $notification->gcm($message_data);
               $send = json_decode($send);
               /*
               if($send->success == 0){
                  $db_ex   = new db_execute("DELETE FROM user_gcm WHERE usg_id = " . $rgcm['usg_id'], __FILE__, DB_NOTIFICATION);
                  unset($db_ex);
               }
               */
            }
         }
         unset($db_gcm);
      }
      unset($db_user);
   }
   
   
   /**
    * Send notification when 1 user comment forum
    * 
    * param
    * array(
    *    for_id => 
    *    for_title =>
    *    use_name => 
    * )
    */
   function send_notification_comment($row = array()){
      global $notification;
      
      $for_id     = isset($row['for_id'])? intval($row['for_id']): 0;
      $use_name   = isset($row['use_name'])? $row['use_name'] : '';
      $for_title  = isset($row['for_title'])? $row['for_title'] : '';
      $url  = isset($row['url'])? $row['url'] : '';
      
      if($for_id <= 0) return 0;
      
      $text = $use_name . ' vừa bình luận ' . $for_title . ' trên diễn đàn';
      $data_msg   = array(
         'title' => $use_name . ' vừa bình luận bài bạn theo dõi'
         ,'description' => $text
         ,'url' => $url
         ,'action' => NOTI_ACTION_ADD_PRODUCT
         ,'date' => time()
         ,'use_id' => 0
      );
      
      $arrayUser  = array();
      
      // lấy ra những ai quan tâm bài viết này
      $table_folow_forum      = $this->create_table_forum_folow(array('for_id' => $for_id));
      $db_u = new db_query("SELECT * FROM " . $table_folow_forum . " WHERE ffl_for_id = " . intval($for_id),
                           __FILE__, DB_NOTIFICATION);
      while($ruser   = $db_u->fetch()){
         $arrayUser[] = $ruser['ffl_use_id'];
      }
      unset($db_u);
      
      // lấy thông tin những người comment
      $table_comment = $this->create_table_comment(array('for_id' => $for_id));
      $db_comment    = new db_query("SELECT DISTINCT foc_use_id FROM " . $table_comment . "
                                     WHERE foc_for_id = " . intval($for_id),__FILE__, DB_NOTIFICATION);
      if($rcomment   = $db_comment->fetch()){
         $arrayUser[] = $rcomment['foc_use_id'];
      }
      unset($db_comment);
      
      if(!empty($arrayUser)){
         $list_id = implode(',', $arrayUser);
         
         $db_user = new db_query("SELECT * FROM user_gcm WHERE usg_use_id IN (". $list_id .")", __FILE__, DB_NOTIFICATION);
         while($ruser   = $db_user->fetch()){
            
            // save notification
            $data_user   = array($ruser['usg_use_id']); 
            $s = $notification->send($data_msg, $data_user);
            
            
            // send gcm
            $message_data  = array(
               'id' => $ruser['usg_register_id'],
               'body' => $text,
               'title' => $use_name . ' vừa bình luận bài bạn theo dõi',
               'url' => $url                     
            );
            $send = $notification->gcm($message_data);
            $send = json_decode($send);
            /*
            if($send->success == 0){
               $db_ex   = new db_execute("DELETE FROM user_gcm WHERE usg_id = " . $ruser['usg_id'], __FILE__, DB_NOTIFICATION);
               unset($db_ex);
            }
            */
            
         }
         unset($db_user);
         
         return 1;
         
      }else{
         return 0;
      }
      
      
   }
   
   
   /**
    * Fuction get user post most
    * (lay nhung user dang bai nang dong nhat )
    * 
    * param
    * array(
    *    type => post || answer
    * )
    * 
    * return array, top 20 user
    */
   function get_top_user($row = array()){
      $type  = isset($row['type'])? ($row['type']) : '';
      
      $array_return = array();
      
      if($type == 0) return $array_return;
      if($type != 'post' && $type != 'answer') return $array_return;
      
      $sql_order   = "";
      switch($type){
         case 'post':
            $sql_order  = " ORDER BY fuc_post_count DESC ";
            break;
         case 'answer':
            $sql_order  = " ORDER BY fuc_answer_count DESC ";
            break;
      }
      $db_top  = new db_query("SELECT * FROM forum_user_count " . $sql_order . " 
                               LIMIT 20", __FILE__, DB_NOTIFICATION);
      while($rtop = $db_top->fetch()){
         $array_return[$rtop['fuc_use_id']] = $rtop;
      }
      unset($db_top);
      
      return $array_return;
   }   
   
   
   /**
    * Functio user set count
    * tinh cau hoi va ca tra loi cua user
    * array(
    *    use_id =>
    *    type => post || answer
    * )
    * 
    */
   function set_user_count($row = array()){
      
      $use_id  = isset($row['use_id'])? intval($row['use_id']) : 0;
      $type    = isset($row['type'])? ($row['type']) : '';
      
      if($use_id <= 0) return 0;
      if($type == '' || ($type != 'post' && $type != 'answer')) return 0;
      
      $field_set  = '';
      $count_post = 0;
      $count_answer  = 0;
      switch($type){
         case 'post':
            $field_set = " fuc_post_count = fuc_post_count + 1";
            $count_post = 1;
            break;
         case 'answer':
            $field_set = " fuc_answer_count = fuc_answer_count + 1";
            $count_answer  = 1;
            break;
      }
      
      $db_ex   = new db_execute("INSERT INTO forum_user_count(fuc_use_id, fuc_post_count, fuc_answer_count) 
                                 VALUES(". intval($use_id) .",". $count_post .",". $count_answer .")
                                 ON DUPLICATE KEY UPDATE " . $field_set);
      unset($db_ex);
      
   }
   
   
   
   
   
   
   
   
}