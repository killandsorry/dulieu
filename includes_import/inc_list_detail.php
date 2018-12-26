<?
// data get list bill
$dataBill   = array(
   'bill_type' => STOCK_CARD_TYPE_IN,
   'bill_code'   => $bill_code,
);
// get list bill
$data  = $killBill->getDetailBill($dataBill);
$isSuccess  = $data['code'];
/**
 * Thống kê
 */
$total_money   = 0; // tổng tiền
$total_paid    = 0; // đã trả
$total_remain  = 0; // còn lại
$total_item    = 0; // tổng số bản ghi 

if($data['code'] == 200){
   $total_money   = isset($data['data']['bill']['usb_total_money'])? $data['data']['bill']['usb_total_money'] : 0; // tổng tiền
   $total_paid    = isset($data['data']['bill']['usb_paid'])? $data['data']['bill']['usb_paid'] : 0; // đã trả
   $total_remain  = isset($data['data']['bill']['usb_pay_remain'])? $data['data']['bill']['usb_pay_remain'] : 0; // còn lại
}else{
   die('Thông tin này không có');
}
?>
<div id="content">
   <div class="fullh ovh" id="listcontent">
      <div class="responsive frm_filter pdtb10" id="frm_filter">
         
         <div class="col w100 fl pdtb5">
            <p class="w35 fl pdlr10 col">Hóa đơn: <b><?=$bill_code?></b></p>
            <p class="w35 fl col pdlr10">Ngày: <b><?=date('d/m/Y - H:i:s', $data['data']['bill']['usb_date'])?></b></p>            
         </div>
         <div class="col w100 fl pdtb5">
            <div class="fl w35 pdlr10 col">
               Tổng tiền: <b class="price price_lager"><?=format_currency($total_money)?></b>
            </div>
            <div class="fl w35 pdlr10 col">
               Đã trả: <b class="price price_lager"><?=format_currency($total_paid)?></b>
            </div>
            <div class="fl w30 pdlr10 col">
               Còn nợ: <b class="price price_lager"><?=format_currency($total_remain)?></b>
            </div>
         </div>
         <div class="col w100 fl pdtb5 pdlr10">
            
            <a href="#" class="btn btn_cancel fl mgr5">In mã vạch</a>
            <a href="#" class="btn btn_cancel fl mgr5">In hóa đơn</a>
            <a href="#" onclick="pm.delete_bill({bill_code : <?=$bill_code?>, bill_type : <?=$bill_type?>})" class="btn btn_del fl mgr5">Xóa</a>
            <a href="javascript:void(0)" onclick="pm.reload()" class="btn btn_do fl mgr5" title="Cập nhật lại dữ liệu đã sửa">Tải lại</a>
            
            
         </div>
         <div class="col w100 pd10 fl note">
            <b>Chú ý:</b> Đối với trường hợp bạn muốn sửa '<b>số lượng</b>' hoặc '<b>giá nhập</b>' thì bạn Click vào nút '<b>Sửa</b>' trên từng dòng bạn muốn sửa, Tiếp theo bạn nhập số lượng hoặc giá nhập muốn sửa và click vào nút '<b>Lưu</b>' để cập nhật hoặc nút '<b>Hủy</b>' để bỏ qua. 
         </div>
      </div>
      
      
      <div class="ova listing">
         <table class="tbl" cellpadding="0" cellspacing="0" >
            <tr class="tbl_head">
               <td width="30" class="t_c">Stt</td>
               <td width="" class="">Tên thuốc</td>
               <td width="180" class="t_c">Số lượng</td>
               <td width="50" class="t_c">Đơn vị</td>
               <td width="200" class="t_c">Đơn giá / Đơn vị</td>
               <td width="100" class="t_r">Tổng tiền</td>
               <td width="80" class="t_c">Trả hàng</td>
            </tr>
            <?
             
                
            if($data['code'] == 200){
               // lặp để in ra dữ liệu
               $i = 1;
               
               foreach($data['data']['stock'] as $s_id => $stock){      
                  
                  $unit_name  = isset($array_unit[$stock['usp_unit_import']])? $array_unit[$stock['usp_unit_import']] : 'đơn vị';
                  ?>
                  <tr>
                     <td width="30" class="t_c"><?=$i?></td>
                     <td width="" class=""><?=$stock['usp_pro_name']?></td>
                     <td width="180" class="t_c quick_edit" data-id="<?=$s_id?>">
                        <p class="qview" onclick="action_small.show_edit(this)">
                           <input type="text" value="<?=format_currency($stock['usc_number_in'])?>" class="px40 t_c" disabled="true" readonly="true" />
                           &nbsp;<span class="btn_small btn_do">Sửa</span>
                        </p>
                        <p class="qedit hide t_r">
                           <?=format_currency($stock['usc_number_in'])?> ->
                           <input type="tel" name="unit_parent[<?=$s_id?>]" onkeyup="pm.trigger_save(event, this)" class="px40 t_c input_add type_number" id="unit_parent_<?=$s_id?>" value="" />
                           <span class="text_button trigger_save" onclick="pm.save_quick_edit_in(this)" data-field="number_in" data-old="<?=$stock['usc_number_in']?>">Lưu</span>
                           <span class="btn_small btn_cancel" onclick="action_small.close_edit(this)">Hủy</span>
                        </p>
                     </td>
                     <td width="50" class="t_c"><?=$unit_name?></td>
                     <td width="200" class="t_c quick_edit" data-id="<?=$s_id?>">
                        <p class="qview" onclick="action_small.show_edit(this)">
                           <input type="text" value="<?=format_currency($stock['usc_price'])?>" class="px60 t_c" disabled="true" readonly="true" />
                           &nbsp;<span class="btn_small btn_do">Sửa</span>
                        </p>
                        <p class="qedit hide t_r">
                           <?=format_currency($stock['usc_price'])?> ->
                           <input type="tel" name="price[<?=$s_id?>]" onkeyup="pm.trigger_save(event, this)" class="px60 t_c input_add type_number" id="price_<?=$s_id?>" value="" />
                           <span class="text_button trigger_save" onclick="pm.save_quick_edit_in(this)" data-field="price_in" data-old="<?=$stock['usc_price']?>">Lưu</span>
                           <span class="btn_small btn_cancel" onclick="action_small.close_edit(this)">Hủy</span>
                        </p>
                     </td>
                     <td width="100" class="t_r"><b class="price"><?=format_currency($stock['usc_total_money'])?></b></td>
                     <td width="80" class="t_c">
                        <a target="_blank" href="/soft/import/add?bill_code=<?=$bill_code?>&stc_id=<?=$s_id?>" class="text_button">Trả hàng</a>
                     </td>
                  </tr>
                  <?
                  $i++;
               }
            }
            ?>
         
         </table>
         
      </div>
      <div id="frm_total" class="frm_total">
         <div class="responsive">
            
            <div class="col w50 fl">
               
            </div>
            <div class="col w50 fr t_r">
               Tổng:<b class="price fs14"><?=format_currency($total_money)?></b>
               - Đã trả:<b class="price fs14"><?=format_currency($total_paid)?></b>
               - Còn nợ:<b class="price fs14"><?=format_currency($total_remain)?></b>
            </div>
         </div>
      </div>
   </div>
</div>