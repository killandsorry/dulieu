<?
function showAdv($row, $file_path, $i = 0){
	$banner_link = $row["adv_link"];
	$banner_link = str_replace("http://","",$banner_link);
	$banner_link = "http://" . $banner_link;
	switch($row["adv_type"]){
		case 1:
			if($i == 0){
				?>
				<div style="<?=($row["adv_height"] > 0) ? 'height: 100px; ' : ''?><?=($row["adv_height"] > 0 && $row["adv_width"] > 0) ? 'overflow: hidden; ' : ''?><?=($row["adv_width"] > 0) ? 'width: 270px;' : ''?>" align="center">
					<a href="<?=$banner_link?>"><img src="<?=$file_path?><?=$row["adv_picture"]?>" border="0"/></a>
				</div>
				<?				
			}else{
				?>
				<div style="height: 100px; overflow: hidden; width: 270px; border: solid 1px #f2f2f2; margin-bottom: 3px;" align="center">
					<img src="<?=$file_path?><?=$row["adv_picture"]?>" border="0"/>
				</div>
				<input type="file" name="picture_<?=$row["adv_id"]?>" class="form_control" />
				<?
			}
		break;
		case 2:
			if($i == 0){
				
			}else{
				?>
				<div style="height: 100px; overflow: hidden; width: 270px; border: solid 1px #f2f2f2; margin-bottom: 3px;" align="center">
					<script language="javascript">ShowFlash_swf('banner_<?=$row["adv_id"]?>', "<?=$file_path?><?=$row["adv_picture"]?>", 270, 100)</script>
				</div>
				<input type="file" name="picture_<?=$row["adv_id"]?>" class="form_control" />
				<?
			}
		break;
		case 3:
			if($i == 0){
				
			}else{
				?>
				<div style="height: 100px; overflow: hidden; width: 270px; border: solid 1px #f2f2f2; margin-bottom: 3px;" align="center">
					<textarea class="form_control" style="height: 100px; width: 270px;"><?=$row["adv_html"]?></textarea>
				</div>
				<div style="display: none;"><input type="file" name="picture_<?=$row["adv_id"]?>" class="form_control" /></div>
				<?
			}
		break;				
	}
}
?><?
function tempkt(){
?>
	<div style="background:url(/template/khung/1.jpg) no-repeat left; padding-left:5px;">
		<div style="background:url(/template/khung/1.jpg) no-repeat right; padding:2px"></div>
	</div>
	<div style="background:url(/template/khung/2.jpg) repeat-y left; padding-left:3px;">
		<div style="background:url(/template/khung/2.jpg) repeat-y right; padding-right:3px">
			<div style="padding-left:1px; padding-right:1px;">
<?
}
?>
<?
function tempkb(){
?>
			</div>
		</div>
	</div>
	<div style="background:url(/template/khung/3.jpg) no-repeat left; padding-left:5px;">
		<div style="background:url(/template/khung/3.jpg) no-repeat right; padding:2px"></div>
	</div>
	<div style="clear:both; margin-bottom:1px"></div>
<?
}
?>