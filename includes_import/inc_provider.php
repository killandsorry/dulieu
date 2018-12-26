<div id="content">
   <div class="fullh ovh" id="listcontent">
      <div class="responsive frm_filter pdtb10" id="frm_filter">
         <form method="get" action="">
            <div class="col w40 fl pdlr5">
               <div class="w60 fl pdlr5">
                  <input type="text" value="<?=((isset($provider_name) && $provider_name != '')? $provider_name : '')?>" class="text_filter w100" placeholder="Tên nhà cung cấp" name="p_name" />
               </div>
               <div class="fl w40 pdlr5">
                  <p class="w100 fl">
                     <input type="text" id="data_phone"  value="<?=((isset($provider_phone) && $provider_phone != '')? $provider_phone : '')?>" name="p_phone" placeholder="Số điện thoại" class="text_filter w100 fl" title="" />
                  </p>
               </div>
            </div>
            <div class="col w30 fl pdlr5">               
               <div class="fl w40 pdlr5">
                  <input type="submit" value="Lọc" class="w100 btn_filter" />
               </div>
               <div class="fl w60 pdlr5">
                  <input type="button" onclick="pm.show_quick_add_provider()" value="Thêm nhà cung cấp" class="w100 btn_filter" />
               </div>
            </div>
         </form>
      </div>
      <div class="ova listing">
         <table class="tbl" cellpadding="0" cellspacing="0" >
            <tr class="tbl_head">
               <td width="30" class="t_c">Stt</td>
               <td width="80" class="t_c">Lịch sử nhập</td>
               <td width="" class="">Tên nhà cung cấp</td>
               <td width="120" class="t_c">Điện thoại</td>
               <td width="120" class="">Địa chỉ</td>
               <td width="120" class="">Người liên hệ</td>
               <td width="90" class="t_c">Hành động</td>
            </tr>
            <?
            // data get list bill
            $dataGet   = array(
               'name'      => $provider_name,
               'phone'     => $provider_phone,
               'page'      => $current_page
            );
            // get list bill
            $data_item  = $killProvider->getList($dataGet);
            
            /**
             * Thống kê
             */
            $total_item    = 0; // tổng số bản ghi  
            $total_page    = 0; // tổng số page      
                
            if($data_item['code'] == 200){
                              
               // tổng số bản ghi               
               if(isset($data_item['count'])) $total_item = intval($data_item['count']);
               
               $total_page = ceil($total_item / PAGE_SIZE);
               
               // lặp để in ra dữ liệu
               $i = (($current_page - 1)*PAGE_SIZE) + 1;
               foreach($data_item['data'] as $d_id => $data_info){
                  // thông tin khi click vào nút sửa
                  $dataEdit   = array(
                     'id' => $data_info['usp_id'],
                     'name' => $data_info['usp_name'],
                     'phone' => $data_info['usp_phone'],
                     'address' => $data_info['usp_address'],
                     'contact' => $data_info['usp_contact']
                  );
                  
                  // thông tin khi click vào nút lịch sử
                  $dataHistory   = array(
                     'url' => '/soft/import/provider-history',
                     'option' => 'id=' . $data_info['usp_id'] . '&name=' . $data_info['usp_name'],
                     'w' => '80%',
                     'h' => '80%',
                     't' => '5%'                     
                  );
                  ?>
                  <tr id="item_<?=$d_id?>">
                     <td width="30" class="t_c"><?=$i?></td>
                     <td width="80" class="t_c"><a href="javascript:void(0)" onclick='dialog.show(<?=json_encode($dataHistory)?>)'>Lịch sử</a></td>
                     <td width="120" class=""><?=$data_info['usp_name']?></td>
                     <td width="120" class="t_c"><?=$data_info['usp_phone']?></td>
                     <td width="120" class=""><?=$data_info['usp_address']?></td>
                     <td width="120" class=""><?=$data_info['usp_contact']?></td>
                     <td width="90" class="t_c">
                        <span class="btn_small btn_save" onclick='pm.provider_edit_show(<?=json_encode($dataEdit)?>)'>Sửa</span>
                        <span class="btn_small btn_del" onclick="pm.delete_provider(<?=$d_id?>)">Xóa</span>
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
         </div>
      </div>
   </div>
</div>