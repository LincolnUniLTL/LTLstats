<?php
	function getAltmetric($path_array) {
		global $altmetric_url, $altmetric_apikey;
		$rowset = array();
		foreach ($path_array as $path) {
			$this_url = $altmetric_url . $altmetric_apikey . $path;
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