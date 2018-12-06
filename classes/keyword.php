<?
class replace_keyword{
	
	protected $str_soucre;
	protected $str_lower;
	protected $keyword_src;
	protected $keyword_lower;
	protected $keyword_noaccent;
	protected $keyword_len = 0;
	protected $from_pos 	= 0;
	protected $finish 	= false;
	protected $link;
	protected $arrayReplace = array(",",".",".",">","<",":","!","&",'"',"`");
	protected $charBreak		= array(" ","(",")",">","<");
	protected $removeAccentKeyword = 0;
	
	
	function __construct($string, $keyword, $link, $removeAccentKeyword = 0){
		$string							= $this->clean($string);
		$this->link						= $link;
		$this->str_soucre 			= $string;	
		$this->keyword_lower			= mb_strtolower($keyword, "UTF-8");
		$this->keyword_lower			= str_replace(
																$this->arrayReplace
																," ", $this->keyword_lower);
		$this->str_lower				= mb_strtolower($string, "UTF-8");
		$this->keyword_len			= mb_strlen($keyword,"UTF-8");
		$this->str_lower				= str_replace(
																$this->arrayReplace
																," ", $this->str_lower);
		$position 						= $this->getPosition();
		$this->removeAccentKeyword = $removeAccentKeyword;
		//truong hop co dau khong replace duoc thi chon khong dau
		if($position == 0 && $removeAccentKeyword == 0){
			$this->from_pos			= 0;
			$this->str_lower			= removeAccent($this->str_lower);
			$this->keyword_lower		= removeAccent($this->keyword_lower);
			$this->finish				= false;
			$this->getPosition();
		}																
		
	}
	
	protected function clean($string){
		return $string;
		//$string	=	str_replace(array("<b>","<strong>"), '<font style="font-weight: bold;">', $string);
//		$string	=	str_replace(array("</b>","</strong>"), '</font>', $string);
		
	}
	

	protected function getPosition(){
		
		//neu da ket thuc roi thi return luon
		if($this->finish) return $this->from_pos;
		
		//lay ra vi tri tu khoa dau tien
		$position	  = mb_strpos($this->str_lower, " " . $this->keyword_lower . " ", $this->from_pos,"UTF-8");
		if(!$position){
			$position	  = mb_strpos($this->str_lower, "(" . $this->keyword_lower . ")", $this->from_pos,"UTF-8");
		}
		if($position !== false){
			$position = $position + 1;
			//gan lai vi tri khoi dau
			$this->from_pos 	= $position + $this->keyword_len ;
			//lay vi tri dau >< tiep theo cua tu khoa day
			$pos_gt		  = intval(mb_strpos($this->str_soucre, ">", $this->from_pos,"UTF-8"));
			$pos_lt		  = intval(mb_strpos($this->str_soucre, "<", $this->from_pos,"UTF-8"));
			//echo $pos_gt . '<' . $pos_lt . '<hr>';
			if($pos_gt < $pos_lt || ($pos_gt > 0 && $pos_lt == 0)){
				$this->finish		= false;
				return $this->getPosition();
			}else{
				$this->finish 		=	true;
				$this->keyword_src = mb_substr($this->str_soucre, $position, $this->keyword_len,"UTF-8");
			}
			return $position;
			
		}
		$this->finish 				=	true;
		return 0;
	}
	
	protected function getKeyReplace(){
		$keyword = $this->keyword_src;
		if($this->removeAccentKeyword == 1) $keyword = removeAccent($keyword);
		return '<a href="' . $this->link . '"  itemprop="url" style="color: inherit;" target="_blank" class="keyword_wiki">' . $keyword . '</a>';
	}
	
	function getHtml(){
		$position = $this->getPosition();
		$keyword  = $this->getKeyReplace();
		if($position <= 0) return $this->str_soucre;
		$str_before = mb_substr($this->str_soucre, 0, $position - $this->keyword_len,"UTF-8");
		$str_after	= mb_substr($this->str_soucre, $position, mb_strlen($this->str_soucre, 'UTF-8'),"UTF-8");
		$html			= $str_before . $keyword . $str_after;
		return $html;
	}
	
}

/**
$string = file_get_contents("text.txt");
$keyword	= "vatgia.com";

$myKeyword = new replace_keyword($string, $keyword,"http://vatgia.com");
//$position  = $myKeyword->getPosition();
echo $myKeyword->getHtml();
*/