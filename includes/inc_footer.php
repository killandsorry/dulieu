<style>
#ovl{
   position: fixed;
   top: 0;
   left: 0;
   width: 100%;
   height: 100%;
   background-color: rgba(0,0,0,0.5);
   z-index: 100;
}
#ovl_content{
   
}
#confirm{
   background-color: #fff;
   margin: 10% auto;
   width: 90%;
   position: fixed;
   top: 10%;
   left: calc((100% - 300px) / 2);
   max-width: 300px;
   border: 1px solid #ddd;
   padding: 12px 10px;
   border-radius: 5px;
   z-index: 101;
}
#confirm .message, #alert .message{
   line-height: 18px;
   margin-bottom: 10px;
   font-style: italic;
}
#confirm .c_title,  #alert .a_title{
   font-weight: bold;
   text-transform: uppercase;
   margin-bottom: 6px;
   color: #EB5236;
}
#confirm .yes, #alert .ok{
   background-color: #25A20C;
   color: #fff;
   cursor: pointer;
   border: none;
   padding: 5px 10px;
   margin: 0 5px;
   border-radius: 5px;
   font-size: 12px;
}
#confirm .no{
   background-color: #EB5236;
   color: #fff;
   cursor: pointer;
   border: none;
   padding: 5px 10px;
   margin: 0 5px;
   border-radius: 5px;
   font-size: 12px;
}
#alert{
   background-color: #fff;
   margin: 10% auto;
   width: 90%;
   position: fixed;
   top: 10%;
   left: calc((100% - 300px) / 2);
   max-width: 300px;
   border: 1px solid #ddd;
   padding: 12px 10px;
   border-radius: 5px;
   z-index: 101;
}
#alert .ok{
   
}
#dialog{
   position: fixed;
   width: 100%;
   height: 100%;
   z-index: 101;
}
#dcontent{
   margin: auto auto;
   position: relative;
   overflow: hidden;
   background-color: #fff;
}
#ifr{
   width: 100%;
   height: 100%;
}
.dclose{
   position: absolute;
   top: 0;
   right: 0;
   cursor: pointer;
   background-color: yellow;
   padding: 2px 10px;
   text-align: center;
   border-radius: 0 0 0 10px;
   font-size: 15px;
}
.dclose:hover{
   background-color: #000;
   color: #fff;
}
</style>
<div id="ovl" class="hide"></div>

<div id="alert" class="hide">
   <div class="a_title t_c">Thông báo</div>
   <div class="message t_c"></div>
   <div class="t_c">
      <button class="ok" onclick="pm.closeAlert()">Ẩn thông báo</button>
   </div>
</div>

<div id="confirm" class="hide">
   <div class="c_title t_c">Yêu cầu xác nhận</div>
   <div class="message t_c"></div>
   <div class="t_c">
      <button class="yes" id="yes">Đồng ý</button>
      <button class="no" id="no">Hủy</button>
   </div>
</div>

<div id="dialog" class="hide">
   <div id="dcontent">
      <div id="ifr"></div>
      <span class="dclose" onclick="dialog.close()" title="Đóng lại">&times;</span>
   </div>
   
</div>