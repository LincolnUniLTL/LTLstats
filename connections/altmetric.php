<?php
	function getAltmetric($path_array) {
		$url = "http://www.altmetric.com/api/v1/summary_report/at?num_results=100&key=" ;
		$api_key = "";						// API KEY
		$rowset = array();
		foreach ($path_array as $path) {
			$this_url = $url . $api_key . $path;
			$rowset[] = getRowset($this_url,'decodeAltmetric');
		}
		return $rowset;
	}

	function decodeAltmetric($stuff) {
		$result = json_decode($stuff, true);
		$rowset = $result['mentions_by_date'];
		foreach ($rowset as $r => $row) {
			$rowset[$r]['date'] = preg_replace('/-\d*T\d*:\d*:\d*\.\d*Z/','',$row['date']);
		}
		return $rowset;
	}
?>