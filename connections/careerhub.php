<?php
	function getCareerhubData($report) {
		global $careerhub_url, $careerhub_host;
		global $version, $site_url, $contact_email, $connections_folder;
		$useragent = $version." (".$site_url."; ".$contact_email.")";
		$url = $careerhub_url . 'api/integrations/v1/reports/' . $report;
		$token = careerhubToken();
		$header = array(
			'Authorization: Bearer '. $token,
			'Host: ' . $careerhub_host,
			'Accept: application/json',
			'Cache-Control: no-cache',
		); 
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($ch);
		curl_close($ch);
		if (!$result) {
			$result = getCache($url);
		} else {
			createCache($url,$result);
		}
		$rowset = decodeCareerHub($result);
		return $rowset;
	}
	
	function decodeCareerHub($result) {
		$obj = json_decode($result, true);
		return ($topics);
	}
	
	function careerhubToken() {
		global $careerhub_url, $careerhub_id, $careerhub_secret, $careerhub_scope;
		global $version, $site_url, $contact_email, $connections_folder;
		$useragent = $version." (".$site_url."; ".$contact_email.")";
		$url = $careerhub_url . 'oauth/token';
		$header = array(
			'Content-Type: application/x-www-form-urlencoded',
		);
		$data = 'grant_type=client_credentials&client_id='.urlencode($careerhub_id).'&client_secret='.urlencode($careerhub_secret).'&scope='.urlencode($careerhub_scope);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);   
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$json = curl_exec($ch);
		curl_close($ch);
		$obj = json_decode($json, TRUE);
		return $obj['access_token'];
	}
?>