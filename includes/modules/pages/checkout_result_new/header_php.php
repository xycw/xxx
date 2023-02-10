<?php
/**
 * checkout_result header_php.php
 */
if (isset($_SESSION['order_id']) && $_SESSION['order_id'] != ''
	&& ($orderInfo = get_order($_SESSION['order_id']))
	&& ($orderProductInfo  = get_order_product($_SESSION['order_id']))) {
	require(DIR_FS_CATALOG_CLASSES . 'payment_method.php');
	$payment_method = new payment_method($orderInfo['payment_method']['code']);
	$payment_result = $payment_method->result();
	//check payment result
	$sql = "SELECT COUNT(*) AS total FROM " . TABLE_ORDER_STATUS . " WHERE order_status_id = :orderStatusID";
	$sql = $db->bindVars($sql, ':orderStatusID', $payment_result, 'integer');
	$check_payment_result = $db->Execute($sql);
	if ($check_payment_result->fields['total'] > 0) {
		//orders
		$sql = "UPDATE " . TABLE_ORDERS . " SET order_status_id = :orderStatusID WHERE order_id = :orderID";
		$sql = $db->bindVars($sql, ':orderStatusID', $payment_result, 'integer');
		$sql = $db->bindVars($sql, ':orderID', $orderInfo['order_id'], 'integer');
		$db->Execute($sql);
		//order_status_history
		$sql_data_array = array(
			array('fieldName'=>'order_id', 'value'=>$orderInfo['order_id'], 'type'=>'integer'),
			array('fieldName'=>'order_status_id', 'value'=>$payment_result, 'type'=>'integer'),
			array('fieldName'=>'date_added', 'value'=>'NOW()', 'type'=>'noquotestring')
		);
		$db->perform(TABLE_ORDER_STATUS_HISTORY, $sql_data_array);
	}
	// 2015-12-02 15:00:00
	$jump_code = 'myorder';
	if ($orderInfo['payment_method']['code'] <> $jump_code && $payment_result == 4) {
		$sql = "SELECT * FROM " . TABLE_PAYMENT_METHOD . " WHERE code = :code AND mark3 = 'jump'";
		$sql = $db->bindVars($sql, ':code', $jump_code, 'string');
		$result = $db->Execute($sql, 1);
		if ($result->RecordCount() > 0) {
			//orders
			$sql_data_array = array(
				array('fieldName'=>'payment_method_code', 'value'=>$result->fields['code'], 'type'=>'string'),
				array('fieldName'=>'payment_method_name', 'value'=>$result->fields['name'], 'type'=>'string'),
				array('fieldName'=>'payment_method_description', 'value'=>$result->fields['description'], 'type'=>'string'),
				array('fieldName'=>'order_status_id', 'value'=>1, 'type'=>'integer')
			);
			$db->perform(TABLE_ORDERS, $sql_data_array, 'UPDATE', 'order_id = ' . $orderInfo['order_id']);
			//order_status_history
			$sql_data_array = array(
				array('fieldName'=>'order_id', 'value'=>$orderInfo['order_id'], 'type'=>'integer'),
				array('fieldName'=>'order_status_id', 'value'=>1, 'type'=>'integer'),
				array('fieldName'=>'date_added', 'value'=>'NOW()', 'type'=>'noquotestring')
			);
			$db->perform(TABLE_ORDER_STATUS_HISTORY, $sql_data_array);
			$message_stack->add_session('checkout_process', __('Your payment failed, please try again！Thanks!'));
			redirect(href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'));
		}
	}
	// 2015-12-02 15:00:00
	$_SESSION['old_order_id'] = $_SESSION['order_id'];
	unset($_SESSION['order_id']);
	redirect(href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL'));
} elseif (isset($_SESSION['old_order_id']) && $_SESSION['old_order_id'] != ''
		&& ($orderInfo = get_order($_SESSION['old_order_id']))
		&& ($orderProductInfo  = get_order_product($_SESSION['old_order_id']))) {
	//nothing
} else {
	redirect(href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
}
//Breadcrumb
$breadcrumb->add(__('Checkout Result'), 'root');
