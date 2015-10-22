<?php
	function getWikiLinks($string) {
		$url = "https://en.wikipedia.org/w/api.php?action=query&list=search&srwhat=text&format=json&srsearch=insource%3A" . $string;
		$rowset = getRowset($url, 'decodeWikiLinks');
		return $rowset;
	}

	function decodeWikiLinks($result) {
		$JSON = json_decode($result, true);
		$rowset = array();
		$rowset['pages'] = $JSON['query']['searchinfo']['totalhits'];
		return $rowset;
	}
?>