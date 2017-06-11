<?php
	function getMRBSRows($from,$to) {
		global $mrbs_url;
		$url = $mrbs_url.'report.php?areamatch=&roommatch=&typematch%5E%5D=I&typematch%5B%5D=I&namematch=&descrmatch=&creatormatch=&match_private=2&match_approved=2&summarize=6&sortby=s&display=d&sumby=c';
		$url .= '&From_day=' . substr($from,8,2);
		$url .= '&From_month=' . substr($from,5,2);
		$url .= '&From_year=' . substr($from,0,4);
		$url .= '&To_day=' . substr($to,8,2);
		$url .= '&To_month=' . substr($to,5,2);
		$url .= '&To_year=' . substr($to,0,4);
		$rowset = getRowset($url,'decodeMRBS');
		return $rowset;
	}

	function decodeMRBS($result) {
		$result = csv2array($result);
		$rows = count($result);
		$columns = count($result[0]);
		$rowset['rooms'] = ($columns - 3) / 2;
		$hours = $result[$rows-2][$columns-1];
		$hours = preg_replace('/"([0-9.]*)0"/','$1',$hours);
		$hours = preg_replace('/\.0/','',$hours);
		$rowset['hours'] = $hours;
		array_shift($result);
		array_pop($result);
		foreach ($result as $r => $row) {
			$users[] = $row[0];
		}
		$users = array_map('strtolower', $users); 
		$users = array_unique($users);
		$rowset['users'] = count($users);
		return $rowset;
	}
?>