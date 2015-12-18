<?php
	header("Content-type: text/css; charset: UTF-8");
	include("config.php");
?>
/* This dummy rule has no purpose except as a fix for a bizarre bug
   whereby the first rule after the include above gets ignored.     */
.dummyrule {}


.statdiv {
	border-radius: 1em;
	-moz-border-radius: 1em;
	padding: 0.5em;
	margin: 0.5em;
	float: left;
}

.width-1 {
	width: 15em;
}

.width-2 {
	width: 32.4em;
}

.width-3 {
	width: 49.8em;
}

.width-4 {
	width: 67.2em;
}

.width-5 {
	width: 84.6em;
}

/* hack for overlapping divs in Chrome */
.wide + .narrow, .wide + .medium {
	clear: left;
}

.statdiv h4 {
	border-radius: .25em;
	-moz-border-radius: .25em;
	text-align: center;
	color: white;
	font-size: 150%;
	margin: 0.5em 0;
}

.statdiv p {
	color: black;
	padding: 0.4em;
}

.statdiv span.value {
	font-weight: bold;
	font-size: 150%;
	border-radius: .25em;
	-moz-border-radius: .25em;
	padding: 0 .125em;
	background-color: white;
	text-decoration: none;
}

.statdiv a:link, .statdiv a:hover, .statdiv a:active, .statdiv a:visited {
	color: #0069b4;
	text-decoration: underline;
}

.statdiv table {
	margin: 0 auto; 
	text-align: right;
	border-collapse:collapse;
	font-size: 150%;
	color: black;
}

.statdiv td, .statdiv th {
	padding: 0.2em;
}

.statdiv .total td {
	font-weight:bold;
	border-top: thin solid black;
}

<?php
	foreach (glob($csv_folder.'*.csv') as $filepath) {
		$id = substr_replace($filepath, '', 0, strlen($csv_folder));
		$divIds[] = preg_replace('/\.csv/', '', $id);
	}
	
	foreach ($divIds as $k => $d) {
		$k = $k % count($darkColours);
		$thisDark = $darkColours[$k];
		$thisPale = $paleColours[$k];
		echo "#$d {\n";
		echo "	border: .2em solid #$thisDark;\n";
		echo "	background-color: #$thisPale;\n";
		echo "}\n\n";
		echo "#$d h4 {\n";
		echo "  background-color: #$thisDark;\n";
		echo "}\n\n";
		
		echo "#$d span.value {\n";
		echo "	border: .1em solid #$thisDark;\n";
		echo "	color: #$thisDark;\n";
		echo "}\n\n";
	}
?>

p.download, p.modified {
	font-size: 100%;
	text-align: right;
}

#credits {
	clear: both;
	padding-top: 1em;
	text-align: center;
}

#Utility-sitename {
	text-align: left;
}