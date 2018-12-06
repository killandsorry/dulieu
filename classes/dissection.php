<?
class dissection{
	
	var $arrayDefine;	
	var $array_type;

	function __construct(){
		$this->arrayDefine		=	 array("rul_cat" => array("label"=>"Luật danh mục", "name"=>"rul_cat", "id"=>"rul_cat", "type"=>"text", "require" => 0)
													,"rul_page" => array("label"=>"Luật phân trang", "name"=>"rul_page", "id"=>"rul_page", "type"=>"text", "require" => 0)
													,"rul_box" => array("label"=>"Box link", "name"=>"rul_box", "id"=>"rul_box", "type"=>"text", "require" => 0)
													,"rul_link" => array("label"=>"Link tin chi tiết", "name"=>"rul_link", "id"=>"rul_link", "type"=>"text", "require" => 0)
												 	,"rul_img" => array("label"=>"Ảnh đại diện", "name"=>"rul_img", "id"=>"rul_img", "type"=>"text", "require" => 0)
												 	,"rul_title" => array("label"=>"Tiêu đề bài viết", "name"=>"rul_title", "id"=>"rul_title", "type"=>"text", "require" => 0)
													,"rul_teaser" => array("label"=>"Nội dung tóm tắt", "name"=>"rul_teaser", "id"=>"rul_teaser", "type"=>"text", "require" => 0)
													,"rul_content" => array("label"=>"Nội dung bài viết", "name"=>"rul_content", "id"=>"rul_content", "type"=>"text", "require" => 0)
													,"rul_remove_tag" => array("label"=>"Remove tag", "name"=>"rul_remove_tag", "id"=>"rul_remove_tag", "type"=>"text", "require" => 0)
													,"rul_keyword" => array("label"=>"Remove keyword", "name"=>"rul_keyword", "id"=>"rul_keyword", "type"=>"text", "require" => 0)
													,"rul_img_detail" => array("label"=>"Ảnh chi tiết", "name"=>"rul_img_detail", "id"=>"rul_img_detail", "type"=>"text", "require" => 0)
													,"rul_date" => array("label"=>"Ngày đăng tin", "name"=>"rul_date", "id"=>"rul_date", "type"=>"int", "require" => 0)
													,"rul_site" => array("label"=>"Site join", "name"=>"rul_site", "id"=>"rul_site", "type"=>"text", "require" => 0)												
													
												);		
	} //end function
	
	/**
	 * Hàm show form lựa chon
	 */
	
	function get_url_html($url){
		$ini = curl_init($url);
		curl_setopt($ini, CURLOPT_HEADER, false);
		$userAgent  = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13";
		curl_setopt($ini, CURLOPT_USERAGENT, $userAgent);
		curl_setopt($ini, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ini, CURLOPT_REFERER, 'http://' . $_SERVER["SERVER_NAME"]);
	
		
		$result 	= curl_exec($ini);
		//echo $userAgent;
		unset($ini);
		$result	= replace_fck($result);
		$html 	= str_get_html($result); 
		unset($result);
		return $html;
	}
	
	function get_list_link($row){
		$foc_url			=	$row["foc_url"];
		$html				=	get_url_html($foc_url);	
		
		//nếu nhóm html khác rỗng thì lấy nhóm trước rồi lấy  phần tử con trong nhóm sau
		$for_group		=	isset($row["for_group"]) ? trim($row["for_group"]) : "";
		$arrayReturn	=	array();
		
		if($for_group != ""){
			$group				=	$html->find($row["for_group"]);
			$i	=	0;
			foreach($group as $tr){
				
				//lap array dinh nghia de lay du lieu
				foreach($this->arrayDefine as $field => $arr){
					if($field == "for_group") continue;
					if(isset($row[$field])){
						
						if($row[$field] != ""){
							//neu index khac rong thi co nghia 1 gia tri
							if($row[$field . "_index"] != ""){
								$field_value = "";
								switch($row[$field . "_inner"]){
									case 0:
										$field_value = @$tr->find($row[$field], intval($row[$field . "_index"]))->plaintext;
									break;
									case 1:
										$field_value = @$tr->find($row[$field], intval($row[$field . "_index"]))->innertext();
									break;
									case 2:
										$field_value = @$tr->find($row[$field], intval($row[$field . "_index"]))->src;
									break;
									case 3:
										$field_value = @$tr->find($row[$field], intval($row[$field . "_index"]))->href;
									break;
								}
								if($field_value != ""){
									if($row[$field . "_rm_string"] != ""){
										$field_value = preg_replace ('/' .  preg_quote($row[$field . "_rm_string"]) . '/si', '', $field_value);
									}
								}
							
								$arrayReturn[$i][$field]	= $field_value;
								$i++;
							}
							
						} //if($row[$field] != "")
						
					} // if(isset($row[$field]))
				}
				
			}
			
		}
		$html->clear();
		unset($html);
		return $arrayReturn;
	}
	
}
?>