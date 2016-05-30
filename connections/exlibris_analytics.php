<?php
	function getAlmaRows($path) {
		global $alma_url, $alma_apikey, $alma_limit;
		$queryParams = '?' . urlencode('path') . '=' . urlencode($path) . '&' . urlencode('limit') . '=' . urlencode($alma_limit) . '&' . urlencode('apikey') . '=' . urlencode($alma_apikey);
		$url = $alma_url . $queryParams;
		$rowset = getRowset($url,'decodeExLibris');
		return $rowset;
	}

	function getPrimoRows($path) {
		global $primo_url, $primo_apikey, $primo_limit;
		$queryParams = '?' . urlencode('path') . '=' . urlencode($path) . '&' . urlencode('limit') . '=' . urlencode($primo_limit) . '&' . urlencode('apikey') . '=' . urlencode($primo_apikey);
		$url = $primo_url . $queryParams;
		$rowset = getRowset($url,'decodeExLibris');
		return $rowset;
	}

	function decodeExLibris($result) {
		$XML = simplexml_load_string($result);
		$XML->registerXPathNamespace('rowset', 'urn:schemas-microsoft-com:xml-analysis:rowset');
		if ($XML->xpath('/report/QueryResult/ResultXml/rowset:rowset')) {
			$rowset = $XML->xpath('/report/QueryResult/ResultXml/rowset:rowset/rowset:Row');
			$rowset = obj2arr($rowset);
		} else {
			$rowset = false;
		}
		return $rowset;
	}
?>