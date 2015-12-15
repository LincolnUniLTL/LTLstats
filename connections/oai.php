<?php
	function getOAIData($OAIbase) {
		$url = $OAIbase . '?verb=ListIdentifiers&metadataPrefix=oai_dc';
		$rowset = getRowset($url,'decodeOAI');
		return $rowset;
	}

	function decodeOAI($result) {
		$XML = simplexml_load_string($result);
		$rowset = array();
		if ($XML->ListIdentifiers[0]->resumptionToken) {
			$rowset['items'] = $XML->ListIdentifiers[0]->resumptionToken[@completeListSize];
		} else {
			$rowset['items'] = $XML->ListIdentifiers[0]->count();
		}
		return $rowset;
	}
?>