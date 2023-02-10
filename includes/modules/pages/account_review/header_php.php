<?php
/**
 * account_review header_php.php
 */
if (!isset($_SESSION['customer_id'])) {
	redirect(href_link(FILENAME_LOGIN, '', 'SSL'));
}

$sql = "SELECT COUNT(*) AS total
		FROM   " . TABLE_PRODUCT_REVIEW . "
		WHERE  customer_id = :customerID";
$sql = $db->bindVars($sql, ':customerID', $_SESSION['customer_id'], 'integer');
$result = $db->Execute($sql);
require(DIR_FS_CATALOG_CLASSES . 'pager.php');
$pager_config['total'] = $result->fields['total'];
$pager = new pager($pager_config);

$sql = "SELECT pr.rating, pr.content, pr.date_added,
			   p.product_id, p.name
		FROM   " . TABLE_PRODUCT_REVIEW . " pr, " . TABLE_PRODUCT . " p
		WHERE  pr.product_id = p.product_id
		AND    pr.customer_id = :customerID
		ORDER BY pr.product_review_id DESC";
$sql = $db->bindVars($sql, ':customerID', $_SESSION['customer_id'], 'integer');
$result = $db->Execute($sql, $pager->getLimitSql());
$reviewList = array();
while (!$result->EOF) {
	$reviewList[] = array(
		'rating'       => $result->fields['rating'],
		'content'      => $result->fields['content'],
		'date_added'   => $result->fields['date_added'],
		'product_id'   => $result->fields['product_id'],
		'product_name' => $result->fields['name']
	);
	$result->MoveNext();
}

//Breadcrumb
$breadcrumb->add(__('My Dashboard'), 'sub', href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(__('My Product Reviews'), 'root');
