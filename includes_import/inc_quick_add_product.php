<?
// lấy thông tin category
$arrayCategory = array();
$db_cat = new db_query("SELECT cat_id, cat_name FROM category WHERE cat_use_id = " . intval($admin_id) . " AND cat_active = 1");
while($rcat = $db_cat->fetch()){
   $arrayCategory[$rcat['cat_id']] = $rcat;
}
unset($db_cat);
?>
<div id="quick_add_product" class="hide">
   <div class="quick_content">
      <p class="quick_title">Thêm nhanh thuốc</p>
      <form id="frm_quick_add_product">
         <table border="0" cellpadding="0" cellspacing="0" class="tbl_form">
            <tr>
               <td colspan="3"><p id="quickadd_err" class="error"></p></td>
            </tr>
            <tr>
               <td class="text">Tên thuốc <b class="price">*</b></td>
               <td><input name="usp_name" id="usp_name" autofocus="true" class="frm_text" type="text" value="" placeholder="vd: Panadol extra" /></td>
               <td></td>            
            </tr>
            
            <tr>
               <td class="text">Danh mục</td>
               <td>
                  <select name="usp_cat" id="usp_cat" class="frm_select">
                     <option value="0">[ Chọn danh mục thuốc ]</option>
                     <?
                     foreach($arrayCategory as $cid => $cname){
                        ?>
                        <option value="<?=$cid?>"><?=$cname['cat_name']?></option>
                        <?
                     }
                     ?>
                  </select>
               </td>
               <td></td>         
            </tr>
            
            <tr>
               <td class="text">Đơn vị nhập <b class="price">*</b></td>
               <td>
                  <select name="usp_unit_import" id="usp_unit_import" class="frm_select" onchange="pm.change_unit()">
                     <?
                     foreach($array_unit as $uid => $uname){
                        ?>
                        <option value="<?=$uid?>"><?=$uname?></option>
                        <?
                     }
                     ?>
                  </select>               
               </td>
               <td width="5"><span class="flaticon-information help" data-key="qa_unit_import" title="Xem hướng dẫn" onclick="pm.show_help(this)"></span></td>         
            </tr>
            
            <tr>
               <td class="text">Đơn vị bán <b class="price">*</b></td>
               <td>
                  <select name="usp_unit" id="usp_unit" class="frm_select" onchange="pm.change_unit()">
                     <?
                     foreach($array_unit as $uid => $uname){
                        ?>
                        <option value="<?=$uid?>"><?=$uname?></option>
                        <?
                     }
                     ?>
                  </select>
               </td>       
               <td width="5"><span class="flaticon-information help" data-key="qa_unit_export" title="Xem hướng dẫn" onclick="pm.show_help(this)"></span></td>  
            </tr>
            <tr>
               <td></td>
               <td colspan="2"><b>Chú ý:</b> <span class="fs12">Phần đơn vị bán bạn vui lòng chọn đơn vị nhỏ nhất để thuận tiện cho việc bán hàng, VD: 1 hộp có 10 vỉ, mỗi vỉ 10 viên thì đơn vị bán bạn nên để là [Viên]</span></td>
            </tr>
            <tr>
               <td class="text">Quy cách <b class="price">*</b></td>
               <td><input name="usp_packing" id="usp_packing"  class="frm_text" type="text" value="" placeholder="vd: 10" /></td>
               <td width="5"><span class="flaticon-information help" data-key="qa_packing" title="Xem hướng dẫn" onclick="pm.show_help(this)"></span></td>
            </tr>
            <tr>
               <td class="text">Mã vạch</td>
               <td><input name="usp_barcode" id="usp_barcode"  class="frm_text" type="text" value="" placeholder="Mã vạch của sản phẩm" /></td>
               <td width="5"></td>
            </tr>
            <tr>
               <td></td>
               <td colspan="2">
                  <input type="submit" class="btn btn_do" value="Thêm thuốc" onclick="return pm.quick_add_product()" />
                  <input type="button" class="btn btn_cancel" value="Bỏ qua" onclick="pm.hide_quick_add_product()" />
                  <input type="hidden" value="0" name="temp_id" id="temp_id" />
                  <input type="hidden" value="<?=(isset($module)? $module : '')?>" name="module" id="module" />
               </td>
            </tr>
         </table>
      </form>
   </div>
</div>