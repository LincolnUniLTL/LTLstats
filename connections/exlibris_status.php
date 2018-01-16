<?php
$alma = "Alma AP01";			// YOUR ALMA SERVER as it appears in the http://status.exlibrisgroup.com/ list
$primo = "Primo MT APAC01";		// YOUR PRIMO SERVER as it appears in the http://status.exlibrisgroup.com/ list
$url = "https://status.exlibrisgroup.com/?page_id=5511";
$envs = str_replace(' ', '+', $alma.','.$primo);

require_once(dirname(dirname(__FILE__)).'\\cache.php');

function getStatus($url,$envs) {
	$result = checkCache($url.$envs,60);
	if (!$result) {
		$data = "act=get_status&client=xml&envs=".$envs;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($ch);
		curl_close($ch);
		if (!$result) {
			$result = getCache($url.$envs);
		} else {
			createCache($url.$envs,$result);
		}
	}
	return $result;
}

$status = simplexml_load_string(getStatus($url,$envs));

foreach($status->instance as $i) {
	if ($i->attributes()->service == "Alma") {
		$status_A = $i->attributes()->status;
		$note_A = (string)($i->schedule);
	}
	if ($i->attributes()->service == "Primo") {
		$status_P = $i->attributes()->status;
		$note_P = (string)($i->schedule);
	}
}

$patterns = array('/OK/','/PERF/','/ERROR/','/MAINT/','/SERVICE/');
$replacements = array('Service is operating normally','Performance issues','Service disruption','Scheduled maintenance','Service is operating normally but note: ');
$status_A = preg_replace($patterns,$replacements,$status_A);
$status_P = preg_replace($patterns,$replacements,$status_P);
$note_A = preg_replace('/\s/',' ',$note_A);
$note_P = preg_replace('/\s/',' ',$note_P);

$pattern = '/(\d*-\w{3}-\d{4} UTC \d*:\d{2}:\d{2})(.*)/';
if (preg_match($pattern,$note_A,$match_A)) {
	$note_A = explode('UTC', $match_A[2]);
	$note_A = $note_A[count($note_A)-1];
	$note_A = $match_A[1]. ' ' . $note_A;
	$note_A = strip_tags($note_A);
	$note_A = preg_replace('/[^a-zA-Z0-9:.,-]/',' ',$note_A);
}
if (preg_match($pattern,$note_P,$match_P)) {
	$note_P = explode('UTC', $match_P[2]);
	$note_P = $note_P[count($note_P)-1];
	$note_P = $match_P[1]. ' ' . $note_P;
	$note_P = strip_tags($note_P);
	$note_P = preg_replace('/[^a-zA-Z0-9:.,-]/',' ',$note_P);
}

// You can now refer to $status_A / $status_P and $note_A / $note_P
?>