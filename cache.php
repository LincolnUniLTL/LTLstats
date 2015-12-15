<?php
require_once('config.php');

	function createCache($url,$content) {
		global $cache_folder;
		$filename = md5($url);
		$filepath = $cache_folder . $filename;
		$handle = fopen($filepath, "w");
		$success = fwrite($handle, $content);
		fclose($handle);
		if (is_writable($filepath) && $success > 0) {
			if (!$handle = fopen($filepath, 'w')) {
//				echo "<p class='warning'>Cannot open file ($filename).</p>";
				exit;
			}
			if (fwrite($handle, $content) === FALSE) {
//				echo "<p class='warning'>Cannot write to file ($filename).</p>";
				exit;
			}
//			echo "<p class='warning'>Saved to file ($filename).</p>";
			fclose($handle);
		} else {
//			echo "<p class='warning'>The file ($filename) is not writable.</p>";
		}
	}

	function checkCache($url,$delay=NULL) {
		global $cache_folder;
		if (!$delay) { $delay = 1 * 24 * 60 * 60; }
		$filename = md5($url);
		$filepath = $cache_folder . $filename;
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
		global $cache_folder;
		$filename = md5($url);
		$filepath = $cache_folder . $filename;
		$handle = fopen($filepath, "r");
		$content = fread($handle, filesize($filepath));
		fclose($handle);
		return $content;
	}
?>