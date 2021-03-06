<div id="content">
   <div class="fullh ovh" id="listcontent">
      <div class="responsive frm_filter pdtb10" id="frm_filter">
         <form method="get" action="">
            <div class="col w15 fl pdlr10">
               <input type="text" value="<?=((isset($bill_code) && $bill_code > 0)? $bill_code : '')?>" class="text_filter w100" placeholder="Số hóa đơn" name="bill_code" />
            </div>
            <div class="col w45 fl pdlr5">
               <div class="fl w35 pdlr5">
                  <p class="css_date w100 fl">
                     <i class="css_date_text">Từ:</i>
                     <input type="text" id="date_from"  value="<?=(isset($start_date)? date('d/m/Y', $start_date) : '')?>" name="start_date"  class="filldate w100 fl" title="Nhập theo định dạng ngày/tháng/năm" />
                  </p>
               </div>
               <div class="fl w35 pdlr5">
                  <p class="css_date w100 fl">
                     <i class="css_date_text">Đến:</i>
                     <input type="text" id="date_to"  value="<?=(isset($end_date)? date('d/m/Y', $end_date) : '')?>" name="end_date"  class="filldate w100 fl" title="Nhập theo định dạng ngày/tháng/năm" />
                  </p>
               </div>
               <div class="fr w30 pdlr5">
                  <input type="submit" value="Lọc" class="w100 btn_filter" />
               </div>
            </div>
         </form>
      </div>
      <div class="ova listing">
         <table class="tbl" cellpadding="0" cellspacing="0" >
            <tr class="tbl_head">
               <td width="30" class="t_c">Stt</td>
               <td width="120" class="t_c">Số hóa đơn</td>
               <td width="120" class="t_c">Ngày</td>
               <td width="120" class="t_r">Tổng tiền</td>
               <td width="120" class="t_r">Đã trả</td>
               <td width="120" class="t_r">Còn lại</td>
               <td>NCC</td>
               <td width="80" class="t_c">Hành động</td>
            </tr>
            <?
            // data get list bill
            $dataBill   = array(
               'bill_type' => STOCK_CARD_TYPE_IN,
               'bill_id'   => $bill_code,
               'start_date'   => $start_date,
               'end_date'  => $end_date,
               'page'      => $current_page
            );
            // get list bill
            $bill_item  = $killBill->getListBill($dataBill);
            
            /**
             * Thống kê
             */
            $total_money   = 0; // tổng tiền
            $total_paid    = 0; // đã trả
            $total_remain  = 0; // còn lại
            $total_item    = 0; // tổng số bản ghi  
            $total_page    = 0; // tổng số page      
                
            if($bill_item['code'] == 200){
               
               // Tổng tiền
               if(isset($bill_item['total']) && !empty($bill_item['total'])){
                  $total_money   += $bill_item['total']['ttm_import'];
                  $total_paid    += $bill_item['total']['ttm_paid'];
                  $total_remain  += $bill_item['total']['ttm_remain'];
               }
               
               // tổng số bản ghi               
               if(isset($bill_item['count'])) $total_item = intval($bill_item['count']);
               
               $total_page = ceil($total_item / PAGE_SIZE);
               
               // lặp để in ra dữ liệu
               $i = (($current_page - 1)*PAGE_SIZE) + 1;
               foreach($bill_item['data'] as $b_id => $bill_info){
                  $provider_id   = isset($bill_info['usb_provider_id'])? intval($bill_info['usb_provider_id']) : 0;
                  $provider      = array();
                  if($provider_id > 0){
                     $response_provider   = $killProvider->getById($provider_id);
                     if(isset($response_provider['code']) && $response_provider['code'] == 200){
                        $provider   = $response_provider['data'];
                     }
                  }
                  
                  $dataListDetail   = array(
                     'id'  => $b_id,
                     'url' => '/soft/import/list-detail',
                     'option' => 'bill_code=' . $bill_info['usb_bill_number'],
                     'w'   => '95%',
                     'h'   => '80%',
                     't' => '5%'  
                  );
                  
                  ?>
                  <tr id="item_<?=$b_id?>">
                     <td width="30" class="t_c"><?=$i?></td>
                     <td width="120" class="t_c"  onclick='dialog.show(<?=json_encode($dataListDetail)?>)'><a href="javascript:void(0)"><?=$bill_info['usb_bill_number']?></a></td>
                     <td width="120" class="t_c"><?=date('d/m/Y', $bill_info['usb_date'])?></td>
                     <td width="120" class="t_r"><b class="price"><?=format_currency($bill_info['usb_total_money'])?></b></td>
                     <td width="120" class="t_r"><b class="price"><?=format_currency($bill_info['usb_paid'])?></b></td>
                     <td width="120" class="t_r"><b class="price"><?=format_currency($bill_info['usb_pay_remain'])?></b></td>
                     <td><?=(!empty($provider)? $provider['usp_name'] : '')?></td>
                     <td width="80" class="t_c"  onclick='dialog.show(<?=json_encode($dataListDetail)?>)'>
                        <span class="btn_small btn_save">Sửa | Xóa</span>
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
               <?
               if ($total_page > 1) {
                  echo '<div class="pagelist">';
                  echo generatePageBar($page_prefix, $current_page, PAGE_SIZE, $total_item, $pageurl, $normal_class, $selected_class, 'Trước', 'Tiếp', 'Đầu', 'Cuối', 1, '', 1, 0);
                  echo '</div>';
               }
               ?>
            </div>
            <div class="col w50 fr t_r">
               Tổng: <b class="price fs14"><?=format_currency($total_money)?></b>
               - Đã trả: <b class="price fs14"><?=format_currency($total_paid)?></b>
               - Còn nợ: <b class="price fs14"><?=format_currency($total_remain)?></b>
            </div>
         </div>
      </div>
   </div>
</div>