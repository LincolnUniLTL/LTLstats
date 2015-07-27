<?php
	function getAltmetrics($path_array) {
		$url = "http://www.altmetric.com/api/v1/summary_report/at?num_results=100&key=" ;
		$api_key = "";						// API KEY
		$rowset = array();
		foreach ($path_array as $path) {
			$this_url = $url . $api_key . $path;
			$result = checkCache($this_url);
			if (!$result) {
				$result = getData($this_url);
				$rowset[] = decodeAltmetrics($result);
				if (!$rowset) {
					$result = getCache($this_url);
					$rowset[] = decodeAltmetrics($result);
				} else {
					createCache($this_url,$result);
				}
			} else {
				$rowset[] = decodeAltmetrics($result);
			}
		}
		return $rowset;
	}

	function decodeAltmetrics($stuff) {
		$result = json_decode($stuff, true);
		$rowset = $result['mentions_by_date'];
		foreach ($rowset as $r => $row) {
			$rowset[$r]['date'] = preg_replace('/-\d*T\d*:\d*:\d*\.\d*Z/','',$row['date']);
		}
		return $rowset;
	}
?>