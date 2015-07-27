﻿<?php
	function getAlmaRows($path) {
		$url = 'https://api-ap.hosted.exlibrisgroup.com/almaws/v1/analytics/reports';	// modify to appropriate domain name
		$api_key = "";								// API KEY
		$queryParams = '?' . urlencode('path') . '=' . urlencode($path) . '&' . urlencode('limit') . '=' . urlencode('50') . '&' . urlencode('apikey') . '=' . urlencode($api_key);
		$url = $url . $queryParams;
		$result = checkCache($url);
		if (!$result) {
			$result = getData($url);
			$rowset = decodeAlma($result);
			if (!$rowset) {
				$result = getCache($url);
				$rowset = decodeAlma($result);
			} else {
				createCache($url,$result);
			}
		} else {
			$rowset = decodeAlma($result);
		}
		return $rowset;
	}

	function decodeAlma($result) {
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