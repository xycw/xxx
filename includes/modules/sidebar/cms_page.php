<?php
/**
 * sideber cms_page.php
 */
$cmsPageSidebarList = array();
$sql = "SELECT cms_page_id, name
		FROM   " . TABLE_CMS_PAGE . "
		WHERE  status = 1
		ORDER BY sort_order";
$result = $db->Execute($sql);
while (!$result->EOF) {
	$cmsPageSidebarList[] = array(
		'cms_page_id' => $result->fields['cms_page_id'],
		'name'       => $result->fields['name'],
	);
	$result->MoveNext();
}
