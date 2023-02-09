<?php
/**
 * sidebar popular_search.php
 */
if (!defined('POPULAR_SEARCH_SIDEBAR_LIMIT')) define('POPULAR_SEARCH_SIDEBAR_LIMIT', '10');
if (!defined('POPULAR_SEARCH_KEYWORDS')) define('POPULAR_SEARCH_KEYWORDS', '');
$popularSearchSidebarList = array();
if (POPULAR_SEARCH_SIDEBAR_LIMIT>0) {
	if (POPULAR_SEARCH_KEYWORDS!='') {
		$popularSearchKeywords = explode(',', POPULAR_SEARCH_KEYWORDS);
		foreach ($popularSearchKeywords as $val) {
			if (count($popularSearchSidebarList) >= POPULAR_SEARCH_SIDEBAR_LIMIT) {
				break;
			}
			$popularSearchSidebarList[] = array(
				'search' => $val,
				'freq'   => 10
			);
		}
	} else {
		$sql = "SELECT search, freq
				FROM   " . TABLE_POPULAR_SEARCH . "
				WHERE  status = 1
				ORDER BY freq DESC, search ASC";
		$result = $db->Execute($sql, POPULAR_SEARCH_SIDEBAR_LIMIT);
		while (!$result->EOF) {
			$popularSearchSidebarList[] = array(
				'search' => $result->fields['search'],
				'freq'   => $result->fields['freq']
			);
			$result->MoveNext();
		}
	}
}