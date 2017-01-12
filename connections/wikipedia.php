<?php
	function getWikiLinks($array) {
		foreach($array as $i => $item) {
			$url = "https://en.wikipedia.org/w/api.php?action=query&list=search&srwhat=text&format=json&srsearch=insource%3A%22" . urlencode($item[0]) . "%22";
			$array[$i][3] = getRowset($url, 'decodeWikiLinks');
		}
		return $array;
	}

	function decodeWikiLinks($result) {
		$JSON = json_decode($result, true);
		$rowset = array();
		$rowset = $JSON['query']['searchinfo']['totalhits'];
		return $rowset;
	}
?>