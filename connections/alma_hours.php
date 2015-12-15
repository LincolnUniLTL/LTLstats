<?php
	function getAlmaHours() {
		global $alma_hours_url, $alma_hours_apikey, $alma_hours_scope;
		$queryParams = '?' . urlencode('scope') . '=' . urlencode($alma_hours_scope) . '&' . urlencode('apikey') . '=' . urlencode($alma_hours_apikey);
		$url = $alma_hours_url . $queryParams;
		$rowset = getRowset($url,'decodeAlmaHours');
		return $rowset;
	}

	function decodeAlmaHours($result) {
		$object = simplexml_load_string( $result , null , LIBXML_NOCDATA );
		$json = json_encode($object);
		$hours = json_decode($json,TRUE);
		$hours = $hours['open_hour'];
		$calendar = array();
		for ($i=-30; $i<90; $i++) {
//		for ($i=0; $i<1; $i++) {
			$test = '2015-12-12'; // many weird exceptions
			$test = '2015-11-03'; // longer hours
			$test = '2015-10-01'; // standard hours
			$test = '2015-11-29'; // last Sunday - closed
			$test = '2015-12-02'; // today - summer hours
			$test = '2015-12-25'; // Christmas - works (overriding other exceptions)
//			$date = strtotime($test) + $i*(24*60*60);
			$date = time() + $i*(24*60*60);
			$calendar[$i]['date'] = date('Y-m-d', $date);
			$calendar[$i]['day_of_week'] = strtoupper(date('l', $date));
		}
		foreach($hours as $h) {
			if ($h['type'] == "WEEK") {
				foreach ($calendar as $i => $c) {
					if ($h['day_of_week'] == $c['day_of_week']) {
						if ($h['status'] == "OPEN") {
							$calendar[$i]['from_hour'] = $h['from_hour'];
							$calendar[$i]['to_hour'] = $h['to_hour'];
							$calendar[$i]['status'] = "OPEN";
						} else {
							$calendar[$i]['status'] = "CLOSE";
						}
					}
				}
			} else if ($h['type'] == "EXCEPTION") {
				$from_date = strtotime(substr($h['from_date'],0,10));
				$to_date = strtotime(substr($h['to_date'],0,10));
				$from_hour = strtotime($h['from_hour']);
				$to_hour = strtotime($h['to_hour']);
				foreach ($calendar as $i => $c) {
					$this_day = strtotime($c['date']);
					$this_from_hour = strtotime($c['from_hour']);
					$this_to_hour = strtotime($c['to_hour']);
					if (($this_day >= $from_date) && ($this_day <= $to_date)) {
						if (($h['day_of_week'] == $c['day_of_week']) || !$h['day_of_week']) {
							if (($h['from_hour'] == "00:00") && ($h['to_hour'] == "23:59") && ($h['status'] == "CLOSE")) {
								$calendar[$i]['from_hour'] = "";
								$calendar[$i]['to_hour'] = "";
								$calendar[$i]['status'] = "CLOSE";
								$calendar[$i]['exception'] = "";
//								echo "<p>Closed.</p>";
							} else if ($from_hour <= $this_from_hour && $h['status'] == "CLOSE" && $c['status'] == "OPEN") {
								$calendar[$i]['from_hour'] = $h['to_hour'];
//								echo "<p>From " . $calendar[$i]['from_hour'] . " to " . $calendar[$i]['to_hour'] . "</p>";
							} else if ($to_hour >= $this_to_hour && $h['status'] == "CLOSE" && $c['status'] == "OPEN") {
								$calendar[$i]['to_hour'] = $h['from_hour'];
//								echo "<p>From " . $calendar[$i]['from_hour'] . " to " . $calendar[$i]['to_hour'] . "</p>";
							} else if ($from_hour < $this_from_hour && $h['status'] == "OPEN" && $c['status'] == "OPEN") {
								$calendar[$i]['from_hour'] = $h['from_hour'];
//								echo "<p>From " . $calendar[$i]['from_hour'] . " to " . $calendar[$i]['to_hour'] . "</p>";
							} else if ($to_hour > $this_to_hour && $h['status'] == "OPEN" && $c['status'] == "OPEN") {
								$calendar[$i]['to_hour'] = $h['to_hour'];
//								echo "<p>From " . $calendar[$i]['from_hour'] . " to " . $calendar[$i]['to_hour'] . "</p>";
							} else {
//								$calendar[$i]['exception'] = "EXCEPT FOR EXCEPTIONS";
//								echo "<p><strong>Exception:</strong> " . date('Y-m-d', $from_date) . " &mdash; " . date('Y-m-d', $to_date) . "</p>";
							}
						}
					}
				}

			}
		}
		return $calendar;
	}
?>