<?php
	function getScopus($inst_id) {
		$url = "http://api.elsevier.com/content/affiliation/affiliation_id/";
		$api_key = ""; // your API key
		$url = $url . $inst_id . "?apiKey=" . $api_key;
		$rowset = getRowset($url,'decodeScopus');
		for ($i=0; $i<12; $i++) {
			$now_month = date("n");
			$this_year = date("Y");
			$months = array("January","February","March","April","May","June","July","August","September","October","November","December");
			while (key($months) != $now_month) {
				next($months);
			}
			for ($j=-1; $j<$i; $j++) {
				if (key($months) == 0) {
					$this_month = end($months);
					$this_year--;
				} else {
					$this_month = prev($months);
				}
			}
			$this_url = "http://api.elsevier.com/content/search/scopus?query=AF-ID%28%22" . $inst_id . "%22%29%20AND%20PUBDATETXT%28%22" . $this_month . "%22%29%20AND%20PUBDATETXT%28%22" . $this_year . "%22%29&apiKey=" . $api_key;
			$this_docs = getRowset($this_url,'decodeScopusMonthly');
			$k = 11-$i;
			$rowset['monthly'][$k] = array($this_year, $this_month, $this_docs);
			ksort($rowset['monthly']);
		}
		return $rowset;
	}
	
	function decodeScopus($result) {
		$XML = simplexml_load_string($result);
		$rowset = array();
		$rowset['authors'] = $XML->coredata->{'author-count'};
		$rowset['documents'] = $XML->coredata->{'document-count'};
		$rowset['institution'] = $XML->{'affiliation-name'};
		return $rowset;
	}
	
	function decodeScopusMonthly($result) {
		$JSON = json_decode($result, true);
		$rowset = $JSON['search-results']['opensearch:totalResults'];
		return $rowset;
	}
?>