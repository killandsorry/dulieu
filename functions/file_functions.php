<?
function generate_name($filename){
	$name = "";
	for($i=0; $i<3; $i++){
		$name .= chr(rand(97,122));
	}
	$today= getdate();
	$name.= $today[0];
	$temp_name = substr($filename, -4);
	$ext	= substr($temp_name, (strrpos($filename, ".") + 1));
	if($ext == "") $ext = "jpg";
	return $name . "." . $ext;
}

// tạo đường dẫn cho ảnh tiêu đề
function create_path_image_title($width = 200, $height = 100, $date = 0, $filename = ''){
	$path	=	'';
	if($date	<= 0 || $filename == ''){
		$path	= '/pictures/news_small/'. $width .'x' . $height .'/no_photo.jpg';
		return $path;
	}else{
		$path	= '/pictures/news_small/'. $width .'x' . $height .'/' . date('Y/m/d', $date) . '/' . $filename;
		return $path;
	}
}

function check_upload_extension($filename,$allow_list){

	$sExtension = substr( $filename, ( strrpos($filename, '.') + 1 ) ) ;
	$sExtension = strtolower( $sExtension ) ;

	$allow_arr = explode(",",$allow_list);
	$pass = 0;

	for ($i=0;$i<count($allow_arr);$i++){
		if ($sExtension == $allow_arr[$i]) $pass = 1;
	}
	return $pass;
}

function getExtension($filename){
	$sExtension = substr($filename, (strrpos($filename, ".") + 1));
	$sExtension = strtolower($sExtension);
	return $sExtension;
}

function delete_file($path, $filename){
	$small_image	= "small_" . $filename;
	$normal_image	= "normal_" . $filename;
	if(file_exists($path . $small_image))	@unlink($path . $small_image);
	if(file_exists($path . $normal_image))	@unlink($path . $normal_image);
	if(file_exists($path . $filename))		@unlink($path . $filename);
}
function getImageFromUrl($url, $path_img_save, $maxwidth = 0, $maxheight = 0,$update = false){

	$image = false;
	$sExtension = getExtension($path_img_save);
	//check extension file for create
	switch($sExtension){
		case "gif":
			$image = @imagecreatefromgif($url);
			break;
		case $sExtension == "jpg" || $sExtension == "jpe" || $sExtension == "jpeg":
			$image = @imagecreatefromjpeg($url);
			break;
		case "png":
			$image = @imagecreatefrompng($url);
			break;
	}
	$file_name = @end(explode("/", $url));
	//Thử lấy từ vatgia.com
	if($image !== false){
		if($maxwidth > 0 || $maxheight > 0){
			if($maxwidth > 0 && $maxheight < 1) $maxheight = $maxwidth * 2;
			if($maxheight > 0 && $maxwidth < 1) $maxwidth = $maxheight * 2;
			$width		= imagesx($image);
			$height		= imagesy($image);
			if($update){
				$db_ex = new db_execute("UPDATE products SET pro_width = " . $width . ",pro_height=" . $height . " WHERE pro_picture = '" . replaceMQ($file_name) . "'");
				unset($db_ex);
			}
			if($maxwidth > $width) $maxwidth = $width;
			if($maxheight > $height) $maxheight = $height;
			$percent		= 0;
			if($width != 0 && $height !=0){
				if($maxwidth / $width > $maxheight / $height) $percent = $maxheight / $height;
				else $percent = $maxwidth / $width;
			}
			$new_width	= $width * $percent;
			$new_height	= $height * $percent;
			//Resample
			$image_p = imagecreatetruecolor($new_width, $new_height);
			$white = imagecolorallocate($image_p, 255, 255, 255);
			//imagefill($image_p, 0, 0, $white);
			//Copy and resize part of an image with resampling
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			//imagecopy($image_p, $image, 0, 0, 0, 0, $new_w, $new_h);

			$image = $image_p;
			//imagedestroy($image_p);
		}
		switch($sExtension){
			case "gif":
				imagegif($image, $path_img_save);
				header('Content-type: image/gif');
				imagegif($image);
				break;
			case $sExtension == "jpg" || $sExtension == "jpe" || $sExtension == "jpeg":

				imagejpeg($image, $path_img_save);
				header('Content-type: image/jpeg');
				imagejpeg($image);
				break;
			case "png":

				imagepng($image, $path_img_save);
				header('Content-type: image/png');
				imagepng($image);
				break;
		}
		imagedestroy($image);
	}else{
		echo('HTTP/1.0 404 Not Found');
	}
}

/**
 * function clear js, link in content
 */
function clear_js_link_content($content = ''){
	$new_content	= '';
	$new_content	= html_entity_decode($content);
	$new_content	= mb_convert_encoding($new_content, 'UTF-8', 'UTF-8');
	$new_content	= preg_replace('#<script[^>]*>.*?</script>#is', '', $new_content);
	$htmlClean		= new html_cleanup($new_content);
	$htmlClean->clean();
	$new_content	= $htmlClean->output_html;
	$new_content	= removeLink($new_content);
	return $new_content;
}

