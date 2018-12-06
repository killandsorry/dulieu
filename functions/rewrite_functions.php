<?
function removeTitle($string,$keyReplace = "/"){
	 $string = removeAccent($string);
	 $string  =  trim(preg_replace("/[^A-Za-z0-9]/i"," ",$string)); // khong dau
	 $string  =  str_replace(" ","-",$string);
	 $string = str_replace("--","-",$string);
	 $string = str_replace("--","-",$string);
	 $string = str_replace("--","-",$string);
	 $string = str_replace("--","-",$string);
	 $string = str_replace("--","-",$string);
	 $string = str_replace("--","-",$string);
	 $string = str_replace("--","-",$string);
	 $string = str_replace($keyReplace,"-",$string);
	 return strtolower($string);
}
function create_link_download_cv($file = ''){
   if($file == '') return '';
   
   if(isset($_SERVER) && $_SERVER['SERVER_NAME'] == 'localhost'){
      return 'http://localhost:8890/upload/cv/' . $file;
   }else{
      return 'http://1viec.com/upload/cv/' . $file;
   }
}

function create_link_group_fb($data = array()){
   $url  = '';
   $id   = isset($data['id'])? intval($data['id']) : 0;
   $name = isset($data['name'])? ($data['name']) : '';
   $url  = '/tim-viec-nhanh/' . removeTitle($name) . '-group' . $id . '.html';
   
   return $url;
}

function create_link_job_fb($data = array()){
   $url  = '';
   $id   = isset($data['jobf_id'])? intval($data['jobf_id']) : 0;
   $name = isset($data['jobf_use_name'])? ($data['jobf_use_name']) : '';
   $url  = '/tim-viec-nhanh/' . removeTitle($name) . '-id' . $id . '.html';
   
   return $url;
}

function create_link_job_user_fb($data = array()){
   $url  = '';
   $id   = isset($data['jobf_use_id'])? ($data['jobf_use_id']) : 0;
   $name = isset($data['jobf_use_name'])? ($data['jobf_use_name']) : '';
   $url  = '/tim-viec-nhanh/detail_user-fbid' . $id . '.html';
   
   return $url;
}

function create_link_filter_fb($data = array()){
   $url = '';
   $city_name  = (isset($data['cit_name']) && $data['cit_name'] != '') ? 'tai-' . removeTitle($data['cit_name']) : '';
   $city_id    = isset($data['cit_id'])? intval($data['cit_id']) : 0;
   
   $cal_name  = (isset($data['cal_name']) && $data['cal_name'] != '')? removeTitle($data['cal_name']) . '-' : '';
   $cal_id    = isset($data['cal_id'])? intval($data['cal_id']) : 0;
   
   $url  = '/tim-viec-nhanh/viec-lam-' . ($cal_name) . ($city_name) . '-ci'. $city_id.'cal'.$cal_id.'.html';
   
   if($city_id == 0 && $cal_id == 0){
      $url  = '/tim-viec-nhanh';
   }
   
   return $url;
}

function createLink_viewcv($id = 0){
   return '/cv/preview?u='.$id;
}

function vl_url_category($cat = array()){
	$url		= '#';
	$cid		= isset($cat['cat_id'])? intval($cat['cat_id']) : 0;
	$cname	= isset($cat['cat_name'])? ($cat['cat_name']) : '';

	if($cid <= 0 || $cname == '') return $url;

	$url	= '/' . removeTitle($cname) . '-c' . $cid ;
	return $url;
}

function vl_url_keysearch($cat = array()){
	$url		= '#';
	$cid		= isset($cat['cal_id'])? intval($cat['cal_id']) : 0;
	$cname	= isset($cat['cal_name'])? ($cat['cal_name']) : '';

	if($cid <= 0 || $cname == '') return $url;

	$url	= '/' . removeTitle($cname) . '-search' . $cid;
	return $url;
}

// url tags
function createlink_tags($row = array()){
   $id   = isset($row['tag_id'])? intval($row['tag_id']) : 0;
   $tag_name   = isset($row['tag_name'])? ($row['tag_name']) : '';
   
   $url     = '/tags/' . removeTitle($tag_name) . '-tag' . $id ;
   return $url;
}

// url cat user
function createLink_catuser($row = array()){
   $id   = isset($row['cat_id'])? intval($row['cat_id']) : 0;
   $cat_name   = isset($row['cat_name'])? ($row['cat_name']) : '';
   
   $url     = '/nguoi-tim-viec/' . removeTitle($cat_name) . '-ca' . $id ;
   return $url;
}

// create link school
function createLinkSchool($row   = array()){
   $id   = isset($row['sch_id'])? intval($row['sch_id']) : 0;
   $school_name   = isset($row['sch_name'])? ($row['sch_name']) : '';
   
   $url     = '/' . removeTitle($school_name) . '-school' . $id ;
   return $url;
}

 // function generate link detail
function createLinkJob($row = array()){
	global $arrayCategory;
	//$url_module	= 'tim-viec-lam';
	//$job_cat_id	= isset($row['job_cat_id'])? intval($row['job_cat_id']) : 0;
	//$cat_name	= isset($arrayCategory[$job_cat_id])? $arrayCategory[$job_cat_id]['cat_name'] : 'category';
	$job_id		= (isset($row['job_id']) && $row['job_id'] > 0)? intval($row['job_id']) : 0;
	$job_name	= (isset($row['job_name']) && $row['job_name'] != '')? removeTitle($row['job_name'], '/') : 'category';
	//$url			= '/'. $url_module . '/' . removeTitle($cat_name) . '/' . $job_name . '-vl'. $job_id .'.html';
	$url			= '/' . $job_name . '-v'. $job_id;
	return $url;
}

 // function generate link detail
function createLinkJob_timviecnhanh($row = array()){
	global $arrayCategory;
	//$url_module	= 'tim-viec-lam';
	//$job_cat_id	= isset($row['job_cat_id'])? intval($row['job_cat_id']) : 0;
	//$cat_name	= isset($arrayCategory[$job_cat_id])? $arrayCategory[$job_cat_id]['cat_name'] : 'category';
	$job_id		= (isset($row['job_id']) && $row['job_id'] > 0)? intval($row['job_id']) : 0;
	$job_name	= (isset($row['job_name']) && $row['job_name'] != '')? removeTitle($row['job_name'], '/') : 'category';
	//$url			= '/'. $url_module . '/' . removeTitle($cat_name) . '/' . $job_name . '-vl'. $job_id .'.html';
	$url			= '/viec-lam/'. $job_id .'/' . $job_name . '.html';
	return $url;
}

function createUrl_redirec($row = array()){
   $job_id		= (isset($row['job_id']) && $row['job_id'] > 0)? intval($row['job_id']) : 0;
	$job_name	= (isset($row['job_name']) && $row['job_name'] != '')? removeTitle($row['job_name'], '/') : 'category';
	//$url			= '/'. $url_module . '/' . removeTitle($cat_name) . '/' . $job_name . '-vl'. $job_id .'.html';
	$url			= '/' . $job_name . '/'. $job_id . '/direct.html';
	return $url;
}

// function generate link city
function createLinkCit($row = array()){
	$cit_id		= (isset($row['cit_id']) && $row['cit_id'] > 0)? intval($row['cit_id']) : 1;
	$cit_name	= (isset($row['cit_name']) && $row['cit_name'] != '')? removeTitle($row['cit_name'], '/') : 'ha-noi';
	$url			= '/tim-viec-lam-tai-' . $cit_name . '-ci'. $cit_id;
	return $url;
}

// function generate link company
function createLinkCompany($row = array()){
   $name       = isset($row['name'])? $row['name'] : '';
   if($name == ''){
      if(isset($row['com_name'])) $name   = $row['com_name'];
   }
   
   $id         = (isset($row['id']) && $row['id'] > 0)? intval($row['id']) : 0;
   if($id   <= 0){
      if(isset($row['com_id'])) $id = intval($row['com_id']);
   }
   
	$com_id		= $id;
	$com_name	= removeTitle($name, '/');
	$url			= '/' . $com_name . '-co'. $com_id ;
	return $url;
}

// create url view profile
function createLinkViewProfile($id = 0){
	if($id == 0) return '#';
	return '/profile-view/p=' . $id;
}

// create link user
function createLinkUser($row = array()){
   $use_id  = isset($row['use_id'])? intval($row['use_id']) : 0;
   
   return $url  = '/profile-view/p=' . $use_id;
}

// url blog
function createLinkBlog($row = array()){
   $id   = isset($row['blo_id'])? intval($row['blo_id']) : 0;
   $blo_title   = isset($row['blo_title'])? ($row['blo_title']) : '';
   
   $url     = '/blog/' . removeTitle($blo_title) . '-blog' . $id ;
   return $url;
}

// url blog
function createLinkBlogCategory($row = array()){
   $id   = isset($row['cat_id'])? intval($row['cat_id']) : 0;
   $blo_title   = isset($row['cat_name'])? ($row['cat_name']) : '';
   
   $url     = '/blog/' . removeTitle($blo_title) . '-cat' . $id ;
   return $url;
}


function create_link_help($data = array()){
   $id = isset($data['hel_id'])? intval($data['hel_id']) : 0;
   $name = isset($data['hel_title'])? removeTitle($data['hel_title']) : '';
   $link = '/' . $name . '-help' . $id;
   return $link;
}