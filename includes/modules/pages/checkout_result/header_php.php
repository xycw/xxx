<?php
/**
 * checkout_result header_php.php
 */
if (isset($_SESSION['order_id']) && !empty($_SESSION['order_id'])
	&& ($orderInfo = get_order($_SESSION['order_id']))
	&& ($orderProductInfo = get_order_product($_SESSION['order_id']))) {
	$_SESSION['old_order_id'] = $_SESSION['order_id'];
	unset($_SESSION['order_id']);
	require(DIR_FS_CATALOG_CLASSES . 'payment_method.php');
	$paymentMethod = new payment_method($orderInfo['payment_method']['code']);
	$paymentResult = $paymentMethod->result();
	if (!isset($paymentResult['order_status_id'])) $paymentResult = array('order_status_id' => $paymentResult, 'billing' => '', 'remarks' => '');
	//check payment result
	$sql = "SELECT COUNT(*) AS total FROM " . TABLE_ORDER_STATUS . " WHERE order_status_id = :orderStatusID";
	$sql = $db->bindVars($sql, ':orderStatusID', $paymentResult['order_status_id'], 'integer');
	$checkPaymentResult = $db->Execute($sql);
	if ($checkPaymentResult->fields['total'] > 0) {
		$orderInfo['payment_method']['billing'] = $paymentResult['billing'];
		//send confirm mail
		if ($orderInfo['send_confirm_mail'] == 0
			&& send_confirm_mail($orderInfo, $orderProductInfo, $paymentResult['order_status_id'])) {
			$orderInfo['send_confirm_mail'] = 1;
		}
		//orders
		$sql = "UPDATE " . TABLE_ORDERS . " SET order_status_id = :orderStatusID, payment_method_billing = :paymentMethodBilling, send_confirm_mail = :sendConfirmMail WHERE order_id = :orderID";
		$sql = $db->bindVars($sql, ':orderStatusID', $paymentResult['order_status_id'], 'integer');
		$sql = $db->bindVars($sql, ':paymentMethodBilling', $paymentResult['billing'], 'string');
		$sql = $db->bindVars($sql, ':sendConfirmMail', $orderInfo['send_confirm_mail'], 'integer');
		$sql = $db->bindVars($sql, ':orderID', $orderInfo['order_id'], 'integer');
		$db->Execute($sql);
		//order_status_history
		$sqlDataArray = array(
			array('fieldName'=>'order_id', 'value'=>$orderInfo['order_id'], 'type'=>'integer'),
			array('fieldName'=>'order_status_id', 'value'=>$paymentResult['order_status_id'], 'type'=>'integer'),
			array('fieldName'=>'remarks', 'value'=>$paymentResult['remarks'], 'type'=>'string'),
			array('fieldName'=>'date_added', 'value'=>'NOW()', 'type'=>'noquotestring')
		);
		$db->perform(TABLE_ORDER_STATUS_HISTORY, $sqlDataArray);
		if ($paymentResult['order_status_id'] == 3) {
			//product in_stock
			foreach ($orderProductInfo as $_product) {
				$sql = "UPDATE " . TABLE_PRODUCT . " SET ordered = ordered+:productQty WHERE product_id = :productID";
				$sql = $db->bindVars($sql, ':productQty', $_product['qty'], 'integer');
				$sql = $db->bindVars($sql, ':productID', $_product['product_id'], 'integer');
				$db->Execute($sql);
				$sql = "UPDATE " . TABLE_PRODUCT . " SET in_stock = 0 WHERE product_id = :productID AND in_stock = 1 AND stock_qty > 0 AND ordered >= stock_qty";
				$sql = $db->bindVars($sql, ':productID', $_product['product_id'], 'integer');
				$db->Execute($sql);
			}
		}
	}
	redirect(href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL'));
} elseif ((isset($_GET['order_token']) && !empty($_GET['order_token']))
	&& ($_SESSION['old_order_id'] = get_order_id_by_token($_GET['order_token']))
	&& ($orderInfo = get_order($_SESSION['old_order_id']))
	&& ($orderProductInfo = get_order_product($_SESSION['old_order_id']))) {
	//nothing
} elseif ((isset($_SESSION['old_order_id']) && !empty($_SESSION['old_order_id']))
	&& ($orderInfo = get_order($_SESSION['old_order_id']))
	&& ($orderProductInfo = get_order_product($_SESSION['old_order_id']))) {
	//nothing
} else {
	redirect(href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
}
$orderStatusHistory = get_order_status_history($_SESSION['old_order_id']);
//Breadcrumb
$breadcrumb->add(__('Checkout Result'), 'root');
