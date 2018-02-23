<?php
require_once('cache.php');
require_once($connections_folder.'alma_hours.php');
require_once($connections_folder.'altmetric.php');
require_once($connections_folder.'careerhub.php');
require_once($connections_folder.'dspace.php');
require_once($connections_folder.'exlibris_analytics.php');
require_once($connections_folder.'exlibris_status.php');
require_once($connections_folder.'ezproxy.php');
require_once($connections_folder.'libraryh3lp.php');
require_once($connections_folder.'mrbs.php');
require_once($connections_folder.'oai.php');
require_once($connections_folder.'scopus.php');
require_once($connections_folder.'supersaas.php');
require_once($connections_folder.'wikipedia.php');

/* Formatting functions */
	function csv2array($string) {
		$array = explode("\n", $string);
		foreach ($array as $n => $row) {
			$row = explode(",", $row);
			$array[$n] = $row;
		}
		return $array;
	}

	function obj2arr($object) {
		$object = (array) $object;
		foreach($object as $o => $obj) {
			if (is_object($obj)) {
				$array[] = (array) obj2arr($obj);
			} else {
				$array[] = $obj;
			}
		}		
		return $array;
	}

	function sortByColumn($array,$c) {
		usort($array, build_sorter($c));
		return $array;
	}
	function build_sorter($c) {
		return function ($a, $b) use ($c) {
			return strnatcmp($a[$c], $b[$c]);
		};
	}
	
	function keepRight($array,$remainder) {
		foreach ($array as $r => $row) {
			if (!is_array($row)) {
				$row = (array) $row;
			}
			array_splice($row,0,-$remainder);
			$array[$r] = $row;
			}
		return $array;
	}
	function keepLeft($array,$remainder) {
		foreach ($array as $r => $row) {
			if (!is_array($row)) {
				$row = (array) $row;
			}
			$offset = $remainder;
			array_splice($row,$offset);
			$array[$r] = $row;
		}
		return $array;
	}
	function keepTop($array,$remainder) {
		if (!is_array($array)) {
			$array = (array) $array;
		}
		$offset = $remainder;
		array_splice($array,$remainder);
		return $array;
	}
	function keepBottom($array,$remainder) {
		if (!is_array($array)) {
			$array = (array) $array;
		}
		$offset = count($array) - $remainder;
		array_splice($array,0,$offset);
		return $array;
	}

	function getColumns($rowset, $columns) {
		$newrowset = array();
		foreach ($rowset as $r => $row) {
			foreach($columns as $c => $column) {
				$t = array_keys($row);
				$newrowset[$r][$c] = $row[$t[$column]];
			}
		}
		return $newrowset;
	}

	function getRows($rowset, $rows) {
		$newrowset = array();
		foreach($rows as $r => $row) {
			$newrowset[$r] = $rowset[$row];
		}
		return $newrowset;
	}

	function swapColRow($rowset) {
		$newrowset = array();
		foreach ($rowset as $r => $row) {
			foreach ($row as $c => $col) {
				$newrowset[$c][$r] = $col;
			}
		}
		return $newrowset;
	}

	function mergeMonthYear($rowset,$month_column,$year_column) {
		foreach ($rowset as $r => $row) {
			$row[$month_column] = $row[$month_column]." ".$row[$year_column];
			unset($row[$year_column]);
			foreach ($row as $cell) {
				$newrowset[$r][] = $cell;
			}
		}
		return $newrowset;
	}
	
	function convertDates($rowset,$column,$from,$to) {
		foreach ($rowset as $r => $row) {
			$parsed = date_create_from_format("!".$from, $row[$column]);
			if ($parsed) {
				$new_date = date_format($parsed, $to);
				$rowset[$r][$column] = $new_date;
			}
		}
		return $rowset;
	}
	
	function splitColumn($rowset,$column,$delimiter) {
		foreach ($rowset as $r => $row) {
			$bits = explode($delimiter, $row[$column]);
			unset($row[$column]);
			foreach($bits as $b => $bit) {
				array_splice($row, $column+$b, 0, $bit);
			}
			$rowset[$r] = $row;
		}
		return $rowset;
	}
	
	function totalColumn($rowset,$column_array="all") {
		if ($column_array == "all") {
			$column_array = array_keys($rowset[0]);
			array_shift($column_array);
		}
		if (!is_array($column_array)) {
			$column_array = array($column_array);
		}
		$count = count($rowset);
		foreach($column_array as $column) {
			$total = 0;
			foreach($rowset as $r => $row) {
				if (!preg_match('/^total/i',$row[0]) && $row[0] != "" && is_numeric($row[$column])) {
					$total = $total + $row[$column];
					}
			} 
			for ($i=0;$i<=$column;$i++) {
				if ($i == $column) {
					$rowset[$count][$i] = $total;
				} elseif ($i == 0) {
					$rowset[$count][$i] = "Total";
				} elseif (!in_array($i, $column_array)) {
					$rowset[$count][$i] = "";
				}
			}
		}
		return $rowset;
	}

	function matrix($rowset, $rowCol, $colCol, $valueCol) {
		$colHead = array_values(array_unique(array_column($rowset, $colCol)));
		$rowHead = array_values(array_unique(array_column($rowset, $rowCol)));
		$newrowset = array();
		foreach ($rowset as $r => $row) {
			$newrowset[$row[$rowCol]][$row[$colCol]] = $row[$valueCol];
		}
		foreach ($colHead as $c) {
			foreach ($rowHead as $r) {
				if (!isset($newrowset[$r][$c])) $newrowset[$r][$c] = "0";
			}
		}
		$finalset = array();
		$finalset[0][0] = "";
		foreach ($colHead as $c) {
			$finalset[0][] = $c;
		}
		foreach ($rowHead as $r => $row) {
			$finalset[][0] = $row;
			foreach ($colHead as $c => $cell) {
				$finalset[$r+1][$c+1] = $newrowset[$row][$cell];
			}
		}
		return $finalset;
	}

	function mergeTables($rowset_array,$headers,$rowCol, $valueCol) {
		$newrowset = array();
		foreach ($rowset_array as $t => $table) {
			foreach ($table as $r => $row) {
				foreach ($row as $c => $cell) {
					$newrowset[$t][$r][] = $cell;
				}
				$newrowset[$t][$r][] = $headers[$t];
			}
		}
		$reallynew = array_shift($newrowset);
		while ($newrowset) {
			$reallynew = array_merge($reallynew,array_shift($newrowset));
		}
		$reallynew = matrix($reallynew,$rowCol,count($reallynew[1])-1,$valueCol);
		return $reallynew;
	}

/* General functions */
	function getData($url) {
		global $version, $site_url, $contact_email;
		$useragent = $version." (".$site_url."; ".$contact_email.")";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}

	function getRowset($url,$function) {
		global $use_cached_data;
		if ($use_cached_data == true) {
			$result = checkCache($url);
		} else {
			$result = false;
		}
		if (!$result) {
			$result = getData($url);
			if (!$result) {
				$result = getCache($url);
			} else {
				createCache($url,$result);
			}
		}
		$rowset = call_user_func($function,$result);
		return $rowset;
	}
	
	function formatTable($rowset,$header_array=array()) {
		if ($header_array != array()) {
			$header_array = array($header_array);
			$rowset = array_merge($header_array, $rowset);
		}
		echo "			<table>\n";
		foreach ($rowset as $n => $row) {
			if (!is_array($row)) { $row = array($row); }
			if (preg_match('/^total/i',$row[0])) {
				echo "				<tr class='total'>\n";
			} else {
				echo "				<tr>\n";
			}
			if ($n==0) {
				foreach ($row as $column) {
					echo "					<th>".$column."</th>\n";
				}
			} else {
				foreach ($row as $column) {
					echo "					<td>".$column."</td>\n";
				}
			}
			echo "				</tr>\n";
		}
		echo "			</table>\n";
		return $rowset;
	}
	
	function array2csv($rowset) {
		$csv = '';
		foreach($rowset as $r => $row) {
			if (!is_array($row)) { $row = array($row); }
			foreach($row as $c => $cell) {
				$csv .= '"'.$cell.'"';
				if ($c < (count($row)-1)) $csv .= ',';
			}
			if ($r < (count($rowset)-1)) $csv .= "\n";
		}
		return $csv;
	}

	function saveTable($rowset,$widget) {
		global $csv_folder;
		global $site_url;
		$prefix = '"'.$widget['title'].'"'."\n";
		$prefix .= '"'.$site_url.'"'."\n";
		$prefix .= '"'.date('Y-m-d H:i',time()).'"'."\n";
		$prefix .= '"'.$widget['note'].'"'."\n";
		$prefix .= '"Display as: '.$widget['format'].'"'."\n";
		$prefix .= '"Tags: '.implode(', ',$widget['tags']).'"'."\n";
		$csv = array2csv($rowset);
		$handle = fopen($csv_folder.$widget['id'].'.csv','w');
		fwrite($handle,$prefix);
		$success = fwrite($handle,$csv);
		fclose($handle);
	}
		
	function doTable($rowset,$widget) {
		if (!isset($widget['title'])) $widget['title'] = "";
		if (!isset($widget['note'])) $widget['note'] = "";
		if (!isset($widget['headers'])) $widget['headers'] = array();
		if (!isset($widget['format'])) $widget['format'] = "Table";
		if (!isset($widget['tags'])) $widget['tags'] = array();
	
		echo "		<div class='statdiv narrow' id='".$widget['id']."'>\n";
		echo "			<h4>".$widget['title']."</h4>\n";
		echo "			<p>".$widget['note']."</p>\n";
		if ($rowset != array()) {
			$rowset = formatTable($rowset,$widget['headers']);
		}
		saveTable($rowset,$widget);
		echo "		</div>\n";
	}

?>