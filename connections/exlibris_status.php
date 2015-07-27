<?php
$alma = "Alma AP01";			// YOUR ALMA SERVER as it appears in the http://status.exlibrisgroup.com/ list
$primo = "Primo MT APAC01";		// YOUR PRIMO SERVER as it appears in the http://status.exlibrisgroup.com/ list
$url = "http://status.exlibrisgroup.com/";

require_once(dirname(dirname(__FILE__)).'\\cache.php');

$html = checkCache($url,60);	// Caches the status for 60 seconds
if (!$html) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$html = curl_exec($ch);
	curl_close($ch);
	if (!$html) {
		$html = getCache($url);
	} else {
		createCache($url,$html);
	}
}

$dom = new DOMDocument;
$dom->loadHTML($html);
$items = $dom->getElementsByTagName('td');
for ($i=0; $i<$items->length; $i++) {
	if ($items->item($i)->nodeValue == $alma) {
		foreach ($items->item($i+2)->childNodes as $j) {
	                if ($j->nodeType != XML_TEXT_NODE) {
				$status_A = $j->getAttribute('class');
				$note_A = $j->nodeValue;
			}
		}
	}
	if ($items->item($i)->nodeValue == $primo) {
		foreach ($items->item($i+2)->childNodes as $j) {
	                if ($j->nodeType != XML_TEXT_NODE) {
				$status_P = $j->getAttribute('class');
				$note_P = $j->nodeValue;
			}
		}
	}
}

$patterns = array('/icon-1/','/icon-2/','/icon-3/','/icon-4/','/icon-5/');
$replacements = array('Service is operating normally','Performance issues','Service disruption','Scheduled maintenance','Service is operating normally but note: ');
$status_A = preg_replace($patterns,$replacements,$status_A);
$status_P = preg_replace($patterns,$replacements,$status_P);

$pattern = '/(\d*-\w{3}-\d{4} UTC \d{2}:\d{2}:\d{2})(.*)/';
if (preg_match($pattern,$note_A,$match_A)) {
	$note_A = $match_A[2];
}
	$note_A = strip_tags($note_A);
	$note_A = preg_replace('/[^a-zA-Z0-9:.,-]/',' ',$note_A);
if (preg_match($pattern,$note_P,$match_P)) {
	$note_P = $match_P[2];
}
	$note_P = strip_tags($note_P);
	$note_P = preg_replace('/[^a-zA-Z0-9:.,-]/',' ',$note_P);

// You can now refer to $status_A / $status_P and $note_A / $note_P
?>