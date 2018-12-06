<?php
define ( "FREQ_THRESHOLD", 40 );
define ( "SUGGEST_DEBUG", 0);
define ( "LENGTH_THRESHOLD", 2 );
define ( "LEVENSHTEIN_THRESHOLD", 2 );
define ( "TOP_COUNT", 1 );

function BuildTrigrams($keyword) {
    $t = "__" . $keyword . "__";
    $trigrams = "";
    for ($i = 0; $i < mb_strlen($t,"UTF-8") - 2; $i++)
        $trigrams .= mb_substr($t, $i, 3, "UTF-8") . " ";
    return $trigrams;
}

function BuildPhraseTrigrams($keyword) {
	$keyword = str_replace(' ','_',$keyword);
	    $t =  $keyword ;
    $trigrams = "";
    for ($i = 0; $i < mb_strlen($t,"UTF-8") - 2; $i++)
        $trigrams .= mb_substr($t, $i, 3, "UTF-8") . " ";
		
    return $trigrams;
}

function MakeSuggestion($keyword) {
    $trigrams = BuildTrigrams($keyword);
    $query = "\"$trigrams\"/1";
    $len = mb_strlen($keyword, "UTF-8");

    $delta = LENGTH_THRESHOLD;
    $cl = new SphinxClient ();
    $cl->SetServer("127.0.0.1", 9312);
    $cl->SetMatchMode(SPH_MATCH_EXTENDED2);
    $cl->SetRankingMode(SPH_RANK_WORDCOUNT);
    $cl->SetFilterRange("len", $len - $delta, $len + $delta);
    $cl->SetSelect("*, @weight+$delta-abs(len-$len) AS myrank");
    $cl->SetSortMode(SPH_SORT_EXTENDED, "myrank DESC");
    $cl->SetArrayResult(true);

    // pull top-N best trigram matches and run them through Levenshtein
    $res = $cl->Query($query, "suggest", 0, TOP_COUNT);
	 if (!$res || !isset($res["matches"]))
        return false;

  	 $arrayReturn = array();
    // further restrict trigram matches with a sane Levenshtein distance limit
    $keyword = removeAccent($keyword);
    foreach ($res["matches"] as $match) {
        $suggested = $match["attrs"]["keyword_noaccent"];
        $suggested_source = $match["attrs"]["keyword"];
        $suggested_source = str_replace("br ","", $suggested_source);
        //if (levenshtein($keyword, $suggested) <= 10 || levenshtein($keyword, $suggested_source) <= 10)
            $arrayReturn[] = trim($suggested_source);
    }
    if(!empty($arrayReturn)){
    	return $arrayReturn;
    }
    return false;
}

function didYouMean($arrayKeyword, $keyword){
	$keyword = trim(removeAccent($keyword));
	foreach ($arrayKeyword as $key => $suggested) {
        if (levenshtein($keyword, trim(removeAccent($suggested))) <= LEVENSHTEIN_THRESHOLD)
            return $suggested;
   }
	return ''; 
}

function cleanKeyword($keyword){
	$keyword = str_replace("br","", $keyword);
	$keyword = str_replace("  "," ", $keyword);
	return $keyword;
}

function searchKeywordProduct($keyword, $limit_from = 0, $page_size = 30){
	$sphinx	= new SphinxClient();
	$sphinx->SetServer( "127.0.0.1", 9312 );
	$sphinx->SetArrayResult(true); 
	$indexes = 'products';
	$delta	= 5;
	$q 		= mb_strtolower($keyword, 'UTF-8');
	$len		= mb_strlen($keyword, 'UTF-8');
	$query	= $sphinx->EscapeString($q);
	$sphinx->SetRankingMode(SPH_RANK_SPH04);
	//$sphinx->SetFilterRange("len", $len - $delta, $len + $delta);
	$sphinx->SetMatchMode(SPH_MATCH_ALL);
	$sphinx->setFieldWeights(array('pro_name' => 1000, 'pro_search' => 1));
	$sphinx->SetLimits($limit_from, $page_size);
	$sphinx->SetSortMode(SPH_SORT_EXTENDED, "@weight DESC,@id desc");
	$result	= $sphinx->Query($query, $indexes);
	return $result;
}

function searchKeywordSuggest($keyword, $limit_from = 0, $page_size = 30){
	$sphinx	= new SphinxClient();
	$sphinx->SetServer( "127.0.0.1", 9312 );
	$sphinx->SetArrayResult(true); 
	$indexes = 'products';
	$delta	= 5;
	$q 		= mb_strtolower($keyword, 'UTF-8');
	$len		= mb_strlen($keyword, 'UTF-8');
	$query	= $sphinx->EscapeString($q);
	//$sphinx->SetRankingMode(SPH_RANK_SPH04);
	//$sphinx->SetFilterRange("len", $len - $delta, $len + $delta);
	$sphinx->SetMatchMode(SPH_MATCH_ANY);
	$sphinx->setFieldWeights(array('pro_name' => 3000, 'pro_search' => 1));
	$sphinx->SetLimits($limit_from, $page_size);
	$sphinx->SetSortMode(SPH_SORT_EXTENDED, "@weight DESC,@id desc");
	$result	= $sphinx->Query($query, $indexes);
	return $result;
}

function searchRelateProduct($keyword, $limit_from = 0, $page_size = 20){
	$sphinx	= new SphinxClient();
	$sphinx->SetServer( "127.0.0.1", 9312 );
	$sphinx->SetArrayResult(true); 
	$indexes = 'products';
	$delta	= 5;
	$q 		= mb_strtolower($keyword, 'UTF-8');
	$len		= mb_strlen($keyword, 'UTF-8');
	$query	= $sphinx->EscapeString($q);
	$sphinx->SetRankingMode(SPH_RANK_SPH04);
	//$sphinx->SetFilterRange("len", $len - $delta, $len + $delta);
	$sphinx->SetMatchMode(SPH_MATCH_ANY);
	$sphinx->setFieldWeights(array('pro_name' => 2000, 'pro_search' => 1));
	$sphinx->SetLimits($limit_from, $page_size);
	$sphinx->SetSortMode(SPH_SORT_EXTENDED, "@weight DESC,@id desc");
	$result	= $sphinx->Query($query, $indexes);
	return $result;
}

function MakePhaseSuggestion($keywords) {
    $trigrams = BuildPhraseTrigrams($keywords);
    $query = "\"$trigrams\"/1";
    $cl = new SphinxClient ();
    $cl->SetServer("127.0.0.1", 9352);
    $cl->SetMatchMode(SPH_MATCH_EXTENDED2);
    $cl->SetRankingMode(SPH_RANK_WORDCOUNT);
    $cl->SetSortMode(SPH_SORT_EXTENDED, "@weight DESC,cnt desc");
    $cl->SetArrayResult(true);
    $res = $cl->Query($query, "historical", 0, 1);

    if (!$res || $res['total_found'] == 0)
        return false;

    return $res['matches'][0]['attrs']['query_string'];
}

function QueryToHistory($query,$ln,$ln_sph) {
	$cl = new SphinxClient ();
    $cl->SetServer("127.0.0.1", 9352);
	$keywords = $cl->BuildKeywords($query,'historical',false);
	$keys = array();
	foreach($keywords as $k)
	{
		$keys[] = $k['normalized'];
	}
	$keys = implode(' ' ,$keys);
	$keyscrc = crc32($keys);
	if($keyscrc < 0) $keyscrc += 4294967296; //stupid bug on 64bit machines
    $q = "SELECT * FROM historical WHERE query='" . $keys . "' LIMIT 1";
    $r = mysqli_query($ln, $q);
    if (mysqli_num_rows($r) > 0) {
        $row = mysqli_fetch_array($r, MYSQLI_ASSOC);
        $q = "UPDATE historical set count=count+1 WHERE id=" . $row['id'];
        mysqli_query($ln, $q);
       $q = "REPLACE INTO historical (id,query,query_string,cnt)  VALUES('" . $keyscrc. "','" . $keys . "','" . $keys . "'," . ($row['count'] + 1) . ")";
        mysqli_query($ln_sph, $q);
    } else {
        $q = "INSERT INTO  historical(query,count) VALUES('" . $query . "',1)";
        mysqli_query($ln, $q);
        $id = mysqli_insert_id($ln);
        $q = "INSERT INTO  historical (id,query,query_string,cnt) VALUES('$keyscrc','" . $keys . "','" . $keys . "',1)";
        mysqli_query($ln_sph, $q);
    }
}
?>