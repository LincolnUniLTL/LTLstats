<?php
	function getScopus() {
		global $scopus_url, $scopus_apikey, $scopus_instid;
		$url = $scopus_url . $scopus_instid . "?apiKey=" . $scopus_apikey;
		$rowset = getRowset($url,'decodeScopus');
		$this_year = date("Y");
		$this_month = date("n")-1;
		$months = array("January","February","March","April","May","June","July","August","September","October","November","December");
		for ($i=0; $i<12; $i++) {  // <12
			$this_url = "http://api.elsevier.com/content/search/scopus?query=AF-ID%28%22" . $scopus_instid . "%22%29%20AND%20PUBDATETXT%28%22" . $months[$this_month] . "%22%29%20AND%20PUBDATETXT%28%22" . $this_year . "%22%29&apiKey=" . $scopus_apikey;
			$this_docs = getRowset($this_url,'decodeScopusMonthly');
			$rowset['monthly'][11-$i] = array($this_year, $months[$this_month], $this_docs);
			ksort($rowset['monthly']);
			if ($this_month == 0) {
				$this_month = 11;
				$this_year--;
			} else {
				$this_month--;
			}
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