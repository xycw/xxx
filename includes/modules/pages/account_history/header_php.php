<?php
/**
 * account_history header_php.php
 */
if (!isset($_SESSION['customer_id'])) {
	redirect(href_link(FILENAME_LOGIN, '', 'SSL'));
}
$sql = "SELECT COUNT(*) AS total FROM " . TABLE_ORDERS . " WHERE customer_id = :customerID";
$sql = $db->bindVars($sql, ':customerID', $_SESSION['customer_id'], 'integer');
$result = $db->Execute($sql);
$pager_config['total'] = $result->fields['total'];
require(DIR_FS_CATALOG_CLASSES . 'pager.php');
$pager = new pager($pager_config);
$sql = "SELECT o.order_id, o.date_added, o.shipping_firstname,
			   o.shipping_lastname, o.shipping_country, o.currency_code,
			   o.currency_value, o.order_total, os.name AS order_status_name
		FROM   " . TABLE_ORDERS . " o, " . TABLE_ORDER_STATUS . " os
		WHERE  o.order_status_id = os.order_status_id
		AND    customer_id = :customerID
		ORDER BY order_id DESC";
$sql = $db->bindVars($sql, ':customerID', $_SESSION['customer_id'], 'integer');
$result = $db->Execute($sql, $pager->getLimitSql());
$orderList = array();
while (!$result->EOF) {
	$orderList[] = array(
		'order_id'          => $result->fields['order_id'],
		'date_added'        => $result->fields['date_added'],
		'shipping_name'     => $result->fields['shipping_firstname'] . ' ' . $result->fields['shipping_lastname'],
		'shipping_country'  => $result->fields['shipping_country'],
		'order_status_name' => $result->fields['order_status_name'],
		'order_total'       => $currencies->display_price($result->fields['order_total'], $result->fields['currency_code'], $result->fields['currency_value'])
	);
	$result->MoveNext();
}
//Breadcrumb
$breadcrumb->add(__('My Dashboard'), 'sub', href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(__('My Orders'), 'root');
