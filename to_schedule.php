<?php
require_once('data_functions.php');

/* Alma opening hours */
	$rowset = getAlmaHours();
	if ($rowset) {
		$widget = array(
			'id' => "alma_hours",
			'title' => "Open Hours",
			'note' => "No-one in their right mind will want to see this on a stats page, but it creates a useful day-by-day listing of open hours in CSV format. By saving it with a 'None' format, data from the file will be never display on the website.",
			'headers' => array("Date","Day","From","To","Status","Exceptions"),
			'format' => "None",
			'tags' => array("library"),
			);
		doTable($rowset,$widget);
	}

/* Altmetrics stats */
	$path = array();
	$path[] = ""; // eg "&group=lincoln%3Agroup%3A4&order_by=score";
	$path[] = ""; // eg "&group=lincoln%3Agroup%3A12&order_by=score";
	$path[] = ""; // eg "&group=lincoln%3Agroup%3A16&order_by=score";
	$rowset = getAltmetric($path);
	if ($rowset) {
		$widget = array(
			'id' => "altmetrics_by_month_and_dept",
			'title' => "Altmetric",
			'note' => "Numbers of social media mentions for each faculty's papers as measured by <a href='http://www.altmetric.com/login.php'>Altmetric</a>.",
			'format' => "Radar",
			'tags' => array("research"),
			);
		foreach($rowset as $s => $set) {
			$rowset[$s] = keepBottom($set,12);
			krsort($rowset[$s]);
		}
		$mergeheaders = array('Faculty 1','Faculty 2','Faculty 3'); // your names
		$newrowset = mergeTables($rowset,$mergeheaders,2,1);
		$newrowset = convertDates($newrowset,0,'Y-m','M Y');
		$totals = array(1,2,3);
		$newrowset = totalColumn($newrowset,$totals);
		doTable($newrowset,$widget);
	}

/* Career Advice */
	$start_date = date("j-M-Y", time() - 60*60*24*31);
	$end_date = date("j-M-Y", time() - 60*60*24*1);
	$report = 'Appointments/111?startDate='.$start_date.'&endDate='.$end_date;
	$rowset = getCareerhubData($report);
	if ($rowset) {
		$widget = array(
			'id' => "careerhub",
			'title' => "Career advice",
			'note' => "People attended <span class='value'>" . $rowset['Total'] . "</span> appointments with our <a href='https://example.com/'>Careers and Employment Advisor</a> on the following topics in the last 30 days.",
			'headers' => array("Topic","Appointments"),
			'format' => "Bar",
			'tags' => array("careers"),
			);
		array_pop($rowset);
		$rowset = sortByColumn($rowset,1);
		doTable($rowset,$widget);
	}

/* DSpace top items */
	$date = date("Y") . "-" . date("n");
	$path = "&date=".$date;
	$rowset = getDSpaceData($path);
	if ($rowset) {
		$widget = array(
			'id' => "dspace_top_items",
			'title' => "Top 5 DSpace items",
			'note' => "Top 5 most-viewed items on <a href='https://example.com/statistics'>our institutional archive</a> this calendar month:",
			'headers' => array("Item","Views"),
			'format' => "Table",
			'tags' => array("research"),
			);
		array_shift($rowset);
		$rowset = keepTop($rowset,5);
		doTable($rowset,$widget);
	}
	
/* Alma checkout locations - example of mergeTables */
	$path = array();
	$path[] = ""; // eg "/shared/Lincoln/Reports/Dashboard/Issues per month at circ desk";
	$path[] = ""; // eg "/shared/Lincoln/Reports/Dashboard/Issues per month at self-check";
	$rowset = array();
	foreach ($path as $p) {
		$rowset[] = getAlmaRows($p);
	}
	if ($rowset[0]) {
		$widget = array(
			'id' => "ex_libris_alma",
			'title' => "Alma checkout locations",
			'format' => "Line",
			'tags' => array("library"),
			);
		foreach ($rowset as $s => $set) {
			$rowset[$s] = keepBottom($set,12);
			krsort($rowset[$s]);
		}
		$mergeheaders = array('Circ desks','Self-checks');
		$newrowset = mergeTables($rowset,$mergeheaders,5,4);
		$newrowset = keepRight($newrowset,4);
		$newrowset = mergeMonthYear($newrowset,0,1);
		$newrowset = convertDates($newrowset,0,'F Y','M Y');
		$totals = array(1,2);
		$newrowset = totalColumn($newrowset,$totals);
		doTable($newrowset,$widget);
	}

/* Primo search terms - example of Wordle */
	$path = ""; // eg "/shared/Primo Lincoln University (New Zealand)/Reports/3. Ten most popular searches in the last 30 days";
	$rowset = getPrimoRows($path);
	if ($rowset) {
		$widget = array(
			'id' => "ex_libris_primo",
			'title' => "What are people searching for in Primo?",
			'note' => "10 most popular search terms in the last month.",
			'headers' => array("Search terms", "Searches"),
			'format' => "Wordle",
			'tags' => array("library"),
			);
		$columnOrder = array(1,2);
		$rowset = getColumns($rowset,$columnOrder);
		$rowset = sortByColumn($rowset, 1);
		krsort($rowset);
		$totals = array(1);
		$rowset = totalColumn($rowset,$totals);
		doTable($rowset,$widget);
	}

/* System status */
	$rowset = array();
	$widget = array(
		'id' => "ex_libris_status",
		'title' => "How is the library system doing right now?",
		'note' => "<strong><a href='http://primo.example.com/path/to/search.do?vid=ABC'>Primo</a>:</strong> $status_P.$note_P<br/><br/><strong>Alma:</strong> $status_A.$note_A",
		'tags' => array("library"),
		);
	doTable($rowset,$widget);

/* EZproxy usage */
	$rowset = getEZproxy();
	if ($rowset) {
		$widget = array(
			'id' => "ezproxy_audit",
			'title' => "Off-campus database logins",
			'note' => "So far today, <span class='value'>" . $rowset['logins'] . "</span> people have logged in to <a href='http://ezproxy.lincoln.ac.nz/login'>library databases</a>. (On-campus, most databases don't need a log-in.)",
			'headers' => array("Usergroup","Log-ins"),
			'format' => "Bar",
			'tags' => array("library"),
			);
		$rowset = array(array_keys($proxy_groups),array_values($rowset['groups']));
		// can also refer to $rowset['logouts'], $rowset['failures']
		$rowset = swapColRow($rowset);
		doTable($rowset,$widget);
	}

/* LibraryH3lp chats per month */
	$rowset = getLH3Rows("reports/chats-per-month");
	if ($rowset) {
		$widget = array(
			'id' => "libraryh3lp_by_month",
			'title' => "LibraryH3lp",
			'note' => "In the last year, we've answered the following questions on <a href='http://example.com/libraryh3lp/'>LibraryH3lp</a>:",
			'headers' => array("Month","Questions"),
			'format' => "Bar",
			'tags' => array("library", "research", "teaching", "academic skills", "careers"),
			);
		$rowset = str_replace("protocol,","",$rowset);
		$rowset = str_replace("web,","",$rowset);
		$rowset = csv2array($rowset);
		$rowset = swapColRow($rowset);
		array_pop($rowset);
		$rowset = keepBottom($rowset,12);
		krsort($rowset);
		$rowset = totalColumn($rowset,1);
		$rowset = convertDates($rowset,0,'n/Y','M Y');
		doTable($rowset,$widget);
	}

/* MRBS room bookings */
	$now = date('Y-m-d');
	$from = date('Y-m-d', strtotime($now . ' -1 year'));
	$to = date('Y-m-d', strtotime($now . ' -1 day'));
	$rowset = getMRBSRows($from,$to);
	if ($rowset['rooms']) {
		$note = "In the last year, <span class='value'>" . $rowset['users'] . "</span>";
		$note .= " people <a href='".$mrbs_url."'>booked ";
		$note .= "<span class='value'>" . $rowset['rooms'] . "</span>";
		$note .= " of our rooms</a> for ";
		$note .= "<span class='value'>" . $rowset['hours'] . "</span>";
		$note .= " hours of study and meetings.";
		$widget = array(
			'id' => "mrbs",
			'title' => "Booking a room",
			'note' => $note,
			'tags' => array("library", "academic skills"),
			);
		$headers = "";
		$rowset = array();
		doTable($rowset,$widget);
	}

/* OAI item count 
	$rowset = getOAIdata("http://example.com/dspace-oai/request");	// your URL
	if ($rowset['items']) {
		$widget = array(
			'id' => "oai_count",
			'title' => "OAI items",
			'note' => "There are <span class='value'>" . $rowset['items'] . "</span> open access items in <a href='http://example.com/'>our institutional repository</a>.",
			'tags' => array("open access"),
			);
		doTable($rowset,$widget);
	}
	*/
/* Scopus stats */
	$rowset = getScopus();
	$doclink = $proxy . "http://www.scopus.com/results/results.url?src=s&sot=aff&s=%28AF-ID%28" . $scopus_instid . "%29%29";
	if ($rowset['authors'] && $rowset['monthly']) {
		$widget = array(
			'id' => "scopus",
			'title' => "Research papers indexed in Scopus",
			'note' => '<span class="value">'.$rowset['authors'].'</span> authors from '.$rowset['institution'].' have authored <a href="'.$doclink.'"><span class="value">'.$rowset['documents'].'</span> research papers</a> indexed in Scopus. Note only papers which include a publication month are counted below.',
			'headers' => array("Month","Number"),
			'format' => "Bar",
			'tags' => array("research"),
			);
		$newrowset = $rowset['monthly'];
		krsort($newrowset);
		$newrowset = mergeMonthYear($newrowset,1,0);
		$newrowset = convertDates($newrowset,0,'F Y','M Y');
		$newrowset = totalColumn($newrowset,1);
		doTable($newrowset,$widget);
	}

/* SuperSaaS workshop bookings */
	$start_date = date('Y-m-d H:i:s',mktime('00','00','00',date('n')-6,date('j'),date('Y'))); // last 6 months - NB limit is 1000 bookings
	$rowset = getSuperSaas($start_date);
	if ($rowset) {
		$widget = array(
			'id' => "m_supersaas",
			'title' => "Booking a workshop",
			'note' => "Bookings made in the last 6 months:",
			'headers' => array("","Workshops","Users","Total&nbsp;bookings","Waitlisted"),
			'format' => "Bar",
			'tags' => array("academic skills", "research", "teaching", "library"),
			);
		$rowset = totalColumn($rowset,array(1,2,3,4));
		doTable($rowset,$widget);
	}

/* Wikipedia citations */
	$sites = array(
			//array("string to search", "url to link to", "name of site"),
			array("example.com","http://www.example.com","Our Institutional Repository"),
			);
	$rowset = getWikiLinks($sites);
	if ($rowset) {
		$note = "";
		foreach($rowset as $r=>$row) {
			if ($row[3]) {
				$note .= "<a href='https://en.wikipedia.org/w/index.php?search=insource%3A%22" . urlencode($row[0]) . "%22&title=Special%3ASearch&go=Go'>";
				$note .= "<span class='value'>" . $row[3] . "</span>";
				$note .= " Wikipedia pages</a>";
				$note .= " link to content in <a href='".$row[1]."'>".$row[2]."</a>.";
			}
			if ($r+1<count($rowset)) {
				$note .= "</p><p>";
			}
		}
		if ($note != "") {
			$widget = array(
				'id' => "wikipedia_articles",
				'title' => "Did you know...",
				'note' => $note,
				'tags' => array("research", "open access"),
				);
			$rowset = array();
			doTable($rowset,$widget);
		}
	}
?>