<div id="top">
   <div class="top_menu">
      <div class="w30" style="max-width: 190px;">
         <p class="find_fn">
            <span class="fn_icon flaticon-search"></span>
            <input id="find_text" spellcheck="false" class="w100" autocomplete="off" onkeyup="search_fn(this)" placeholder="Nhấn (F2) Tìm tính năng" />
         </p>
      </div>
   </div>   
</div>
<script>
   shortcut.add("f2",function() {
   	$('#find_text').focus();
   });

   function search_fn(obj){
      var hold_val = $(obj).val();
   	if( hold_val != ''){
      	//do something
      	$('.left_menu').find("a").each(function(){
      		var search_name = $.trim($(this).text()).toLowerCase();
            var search_noaccent = $.trim($(this).data('title')).toLowerCase();
      		var keyword =  $.vn_str_filter(hold_val).toLowerCase();
      		if($.vn_str_filter(search_name +' '+ search_noaccent).match(keyword) != keyword){
      			$(this).hide();
      		}else{
      			$(this).show().css('color', '#0000ff');
      		}
      	});
   
   	}else{
   		$('.left_menu').find("a").show().css('color', '#4e4e4e');
   	}
   }
   
   $(function(){   
      // auto focus barcode
      if($('#barcode').length){
         $('#barcode').focus();
         
         shortcut.add("f4",function() {
         	$('#barcode').focus();
         });
      }
      
      $.vn_str_filter = function (str){
      	if(typeof(str)!="string") return;
      	if(str==null) return;
      	var mang = new Array();
      	var strreplace = new Array("A","D","E","I","O","U","Y","a","d","e","i","o","u","y");
      
      	mang[0]=new Array("Ã€","Ã","áº¢","Ãƒ","áº ","Ã‚","áº¤","áº¦","áº¨","áºª","áº¬","Ä‚","áº®","áº°","áº²","áº´","áº¶");
      	mang[1]=new Array("Ä");
      	mang[2]=new Array("Ãˆ","Ã‰","áºº","áº¼","áº¸","ÃŠ","á»€","áº¾","á»‚","á»„","á»†");
      	mang[3]=new Array("ÃŒ","Ã","á»ˆ","Ä¨","á»Š");
      	mang[4]=new Array("Ã’","Ã“","á»Ž","Ã•","á»Œ","Ã”","á»’","á»","á»”","á»–","á»˜","Æ ","á»œ","á»š","á»ž","á» ","á»¢");
      	mang[5]=new Array("Ã™","Ãš","á»¦","Å¨","á»¤","Æ¯","á»ª","á»¨","á»¬","á»®","á»°");
      	mang[6]=new Array("á»²","Ã","á»¶","á»¸","á»´");
      
      	mang[7]=new Array("Ã ","Ã¡","áº£","Ã£","áº¡","Ã¢","áº¥","áº§","áº©","áº«","áº­","Äƒ","áº¯","áº±","áº·","áº³","áºµ");
      	mang[8]=new Array("Ä‘");
      	mang[9]=new Array("Ã¨","Ã©","áº»","áº½","áº¹","Ãª","á»","áº¿","á»ƒ","á»…","á»‡");
      	mang[10]=new Array("Ã¬","Ã­","á»‰","Ä©","á»‹");
      	mang[11]=new Array("Ã²","Ã³","á»","Ãµ","á»","Ã´","á»“","á»‘","á»•","á»—","á»™","Æ¡","á»","á»›","á»Ÿ","á»¡","á»£");
      	mang[12]=new Array("Ã¹","Ãº","á»§","Å©","á»¥","Æ°","á»«","á»©","á»­","á»¯","á»±");
      	mang[13]=new Array("á»³","Ã½","á»·","á»¹","á»µ");
      
      	for (i=0;i<=mang.length-1;i++)
      		for (i1=0;i1<=mang[i].length-1;i1++){
      		    var regex = new RegExp(mang[i][i1], 'g');
      		  	str=str.replace(regex,strreplace[i]);
      		}
      
      	return str;
      }
   })
</script>