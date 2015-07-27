<?php
	function getLH3Rows($path) {
		$api = 'https://sg.libraryh3lp.com/2011-12-03/';	// modify to appropriate domain name
		$url = $api.$path;
		$result = checkCache($url);
		if (!$result) {
			$cookie = lh3cookie();
			if ($cookie) {
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				$result = curl_exec($ch);
				curl_close($ch);
				if (!preg_match('/^protocol/',$result)) {
					$cookies = dirname(dirname(__FILE__)) . '\cookies\libraryh3lp.txt';	// modify path as appropriate
					unlink($cookies);
					$result = false;
				}
			} else {
				$result = false;
			}
			if (!$result) {
				$result = getCache($url);
			} else {
				createCache($url,$result);
			}
		}
		return $result;
	}

	function lh3cookie() {
		$cookies = dirname(dirname(__FILE__)) . '\cookies\libraryh3lp.txt'; // modify path as appropriate
		if (file_exists($cookies)) {
			return $cookies;
		} else {
			$api = 'https://sg.libraryh3lp.com/2011-12-03/';	// modify to appropriate domain name
			$username = '';								// USERNAME
			$password = '';								// PASSWORD
			$url = $api.'auth/login';
			$data = array(
				'username' => $username,
				'password' => $password
			);
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
			$output = curl_exec($ch);
			curl_close($ch);
			if (file_exists($cookies)) {
				return $cookies;
			} else {
				return false;
			}
		}
	}
?>