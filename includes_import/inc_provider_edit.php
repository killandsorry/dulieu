<div id="quick_edit" class="hide">
   <div class="quick_content">
      <p class="quick_title">Sửa thông tin nhà cung cấp</p>
      <form id="frm_quick_add_provider">
         <table border="0" cellpadding="0" cellspacing="0" class="tbl_form">
            <tr>
               <td colspan="3"><p id="quickadd_err" class="error"></p></td>
            </tr>
            <tr>
               <td class="text">Tên NCC <b class="price">*</b></td>
               <td><input name="prd_name" id="prd_name" autofocus="true" class="frm_text" type="text" value="" placeholder="vd: Dược hậu giang .." /></td>
               <td></td>            
            </tr>
            
            <tr>
               <td class="text">Số ĐT</td>
               <td><input name="prd_phone" id="prd_phone" autofocus="true" class="frm_text" type="text" value="" placeholder="vd: 0989xxxxxx" /></td>
               <td></td>      
            </tr>
            
            <tr>
               <td class="text">Địa chỉ</td>
               <td><input name="prd_address" id="prd_address" autofocus="true" class="frm_text" type="text" value="" placeholder="vd: Chợ thuốc Hapu" /></td>
               <td></td>      
            </tr>
            
            <tr>
               <td class="text">Người liên hệ</td>
               <td><input name="prd_contact" id="prd_contact" autofocus="true" class="frm_text" type="text" value="" placeholder="vd: Chị Minh Anh" /></td>
               <td></td>      
            </tr>
            
            <tr>
               <td></td>
               <td colspan="2">
                  <input type="submit" class="btn btn_do" value="Lưu lại" onclick="return pm.quick_add_provider()" />
                  <input type="button" class="btn btn_cancel" value="Bỏ qua" onclick="pm.hide_quick_add_provider()" />
                  <input type="hidden" value="0" name="prd_id" id="prd_id" />
               </td>
            </tr>
         </table>
      </form>
   </div>
</div>