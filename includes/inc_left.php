<div id="left">
   <ul class="left_menu">
      <?
      foreach($arrayMenu as $k => $menu){
         ?>
         <li class="lm_item">
            <span class="menu_text fb" onclick="toggle_menu(this)"><i class="<?=$menu['ic']?>"></i><?=$menu['name']?> </span>
            <ul class="lm_sub_menu">
               <?
               if(isset($menu['sub'])){
                  $line = 0;
                  foreach($menu['sub'] as $ks => $submenu){
                     
                     if($line == 1){
                        echo '<li class="line"></li>';
                     }
                     ?>
                     <li>
                        <a data-title="<?=removeAccent($submenu['title'])?>" href="/soft/<?=$menu['url'] . $submenu['url']?>"><?=$submenu['title']?></a>
                     </li>
                     <?
                     $line = isset($submenu['line'])? $submenu['line'] : 0;
                  }
               }
               ?>
            </ul>
         </li>
         <?
      }
      ?>
   </ul>
</div>
<script>
   function toggle_menu(obj){
      $('.lm_sub_menu').addClass('hide');
      $(obj).parent().find('.lm_sub_menu').removeClass('hide');
      $('.menu_text').removeClass('menu_active');
      $(obj).addClass('menu_active');
   }
</script>