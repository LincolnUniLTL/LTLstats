<?php
	function getEZproxy() {
		global $proxy_audit_path;
		$filepath = $proxy_audit_path . date('Ymd') . ".txt";
		$handle = fopen($filepath, 'r');
		$result = array();
		while (($result[] = fgets($handle)) !== false) {}
		fclose($handle);
		$rowset = decodeEZproxy($result);
		return $rowset;
	}
	
	function decodeEZproxy($result) {
		global $proxy_groups;
		$logins = array();
		$groups = array();
		$logouts = array();
		$failures = array();
		foreach ($result as $l => $line) {
			$line = explode('	',$line);
			if (count($line) > 1) {
				if ($line[1] == "Login.Success" && !in_array($line[3],$logins)) {
					$logins[] = $line[3];
					foreach ($proxy_groups as $label => $group_names) {
						if (!isset($groups[$label])) {
								$groups[$label] = 0;
							}
						if ("|Groups  " . $group_names . "|" == trim($line[5])) {
							$groups[$label]++;
						}
					}
				}
				if ($line[1] == "Logout" && !in_array($line[3],$logouts)) $logouts[] = $line[3];
				if ($line[1] == "Login.Failure" && !in_array($line[3],$failures)) $failures[] = $line[3];
			}
		}
		$rowset = array();
		$rowset['logins'] = count($logins);
		$rowset['logouts'] = count($logouts);
		$rowset['failures'] = count($failures);
		$rowset['groups'] = $groups;
		return $rowset;
	}
	
?>