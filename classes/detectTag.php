<?
class detectTag{
	var $output_html = '';
	var $text_search_index = '';
	var $array_fck	= array ("à" => array("&#224;","&#224","&agrave;","&agrave"),
									"á" => array("&#225;","&#225","&aacute;","&aacute"),
									"ạ" => array("&#7841;","&#7841","&agrave;","&agrave"),
									"ả" => array("&#7843;","&#7843"),
									"ã" => array("&#227;","&#227","&atilde;","&atilde"),
									"â" => array("&#226;","&#226","&acirc;","&acirc"),
									"ầ" => array("&#7847;","&#7847"),
									"ấ" => array("&#7845;","&#7845"),
									"ậ" => array("&#7853;","&#7853"),
									"ẩ" => array("&#7849;","&#7849"),
									"ẫ" => array("&#7851;","&#7851"),
									"ă" => array("&#259;","&#259"),
									"ằ" => array("&#7857;","&#7857"),
									"ắ" => array("&#7855;","&#7855"),
									"ặ" => array("&#7863;","&#7863"),
									"ẳ" => array("&#7859;","&#7859"),
									"ẵ" => array("&#7861;","&#7861"),
									"è" => array("&#232;","&#232","&egrave;","&egrave"),
									"é" => array("&#233;","&#233","&eacute;","&eacute"),
									"ẹ" => array("&#7865;","&#7865"),
									"ẻ" => array("&#7867;","&#7867"),
									"ẽ" => array("&#7869;","&#7869"),
									"ê" => array("&#234;","&#234","&ecirc;","&ecirc"),
									"ề" => array("&#7873;","&#7873"),
									"ế" => array("&#7871;","&#7871"),
									"ệ" => array("&#7879;","&#7879"),
									"ể" => array("&#7875;","&#7875"),
									"ễ" => array("&#7877;","&#7877"),
									"ì" => array("&#236;","&#236","&igrave;","&igrave"),
									"í" => array("&#237;","&#237","&iacute;","&iacute"),
									"ị" => array("&#7883;","&#7883"),
									"ỉ" => array("&#7881;","&#7881"),
									"ĩ" => array("&#297;","&#297"),
									"ò" => array("&#242;","&#242","&ograve;","&ograve"),
									"ó" => array("&#243;","&#243","&oacute;","&oacute"),
									"ọ" => array("&#7885;","&#7885"),
									"ỏ" => array("&#7887;","&#7887"),
									"õ" => array("&#245;","&#245","&otilde;","&otilde"),
									"ô" => array("&#244;","&#244","&ocirc;","&ocirc"),
									"ồ" => array("&#7891;","&#7891"),
									"ố" => array("&#7889;","&#7889"),
									"ộ" => array("&#7897;","&#7897"),
									"ổ" => array("&#7893;","&#7893"),
									"ỗ" => array("&#7895;","&#7895"),
									"ơ" => array("&#417;","&#417"),
									"ờ" => array("&#7901;","&#7901"),
									"ớ" => array("&#7899;","&#7899"),
									"ợ" => array("&#7907;","&#7907"),
									"ở" => array("&#7903;","&#7903"),
									"ỡ" => array("&#7905;","&#7905"),
									"ù" => array("&#249;","&#249","&ugrave;","&ugrave"),
									"ú" => array("&#250;","&#250","&uacute;","&uacute"),
									"ụ" => array("&#7909;","&#7909"),
									"ủ" => array("&#7911;","&#7911"),
									"ũ" => array("&#361;","&#361"),
									"ư" => array("&#432;","&#432"),
									"ừ" => array("&#7915;","&#7915"),
									"ứ" => array("&#7913;","&#7913"),
									"ự" => array("&#7921;","&#7921"),
									"ử" => array("&#7917;","&#7917"),
									"ữ" => array("&#7919;","&#7919"),
									"ỳ" => array("&#7923;","&#7923"),
									"ý" => array("&#253;","&#253","&yacute;","&yacute"),
									"ỵ" => array("&#7925;","&#7925"),
									"ỷ" => array("&#7927;","&#7927"),
									"ỹ" => array("&#7929;","&#7929"),
									"đ" => array("&#273;","&#273"),
									"À" => array("&#192;","&#192","&Agrave;","&Agrave"),
									"Á" => array("&#193;","&#193","&Aacute;","&Aacute"),
									"Ạ" => array("&#7840;","&#7840"),
									"Ả" => array("&#7842;","&#7842"),
									"Ã" => array("&#195;","&#195","&Atilde;","&Atilde"),
									"Â" => array("&#194;","&#194","&Acirc;","&Acirc"),
									"Ầ" => array("&#7846;","&#7846"),
									"Ấ" => array("&#7844;","&#7844"),
									"Ậ" => array("&#7852;","&#7852"),
									"Ẩ" => array("&#7848;","&#7848"),
									"Ẫ" => array("&#7850;","&#7850"),
									"Ă" => array("&#258;","&#258"),
									"Ằ" => array("&#7856;","&#7856"),
									"Ắ" => array("&#7854;","&#7854"),
									"Ặ" => array("&#7862;","&#7862"),
									"Ẳ" => array("&#7858;","&#7858"),
									"Ẵ" => array("&#7860;","&#7860"),
									"È" => array("&#200;","&#200","&Egrave;","&Egrave"),
									"É" => array("&#201;","&#201","&Eacute;","&Eacute"),
									"Ẹ" => array("&#7864;","&#7864"),
									"Ẻ" => array("&#7866;","&#7866"),
									"Ẽ" => array("&#7868;","&#7868"),
									"Ê" => array("&#202;","&#202","&Ecirc;","&Ecirc"),
									"Ề" => array("&#7872;","&#7872"),
									"Ế" => array("&#7870;","&#7870"),
									"Ệ" => array("&#7878;","&#7878"),
									"Ể" => array("&#7874;","&#7874"),
									"Ễ" => array("&#7876;","&#7876"),
									"Ì" => array("&#204;","&#204","&Igrave;","&Igrave"),
									"Í" => array("&#205;","&#205","&Iacute;","&Iacute"),
									"Ị" => array("&#7882;","&#7882"),
									"Ỉ" => array("&#7880;","&#7880"),
									"Ĩ" => array("&#296;","&#296"),
									"Ò" => array("&#210;","&#210","&Ograve;","&Ograve"),
									"Ó" => array("&#211;","&#211","&Oacute;","&Oacute"),
									"Ọ" => array("&#7884;","&#7884"),
									"Ỏ" => array("&#7886;","&#7886"),
									"Õ" => array("&#213;","&#213","&Otilde;","&Otilde"),
									"Ô" => array("&#212;","&#212","&Ocirc;","&Ocirc"),
									"Ồ" => array("&#7890;","&#7890"),
									"Ố" => array("&#7888;","&#7888"),
									"Ộ" => array("&#7896;","&#7896"),
									"Ổ" => array("&#7892;","&#7892"),
									"Ỗ" => array("&#7894;","&#7894"),
									"Ơ" => array("&#416;","&#416"),
									"Ờ" => array("&#7900;","&#7900"),
									"Ớ" => array("&#7898;","&#7898"),
									"Ợ" => array("&#7906;","&#7906"),
									"Ở" => array("&#7902;","&#7902"),
									"Ỡ" => array("&#7904;","&#7904"),
									"Ù" => array("&#217;","&#217","&Ugrave;","&Ugrave"),
									"Ú" => array("&#218;","&#218","&Uacute;","&Uacute"),
									"Ụ" => array("&#7908;","&#7908"),
									"Ủ" => array("&#7910;","&#7910"),
									"Ũ" => array("&#360;","&#360"),
									"Ư" => array("&#431;","&#431"),
									"Ừ" => array("&#7914;","&#7914"),
									"Ứ" => array("&#7912;","&#7912"),
									"Ự" => array("&#7920;","&#7920"),
									"Ử" => array("&#7916;","&#7916"),
									"Ữ" => array("&#7918;","&#7918"),
									"Ỳ" => array("&#7922;","&#7922"),
									"Ý" => array("&#221;","&#221","&Yacute;","&Yacute"),
									"Ỵ" => array("&#7924;","&#7924"),
									"Ỷ" => array("&#7926;","&#7926"),
									"Ỹ" => array("&#7928;","&#7928"),
									"Đ" => array("&#272;","&#272"),
									" " => array("&nbsp;")
									);
	/**
	 * Ham boc tach noi dung ra nhung tu khoa
	 */
	function detectTagFromText($string, $insert_db = 0){
		$convmap				= array(0x0, 0x2FFFF, 0, 0xFFFF);
		$string = @mb_decode_numericentity($string, $convmap, "UTF-8");
		foreach($this->array_fck as $key => $val){
			foreach($val as $k2){
				$string = str_replace($k2,$key,$string);
			}
		}
		$this->output_html = $string;
		$string = preg_replace ('/<script.*?\>.*?<\/script>/si', '_', $string);
		$string = preg_replace ('/<style.*?\>.*?<\/style>/si', '_', $string);
		$string = preg_replace ('/<.*?\>/si', '_', $string);
		$string = str_replace ('&nbsp;', ' ', $string);
		$string = str_replace (array(chr(9),chr(10),chr(13)), '_', $string);
		$string = mb_convert_encoding($string, "UTF-8", "UTF-8");
		$string_old = $string;
		$string = mb_strtolower($string,"UTF-8");
		$string_lower = $string;
		$string 	=  trim(preg_replace("/[^a-z0-9àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ\_\&\s\/]/i","_",$string));
		$array_bad_word = array("?","^",",",";","*","/","~","@","-","!","[","]","(",")","=","|",":",".");
		$string = str_replace($array_bad_word,"_",$string);
		for($i = 0; $i < 10; $i++){
			$string = str_replace("__","_",$string);
		}
		$this->text_search_index = removeHTML(str_replace("_"," ",$string));
		$keywords = explode("_",$string);
		$arrayReturn = array();
		foreach($keywords as $key =>  $val){
			if($new_key = $this->checkIsKeywordTag($val)){
				$start = mb_strpos($string_lower,$new_key,1,"UTF-8");
				$old_keyword = mb_substr($string_old,$start,mb_strlen($new_key,'UTF-8'),'UTF-8');
				//kiem tra xem lay tu khoa co chuan ko
				if(trim($new_key) != trim(mb_strtolower($old_keyword,'UTF-8'))){
					$old_keyword = $new_key;
				}
				$trigrams				 = $this->BuildTrigrams($new_key);
				$keyword_noaccent		 = $this->removeAccent($new_key);
				$trigrams_noaccent	 = $this->removeAccent($trigrams);
				$arrayReturn[$new_key] = array("keyword" => $old_keyword
														,"keyword_noaccent" => $keyword_noaccent
														,"link" => "/s/" . urlencode($new_key) . ".html"
														,"trigrams" => $trigrams
														,"trigrams_noaccent" => $trigrams_noaccent
														);
				//nếu yêu cầu insert vào db thì thêm vào
				if($insert_db){
					if($keyword_noaccent == $new_key){
						$keyword_noaccent = '';
						$trigrams_noaccent = '';
					}
					if($old_keyword == ""){
						$old_keyword = $new_key;
					}
					if(trim($old_keyword) != ''){
						$tag_word_len	= count(explode(" ",$new_key));
						$db_ex = new db_execute("INSERT IGNORE INTO keywords(tag_name,tag_name_noaccent,tag_md5,tag_trigrams,tag_trigrams_noaccent,tag_active,tag_word_len)
														 VALUES('" . replaceMQ($old_keyword) . "','" . replaceMQ($keyword_noaccent) . "','" . md5($new_key) . "','" . replaceMQ($trigrams) . "','" . replaceMQ($trigrams_noaccent) . "',1," . $tag_word_len . ")");
					 	//nếu insert khong thanh cong thi cap nhat so lan lap lai tu khoa
					 	if($db_ex->total < 1){
					 		$db_ex1 = new db_execute("UPDATE keywords SET tag_priority=tag_priority+1 WHERE tag_md5 = '" . md5($new_key) . "'");
					 		unset($db_ex1);
					 	}
				 		unset($db_ex);
			 		}
				}
			}
		}
		return $arrayReturn;
	}
	
	/**
	 * Ham loc nhung tu khoa phu hop tieu chi
	 */
	function checkIsKeywordTag($keyword){
		$keyword = trim($keyword);
		for($i = 1; $i < 10; $i++){
			$keyword = str_replace("  "," ",$keyword);
		}
		$keyword = trim($keyword);
		
		
		$len		 = mb_strlen($keyword,"UTF-8");
		if($len < 6) return false;
		//neu co ky tu & thi loai luon
		if(strpos($keyword,'&') !== false) return false;
		$len_word = count(explode(" ",$keyword));
		//neu tu khoa co nhieu tu qua cung loai
		if($len_word > 5) return false;
		//neu tu khoa co nhieu tu ngan qua cung loai
		if(($len / $len_word) < 4) return false;
		return $keyword;
	}
	
	function BuildTrigrams($keyword) {
	    $t = "__" . $keyword . "__";
	    $trigrams = "";
	    for ($i = 0; $i < mb_strlen($t,"UTF-8") - 2; $i++)
	        $trigrams .= mb_substr($t, $i, 3, "UTF-8") . " ";
	    return $trigrams;
	}
	function removeAccent($mystring){
		$marTViet=array(
			// Chữ thường
			"à","á","ạ","ả","ã","â","ầ","ấ","ậ","ẩ","ẫ","ă","ằ","ắ","ặ","ẳ","ẵ",
			"è","é","ẹ","ẻ","ẽ","ê","ề","ế","ệ","ể","ễ",
			"ì","í","ị","ỉ","ĩ",
			"ò","ó","ọ","ỏ","õ","ô","ồ","ố","ộ","ổ","ỗ","ơ","ờ","ớ","ợ","ở","ỡ",
			"ù","ú","ụ","ủ","ũ","ư","ừ","ứ","ự","ử","ữ",
			"ỳ","ý","ỵ","ỷ","ỹ",
			"đ","Đ","'",
			// Chữ hoa
			"À","Á","Ạ","Ả","Ã","Â","Ầ","Ấ","Ậ","Ẩ","Ẫ","Ă","Ằ","Ắ","Ặ","Ẳ","Ẵ",
			"È","É","Ẹ","Ẻ","Ẽ","Ê","Ề","Ế","Ệ","Ể","Ễ",
			"Ì","Í","Ị","Ỉ","Ĩ",
			"Ò","Ó","Ọ","Ỏ","Õ","Ô","Ồ","Ố","Ộ","Ổ","Ỗ","Ơ","Ờ","Ớ","Ợ","Ở","Ỡ",
			"Ù","Ú","Ụ","Ủ","Ũ","Ư","Ừ","Ứ","Ự","Ử","Ữ",
			"Ỳ","Ý","Ỵ","Ỷ","Ỹ",
			"Đ","Đ","'"
			);
		$marKoDau=array(
			/// Chữ thường
			"a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a",
			"e","e","e","e","e","e","e","e","e","e","e",
			"i","i","i","i","i",
			"o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o",
			"u","u","u","u","u","u","u","u","u","u","u",
			"y","y","y","y","y",
			"d","D","",
			//Chữ hoa
			"A","A","A","A","A","A","A","A","A","A","A","A","A","A","A","A","A",
			"E","E","E","E","E","E","E","E","E","E","E",
			"I","I","I","I","I",
			"O","O","O","O","O","O","O","O","O","O","O","O","O","O","O","O","O",
			"U","U","U","U","U","U","U","U","U","U","U",
			"Y","Y","Y","Y","Y",
			"D","D","",
			);
		return str_replace($marTViet, $marKoDau, $mystring);
	}
}
