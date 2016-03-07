<?php
require_once('cache.php');
require_once($connections_folder.'alma.php');
require_once($connections_folder.'alma_hours.php');
require_once($connections_folder.'altmetric.php');
require_once($connections_folder.'dspace.php');
require_once($connections_folder.'ezproxy.php');
require_once($connections_folder.'exlibris_status.php');
require_once($connections_folder.'libraryh3lp.php');
require_once($connections_folder.'mrbs.php');
require_once($connections_folder.'oai.php');
require_once($connections_folder.'scopus.php');
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
			$parsed = date_create_from_format($from, $row[$column]);
			if ($parsed) {
				$new_date = date_format($parsed, $to);
				$rowset[$r][$column] = $new_date;
			}
		}
		return $rowset;
	}
	
	function totalColumn($rowset,$column_array) {
		if (!is_array($column_array)) {
			$column_array = array($column_array);
		}
		$count = count($rowset);
		foreach($column_array as $column) {
			$total = 0;
			foreach($rowset as $row) {
				if (!preg_match('/^total/i',$row[0]) && is_numeric($row[$column])) {
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

	function matrix($rowset,$groupCol,$valueCol) {
		$compareCol = array_keys($rowset[0]);
		unset($compareCol[array_search($groupCol, $compareCol)]);
		unset($compareCol[array_search($valueCol, $compareCol)]);
		$compareCol = array_values($compareCol);
		$headers = array();
		for ($i=0;$i<$valueCol;$i++) {
			$headers[] = "";
		}
		foreach($rowset as $r => $row) {
			if(!in_array($row[$groupCol],$headers)) {
				$headers[] = $row[$groupCol];
			}
		}
		$newrowset = array($headers);
		foreach($rowset as $r => $row) {
			$used = 0;
			$column = $row[$groupCol];
			foreach($newrowset[0] as $t => $title) {
				if ($column == $title) {
					$c = $t;
				}
			}
			$value = $row[$valueCol];
			foreach ($newrowset as $n => $new) {
				$match = 1;
				foreach ($compareCol as $cf => $cval) {
					if ($new[$cval] != $row[$cval]) {
						$match = $match * 0;
					}
				}
				if ($match == 1) {
					$newrowset[$n][$c] = $value;
					$used = 1;
				}
			}
			if ($used != 1) {
				$row[$c] = $value;
				$newrowset[] = $row;
			}
		}
		foreach($newrowset as $n => $new) {
			for ($e = 0; $e < count($headers); $e++) {
				if (!isset($new[$e])) {
					$rnew[$e] = 0;
				} else {
					$rnew[$e] = $new[$e];
				}
			}
			if ($groupCol < $valueCol) {
				unset($rnew[$groupCol]);
			}
			$newrowset[$n] = array_values($rnew);
		}
		return $newrowset;
	}
	
	function mergeTables($rowset_array,$headers,$groupCol,$valueCol) {
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
		$reallynew = matrix($reallynew,$groupCol,$valueCol);
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
		$csv = "";
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

	function saveTable($id,$title,$note,$rowset,$format) {
		global $csv_folder;
		global $site_url;
		$prefix = '"'.$title.'"'."\n";
		$prefix .= '"'.$site_url.'"'."\n";
		$prefix .= '"'.date('Y-m-d H:i',time()).'"'."\n";
		$prefix .= '"'.$note.'"'."\n";
		$prefix .= '"Display as: '.$format.'"'."\n";
		$csv = array2csv($rowset);
		$handle = fopen($csv_folder.$id.'.csv','w');
		fwrite($handle,$prefix);
		$success = fwrite($handle,$csv);
		fclose($handle);
	}
		
	function doTable($id,$title,$note,$headers,$rowset,$format="Table") {
		echo "		<div class='statdiv narrow' id='$id'>\n";
		echo "			<h4>$title</h4>\n";
		echo "			<p>$note</p>\n";
		$rowset = formatTable($rowset,$headers);
		saveTable($id,$title,$note,$rowset,$format);
		echo "		</div>\n";
	}

?>