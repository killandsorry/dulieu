<?
//Function generatePageBar 2.0 (Support Ajax) -- Code Editor: boy_infotech
function generatePageBar($page_prefix, $current_page, $page_size, $total_record, $url, $normal_class, $selected_class, $previous='<', $next='>', $first='<<', $last='>>', $break_type='1', $obj_response='',$page_space=5, $page_rewrite=0,$page_current_type=0){
	//Title show onMouseover
	$title_page_first			= "Trang đầu";
	$title_page_previous		= "Trang trước";
	$title_page_number		= "Trang ";
	$title_page_next			= "Trang sau";
	$title_page_last			= "Trang cuối";
	
	$page_query_string = "&page=";
	//rewrite
	if ($page_rewrite==1) $page_query_string = ",";
	
	if($total_record % $page_size == 0) $num_of_page = $total_record / $page_size;
	else $num_of_page = (int)($total_record / $page_size) + 1;
	
	$start_page = $current_page - $page_space;
	if($start_page <= 0) $start_page = 1;
	
	$end_page = $current_page + $page_space;
	if($end_page > $num_of_page) $end_page = $num_of_page;
	
	$url = str_replace('\"', '"', $url); //Remove XSS
	$url = str_replace('"', '', $url); //Remove XSS
	
	$page_bar = "";
	
	if($break_type < 1) $break_type = 1;
	if($break_type > 4) $break_type = 4;
	//Write prefix on screen
	if($page_prefix != "") $page_bar .= '<li><span>' . $page_prefix . '</span></li> ';
	//Write frist page
	if($break_type == 1){
		if(($start_page != 1) && ($num_of_page > 1)){
			if($obj_response != '') $href = 'javascript:load_data(\'' . $url . $page_query_string . '1' . '\',\'' . $obj_response . '\')';
			else $href = $url . $page_query_string . '1';
			$page_bar .=  '<li class="' . $normal_class . '"><a title="' . $title_page_first . '" href="' . $href . '">' . $first . '</a></li>';
		}
	}
	//Write previous page
	if($break_type == 1 || $break_type == 2 || $break_type == 4){
		if(($current_page > 1) && ($num_of_page > 1)){
			if($obj_response != '') $href = 'javascript:load_data(\'' . $url . $page_query_string . ($current_page - 1) . '\',\'' . $obj_response . '\')';
			else $href = $url . $page_query_string . ($current_page - 1);
			$page_bar .= '<li class="' . $normal_class . '"><a title="' . $title_page_previous . '" href="' . $href . '">' . $previous . '</a></li>';
			if(($start_page > 1) && ($break_type == 1 || $break_type == 2)){
				$page_dot_before = $start_page - 1;
				if($page_dot_before < 1) $page_dot_before = 1;
				$page_bar .= "<li class='" . $normal_class . "'><a title='" . $title_page_number . $page_dot_before . "' href='" . $url . $page_query_string . $page_dot_before . "'>..</a></li>";
			}
		}
	}
	//Write page numbers
	if($break_type == 1 || $break_type == 2 || $break_type == 3){
		$start_loop = $start_page;
		if($break_type == 3) $start_loop = 1;
		$end_loop	= $end_page;
		if($break_type == 3) $end_loop = $num_of_page;
		for($i=$start_loop; $i<=$end_loop; $i++){
			if($i != $current_page){
				if($obj_response != '') $href = 'javascript:load_data(\'' . $url . $page_query_string . $i . '\',\'' . $obj_response . '\')';
				else $href = $url . $page_query_string . $i;
				$page_bar .= '<li class="' . $normal_class . '"><a title="' . $title_page_number . $i . '" href="' . $href . '">' . $i . '</a></li>';
			}
			else{
				$page_current	= '' . $i . '';
				if($page_current_type == 1) $page_current = $i;
				$page_bar .= '<li class="' . $selected_class . '"><span title="' . $title_page_number . $i . '">' . $page_current . '</span></li>';
			}
		}
	}
	//Write next page
	if($break_type == 1 || $break_type == 2 || $break_type == 4){
		if(($current_page < $num_of_page) && ($num_of_page > 1)){
			if($obj_response != '') $href = 'javascript:load_data(\'' . $url . $page_query_string . ($current_page + 1) . '\',\'' . $obj_response . '\')';
			else $href = $url . $page_query_string . ($current_page + 1);
			if(($end_page < $num_of_page) && ($break_type == 1 || $break_type == 2)){
				$page_dot_after = $end_page + 1;
				if($page_dot_after > $num_of_page) $page_dot_after = $num_of_page;
				$page_bar .= "<li class='" . $normal_class . "'><a title='" . $title_page_number . $page_dot_after . "' href='" . $url . $page_query_string . $page_dot_after . "' class='" . $normal_class . "'>..</a></li>";
			}
			//if(($end_page < $num_of_page) && ($break_type == 1 || $break_type == 2)) echo '<b class="' . $normal_class . '">..</b>';
			$page_bar .= '<li class="' . $normal_class . '"><a title="' . $title_page_next . '" href="' . $href . '" >' . $next . '</a></li>';
		}
	}
	//Write last page
	if($break_type == 1){
		if(($end_page < $num_of_page) && ($num_of_page > 1)){
			if($obj_response != '') $href = 'javascript:load_data(\'' . $url . $page_query_string . $num_of_page . '\',\'' . $obj_response . '\')';
			else $href = $url . $page_query_string . $num_of_page;
			$page_bar .= '<li class="' . $normal_class . '"><a title="' . $title_page_last . '" href="' . $href . '" >' . $last . '</a></li>';
		}
	}
	return $page_bar;
}
?>