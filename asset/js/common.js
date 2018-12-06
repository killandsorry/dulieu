var _setchange = '';
/*readly*/
$(function(){
   
   /* select all text in input */
   $('input[type=text],input[type=number],input[type=tel]').live('focus', 'input', function(e){        
      var save_this = $(this);
      if(save_this.val == '') return false;
      save_this.select();
   });
   
   /* change currency input */
   $('input.type_number').live('keyup', function(event){
      var vl = $(this).val();
       if(parseInt(vl) <= 0 || isNaN(parse_type_number(vl)) == true){
           vl = '0';
       }
       var m = parse_type_number(vl);
       
      $(this).val(formatNumber(m));
   });
   
   /* phần khuyến mại */
   $('.show_discount').click(function(event){
      event.stopPropagation();
   });
});


function formatNumber(nStr){nStr+='';var x=nStr.split(',');var x1=x[0];var x2='';var x2=x.length>1?'.'+x[1]:'';var rgx=/(\d+)(\d{3})/;while(rgx.test(x1)){x1=x1.replace(rgx,'$1'+',' + '$2');}return x1+x2;}
function parse_type_number(vl){
   var m = 0;
   if(vl == '') return m;
   vl = vl.toString().split(',');
   if(vl.length > 1){
      m = vl.join('');
      m *= 1;
   }else{
      m = vl * 1;
   }
   return m;
}


var pm = {
   myConfirm : function(msg, fnyes, fnno){
      var confirmBox = $("#confirm");
      confirmBox.find(".message").text(msg);
      confirmBox.find(".yes,.no").unbind().click(function() {
         confirmBox.hide();
      });
      pm.openOverlay();
      confirmBox.find(".yes").click(fnyes);
      confirmBox.find(".no").click(fnno);
      confirmBox.show();
   },
   
   myAlert : function(msg){
      pm.openOverlay();
      $('#alert').removeClass('hide');
   },
   
   closeAlert : function(){
      pm.close_overlay();
      $('#alert').addClass('hide');
   },
   
   openOverlay : function(){
      $('#ovl').removeClass('hide');
   },
   
   closeOverlay : function(){
      $('#ovl').addClass('hide');
   },
   
   /*show help*/
   show_help : function(key){
      alert($(key).data('key'));
   },
   
   /* close helo */
   close_help : function(){
      
   },
   
   show_quick_add_product : function(data){
      var _temid = data.temp || 0;
      var _name = data.name || '';
      
      
      $('#quick_add_product').removeClass('hide');
      this.openOverlay();
      if(_temid > 0){
         $('#temp_id').val(_temid);
      }
      $('#usp_name').val(_name).focus();
      pm.clear_search();
   },
   
   hide_quick_add_product : function(){
      this.closeOverlay();
      $('#quick_add_product').addClass('hide');
   },
   
   /* quick add product */
   quick_add_product : function(){
      var data = $('#frm_quick_add_product').serialize();
      $.post(
         '/ajaxs/ajax_product_quick_add.php',
         data,
         function(res){
            pm.add_template_to_dasboad(res.html);
         },
         'json'
      );
      
      return false;
   },
   
   show_quick_add_provider : function(data){
      var _name = data.name || '';
      $('#quick_add_provider').removeClass('hide');
      this.openOverlay();      
      $('#prd_name').val(_name).focus();
   },
   
   /* hide quick add provider */
   hide_quick_add_provider : function(){
      this.closeOverlay();
      $('#quick_add_provider').addClass('hide');
   },
   
   /* quick add product */
   quick_add_provider : function(){
      var data = $('#frm_quick_add_provider').serialize();
      $.post(
         '/ajaxs/ajax_provider_quick_add.php',
         data,
         function(res){
            if($('#provider_name').length > 0){
               $('#provider_name').val(res.data.name);
               $('#provider_id').val(res.data.id);
            }
            pm.hide_quick_add_provider();
         },
         'json'
      );
      
      return false;
   },
   
   select_provider : function(data){
      var _id = data.id || 0;
      var _name = data.name || 0;
      
      if(_id > 0 && _name != ''){
         if($('#provider_name').length > 0){
            $('#provider_name').val(_name);
            $('#provider_id').val(_id);
         }
         pm.hide_quick_add_provider();
      }
   },
   
   // add html template after insert success example
   add_template_to_dasboad : function(tem){
      $('#bodyappend').prepend(tem);
      pm.hide_quick_add_product()
      pm.clear_search();
      recount.item();
   },
   
   /* change unit in quick add product */
   change_unit : function(){
      var _unit = $('#usp_unit option:selected').text();
      var _unit_import = $('#usp_unit_import option:selected').text();
      
      var text = 'Số ' + _unit +' / 1 '+ _unit_import;
      $('#usp_packing').attr({'placeholder': text, 'title' : text});
   },
   
   /* clear barcode text */
   clear_search : function(){
      $('#barcode').val('').focus();
   },
   
   /* change đơn giá */
   change_price_import : function(obj){
      var _value  = $(obj).val() || 0;
      var _id     = parseInt($(obj).parents('.item_add').data('id')) || 0;
      
      if(_id > 0){
         
         var _unit_parent  = 1;
         var _unit_child   = 1;
         var _price_import = 0;
         var _price_export = 0;
         var _lo           = '';
         var _date_expires = '';
         
         _price_import    = $('#price_import_'+_id).val() || 0;
         _price_import        = parse_type_number(_price_import);
         
         _unit_child      = $('#usp_packing_'+_id).val() || 0;
         _unit_child          = parse_type_number(_unit_child);
         
         _unit_parent     = $('#unit_parent_'+_id).val() || 0;
         _unit_parent         = parse_type_number(_unit_parent);
         
         var _price_import_small = Math.ceil(_price_import / _unit_child);
         var _total_count        = _unit_parent*_unit_child;
         var _total_money        = _unit_parent*_price_import;
         
         $('#price_import_small_'+_id).val(formatNumber(_price_import_small));
         $('#total_count_'+_id).val(formatNumber(_total_count));
         $('#total_import_money_'+_id).val(formatNumber(_total_money));
         
         _price_export        = $('#price_export_'+_id).val() || 0;
         _price_export        = parse_type_number(_price_export);
         
         _lo                  = $('#usp_lo_'+_id).val() || 0;
         _date_expires        = $('#usp_date_expires_'+_id).val() || 0;
         
         // send data to server update template
         
         clearTimeout(_setchange);
         _setchange = setTimeout(function(){
            $.post(
               '/ajaxs/ajax_warehouse_update_template.php',
               {
                  unit_parent    : _unit_parent,
                  unit_child     : _unit_child,
                  price_import   : _price_import,
                  price_export   : _price_export,
                  lo             : _lo,
                  date_expires   : _date_expires,
                  id             : _id
               },
               function(res){
                  console.log(res);
               },
               'json'
            )
         }, 500)
         
      }
      
      // caculator money
      recount.total_money();
            
   },
   
   /* Delete warehouse template */
   remove_item : function(obj){
      var id = parseInt($(obj).parents('.item_add').data('id')) || 0;
      if(id > 0){
         $.post(
            '/ajaxs/ajax_warehouse_delete_item.php',
            {
               id : id
            },
            function(res){
               if(res.code == 200){
                  $('#item_' + id).remove();
                  recount.item();
                  recount.total_money();
               }else{
                  pm.myAlert(res.error);
                  return false;
               }
            },
            'json'
         )
      }
   },
   
   /* insert template warehouse */
   insert_temp_wearhouse : function(data){
      var _id = data.id || 0;
      var _temid = data.temp || 0;
      
      // nếu id <= 0 là sản phẩm chưa có trong kho cần thêm mới
      if(_id <= 0){
         pm.show_quick_add_product(data);
         return;         
      }
      
      
      
      // bắn lên thêm vào temp
      data.module = _module;
      $.post(
         '/ajaxs/ajax_warehouse_insert_template.php',
         data,
         function(res){
            console.log(res);
            
            if(res.item_exits == 1){
               $('#unit_parent_'+res.item_id).val(res.item_count); 
               $('#total_count_'+res.item_id).val(res.total_count);   
               $('#total_import_money_'+res.item_id).text(res.total_money);             
               recount.total_money();
            }else{
               pm.add_template_to_dasboad(res.html);
            }
            
            pm.hide_quick_add_product()
            pm.clear_search();
            recount.item();
            recount.total_money();
            
         },
         'json'
      );
   }
   
};

// caculator total item,money
var recount = {
   item : function(){
      var _len = $('#dasboad_add tr').length - 1 || 0;
      $('#total_count_pro').text(_len);
   },
   
   total_money : function(){
      var total_money = 0;
      
      $('#bodyappend tr').each(function(){
         var _id = parseInt($(this).data('id')) || 0;
         if(_id > 0){
            
            var _money = $('#total_import_money_'+_id).val() || 0;
            _money = parse_type_number(_money);
            
            total_money += _money;
            
         }
      })
      
      $('#total_count_money').text(formatNumber(total_money));
      $('#total_amount').val(formatNumber(total_money));
      $('#total_pay').val(formatNumber(total_money));
      
      // caculator paid
      calc.paid_warehouse();
   }
}

// caculator has money
var calc = {
   paid_warehouse : function(){
      var _total_pay = $('#total_pay').val() || 0;
      _total_pay     = parse_type_number(_total_pay);
      
      var _paid      = $('#total_payed').val() || 0;
      _paid     = parse_type_number(_paid);
      
      var _pay_remain   = _total_pay - _paid;
      $('#total_remain').val(formatNumber(_pay_remain));
   }
}

var discount = {
   chose : function(obj){
      var _type = $(obj).data('dis') || 1; //1: vnđ, 2: %
      $(obj).parent().find('.icon_discount').removeClass('dis_active');
      $(obj).addClass('dis_active');
      $(obj).parent().find('.type_discount').val(_type);
      if(_type == 1){
         $(obj).parent().find('.money_discount').addClass('type_number').val(0);
      }else{
         $(obj).parent().find('.money_discount').removeClass('type_number').val(0);
      }
   },
   
   show : function(obj){
      $('.show_discount').addClass('hide');
      $(obj).parent().find('.show_discount').removeClass('hide');
      
      $('html,body').one('click',function(){
         if(!$(obj).parent().find('.show_discount').hasClass('hide')) $(obj).parent().find('.show_discount').addClass('hide');
      });
      event.stopPropagation();
   },
   
   caculator : function(obj){
      
   }
}