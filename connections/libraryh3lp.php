<?php
	function getLH3Rows($path) {
		global $lh3_url, $lh3_cookies;
		$url = $lh3_url.$path;
		$header = array(
			'X-API-Version: 2011-12-03',
		);
		$cookie = lh3cookie();
		if ($cookie) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			$result = curl_exec($ch);
			if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != '200') {
				unlink($lh3_cookies);
				$result = false;
			}
			curl_close($ch);
		} else {
			$result = false;
		}
		if (!$result) {
			$result = getCache($url);
		} else {
			createCache($url,$result);
		}
		return $result;
	}

	function lh3cookie() {
		global $lh3_url, $lh3_username, $lh3_password, $lh3_cookies;
		if (file_exists($lh3_cookies)) {
			return $lh3_cookies;
		} else {
			$url = $lh3_url.'auth/login';
			$data = array(
				'username' => $lh3_username,
				'password' => $lh3_password
			);
			$header = array(
				'X-API-Version: 2011-12-03',
			);
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $lh3_cookies);
			$output = curl_exec($ch);
			curl_close($ch);
			if (file_exists($lh3_cookies)) {
				return $lh3_cookies;
			} else {
				return false;
			}
		}
	}
?>