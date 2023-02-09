<?php
/**
 * account header_php.php
 */
if (!isset($_SESSION['customer_id'])) {
	redirect(href_link(FILENAME_LOGIN, '', 'SSL'));
}
//Recent Orders
$sql = "SELECT o.order_id, o.date_added, o.shipping_firstname,
			   o.shipping_lastname, o.shipping_country, o.currency_code,
			   o.currency_value, o.order_total, os.name AS order_status_name
		FROM   " . TABLE_ORDERS . " o, " . TABLE_ORDER_STATUS . " os
		WHERE  o.order_status_id = os.order_status_id
		AND    o.customer_id = :customerID
		ORDER BY order_id DESC";
$sql = $db->bindVars($sql, ':customerID', $_SESSION['customer_id'], 'integer');
$result = $db->Execute($sql, 5);
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
//Default Address
$billingAddress = get_address($_SESSION['customer_billing_address_id'], $_SESSION['customer_id']);
$shippingAddress = get_address($_SESSION['customer_shipping_address_id'], $_SESSION['customer_id']);
//Recent Reviews
$sql = "SELECT pr.rating, pr.date_added,
			   p.product_id, p.name
		FROM   " . TABLE_PRODUCT_REVIEW . " pr, " . TABLE_PRODUCT . " p
		WHERE  pr.product_id = p.product_id
		AND    pr.customer_id = :customerID
		ORDER BY pr.product_review_id DESC";
$sql = $db->bindVars($sql, ':customerID', $_SESSION['customer_id'], 'integer');
$result = $db->Execute($sql, 5);
$reviewList = array();
while (!$result->EOF) {
	$reviewList[] = array(
		'rating'       => $result->fields['rating'],
		'date_added'   => $result->fields['date_added'],
		'product_id'   => $result->fields['product_id'],
		'product_name' => $result->fields['name']
	);
	$result->MoveNext();
}
//Breadcrumb
$breadcrumb->add(__('My Dashboard'), 'root');
