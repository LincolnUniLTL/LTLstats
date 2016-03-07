﻿<?php
require_once('data_functions.php');

	$id = "alma_example_1";
	$title = "New items in Alma";
	$note = "Link to our <a href='http://example.com/newitems/'>new items list</a>."; // your URL here
	$headers = array("Year","Month","New items");
	$path = ""; // eg "/shared/Lincoln/Reports/New Records";
	$rowset = getAlmaRows($path);
	if ($rowset) {
		$columnOrd = array(3,2,8);
		$rowset = getColumns($rowset,$columnOrd);
		$rowset = keepBottom($rowset,12);
		krsort($rowset);
		$rowset = totalColumn($rowset,2);
		doTable($id,$title,$note,$headers,$rowset,"Bar");
	}

	$id = "alma_example_2";
	$title = "Alma checkout locations";
	$note = "";
	$headers = array();
	$path = array();
	$path[] = ""; // eg "/shared/Lincoln/Reports/Issues per month at circ desk";
	$path[] = ""; // eg "/shared/Lincoln/Reports/Issues per month at self-check";
	$rowset = array();
	foreach ($path as $p) {
		$rowset[] = getAlmaRows($p);
	}
	if ($rowset) {
		foreach ($rowset as $s => $set) {
			$rowset[$s] = keepBottom($set,12);
			krsort($rowset[$s]);
		}
		$mergeheaders = array('Circ desks','Self-checks');
		$newrowset = mergeTables($rowset,$mergeheaders,5,4);
		$newrowset = keepRight($newrowset,4);
		$totals = array(2,3);
		$newrowset = totalColumn($newrowset,$totals);
		doTable($id,$title,$note,$headers,$newrowset,"Line");
	}

	$id = "alma_hours";
	$title = "Open Hours";
	$note = "No-one in their right mind will want to see this on a stats page, but it creates a useful day-by-day listing of open hours in CSV format. By saving it with a 'None' format, data from the file will be never display on the website.";
	$headers = array("Date","Day","From","To","Status","Exceptions");
	$rowset = getAlmaHours();
	if ($rowset) {
		doTable($id,$title,$note,$headers,$rowset,"None");
	}

	$id = "altmetrics_by_month_and_dept";
	$title = "Altmetric";
	$note = "Numbers of social media mentions for each faculty's papers as measured by <a href='http://www.altmetric.com/login.php'>Altmetric</a>.";
	$headers = array();
	$path = array();
	$path[] = ""; // eg "&group=lincoln%3Agroup%3A4&order_by=score";
	$path[] = ""; // eg "&group=lincoln%3Agroup%3A12&order_by=score";
	$path[] = ""; // eg "&group=lincoln%3Agroup%3A16&order_by=score";
	$rowset = getAltmetric($path);
	if ($rowset) {
		foreach($rowset as $s => $set) {
			$rowset[$s] = keepBottom($set,12);
			krsort($rowset[$s]);
		}
		$mergeheaders = array('Faculty 1','Faculty 2','Faculty 3'); // your names
		$newrowset = mergeTables($rowset,$mergeheaders,2,1);
		$totals = array(1,2,3);	// more/less if you have more/less faculties
		$newrowset = totalColumn($newrowset,$totals);
		doTable($id,$title,$note,$headers,$newrowset,"Radar");
	}

	$id = "dspace_top_items";
	$title = "Top 5 DSpace items";
	$note = "Top 5 most-viewed items on <a href='https://example.com/statistics'>our institutional archive</a> this calendar month:";	// your URL
	$headers = array('Item','Views');
	$date = date("Y") . "-" . date("n");
	$path = "&date=".$date;
	$rowset = getDSpaceData($path);
	if ($rowset) {
		array_shift($rowset);
		$rowset = keepTop($rowset,5);
		doTable($id,$title,$note,$headers,$rowset,"Table");
	}
	
	$id = "ex_libris_status";
	$title = "How is the library system doing right now?";
	$note = "<strong><a href='http://primo.example.com/path/to/search.do?vid=LIN'>Primo</a>:</strong> $status_P.$note_P<br/><br/><strong>Alma:</strong> $status_A.$note_A"; // your URL
	$headers = array();
	$rowset = array();
	doTable($id,$title,$note,$headers,$rowset);

	$id = "ezproxy_audit";
	$title = "Database logins";
	$rowset = getEZproxy();
	if ($rowset) {
		$note = "So far today, <span class='value'>" . $rowset['logins'] . "</span> people have logged in to library databases. (On-campus, most databases don't need a log-in.)";
		// can also refer to $rowset['logouts'], $rowset['failures']
		$headers = array("Usergroup","Log-ins");
		$rowset = array(array_keys($proxy_groups),array_values($rowset['groups']));
		$rowset = swapColRow($rowset);
		doTable($id,$title,$note,$headers,$rowset,"Bar");
	}

	$id = "libraryh3lp_by_month";
	$title = "LibraryH3lp";
	$note = "In the last year, we've answered the following questions on <a href='http://example.com/libraryh3lp/'>LibraryH3lp</a>:"; // your URL
	$headers = array("Month","Questions");
	$path = "reports/chats-per-month";
	$rowset = getLH3Rows($path);
	if ($rowset) {
		$rowset = str_replace("protocol,","",$rowset);
		$rowset = str_replace("web,","",$rowset);
		$rowset = csv2array($rowset);
		$rowset = swapColRow($rowset);
		array_pop($rowset);
		$rowset = keepBottom($rowset,12);
		krsort($rowset);
		$rowset = totalColumn($rowset,1);
		doTable($id,$title,$note,$headers,$rowset,"Bar");
	}

	$id = "mrbs";
	$title = "Booking a room";
	$now = date('Y-m-d');
	$from = strtotime($now . ' -1 year');
	$from = date('Y-m-d', $from);
	$to = strtotime($now . ' -1 day');
	$to = date('Y-m-d', $to);
	$rowset = getMRBSRows($from,$to);
	if ($rowset['rooms']) {
		$note = "In the last year, ";
		$note .= "<span class='value'>" . $rowset['users'] . "</span>";
		$note .= " people <a href='".$mrbs_url."'>booked ";
		$note .= "<span class='value'>" . $rowset['rooms'] . "</span>";
		$note .= " of our rooms</a> for ";
		$note .= "<span class='value'>" . $rowset['hours'] . "</span>";
		$note .= " hours of study and meetings.";
		$headers = "";
		$rowset = array();
		doTable($id,$title,$note,$headers,array());
	}

	$id = "oai_count";
	$title = "OAI items";
	$headers = array();
	$rowset = getOAIdata("http://example.com/dspace-oai/request");	// your URL
	if ($rowset['items']) {
		$note = "There are <span class='value'>" . $rowset['items'] . "</span> open access items in <a href='http://example.com/'>our institutional repository</a>.";	// your URL
		doTable($id,$title,$note,$headers,array());
	}
	
	$id = "scopus_total";
	$title = "Scopus";
	$rowset = getScopus();
	if ($rowset['authors']) {
		$doclink = $proxy . "http://www.scopus.com/results/results.url?src=s&sot=aff&s=%28AF-ID%28" . $scopus_instid . "%29%29";
		$note = '<span class='value'>'.$rowset['authors'].'</span> authors ';
		$note .= 'from '.$rowset['institution'].' ';
		$note .= 'have authored <a href="'.$doclink.'"><span class='value'>'.$rowset['documents'].'</span> research papers</a> ';
		$note .= 'indexed in Scopus.';
		$headers = "";
		doTable($id,$title,$note,$headers,array());
	}

	$id = "scopus_monthly";
	$title = "Recent papers indexed in Scopus";
	$note = "Note only papers which include a publication month are counted.";
	$headers = array("Year","Month","Number");
//	$rowset = getScopus();  // Same as above; no need to fetch it from Scopus again!
	if ($rowset['monthly']) {
		$rowset=$rowset['monthly'];
		krsort($rowset);
		$rowset = mergeMonthYear($rowset,1,0);
		$rowset = convertDates($rowset,0,'F Y','M Y');
		$rowset = totalColumn($rowset,1);
		doTable($id,$title,$note,$headers,$rowset,"Bar");
	}

	$id = "wikipedia_articles";
	$title = "Did you know...";
	$string = "http://example.com/"; // your URL here
	$string = '"'.$string.'"';
	$rowset = getWikiLinks(urlencode($string));
	if ($rowset['pages']) {
		$note = "<a href='https://en.wikipedia.org/w/index.php?search=insource%3A" . $string . "&title=Special%3ASearch&go=Go'>";
		$note .= "<span class='value'>" . $rowset['pages'] . "</span>";
		$note .= " Wikipedia pages</a>";
		$note .= " link to research in <a href='http://example.com/'>our institutional repository</a>."; // your URL here
		$headers = "";
		$rowset = array();
		doTable($id,$title,$note,$headers,$rowset);
	}
?>