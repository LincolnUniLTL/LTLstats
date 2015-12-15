<html>
	<head>
		<title>LTLstats</title>
		<link rel="stylesheet" type="text/css" href="layout.css.php"/>
	</head>
	<body>
<?php
require_once('config.php');

	function csv2array($string) {
		$rowset = explode("\n", $string);
		foreach ($rowset as $r => $row) {
			$row = explode('",', $row);
			foreach ($row as $c => $cell) {
				$array[$r][$c] = strip_quotes($cell);
			}
		}
		return $array;
	}
	
	function strip_quotes($string) {
		$stripped = preg_replace('/\r\n|\r|\n/','',$string);
		$stripped = preg_replace('/"(.*)/','$1',$stripped);
		$stripped = preg_replace('/(.*)"/','$1',$stripped);
		return $stripped;
	}

	foreach (glob($csv_folder.'*.csv') as $filepath) {
		$id = substr_replace($filepath, '', 0, strlen($csv_folder));
		$id = preg_replace('/\.csv/', '', $id);
		$handle = fopen($filepath, 'r');
		$title = strip_quotes(fgets($handle));
		$source = strip_quotes(fgets($handle));
		$timestamp = strip_quotes(fgets($handle));
		$note = strip_quotes(fgets($handle));
		$file = fread($handle, filesize($filepath));
		fclose($handle);
		$rowset = csv2array($file);
		$width = round(strlen(implode("     ",$rowset[1]))/15); // where the CSS defines .width-1 as 15em
		if ($width>5) $width=5;
		if ($width<1) $width=1;
?>

		<div class='statdiv width-<?=$width?>' id='<?=$id?>'>
			<h4><?=$title?></h4>
			<p><?=$note?></p>
			<table>
<?		foreach ($rowset as $n => $row) {
			if (preg_match('/^total/i',$row[0])) {
				echo "				<tr class='total'>\n";
			} else {
				echo "				<tr>\n";
			}
			if ($n==0) {
				foreach ($row as $cell) {
					echo "					<th>".$cell."</th>\n";
				}
			} else {
				foreach ($row as $cell) {
					echo "					<td>".$cell."</td>\n";
				}
			}
			echo "				</tr>\n";
		}
?>
			</table>
			<p class='modified'>As of: <?=$timestamp?></p>
			<p class='download'>Save as: <a href='<?=$csv_download_folder . $id . ".csv";?>'>CSV</a></p>
		</div>
<?
	}
?>
	</body>
</html>