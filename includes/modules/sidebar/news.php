<?php
/**
 * sideber news.php
 */
$newsSidebarList = array();
$sql = "SELECT news_id, name
		FROM   news
		ORDER BY news_id DESC";
$result = $db->Execute($sql, 10);
while (!$result->EOF) {
	$newsSidebarList[] = array(
		'news_id' => $result->fields['news_id'],
		'name'    => $result->fields['name'],
	);
	$result->MoveNext();
}
