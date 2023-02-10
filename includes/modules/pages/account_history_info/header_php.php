<?php
/**
 * account_history_info header_php.php
 */
if (!isset($_SESSION['customer_id'])) {
	redirect(href_link(FILENAME_LOGIN, '', 'SSL'));
}
if (!isset($_GET['oID'])
	|| !($orderInfo = get_order($_GET['oID']))
	|| !($orderProductInfo = get_order_product($_GET['oID']))) {
	redirect(href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
}
$orderStatusHistory = get_order_status_history($_GET['oID']);
//Breadcrumb
$breadcrumb->add(__('My Dashboard'), 'sub', href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(__('My Orders'), 'sub', href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
$breadcrumb->add(__('Order #%s - %s', put_orderNO($orderInfo['order_id']), get_order_status_name($orderInfo['order_status_id'])), 'root');
