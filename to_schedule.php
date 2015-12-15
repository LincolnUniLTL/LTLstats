<?php
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
		doTable($id,$title,$note,$headers,$rowset);
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
		doTable($id,$title,$note,$headers,$newrowset);
	}

	$id = "alma_hours";
	$title = "Open Hours";
	$note = "No-one in their right mind will want to see this on a stats page, but it creates a useful day-by-day listing of open hours in CSV format. By saving it with a .txt extension, the file will be generated for you but won't display for users on the website.";
	$headers = array("Date","Day","From","To","Status","Exceptions");
	$rowset = getAlmaHours();
	if ($rowset) {
		doTable($id,$title,$note,$headers,$rowset,"txt");
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
		doTable($id,$title,$note,$headers,$newrowset);
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
		doTable($id,$title,$note,$headers,$rowset);
	}
	
	$id = "ex_libris_status";
	$title = "How is the library system doing right now?";
	$note = "<strong><a href='http://primo.example.com/path/to/search.do?vid=LIN'>Primo</a>:</strong> $status_P.$note_P<br/><br/><strong>Alma:</strong> $status_A.$note_A"; // your URL
	$headers = array();
	$rowset = array();
	doTable($id,$title,$note,$headers,$rowset);

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
		doTable($id,$title,$note,$headers,$rowset);
	}

	$id = "oai_count";
	$title = "OAI items";
	$headers = array();
	$rowset = getOAIdata("http://example.com/dspace-oai/request");	// your URL
	if ($rowset['items']) {
		$note = "There are " . $rowset['items'] . " open access items in <a href='http://example.com/'>our institutional repository</a>.";	// your URL
		doTable($id,$title,$note,$headers,array());
	}
	
	$id = "scopus_total";
	$title = "Scopus";
	$rowset = getScopus();
	if ($rowset['authors']) {
		$doclink = $proxy . "http://www.scopus.com/results/results.url?src=s&sot=aff&s=%28AF-ID%28" . $scopus_instid . "%29%29";
		$note = '<strong>'.$rowset['authors'].'</strong> authors ';
		$note .= 'from '.$rowset['institution'].' ';
		$note .= 'have authored <a href="'.$doclink.'"><strong>'.$rowset['documents'].'</strong> research papers</a> ';
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
		krsort($rowset['monthly']);
		$rowset['monthly'] = totalColumn($rowset['monthly'],2);
		doTable($id,$title,$note,$headers,$rowset['monthly']);
	}

	$id = "wikipedia_articles";
	$title = "Did you know...";
	$string = "http://example.com/"; // your URL here
	$string = '"'.$string.'"';
	$rowset = getWikiLinks(urlencode($string));
	if ($rowset['pages']) {
		$note = "<a href='https://en.wikipedia.org/w/index.php?search=insource%3A" . $string . "&title=Special%3ASearch&go=Go'>";
		$note .= $rowset['pages'];
		$note .= " Wikipedia pages</a>";
		$note .= " link to research in <a href='http://example.com/'>our institutional repository</a>."; // your URL here
		$headers = "";
		$rowset = array();
		doTable($id,$title,$note,$headers,$rowset);
	}
?>