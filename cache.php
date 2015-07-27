<?php
	function createCache($url,$content) {
		$filename = md5($url);
		$filepath = dirname(__FILE__) . '\cache\\' . $filename;
		$handle = fopen($filepath, "w");
		$success = fwrite($handle, $content);
		fclose($handle);
		if (is_writable($filepath) && $success > 0) {
			if (!$handle = fopen($filepath, 'w')) {
				exit;
			}
			if (fwrite($handle, $content) === FALSE) {
				exit;
			}
			fclose($handle);
		} else {
		}
	}

	function checkCache($url,$delay=NULL) {
		if (!$delay) { $delay = 1 * 24 * 60 * 60; }
		$filename = md5($url);
		$filepath = dirname(__FILE__) . '\cache\\' . $filename;
		if (!file_exists($filepath)) {
			return false;
		} else {
			$timestamp = filemtime($filepath);
			if ($timestamp + $delay > time()) {
				$content = getCache($url);
				return $content;
			} else {
				return false;
			}
		}
	}
	
	function getCache($url) {
		$filename = md5($url);
		$filepath = dirname(__FILE__) . '\cache\\' . $filename;
		$handle = fopen($filepath, "r");
		$content = fread($handle, filesize($filepath));
		fclose($handle);
		return $content;
	}
?>