<?php
	function getSuperSaas($start_date) {
		global $supersaas_schedules, $supersaas_password, $supersaas_account;
		$checksum = md5($supersaas_account.$supersaas_password);
		$start_date = str_replace(" ","%20",$start_date);
		$rowset = array();
		foreach ($supersaas_schedules as $schedule) {
			$this_url = "https://www.supersaas.com/api/changes/".$schedule[0].".json?from=".$start_date."&checksum=".$checksum."&limit=1000";
			$rowset[] = array_merge(array($schedule[1]),getRowset($this_url,'decodeSuperSaas'));
		}
		return $rowset;
	}

	function decodeSuperSaas($json) {
		$result = json_decode($json, true);
		$users = array();
		$slots = array();
		$waitlisted = 0;
		foreach ($result['bookings'] as $b) {
			if ($b['waitlisted'] != "1" && $b['deleted'] != "1") {
				$users[] = $b['user_id'];
				$slots[] = $b['slot_id'];
			}
			$waitlisted += $b['waitlisted'];
		}
		$rowset[] = count(array_unique($slots));
		$rowset[] = count(array_unique($users));
		$rowset[] = count($result['bookings']);
		$rowset[] = $waitlisted;
		return $rowset;
	}


?>

