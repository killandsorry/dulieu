<?
function translate_text($variable){
	global $isSavelang;
   global $langTranslate;
   global $langDefault;
   global $checkLangDefault;
   if(!isset($langTranslate)) $langTranslate = array();
   $string = trim($variable);
   $string = str_replace(array('"', "'"), '', $string);
   $key    = md5($string);
   
   if($checkLangDefault){
      if(!isset($langDefault[$key])){
         $langDefault[$key] = $variable;
         $isSavelang = true;       
      }  
   }
   
   if(!isset($langTranslate[$key])){
      $langTranslate[$key] = $variable;
      return $variable;
   }else{
      if($langTranslate[$key] != ''){
         return $langTranslate[$key];
      }else{
         return $variable;
      }
   }
   
}
function defineLang(){
   $arrayLang = array(      
      "vi" => "Tiếng Viêt",
      "en" => "Tiếng Anh"
   );
   return $arrayLang;
}
function saveLang($mylang = "default"){
   global $langTranslate,$isSavelang, $langDefault;
   if(isset($isSavelang)){
      //echo 'save';
      $path = dirname(__FILE__);
      $filelang = $path . "/../lang/$mylang.lang";
      if(file_exists($filelang)){         
         @file_put_contents($filelang,json_encode($langDefault));
      }    
      
   }
}
function saveConfigLang($mylang = '', $langtext = array()){
   if($mylang != '' && !empty($langtext)){
      $path = dirname(__FILE__);
      $filelang = $path . "/../lang/$mylang.lang";
      if(file_exists($filelang)){         
         @file_put_contents($filelang,json_encode($langtext));
      } 
   }
}
function setLang($mylang = "en"){
   global $langTranslate;
   $path = dirname(__FILE__);
   $filelang = $path . "/../lang/" . $mylang . ".lang";
   if(file_exists($filelang)){
      $langTranslate = json_decode(file_get_contents($filelang),true);     
   }
   return $langTranslate;
}
function translate($variable){

	$variable = trim($variable);
	$variable = str_replace("\'","'",$variable);
	$variable = str_replace("'","''",$variable);
	global $lang_display;
	if (isset($lang_display[md5(trim($variable))])){
		if($lang_display[md5(trim($variable))] !=''){
			return $lang_display[md5(trim($variable))];
		}else{
			return "";
		}
	}
	else{
		return $variable;
	}
}

function translate_string($string){
	return trim($string);
}

function tt($variable){
	return "" . $variable . "";
}
?>