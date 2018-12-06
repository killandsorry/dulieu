<?
function search_teaser($text_normal, $search_text, $range = 120){
	if(trim($text_normal) == "") return "";
	$array_bad_word = array("?","^",",",";","*","/","~","@","-","!","[","]","(",")","=","|",":");
	$search_text = str_replace($array_bad_word,"",$search_text);

	//Lower các tham số để search
	$text_lower 	 = mb_strtolower($text_normal,"UTF-8"); 
	$search_text	 = mb_strtolower($search_text,"UTF-8");
	//Bẻ search text
	$search_text_array = explode(" ",$search_text);
	//Nhóm lại thành unique text 
	$search_text_array = array_unique($search_text_array);
	//Tạo lại array để tránh bị lỗi do array_unique gây ra
	//Loop 1 lần ghép lại chuỗi
	$temp_str = "";
	foreach ($search_text_array as $m_key => $m_value){
		if (trim($m_value) != "") $temp_str .= $m_value . " ";
	}
	//Bẻ lại temp_str 1 lần nữa
	$search_text_array = explode(" ",trim($temp_str));
	
	//Tạo array bold sau này replace cho dễ
	$search_bold_text_array = array();
	//*/
	for ($i=0; $i<count($search_text_array);$i++){
		$search_bold_text_array = array_merge($search_bold_text_array,array('' . $search_text_array[$i] . ""));
	}
	//print_r($search_bold_text_array);
	//*/
	$array_position = array();
	for ($i=0; $i<count($search_text_array);$i++){
		if($search_text_array[$i] != ""){
			//Nếu phần từ không rỗng bắt đầu tìm
			$postion = mb_strpos($text_lower, $search_text_array[$i], 0, "UTF-8");
			
			//Nếu vị trí của từng chữ đơn nằm trong đoạn text thì nhét vào array_position
			while ($postion !== false){
				$array_position = array_merge($array_position,array($postion));
				//Loop để tìm tiếp các vị trí tiếp theo
				$offset = $postion + 1;
				$postion = mb_strpos($text_lower, $search_text_array[$i],$offset,"UTF-8");
			}
		
		}

	}
	
	//Sắp xếp array_position
	sort($array_position);
	
	//Lọc ra những vùng xuất hiện nhiều từ khóa nhất
	//Thiêt lập array_range
	$array_range = array();
	
	//Thiết lập các biến để chuẩn bị loop
	$j = -1;
	$start_new_value = true;
	//Vị trí chốt
	$fixed_position = 0;
	
	//loop array_position
	for ($i=0; $i<count($array_position);$i++){
		
		//Nếu i > 0 bắt đầu xet
		if ($i > 0){
			//Nếu vị trí htại trừ vị trí $fixed_position (chốt) nằm trong range thì gán lại end và tăng count_number_position
			if ($array_position[$i] - $fixed_position <= $range){
				$array_range[$j]["end"] = $array_position[$i];
				$array_range[$j]["count_number_position"]++;
			}
			//Nếu nằm ngoài range
			else{
				//Khởi tạo value mới
				$start_new_value = true;
			}
		}

		//Nếu biến start_new_value là true bắt đầu khởi tạo 1 value mới trong array
		if ($start_new_value){
			//Tăng j
			$j++;
			
			$array_range[$j]["start"] = $array_position[$i];
			$array_range[$j]["end"] = $array_position[$i];
			$array_range[$j]["count_number_position"] = 1;
			
			//Tắt biến start_new_value
			$start_new_value = false;

			//Gán lại vi trí chốt
			$fixed_position = $array_position[$i];
			
		}		
	}
	
	//Sắp xếp lại array
	for ($i=0; $i<count($array_range)-1;$i++){
		for ($j=$i+1; $j<count($array_range);$j++){
			if ($array_range[$i]["count_number_position"] < $array_range[$j]["count_number_position"]){
				$temp = $array_range[$i];
				$array_range[$i] = $array_range[$j];
				$array_range[$j] = $temp;
			}
		}
	}
	
	$str_return	= "";
	
	//Loop lại 1 lần nữa để cut string
	for ($i=0; $i<count($array_range);$i++){
		
		$start_pos = $array_range[$i]["start"];
		$end_pos = $array_range[$i]["end"];
		
		//Kiểm tra lại range nếu  end_pos -  start_pos < range thì set lại
		if (($end_pos - $start_pos) < $range){
			//Thêm khoảng không
			$more_space = ($range - ($end_pos - $start_pos)) / 2;
			
			//Set lai start và end
			$start_pos = $start_pos - $more_space;
			if ($start_pos < 0) $start_pos = 0;
			
			$end_pos = $end_pos + $more_space;
			if ($end_pos > mb_strlen($text_normal, "UTF-8")) $end_pos = mb_strlen($text_normal, "UTF-8");
		}
		
		//Tìm đến ký tự space bên trái
		$left_pos = mb_strrpos(mb_substr($text_normal,0, $start_pos ,"UTF-8"), " ", "UTF-8");
		if ($left_pos === false) $left_pos = 0;

		//Tìm đến ký tự space bên phải
		$right_pos = mb_strpos($text_normal, " ", $end_pos ,"UTF-8");
		if ($right_pos === false) $right_pos = mb_strlen($text_normal, "UTF-8");
		
		$str_return .= " ... " .  str_replace($search_text_array, $search_bold_text_array, mb_substr($text_normal, $left_pos, ($right_pos - $left_pos),"UTF-8"));
		
		//Chỉ lấy tối đa 3 đoạn text
		if ($i >= 2) break;
	}
	
	//ghep $search_text_array thanh mot chuoi cach nhau boi dau | 
	$arrayReplace =  $search_text_array;
	
	/*
		một số ký tự đứng đầu tiếng việt như Đ Ô thì khong replace được từ có đứng đầu là đ hay ô
		nên tao ra array mới $arrayReplace =  $search_text_array; và những từ có ký tự tiếng việt đừng đầu cả hoa lẫn thường
	*/
	
	foreach($search_text_array as $key=>$value){
		// loai bo het ky thu đặc biệt	
		$kytudau	= mb_substr($value,0,1,"UTF-8");	
		$value	= str_replace($kytudau,mb_strtoupper($kytudau,"UTF-8"),$value);
		
		//kiểm tra có phải là ô hay đ .... thì mới thêm vào array
		if(preg_match("|[^A-Za-z0-9]|si",$kytudau)){
			$arrayReplace[]	= $value;
		}
	}
	


	foreach($arrayReplace as $key=>$value){
		// loai bo het ky thu đặc biệt	
		$str_return	= preg_replace("|([^A-Za-z])(" . preg_quote($value) . ")([^A-Za-z])|si"," <b>$0</b> ",$str_return);
		
	}
	$str_return		= mb_convert_encoding($str_return,"UTF-8","UTF-8");

	return $str_return;

	//print_r($array_position);
	//echo "<br>";
	//print_r($array_range);
}

/*
$text_normal = '
Nhiều người vợ đau khổ tột cùng, không hiểu tại sao chẳng "đói khát" gì nhưng chồng vẫn ra ngoài "ăn vụng", thậm chí đi bia ôm, tìm đến gái điếm hay "quơ" cả người giúp việc, bạn của vợ, vợ của bạn... Tóm lại là những phụ nữ nào trong tầm tay họ.

Chuyện ứng biến trong tình dục giữa phái nam và phái nữa rất khác nhau, nếu không muốn nói là trái ngược nhau. Với đa số phụ nữ, họ thực sự thưởng thức một cuộc ân ái với người đàn ông quen hơi mà thôi. Đó là người họ yêu thương, tin cậy và nhất là sau chuyện ấy họ biết mình vẫn được gắn bó, nâng niu, tôn trọng. Còn với phái nam, có ông tuy vẫn yêu vợ, giữ vợ khư khư thế nhưng nếu gặp một phụ nữ xinh đẹp hấp dẫn và ngay cả khi người ấy chẳng hơn vợ họ về bất cứ phương diện nào nhưng nếu người đó dễ dãi chấp nhận "cho" là họ nhận một cách thích thú.

Theo các nhà nghiên cứu, sở dĩ phái nam hay vướng phải thói tật ấy là vì bản chất họ là những người ham của lạ. Vợ họ dù tuyệt vời đến đâu nhưng cũng chỉ là "của quen". Trong khi họ cho rằng "Một cái lạ bằng tạ cái quen". Và dù đi với cái lạ nhưng họ vẫn trở về với cái "quen" không thể sống thiếu cái "quen" được. Nhiều người vợ hiểu điều này nhưng chấp nhận là điều không thể vì hệ lụy của nó là không thể lường hết, từ việc các ông mắc bệnh tật qua đường tình dục, về nhà lây cho vợ. Việc trăng hoa ấy còn ảnh hưởng đến danh dự bản thân và có thể dẫn đến gia dình tan nát khi có vợ lẽ, con rơi với những bi kịch của nó.

Chồng chọi với thói tật "ham của lạ" ở chồng luôn là bài toán khó với nhiều người vợ, nhất là với những người có chồng thường xuyên làm việc xa nhà, trong môi trường có nhiều cám dỗ, khi túi các ông rủng rỉnh tiền bạc hay có quyền lực trong tay...
';
$search_text = "thói ham nhà nam phái nam vợ";

search_teaser($text_normal,$search_text);
*/
?>