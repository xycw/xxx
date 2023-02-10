<?php
/**
 * checkout_process header_php.php
 */
if (isset($_SESSION['order_id']) && ($orderInfo = get_order($_SESSION['order_id']))
	&& ($orderProductInfo = get_order_product($_SESSION['order_id']))) {
	require(DIR_FS_CATALOG_CLASSES . 'payment_method.php');
	$payment_method = new payment_method($orderInfo['payment_method']['code']);
} else {
	redirect(href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
}
