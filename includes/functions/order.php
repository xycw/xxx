<?php
function get_shipping_method($shipping_method_id, $product_qty, $product_amount)
{
	global $db;
	$sql = "SELECT *
			FROM   " . TABLE_SHIPPING_METHOD . "
			WHERE  status = 1
			AND    shipping_method_id = :shippingMethodID
			LIMIT 1";
	$sql = $db->bindVars($sql, ':shippingMethodID', $shipping_method_id, 'integer');
	$result = $db->Execute($sql);
	if ($result->RecordCount() > 0) {
		if (($product_qty > $result->fields['free_shipping_qty']
			&& $result->fields['free_shipping_qty'] > 0)
			|| ($product_amount > $result->fields['free_shipping_amount']
			&& $result->fields['free_shipping_amount'] > 0)) {
			$fee = 0;
		} else {
			$fee = ($result->fields['is_item']==1?$result->fields['fee']*$product_qty:$result->fields['fee']);
			// 获取超过件数费用
			$fee = get_shipping_sub_fee($fee, $result->fields['sub_fee'], $product_qty, $product_amount);
			if ($result->fields['max_fee']>0&&$fee>$result->fields['max_fee']) {
				$fee = $result->fields['max_fee'];
			}
		}
		$insurance_fee = $product_qty * $result->fields['insurance_fee'];
		return array(
			'shipping_method_id' => $result->fields['shipping_method_id'],
			'code'               => $result->fields['code'],
			'name'               => $result->fields['name'],
			'description'        => $result->fields['description'],
			'fee'                => $fee,
			'insurance_fee'      => $insurance_fee
		);
	}
	
	return false;
}

/**
 * 获取超过件数费用
 */
function get_shipping_sub_fee($fee, $sub_fee, $qty, $amount)
{
	if (isset($sub_fee) && strlen($sub_fee) > 0) {
		$subFeeList = json_decode($sub_fee, true);
		if (!empty($subFeeList)) {
			krsort($subFeeList);
			foreach ($subFeeList as $val) {
				if ($qty >= $val['num'] && $amount >= $val['amount'] && is_numeric($val['price']) && $val['price'] >= 0) {
					$fee = $val['price'];
					break;
				}
			}
		}
	}
	return $fee;
}

function get_payment_method($payment_method_id)
{
	global $db;
	$sql = "SELECT *
			FROM   " . TABLE_PAYMENT_METHOD . "
			WHERE  status = 1
			AND    payment_method_id = :paymentMethodID
			LIMIT 1";
	$sql = $db->bindVars($sql, ':paymentMethodID', $payment_method_id, 'integer');
	$result = $db->Execute($sql);
	if ($result->RecordCount() > 0) {
		return array(
			'payment_method_id' => $result->fields['payment_method_id'],
			'code'              => $result->fields['code'],
			'name'              => $result->fields['name'],
		    'description'       => $result->fields['description'],
			'account'           => $result->fields['account'],
			'discount'          => $result->fields['discount'],
			'is_inside'         => $result->fields['is_inside']
		);
	}
	
	return false;
}

function put_orderNO($order_id)
{
	return STORE_NAME . '-1' . str_pad($order_id, 7, "0", STR_PAD_LEFT);
}

function get_orderNO($order_id)
{
	return (int)preg_replace('/' . STORE_NAME . '-1/', '', $order_id);
}

function get_order_status_name($order_status_id)
{
	global $db;
	$sql = "SELECT name
			FROM   " . TABLE_ORDER_STATUS . "
			WHERE order_status_id = :orderStatusID";
	$sql = $db->bindVars($sql, ':orderStatusID', $order_status_id, 'integer');
	$result = $db->Execute($sql);
	if ($result->RecordCount() > 0) {
		return $result->fields['name'];
	}
	
	return '';
}

function get_order_id_by_token($order_token)
{
	global $db;
	$sql = "SELECT order_id
			FROM   " . TABLE_ORDERS . "
			WHERE order_token = :orderToken";
	$sql = $db->bindVars($sql, ':orderToken', $order_token, 'string');
	$result = $db->Execute($sql);
	if ($result->RecordCount() > 0) {
		return $result->fields['order_id'];
	}

	return false;
}

function get_order($order_id)
{
	global $db;
	$sql = "SELECT *
			FROM   " . TABLE_ORDERS . "
			WHERE  order_id = :orderID
			AND    customer_id = :customerID";
	$sql = $db->bindVars($sql, ':orderID', $order_id, 'integer');
	$sql = $db->bindVars($sql, ':customerID', (isset($_SESSION['customer_id'])?$_SESSION['customer_id']:0), 'integer');
	$result = $db->Execute($sql);
	if ($result->RecordCount() > 0) {
		return array(
			'order_id' => $result->fields['order_id'],
			'customer' => array(
				'customer_id'   => $result->fields['customer_id'],
				'firstname'     => $result->fields['customer_firstname'],
				'lastname'      => $result->fields['customer_lastname'],
				'email_address' => $result->fields['customer_email_address']
			),
			'billing' => array(
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
			'shipping' => array(
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
			'payment_method' => array(
				'code'        => $result->fields['payment_method_code'],
				'account'     => $result->fields['payment_method_account'],
				'billing'     => $result->fields['payment_method_billing'],
				'name'        => $result->fields['payment_method_name'],
				'description' => $result->fields['payment_method_description']
			),
			'shipping_method' => array(
				'code'          => $result->fields['shipping_method_code'],
				'name'          => $result->fields['shipping_method_name'],
				'description'   => $result->fields['shipping_method_description'],
				'fee'           => $result->fields['shipping_method_fee'],
				'insurance_fee' => $result->fields['shipping_method_insurance_fee']
			),
			'coupon' => array(
				'code'     => $result->fields['coupon_code'],
				'discount' => $result->fields['coupon_discount']
			),
			'currency' => array(
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

function get_order_status_history($order_id)
{
	global $db;
	$sql = "SELECT osh.date_added, os.name, osh.remarks
			FROM   " . TABLE_ORDER_STATUS_HISTORY . " osh, " . TABLE_ORDER_STATUS . " os
			WHERE  osh.order_status_id = os.order_status_id
			AND    osh.order_id = :orderID
			ORDER BY osh.order_status_history_id";
	$sql = $db->bindVars($sql, ':orderID', $order_id, 'integer');
	$result = $db->Execute($sql);
	while (!$result->EOF) {
		$arr[] = array(
			'date_added' => $result->fields['date_added'],
			'name'       => $result->fields['name'],
			'remarks'    => $result->fields['remarks']
		);
		$result->MoveNext();
	}
	return $arr;
}

function get_order_product($order_id)
{
	global $db;
	$sql = "SELECT *
			FROM   " . TABLE_ORDER_PRODUCT . "
			WHERE  order_id = :orderID
			ORDER BY order_product_id";
	$sql = $db->bindVars($sql, ':orderID', $order_id, 'integer');
	$result = $db->Execute($sql);
	if ($result->RecordCount() > 0) {
		while (!$result->EOF) {
			$arr[] = array(
				'product_id' => $result->fields['product_id'],
				'sku'        => $result->fields['sku'],
				'name'       => $result->fields['name'],
				'image'      => $result->fields['image'],
				'price'      => $result->fields['price'],
				'qty'        => $result->fields['qty'],
				'attribute'  => $result->fields['attribute']
			);
			$result->MoveNext();
		}
		return $arr;
	}
	
	return false;
}

function send_confirm_mail($orderInfo, $orderProductInfo, $orderStatusId)
{
	$orderStatus = 2;

	switch ($orderStatusId) {
		case '1':
			$orderStatus = 2;
		break;
		case '2':
		case '3':
			$orderStatus = 1;
		break;
		case '4':
			$orderStatus = 0;
		break;
	}

	$orderStatusUrl = HTTPS_SERVER . DIR_WS_CATALOG . 'login.html';
	if (isset($orderInfo['order_token'])
		&& !empty($orderInfo['order_token'])) {
		$orderStatusUrl = HTTPS_SERVER . DIR_WS_CATALOG . 'orders_' . $orderInfo['order_token'];
	}

	$order = array(
		'payment'           => $orderInfo['payment_method']['code'],
		'payment_account'   => $orderInfo['payment_method']['account'],
		'payment_billing'   => $orderInfo['payment_method']['billing'],
		'order_number'      => put_orderNO($orderInfo['order_id']),
		'email'             => $orderInfo['customer']['email_address'],
		'currency'          => $orderInfo['currency']['code'],
		'currency_value'    => $orderInfo['currency']['value'],
		'amount'            => number_format($orderInfo['order_total'] * $orderInfo['currency']['value'], '2', '.', ''),
		'discount'          => number_format(($orderInfo['order_discount'] + $orderInfo['coupon']['discount']) * $orderInfo['currency']['value'], '2', '.', ''),
		'fee'               => number_format($orderInfo['shipping_method']['fee'] * $orderInfo['currency']['value'], '2', '.', ''),
		'insurance'         => number_format($orderInfo['shipping_method']['insurance_fee'] * $orderInfo['currency']['value'], '2', '.', ''),
		'billing_name'      => $orderInfo['billing']['firstname'] . ' ' . $orderInfo['billing']['lastname'],
		'billing_country'   => $orderInfo['billing']['country'],
		'billing_state'     => $orderInfo['billing']['region'],
		'billing_city'      => $orderInfo['billing']['city'],
		'billing_postcode'  => $orderInfo['billing']['postcode'],
		'billing_phone'     => $orderInfo['billing']['telephone'],
		'billing_address'   => trim($orderInfo['billing']['street_address'] . ' ' . $orderInfo['billing']['suburb']),
		'billing_fax'       => $orderInfo['billing']['fax'],
		'delivery_name'     => $orderInfo['shipping']['firstname'] . ' ' . $orderInfo['shipping']['lastname'],
		'delivery_country'  => $orderInfo['shipping']['country'],
		'delivery_state'    => $orderInfo['shipping']['region'],
		'delivery_city'     => $orderInfo['shipping']['city'],
		'delivery_postcode' => $orderInfo['shipping']['postcode'],
		'delivery_phone'    => $orderInfo['shipping']['telephone'],
		'delivery_address'  => trim($orderInfo['shipping']['street_address'] . ' ' . $orderInfo['shipping']['suburb']),
		'delivery_fax'      => $orderInfo['shipping']['fax'],
		'date_create'       => $orderInfo['date_added'],
		'ip'                => $orderInfo['ip_address'],
		'status'            => $orderStatus,
		'order_status_url'  => $orderStatusUrl,
		'product'           => array()
	);

	foreach ($orderProductInfo as $orderProduct) {
		$attributeArr = json_decode($orderProduct['attribute'], true);
		$orderProduct['attribute'] = array();
		foreach ($attributeArr as $key => $value) {
			$orderProduct['attribute'][] = $key . ':' . $value;
		}

		$order['product'][] = array(
			'sku'        => $orderProduct['sku'],
			'name'       => $orderProduct['name'],
			'image'      => $orderProduct['image'],
			'price'      => number_format($orderProduct['price'] * $order['currency_value'], '2', '.', ''),
			'qty'        => $orderProduct['qty'],
			'attributes' => implode(';', $orderProduct['attribute'])
		);
	}

	$data = array(
		'website' => $_SERVER['HTTP_HOST'],
		'token'   => OA_EMAIL_API_TOKEN,
		'order'   => json_encode($order)
	);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, OA_EMAIL_API_URL . '/api/email/sendConfirmMail');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch ,CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch ,CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_TIMEOUT, 120);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	$response = curl_exec($ch);
	curl_close($ch);

	if ($response == 1) return 1;

	return 0;
}
