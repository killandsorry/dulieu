<?
$errMsg     = '';

// bắt đầu submit dữ liệu
$action  = getValue('action', 'str', 'POST', '');
if($action == 'add'){
   $dataPost   = isset($_POST)? $_POST : array();
   
   if(empty($dataPost)){
      $errMsg  = 'Không có thông tin nhập hàng';
   }else{
      
      $result  = $killStockCard->add_stock_card($dataPost);
      
      if($result['code'] == 500){
         $errMsg  = $result['error'];
      }else{
         $errMsg  = 'Thành công';
      }
      
      
   }
}
?>
<div id="content">
   <div class="responsive fullh ovh">
      <form method="post" action="" class="fullh ovh">
         <p class="error"><?=$errMsg?></p>
         <div class="col w75 fl fullh pd5 form_cal">
            <div class="find_pro">
               <span class="flaticon-barcode icon_barcode"></span>
               <div class="typeahead__container">
                 <div class="typeahead__field">
                     <div class="typeahead__query">
                         <input id="barcode" type="search" autocomplete="off" spellcheck="false" value="" onkeyup="" placeholder="Nhấn (F4) để tít mã vạch hoặc tìm sản phẩm nhập kho" />
                     </div>
                 </div>
               </div>
               
               
            </div>
            <div id="import">
               <table class="tbl" id="dasboad_add">
                  <tr class="tbl_head">
                     <td width="5"></td>
                     <td>Tên</td>
                     <td width="80" colspan="5" class="t_c">Sl &#215; Quy cách = Tổng</td>     
                     <td width="60" class="t_r">Đơn giá<br /><i class="fs">(vnđ)</i></td>  
                     <?/*<td width="60" class="t_r">KM<br /><i class="fs">(vnđ)</i></td>*/?> 
                     <td width="70" class="t_r">Thành tiền<br /><i class="fs">(vnđ)</i></td>                            
                     <td width="50" class="t_r">Giá vốn<br /><i class="fs">(vnđ)</i></td>
                     <td width="60" class="t_r">Giá bán<br /><i class="fs">(vnđ)</i></td>                  
                     <td width="60" class="t_c">Số lô</td>
                     <td width="80" class="t_c">Hạn dùng<br /><i class="fs">(ngày/tháng/năm)</i></td>
                  </tr>
                  <tbody id="bodyappend">
                     <?
                        // lấy thông tin các phiếu đang có sẵn
                        $dataTemplate     = $killWareHouseTemplate->get_insert_template();
                        $total_money_temp = 0;
                        $bill_code        = date('ymdHi');
                                                
                        if(!empty($dataTemplate)){
                           foreach($dataTemplate as $temp){
                              $productInfo         = $killProduct->get_product_by_id($temp['usw_usp_id']);
                              $total_money_temp    += $temp['usw_total_money_import'];
                              $price_import_small  = floor($temp['usw_price_import'] / $temp['usw_number_unit_child']);
                              // lấy template warehouse
                              $fileTemplate  = '../../template/example_warehouse.html';
                              $template      = @file_get_contents($fileTemplate);
                              //echo $template;
                              
                              // dữ liệu replate vào temp
                              $dataReplate   = array(
                                 'id'                    => $temp['usw_id']
                                 ,'usp_pro_name'         => $productInfo['usp_pro_name']
                                 ,'unit_import'          => $productInfo['unit_import_name']
                                 ,'unit'                 => $productInfo['unit_name']
                                 ,'usp_packing'          => $productInfo['usp_packing']
                                 ,'unit_parent'          => $temp['usw_number_unit_parent']
                                 ,'total_count'          => format_currency($temp['usw_number'])
                                 ,'price_import'         => format_currency($temp['usw_price_import'])
                                 ,'price_export'         => format_currency($temp['usw_price_export'])
                                 ,'total_import_money'   => format_currency($temp['usw_total_money_import'])
                                 ,'price_import_small'   => format_currency($price_import_small)
                                 ,'unit_note'            => '1 ' . $array_unit[$productInfo['usp_unit_import']] . ' ' . $productInfo['usp_packing'] . ' ' . $array_unit[$productInfo['usp_unit']]
                                 ,'lo'                   => $temp['usw_lo']
                                 ,'date_expires'         => ($temp['usw_date_expires'] > 0)? date('d/m/Y', $temp['usw_date_expires']) : ''
                                 /*,'discount_money'       =>
                                 ,'discount_value'       =>
                                 ,'discount_type'        =>
                                 ,'discount_class_p'     =>
                                 ,'discount_class_v'     =>*/
                              );
                              
                              foreach($dataReplate as $key => $value){
                                 $template = str_replace('{{'. $key .'}}', $value, $template);
                              }
                              
                              echo $template;
                              
                           }
                        }                                             
                     ?>
                  </tbody>
               </table>
            </div>
            <div class="form_total_cal">
               <b class="price" id="total_count_pro"><?=count($dataTemplate)?></b> Sản phẩm  &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; 
               Tổng tiền: <b class="price" id="total_count_money"><?=format_currency($total_money_temp)?></b>
            </div>
         </div>
         
         <div class="col w25 fl fullh form_act pd10">
            <ul class="form_li">            
               <li>
                  <input type="text" class="no_border tran fs11 w50  pdtb5" value="Số hóa đơn:" />               
                  <input type="text" value="<?=$bill_code?>" class="no_border w50 fl pdtb5 t_c fb fr bgy" readonly="true" placeholder="HDN001" name="bill_code" id="bill_code" />
                  </p>
               </li>
               <li>
                  Ngày hóa đơn:
                  <input type="text" class="date_add fr w50 bgy" value="<?=date('d/m/Y')?>" data-date-format="DD-MM-YYYY" placeholder="ví dụ: <?=date('d/m/Y')?>" />
               </li>
               <li>
                  <p>
                     <div class="typeahead__container">
                        <div class="typeahead__field">
                           <div class="typeahead__query">
                              <input type="search" autocomplete="off" spellcheck="false" value="" class="w90 fl pd5" placeholder="Tên nhà cung cấp" name="provider_name" id="provider_name" />
                              <span class="w10 t_c inline_b quick_add" onclick="pm.show_quick_add_provider()" title="Thêm nhanh nhà cung cấp">&#43;</span>
                              <input type="hidden" value="0" id="provider_id" name="provider_id" />
                           </div>
                        </div>
                     </div>
                  </p>
               </li>
            </ul>
            <table class="w100 tbl_right" border="0">
               
               <?/*
               <tr>
                  <td class="w40">Tổng tiền:</td>
                  <td class="t_r">
                     <input type="text" readonly="true" value="<?=format_currency($total_money_temp)?>" id="total_amount" name="total_amount" class="price_lager no_border t_r" />
                  </td>
               </tr>
               
               <tr>
                  <td class="w40">Khuyến mại / Cả đơn:</td>
                  <td class="t_r">                  
                     <span class="icon_discount dis_active">%</span>
                     <span class="icon_discount">vnđ</span>
                     <input type="tel" value="" class="w50 pd3 t_r type_number fs13" id="discount" name="discount" />
                  </td>
               </tr>
               */?>
               <tr>
                  <td class="w40">Tiền phải trả:</td>
                  <td class="t_r">
                     <input type="text" readonly="true" value="<?=format_currency($total_money_temp)?>" id="total_pay" name="total_pay" class="price_lager price fs22 no_border t_r w80" />
                  </td>
               </tr>
               <tr>
                  <td class="w40">Đã trả:</td>
                  <td class="t_r">
                     <input type="text" onkeyup="calc.paid_warehouse()" value="<?=format_currency($total_money_temp)?>" id="total_payed" name="total_payed" class="price_lager t_r pd3 w80 type_number" />
                  </td>
               </tr>
               <tr>
                  <td class="w40">Còn nợ:</td>
                  <td class="t_r">
                     <input type="text" readonly="true" value="0" id="total_remain" name="total_remain" class="price_lager no_border t_r type_number" />
                  </td>
               </tr>
               <tr>
                  <td class="w40">Hình thức trả:</td>
                  <td class="t_r">
                     <select class="w80 pd3 fs15 t_r" name="method_pay">
                        <?
                        foreach($arrayPayMethod as $mid => $method){
                           ?>
                              <option value="<?=$mid?>"><?=$method?></option>
                           <?
                        }
                        ?>
                     </select>
                  </td>
               </tr>
            </table>
            <p>
               <input type="submit" value="(F9) Hoàn thành" class="btn btn_save pd10" />
               <input type="hidden" value="add" name="action" />
            </p>
         </div>
      </form>
   </div>
</div>
<script>
   $(document).ready(function() {
      
      /* search product */
   	$.typeahead({
         input: '#barcode',
         order: 'asc',
         maxItem: 10, 
         minLength: 1,
         cache: false,
         accent: true,
         display: ['name', 'unit', 'remain', 'price', 'id'], // Search objects by the title-key
         dynamic: true,
         //href: "{{url}}",
         emptyTemplate: 'Không có kết quả cho <strong>{{query}}</strong> &#8594; <input type="button" value="Thêm mới thuốc này" class="btn btn_do" onclick="pm.show_quick_add_product({name:\'{{query}}\'})" />',
         searchOnFocus: true,
         cancelButton: false,
         debug: true,
         group: true,
         group: {
            key: "type",
            template: "<p class='fs13 fw'>{{type}}</p>"
         },
         template : function(){
            var template = ''+
            '<div>'+
               '<p class="fs13">{{name}}</p>'+
               '<p><span class="fs cl6">{{unit}}</span>' +
               '<span class="fs">{{remain_name}}</span></p>'+
            '</div>';           
            return template;            
         },   
         source: {
            ajax: {
               method: "GET",
               url: '/ajaxs/ajax_search_product.php',
               data: {
                  q: '{{query}}',
                  module : _module
               },
               complete : function(response){
                  console.log(response);
               }
            }
         },
         callback: {
            onHideLayout: function (node, query) {
            $('#searchform').hide();
               console.log('hide search');
            },
            
            onMouseEnter : function(node, a, item, event){
               pm.insert_temp_wearhouse(item);
            },
            
            onClick : function(node, a, item, event){
               pm.insert_temp_wearhouse(item);
            }

         }
      });  
      
      /* search provider */
      $.typeahead({
         input: '#provider_name',
         order: 'asc',
         maxItem: 10, 
         minLength: 1,
         cache: false,
         accent: true,
         display: ['name', 'phond', 'id'], // Search objects by the title-key
         dynamic: true,
         //href: "{{url}}",
         emptyTemplate: 'Không có kết quả cho <strong>{{query}}</strong> &#8594; <input type="button" value="Thêm mới nhà cung cấp" class="btn btn_do" onclick="pm.show_quick_add_provider({name:\'{{query}}\'})" />',
         searchOnFocus: true,
         cancelButton: false,
         debug: true,         
         template : function(){
            var template = ''+
            '<div>'+
               '<p class="fs13">{{name}} - {{phone}}</p>'+
            '</div>';           
            return template;            
         },   
         source: {
            ajax: {
               method: "GET",
               url: '/ajaxs/ajax_provider_search.php',
               data: {
                  q: '{{query}}'
               },
               complete : function(response){
                  console.log(response);
               }
            }
         },
         callback: {
            onHideLayout: function (node, query) {
            $('#searchform').hide();
               console.log('hide search');
            },
            
            onMouseEnter : function(node, a, item, event){
               pm.select_provider(item);
            },
            
            onClick : function(node, a, item, event){
               pm.select_provider(item);
            }

         }
      });     
      
   });
</script>