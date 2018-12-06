<?
$id      = getValue('id', 'int', 'GET', 0);
$action  = getValue('myaction', 'str', 'POST', '');
$errMsg  = '';
if($action == 'myaction'){
   $box_name  = getValue('boxname', 'arr', 'POST', array()); // tên tủ thuốc
   
   if(!empty($box_name)){
      foreach($box_name as $k => $name){
         $name = trim($name);
         if($name != ''){            
            if($id <= 0){               
               $db_ex   = new db_execute("INSERT IGNORE INTO category (cat_name,cat_use_id,cat_active)
                                          VALUES('". replaceMQ($name) ."',". $admin_id .",1)");
               unset($db_ex);
            }else{
               $db_ex   = new db_execute("UPDATE category SET cat_name = '". replaceMQ($name) ."'
                                          WHERE cat_id = ". intval($id));
               unset($db_ex);
            }
         }else{
            $errMsg .= 'Dòng ['. $k .'] Không được để trống dữ liệu' . "\n";
         }
      }
      
      if($errMsg == '') redirect($current_url);
      
   }else{
      $errMsg .= 'Các dòng dữ liệu không được để trống';
   }
}
?>
<h1 class="par_title"><?=$title?></h1>
<div class="prow">
   <form method="post" action="">
      <p class="text_err"><?=nl2br($errMsg)?></p>
      <?if($id == 0){?>
         <p class="sub_title">Thêm mới thông tin:</p>
         <ul class="add_item" id="box_add">
            <li>
               <input class="txt w100" type="text" value="" placeholder="Nhập tên danh mục thuốc" name="boxname[1]" />
            </li>
         </ul>
         <p>
            <input class="add_more" type="button" value="+ Thêm danh mục khác" onclick="add_more_box()"/>
         </p>
         
      <?}else{?>
         
      <?}?>
      <p>
         <input class="btn btn_save" type="submit" value="Lưu lại" />
         <input type="hidden" value="myaction" name="myaction" />
      </p>
   </form>
</div>

<div class="prow">
   <p class="sub_title">Danh sách tủ thuốc</p>
   <table class="tbl">
      <tr class="head">
         <td><b>Tên danh mục</b></td>
         <td></td>
         
      </tr>
      <?
      $db_box  = new db_query("SELECT * FROM category WHERE cat_use_id = " . $admin_id);
      while($rbox = $db_box->fetch()){
         ?>
            <tr>
               <td><?=$rbox['cat_name']?></td>
               <td>
                  <a class="ac_small ac_edit" href="<?=$requestUri . "?id=" . $rbox['cat_id']?>">[Sửa]</a>                  
                  <a class="ac_small ac_del" href="javascript:void(0);" onclick="delbox(<?=$rbox['cat_id']?>)">[Xóa]</a>
                  
               </td>
            </tr>
         <?
      }
      unset($db_box);
      ?>
   </table>
</div>

<style>
.add_item{
   
}
.add_item li{
   margin-bottom: 10px;
}
</style>

<script>
   var _box_id = 1;
      
   function add_more_box(){
      _box_id += 1;
      var box = '';
      box += '<input class="txt w100" type="text" value="" placeholder="Nhập tên danh mục thuốc" name="boxname['+_box_id+']" />';
      //branch += '<input type="hidden" name="add['+_box_id+']" id="add_'+_box_id+'" value="" />';
      $('#box_add').append('<li>'+box+'</li>');
   }
      
   function delbox(id){
      if(id > 0){
         pm.myConfirm('Bạn có muốn xóa bản ghi này không?', function(){
            alert('Xóa id ' + id);
            pm.closeOverlay();
         }, function(){
            alert('không id ' + id);
            pm.closeOverlay();
         } );
      }else{
         pm.myAlert('Thông tin bản ghi không có');
      }
   }
</script>