<?php
/**
 * checkout_notify header_php.php
 */
function _get_order($order_id)
{
	global $db;
	$sql    = "SELECT * FROM   " . TABLE_ORDERS . " WHERE  order_id = :orderID";
	$sql    = $db->bindVars($sql, ':orderID', $order_id, 'integer');
	$result = $db->Execute($sql);

	if ($result->RecordCount() > 0) {
		return array(
			'order_id'          => $result->fields['order_id'],
			'customer'          => array(
				'customer_id'   => $result->fields['customer_id'],
				'firstname'     => $result->fields['customer_firstname'],
				'lastname'      => $result->fields['customer_lastname'],
				'email_address' => $result->fields['customer_email_address']
			),
			'billing'           => array(
				'firstname'      => $result->fields['billing_firstname'],
				'lastname'       => $result->fields['billing_lastname'],
				'company'        => $result->fields['billing_company'],
				'street_address' => $result->fields['billing_street_address'],
				'suburb'         => $result->fields['billing_suburb'],
				'city'           => $result->fields['billing_city'],
				'region_id'      => $result->fields['billing_region_id'],
				'region'         => $result->fields['billing_region'],
				'postcode'       => $result->fields['billing_postcode'],
				'country_id'     => $result->fields['billing_country_id'],
				'country'        => $result->fields['billing_country'],
				'telephone'      => $result->fields['billing_telephone'],
				'fax'            => $result->fields['billing_fax']
			),
			'shipping'          => array(
				'firstname'      => $result->fields['shipping_firstname'],
				'lastname'       => $result->fields['shipping_lastname'],
				'company'        => $result->fields['shipping_company'],
				'street_address' => $result->fields['shipping_street_address'],
				'suburb'         => $result->fields['shipping_suburb'],
				'city'           => $result->fields['shipping_city'],
				'region_id'      => $result->fields['shipping_region_id'],
				'region'         => $result->fields['shipping_region'],
				'postcode'       => $result->fields['shipping_postcode'],
				'country_id'     => $result->fields['shipping_country_id'],
				'country'        => $result->fields['shipping_country'],
				'telephone'      => $result->fields['shipping_telephone'],
				'fax'            => $result->fields['shipping_fax']
			),
			'payment_method'    => array(
				'code'        => $result->fields['payment_method_code'],
				'account'     => $result->fields['payment_method_account'],
				'billing'     => $result->fields['payment_method_billing'],
				'name'        => $result->fields['payment_method_name'],
				'description' => $result->fields['payment_method_description']
			),
			'shipping_method'   => array(
				'code'          => $result->fields['shipping_method_code'],
				'name'          => $result->fields['shipping_method_name'],
				'description'   => $result->fields['shipping_method_description'],
				'fee'           => $result->fields['shipping_method_fee'],
				'insurance_fee' => $result->fields['shipping_method_insurance_fee']
			),
			'coupon'            => array(
				'code'     => $result->fields['coupon_code'],
				'discount' => $result->fields['coupon_discount']
			),
			'currency'          => array(
				'code'  => $result->fields['currency_code'],
				'value' => $result->fields['currency_value']
			),
			'order_subtotal'    => $result->fields['order_subtotal'],
			'order_discount'    => $result->fields['order_discount'],
			'order_total'       => $result->fields['order_total'],
			'date_added'        => $result->fields['date_added'],
			'order_status_id'   => $result->fields['order_status_id'],
			'ip_address'        => $result->fields['ip_address'],
			'order_token'       => $result->fields['order_token'],
			'send_confirm_mail' => $result->fields['send_confirm_mail']
		);
	}

	return false;
}

/**
 * @param       $url  请求路径
 * @param array $data 请求参数
 * @return bool|string
 */
function post($url, array $data)
{
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_TIMEOUT, 120);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	$response = curl_exec($ch);
	$errno    = curl_errno($ch);
	if ($errno > 0) {
		$info          = curl_getinfo($ch);
		$info['errno'] = $errno;
	}
	curl_close($ch);

	return $response;
}

// 验证 ccs2s
if (!isset($_POST['version'], $_POST['merchant_id'], $_POST['order_number'], $_POST['sign'])) {
	error_log("[" . date('Y-m-d H:i:s') . "] " . print_r($_POST, true), 3, 'error1.txt');
	exit('error1');
}

$_POST         = array_map('urldecode', $_POST);
$orderId       = get_orderNO($_POST['order_number']);
$orderInfo     = _get_order($orderId);
$orderStatusId = 3;

if (empty($orderInfo)) {
	error_log("[" . date('Y-m-d H:i:s') . "] " . print_r($_POST, true), 3, 'error2.txt');
	exit('error2');
}

// 成功则退出
if (in_array($orderInfo['order_status_id'], array('3', '6', '7'))) {
	exit('ok');
}

// 这是成功交易
if ($_POST['resp_code'] != '0000') {
	error_log("[" . date('Y-m-d H:i:s') . "] " . print_r($_POST, true), 3, 'error3.txt');
	exit('error3');
}

require(DIR_FS_CATALOG_CLASSES . 'payment_method.php');
$paymentMethod = new payment_method($orderInfo['payment_method']['code']);
$md5key        = trim($paymentMethod->get_md5key());

// 签名验证
$sign = $_POST['sign'];
unset($_POST['sign']);
$signStr = implode('', $_POST) . $md5key;

if (strtoupper(md5($signStr)) != strtoupper($sign)) {
	error_log("[" . date('Y-m-d H:i:s') . "] " . print_r($_POST, true), 3, 'error4.txt');
	exit('error4');
}

$orderProductInfo = get_order_product($orderId);

if (empty($orderProductInfo)) {
	error_log("[" . date('Y-m-d H:i:s') . "] " . print_r($_POST, true), 3, 'error5.txt');
	exit('error5');
}

// 判定是否捕获, 千万不能重新捕获
if ($orderInfo['order_status_id'] == '1') {
	// capture 交易请款
	$captureData = array(
		'version'           => $_POST['version'],
		'merchant_id'       => $_POST['merchant_id'],
		'business_id'       => $_POST['business_id'],
		'access_type'       => $_POST['access_type'],
		'trans_channel'     => '',
		'original_order_id' => $_POST['order_id'],
		'trans_type'        => 'capture',
		'amount'            => '',
		'notify_url'        => '',
		'req_reserved'      => '',
		'reserved'          => '',
		'sign_type'         => $_POST['sign_type'],
	);

	$md5Capture          = implode('', $captureData) . $md5key;
	$captureData['sign'] = md5($md5Capture);
	$captureResult       = post($paymentMethod->get_submit_url(), $captureData);
	$captureResult       = json_decode($captureResult, true);

	$sign = $captureResult['sign'];
	unset($captureResult['sign']);

	$signStr = implode('', $captureResult) . $md5key;

	if (strtoupper(md5($signStr)) != strtoupper($sign)) {
		error_log("[" . date('Y-m-d H:i:s') . "] " . print_r($_POST, true), 3, 'error6.txt');
		exit('error6');
	}
}

//send confirm mail
if ($orderInfo['send_confirm_mail'] == 0
	&& send_confirm_mail($orderInfo, $orderProductInfo, $orderStatusId)) {
	$orderInfo['send_confirm_mail'] = 1;
}

// 更新数据
$sql = "UPDATE " . TABLE_ORDERS . " SET order_status_id = :orderStatusID, send_confirm_mail = :sendConfirmMail WHERE order_id = :orderID";
$sql = $db->bindVars($sql, ':orderStatusID', $orderStatusId, 'integer');
$sql = $db->bindVars($sql, ':sendConfirmMail', $orderInfo['send_confirm_mail'], 'integer');
$sql = $db->bindVars($sql, ':orderID', $orderInfo['order_id'], 'integer');
$db->Execute($sql);

//order_status_history
$sqlDataArray = array(
	array('fieldName' => 'order_id', 'value' => $orderInfo['order_id'], 'type' => 'integer'),
	array('fieldName' => 'order_status_id', 'value' => $orderStatusId, 'type' => 'integer'),
	array('fieldName' => 'remarks', 'value' => 'ccs2s notify', 'type' => 'string'),
	array('fieldName' => 'date_added', 'value' => 'NOW()', 'type' => 'noquotestring')
);
$db->perform(TABLE_ORDER_STATUS_HISTORY, $sqlDataArray);

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

exit('ok');
