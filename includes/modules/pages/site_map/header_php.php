<?php
/**
 * site_map header_php.php
 */
$sql = "SELECT category_id, name
		FROM   " . TABLE_CATEGORY . "
		WHERE status = 1
		ORDER BY category_id";
$result = $db->Execute($sql, '', true, 86400);
$siteMapList = array();
while (!$result->EOF) {
	$sql = "SELECT product_id, name
			FROM   " . TABLE_PRODUCT . "
			WHERE  status = 1
			AND    master_category_id = :masterCategoryID
			ORDER BY product_id";
	$sql = $db->bindVars($sql, ':masterCategoryID', $result->fields['category_id'], 'integer');
	$productResult = $db->Execute($sql, '', true, 86400);
	$tempList = array();
	while (!$productResult->EOF) {
		$tempList[] = array(
			'name' => $productResult->fields['name'],
			'link' => href_link(FILENAME_PRODUCT, 'pID=' . $productResult->fields['product_id'])
		);
		$productResult->MoveNext();
	}
	$siteMapList[] = array(
		'name' => $result->fields['name'],
		'link' => href_link(FILENAME_CATEGORY, 'cID=' . $result->fields['category_id']),
		'children' => $tempList
	);
	$result->MoveNext();
}
//Breadcrumb
$breadcrumb->add(__('Site Map'), 'root');
