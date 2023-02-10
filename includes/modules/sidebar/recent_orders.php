<?php
/**
 * sidebar recent_orders.php
 */
if (!defined('RECENT_ORDERS_SIDEBAR_LIMIT')) define('RECENT_ORDERS_SIDEBAR_LIMIT', '10');
$recentOrdersSidebarList = array();
if (RECENT_ORDERS_SIDEBAR_LIMIT>0) {
	$sql = "SELECT o.shipping_country, op.product_id, op.name 
			FROM " . TABLE_ORDER_PRODUCT . " op RIGHT JOIN " . TABLE_ORDERS . " o on op.order_id = o.order_id
			ORDER BY op.order_id DESC";
	$result = $db->Execute($sql, RECENT_ORDERS_SIDEBAR_LIMIT, true, 300);
	while (!$result->EOF) {
		$recentOrdersSidebarList[] = array(
			'product_id'       => $result->fields['product_id'],
			'nameAlt'          => output_string($result->fields['name']),
			'name'             => trunc_string($result->fields['name'], PRODUCT_NAME_SIDEBAR_MAX_LENGTH),
			'shipping_country' => $result->fields['shipping_country'],
		);
		$result->MoveNext();
	}
}
