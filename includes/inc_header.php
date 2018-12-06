<div id="header">
   <ul class="menu">
      <li class="mitem">
         <a class="logo"></a>
      </li>
      <?
      foreach($arrayMenu as $k => $menu){
         ?>
         <li class="mitem">
            <span><?=$menu['name']?></span>
            <ul class="sub_menu">
               <?
               if(isset($menu['sub'])){
                  $line = 0;
                  foreach($menu['sub'] as $ks => $submenu){
                     
                     if($line == 1){
                        echo '<li class="line"></li>';
                     }
                     ?>
                     <li>
                        <a href="/soft/<?=$menu['url'] . $submenu['url']?>"><?=$submenu['title']?></a>
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