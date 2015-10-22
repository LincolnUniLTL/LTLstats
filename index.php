<html>
	<head>
		<title>Dashboard</title>
		<link rel="stylesheet" type="text/css" href="layout.css"/>
	</head>
	<body>
<?php
require_once('cache.php');
require_once('connections/alma.php');
require_once('connections/altmetric.php');
require_once('connections/dspace.php');
require_once('connections/exlibris_status.php');
require_once('connections/libraryh3lp.php');
require_once('connections/scopus.php');
require_once('connections/wikipedia.php');

	function getData($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_USERAGENT, 'LTLStats/1.1 (http://example.com/; email@example.com)');  // Identify your app (site; email)
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}

	function getRowset($url,$function) {
		$result = checkCache($url);
		if (!$result) {
			$result = getData($url);
			$rowset = call_user_func($function,$result);
			if (!$rowset) {
				$result = getCache($url);
				$rowset = call_user_func($function,$result);
			} else {
				createCache($url,$result);
			}
		} else {
			$rowset = call_user_func($function,$result);
		}
		return $rowset;
	}
	
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
		if (!is_array($row)) {
			$array = (array) $array;
		}
		$offset = $remainder;
		array_splice($array,$remainder);
		return $array;
	}
	function keepBottom($array,$remainder) {
		if (!is_array($row)) {
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

	function totalColumn($rowset,$column_array) {
		if (!is_array($column_array)) {
			$column_array = array($column_array);
		}
		$count = count($rowset);
		foreach($column_array as $column) {
			$total = 0;
			foreach($rowset as $row) {
				$total = $total + $row[$column];
			}
			for ($i=0;$i<=$column;$i++) {
				if ($i == $column) {
					$rowset[$count][$i] = $total;
				} elseif ($i == 0) {
					$rowset[$count][$i] = "Total";
				} elseif (!$rowset[$count][$i]) {
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
	
	function formatTable($rowset,$header_array=array()) {
		if ($header_array != array()) {
			$header_array = array($header_array);
			$rowset = array_merge($header_array, $rowset);
		}
		echo "<table>\n";
		foreach ($rowset as $n => $row) {
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
	}
?>
		<div class="statdiv medium" id="alma1">
			<h3>Alma New Items</h3>
			<?php
				$reportpath = "/shared/Lincoln/Reports/New Records";	// Your report path
				$rowset = getAlmaRows($reportpath);
				if (!$rowset) {
					echo "[data unavailable]";
				} else {
					$columnOrd = array(3,2,8);
					$rowset = getColumns($rowset,$columnOrd);
					$rowset = keepBottom($rowset,12);
					krsort($rowset);
					$rowset = totalColumn($rowset,2);
					$headers = array("Year","Month","New items");
					formatTable($rowset,$headers);
				}
			?>
		</div>

		<div class="statdiv wide" id="alma2">
			<h3>Alma checkout locations</h3>
			<?php
				$reportpath = "/shared/Lincoln/Reports/Issues per month by circ desk 2";	// Your report path
				$rowset = getAlmaRows($reportpath);
				if (!$rowset) {
					echo "[data unavailable]";
				} else {
					$columnOrd = array(1,4,3,5);
					$rowset = getColumns($rowset,$columnOrd);
					$rowset = matrix($rowset,0,3);
					$totals = array(2,3,4,5);
					$headers = keepTop($rowset,1);
					$headers = $headers[0];
					$rowset = keepBottom($rowset,12);
					krsort($rowset);
					$rowset = totalColumn($rowset,$totals);
					formatTable($rowset,$headers);
				}
			?>
		</div>

		<div class="statdiv medium" id="altmetric">
			<h3>Altmetric</h3>
			<p>Numbers of social media mentions for each faculty's papers as measured by <a href="http://www.altmetric.com/login.php">Altmetric</a>.</p>
			<?php
				$reportpath = array();
				$reportpath[] = "&group=lincoln%3Agroup%3A4&order_by=score";	// Faculty 1
				$reportpath[] = "&group=lincoln%3Agroup%3A12&order_by=score";	// Faculty 2
				$reportpath[] = "&group=lincoln%3Agroup%3A16&order_by=score";	// Faculty 3
				$headers = array('Faculty 1','Faculty 2','Faculty 3');
				$rowset = getAltmetrics($reportpath);
				if (!$rowset) {
					echo "[data unavailable]";
				} else {
					foreach($rowset as $s => $set) {
						$rowset[$s] = keepBottom($set,12);
						krsort($rowset[$s]);
					}
					$newrowset = mergeTables($rowset,$headers,2,1);
					$totals = array(1,2,3);
					$newrowset = totalColumn($newrowset,$totals);
					formatTable($newrowset);
				}
			?>
		</div>

		<div class="statdiv medium" id="dspace_top_items">
			<h4>What Lincoln research is popular this month?</h4>
			<p>Top 5 most-viewed items on DSpace this month:</p>
			<?php
				$date = date("Y") . "-" . date("n");
				$reportpath = "&date=".$date;
				$rowset = getDSpaceData($reportpath);
				if (!$rowset) {
					echo "[data unavailable]";
				} else {
					$n = 5;										// how many items to include
					$headers = array('Item','Views');
					$rowset = keepBottom(keepTop($rowset,($n+1)),$n);
					formatTable($rowset,$headers);
				}
			?>
		</div>

		<div class="statdiv medium" id="exlibris_status">
			<h3>How is the library system doing right now?</h3>
			<p><strong>Alma:</strong> <?=$status_A.$note_A?></p>
			<p><strong>Primo:</strong> <?=$status_P.$note_P?></p>
		</div>

		<div class="statdiv narrow" id="libraryh3lp">
			<h3>LibraryH3lp</h3>
			<p>In the last year, we've answered the following questions:</p>
			<?php
				$path = "reports/chats-per-month";
				$rowset = getLH3Rows($path);
				if (!$rowset) {
					echo "[data unavailable]";
				} else {
					$rowset = str_replace("protocol,","",$rowset);
					$rowset = str_replace("web,","",$rowset);
					$rowset = csv2array($rowset);
					$rowset = swapColRow($rowset);
					array_pop($rowset);
					$rowset = keepBottom($rowset,12);
					krsort($rowset);
					$rowset = totalColumn($rowset,1);
					$headers = array("Month","Questions");
					formatTable($rowset,$headers);
				}
			?>
		</div>

		<div class="statdiv narrow" id="scopus_total">
			<h3>Scopus total</h3>
			<?php
				$inst_id = "60006625"; // Scopus's institution ID for your institution
				$proxy = "http://ezproxy.example.com/login?url="; // proxy prefix to allow authenticated access to Scopus search results
				$rowset = getScopus($inst_id);
				if (!$rowset['authors']) {
					echo "[data unavailable]";
				} else {
					$doclink = $proxy . "http://www.scopus.com/results/results.url?src=s&sot=aff&s=%28AF-ID%28" . $inst_id . "%29%29";
					echo '<p>';
					echo '<strong>'.$rowset['authors'].'</strong> authors ';
					echo 'from '.$rowset['institution'].' ';
					echo 'have authored <a href="'.$doclink.'"><strong>'.$rowset['documents'].'</strong> research papers</a> ';
					echo 'indexed in Scopus.';
					echo '</p>';
				}
			?>
		</div>
		
		<div class="statdiv narrow" id="scopus_monthly">
			<h4>Scopus monthly</h4>
			(Note only papers which include a publication month are counted.)
			<?php
				$inst_id = "60006625"; // Scopus's institution ID for your institution
				$proxy = "http://ezproxy.example.com/login?url="; // proxy prefix to allow authenticated access to Scopus search results
				$rowset = getScopus($inst_id);
				if (!$rowset['monthly']) {
					echo "[data unavailable]";
				} else {
					$rowset = $rowset['monthly'];
					krsort($rowset);
					$rowset = totalColumn($rowset,2);
					$headers = array("Year","Month","No.");
					formatTable($rowset,$headers);
				}
			?>
		</div>

		<div class="statdiv narrow" id="wikipedia">
			<h3>Wikipedia</h3>
			<p><?php
				$string = "http://hdl.handle.net/10182/";		// Your handle prefix
				$string = '"'.$string.'"';
				$rowset = getWikiLinks(urlencode($string));
				if (!$rowset['pages']) {
					echo "[data unavailable]";
				} else {
					echo "<p><a href='https://en.wikipedia.org/w/index.php?search=insource%3A" . $string . "&title=Special%3ASearch&go=Go'>";
					echo $rowset['pages'];
					echo " Wikipedia pages</a>";
					echo " link to research in <a href='http://example.com/'>Your institutional Archive</a>.</p>"; // Your domain / archive name
				}
			?>
		</div>
	</body>
</html>