<?php require_once('config.php'); ?>
<html>
	<head>
		<title>LTLstats</title>
		<link rel="stylesheet" type="text/css" href="layout.css.php"/>
		<script src="<?php echo $js['chart.js']; ?>" type="text/javascript"></script>
		<script src="<?php echo $js['wordcloud2.js']; ?>" type="text/javascript"></script>
		<script src="<?php echo $js['tags']; ?>" type="text/javascript"></script>
	</head>
	<body>
<?php

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

	function swapColRow($rowset) {
		$newrowset = array();
		foreach ($rowset as $r => $row) {
			foreach ($row as $c => $col) {
				$newrowset[$c][$r] = $col;
			}
		}
		return $newrowset;
	}

	$chartload = array();
	$fill = $paleChart;
	$point = $darkChart;
	foreach (glob($csv_folder.'*.csv') as $filepath) {
		$id = substr_replace($filepath, '', 0, strlen($csv_folder));
		$id = preg_replace('/\.csv/', '', $id);
		$handle = fopen($filepath, 'r');
		$title = strip_quotes(fgets($handle));
		$source = strip_quotes(fgets($handle));
		$timestamp = strip_quotes(fgets($handle));
		$note = strip_quotes(fgets($handle));
		$format = preg_replace('/Display as: ([^,]*),*/','$1',strip_quotes(fgets($handle)));
		$tags = strip_quotes(fgets($handle));
		$file = fread($handle, filesize($filepath));
		fclose($handle);
		if (substr($tags,0,6) == "Tags: ") {
			$tags = explode(", ",substr($tags,6));
		} else {
			$file = $tags . $file;
			$tags = "";
		}
		$rowset = csv2array($file);
		if ($format=="Line") {
			$chartset = swapColRow($rowset);
			$width=round(count($chartset[0])/6);
			$height=1.618;
		} elseif ($format=="Bar") {
			$chartset = swapColRow($rowset);
			$width=round(count($chartset[0])*count($chartset)/10);
			$height=1.618;
		} elseif ($format=="Pie" || $format=="Doughnut") {
			$chartset = $rowset;
			$width=round(count($chartset)/12);
			$height=$width;
		} elseif ($format=="Radar") {
			$chartset = swapColRow($rowset);
			$width=round((count($chartset[0])-1)/6);
			$height=$width;
		} elseif ($format=="PolarArea") {
			$chartset = $rowset;
			$width=round(count($chartset)/6);
			$height=$width;
		} elseif ($format =="Table") {
			$width_header = round(strlen(implode("     ",$rowset[0]))/15); // where the CSS defines .width-1 as 15em
			$width_row1 = round(strlen(implode("     ",$rowset[1]))/15); // where the CSS defines .width-1 as 15em
			$width = max($width_header,$width_row1);
			$height = 1;
		} elseif ($format =="Wordle") {
			$chartset = $rowset;
			$width = round(count($chartset)/4);
			$height=$width*.7;
		} else {
			$format = "None";
		}
		if ($width>5) $width=5;
		if ($width<1) $width=1;
/* START "If it's displayable, create a div for it" */
		if ($format && $format!="None") {
?>
		<div class='statdiv width-<?php echo $width;
			if ($tags) {
				foreach ($tags as $t) {
					$t = str_replace(" ","_",$t);
					echo ' tagged_'.$t;
				}
			}?>' id='<?php echo $id; ?>'>
			<h4><?php echo $title; ?></h4>
			<p><?php echo $note; ?></p>
<?php	/* If there's a chart (and associated javascript), create a canvas and script for it */
		if ($format!="Table" && $format!="None" && file_exists($js['chart.js'])) {
			$this_colour = rand(0,count($darkChart)-1);
?>
			<canvas id='<?php echo $id; ?>-chart' height='<?php echo round($height*150); ?>' width='<?php echo round($width*150); ?>'></canvas>
			<script>
<?php		/* Wordles are special */
			if ($format=="Wordle" && file_exists($js['wordcloud2.js'])) {
				$maxlength = 1;
				array_shift($chartset);
				array_pop($chartset);
				/*truncate ridiculously long search terms*/
				foreach($chartset as $c => $term) {
					$words = explode(" ", $term[0]);
					if (count($words) > 4) {
						$words = array_slice($words,0,4);
						$chartset[$c][0] = implode(" ",$words) . " [...]";
					}
				}
?>
				var <?php echo $id; ?>Data = {list: [
<?php			foreach ($chartset as $c) {
					if (strlen($c[0])*$c[1] > $maxlength) {
						$maxlength = strlen($c[0])*$c[1];
					}
					echo "				[";
					echo '"'.$c[0].'"';
					echo ", ";
					echo $c[1];
					echo "],\n";
				}
?>
				],
				weightFactor: <?php echo round($width*180)/$maxlength; ?>,
				gridSize: 5,
				drawOutOfBound: false,
				maskGapWidth: 0.1,
				rotateRatio: .9
				};
<?php	/* Line, Bar, and Radar charts have their data in a certain format */
		} elseif ($format=="Line" || $format=="Bar" || $format=="Radar") { 
				$chartlabels = $chartset[0];
				array_shift($chartset);
				array_shift($chartlabels);
				foreach ($chartlabels as $l => $label) {
					$chartlabels[$l] = '"'.$label.'"';
				}
				if ($chartlabels[count($chartlabels)-1] == '"Total"') {
					array_pop($chartlabels);
					foreach ($chartset as $s => $set) {
						array_pop($set);
						$chartset[$s] = $set;
					}
				}
				krsort($chartlabels);
				$chartlabels = implode(",", $chartlabels);
?>
				var <?php echo $id; ?>Data = {
				labels : [<?php echo $chartlabels; ?>],
				datasets : [
<?php			foreach ($chartset as $s => $set) {
					$label = array_shift($set);
					krsort($set);
					$set = implode(",",$set);
?>
					{
						label: "<?php echo $label; ?>",
<?php		/* Line and Radar charts have certain colour data */
 					if ($format == "Line" || $format=="Radar") { ?>
						fillColor : "<?php echo $fill[fmod($s+$this_colour,count($darkChart))]; ?>",
						strokeColor : "<?php echo $point[fmod($s+$this_colour,count($darkChart))]; ?>",
						pointColor : "<?php echo $point[fmod($s+$this_colour,count($darkChart))]; ?>",
						pointStrokeColor : "#fff",
						pointHighlightFill : "<?php echo $fill[fmod($s+$this_colour,count($darkChart))]; ?>",
						pointHighlightStroke : "$fff",
<?php		/* Bar charts have different colour data */
					} elseif ($format == "Bar" ) { ?>
						fillColor : "<?php echo $point[fmod($s+$this_colour,count($darkChart))]; ?>",
						strokeColor : "<?php echo $point[fmod($s+$this_colour,count($darkChart))]; ?>",
						highlightFill: "<?php echo $fill[fmod($s+$this_colour,count($darkChart))]; ?>",
						highlightStroke: "$fff",
<?php				} ?>
						data : [<?php echo $set; ?>]
					},
<?php			} ?>
				]
				}
<?php		/* Pie, Doughnut, and PolarArea charts have their data in a different format */
			} elseif ($format=="Pie" || $format=="Doughnut" || $format=="PolarArea") {
				array_shift($chartset);
				array_pop($chartset);
?>
				var <?php echo $id; ?>Data = [
<?php			foreach ($chartset as $s => $set) { ?>
					{ value: <?php echo $set[1]; ?>,
					color:"<?php echo $point[fmod($s+$this_colour,count($darkChart))]; ?>",
					highlight:"<?php echo $fill[fmod($s+$this_colour,count($darkChart))]; ?>",
					label: "<?php echo $set[0]; ?>"
					},
<?php			} ?>
				]
<?php
			}
	/* Wordles are special but can't for the life of me figure out PNGs */
		if ($format=="Wordle") {
			$chartload[] = 'var ctx = document.getElementById("'.$id.'-chart");
			ctx.style.width = ctx.parentNode.firstElementChild.offsetWidth+"px";
			WordCloud(ctx, '.$id.'Data);';
		} else {
	/* Every other chart needs its own chartload instruction */
			$chartload[] = 'var ctx = document.getElementById("'.$id.'-chart").getContext("2d");
			myChart = new Chart(ctx).'.$format.'('.$id.'Data, {responsive : true, onAnimationComplete: function(){if(!document.getElementById("'.$id.'PNG")) {var myPNG = this.toBase64Image(); var pngHolder = document.getElementById("'.$id.'-chart").parentNode.children[8]; var pngLink = document.createElement("span"); pngLink.id = "'.$id.'PNG"; pngLink.innerHTML = "<a download=\''.$id.'.png\' href=\'" + myPNG + "\'>PNG</a>"; pngHolder.appendChild(pngLink);}}});
			var location = document.getElementById("'.$id.'-chart");
			var legendHolder = document.createElement("div");
			legendHolder.innerHTML = myChart.generateLegend();
			location.parentNode.insertBefore(legendHolder,location);
			';
		}
?>
			</script>
			<noscript>
<?php /* For both tables and (in a noscript behind the canvas) charts, include the tabular data - assuming there is some */
	}
	if ($format && $format!="None" && $rowset[0][1]) {
		echo "				<table>\n";
		foreach ($rowset as $n => $row) {
			if (preg_match('/^total/i',$row[0])) {
				echo "					<tr class='total'>\n";
			} else {
				echo "					<tr>\n";
			}
			if ($n==0) {
				foreach ($row as $cell) {
					echo "						<th>".$cell."</th>\n";
				}
			} else {
				foreach ($row as $cell) {
					echo "						<td>".$cell."</td>\n";
				}
			}
			echo "					</tr>\n";
		}
		echo "				</table>\n";
	}
	if ($format!="Table" && $format!="None") {
		echo "			</noscript>\n";
	}
?>
 			<p class='modified'>As of: <?php echo $timestamp; ?></p>
			<p class='tags'><?php
	if ($tags) {
		echo "\n				<span>Tags: </span>";
		foreach ($tags as $t) {
			$s = str_replace(" ","_",$t);
			$t = str_replace(" ","&nbsp;",$t);
			echo "\n				<span class='tag tag_$s'>$t</span>";
		}
	}

 			?></p>
			<p class='download'>
<?php
	if ($format && $format!="None" && $rowset[0][1]) { ?>
				<span>Save as: </span>
				<span><a href='<?php echo $csv_download_folder . $id . ".csv"; ?>'>CSV</a></span>
<?php
	} ?>
			</p>
		</div>
<?php
	}
	}
?>
</div>
<div style="clear:both;"></div>
		<script>
		window.onload = function(){
<?php
	foreach ($chartload as $c) {
	echo $c;
}
?>
			init();
		}
		</script>
	</body>
</html>