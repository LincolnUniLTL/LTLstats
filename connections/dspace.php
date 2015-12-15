<?php
	function getDSpaceData($params) {
		global $dspace_url;
		$url = $dspace_url . $params;
		$rowset = getRowset($url,'decodeDSpace');
		return $rowset;
	}

	function decodeDSpace($result) {
		$XML = simplexml_load_string($result);
		$rowset = array();
		foreach ($XML->body->div[0] as $div) {
			if ($div->attributes()->id == "aspect.artifactbrowser.StatisticsViewer.div.items_viewed") {
				$n = 0;
				foreach ($div->table[0] as $row) {
					if ($row->cell[0]->xref) {
						$title = $row->cell[0]->xref;
						$url = $row->cell[0]->xref->attributes()->target;
						$rowset[$n][0] = "<a href='".$url."'>".$title."</a>";
					} else {
						$rowset[$n][0] = $row->cell[0];
					}
					$rowset[$n][1] = $row->cell[1];
					$n++;
				}
			}
		}
		return $rowset;
	}
?>