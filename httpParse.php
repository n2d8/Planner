<?php
include('simple_html_dom.php');

$link = 'http://www.calendar.ubc.ca/vancouver/courses.cfm?page=institution';
$html = file_get_html($link);
$counter = false;
$rtn = array();
$fac = "";
foreach($html->find('td') as $element){
	$text =  $element->innertext;
	if(strpos($text, "Faculty")===0|| strpos($text, "Vancouver")===0|| strpos($text, "School")===0|| strpos($text, "Centre") ===0){
		$counter = true;
		$fac = $text;
	}else{
		if($counter){
			$matches = array();
			preg_match('/href=([a-zA-Z0-9]|\"|\.|\?|\=|&)*>/', $text, $matches);
			$href = $matches[0];
			$href = substr($href, 6, strlen($href)-8);
			$pre = substr($link, 0, 37);
			$html2 = file_get_html($pre.$href);
			$flag = 0;
			$abbr = "";
			$courses = array();
			foreach($html2->find('a') as $ele){
				$current = $ele->innertext;
				if($flag == 1){
					$courses[$abbr] = $current;
					$flag = 0;
				}
				if(isAbbr($current)){
					$abbr = $current;
					$flag = 1;
				}
			}
			$rtn[$fac] = $courses;
		}
		$counter = false;
	}
	//var_dump($rtn);
	$json_code = json_encode($rtn);
	$myfile = fopen("data.json", "w");
	fwrite($myfile, "[".json_encode($rtn)."]");
	fclose($myfile);
}

function isAbbr($string){
	$match = array();
	$len = strlen($string);
	if($len > 4 || $len < 3){
		return false;
	}
	preg_match('/[A-Z]*/', $string, $match);
	return $len == strlen($match[0]);
}


?>