<?php
	function getWikiLinks($string) {
		$url = "https://en.wikipedia.org/w/api.php?action=query&list=search&srwhat=text&format=json&srsearch=insource%3A" . $string;
		$result = checkCache($url);
		if (!$result) {
			$result = getData($url);
			if (!$result) {
				$result = getCache($url);
			} else {
				createCache($url,$result);
			}
		}
		if (!$result) {
			$num = "[data unavailable]";
		} else {
			$rowset = json_decode($result, true);
			$num = $rowset['query']['searchinfo']['totalhits'];
		}
		$response = "<a href='https://en.wikipedia.org/w/index.php?search=insource%3A" . $string . "&title=Special%3ASearch&go=Go'>";
		$response = $response . $num . " Wikipedia pages</a>";
		return $response;
	}
?>