<?php
/**
 * 根据条件获取订单列表
 */
function getOrderList($where) {
	global $db;
	$sql = "SELECT * FROM " . TABLE_ORDERS . $where . " ORDER BY order_id ASC LIMIT 100";
	$result = $db->Execute($sql);
	$orderList = array();
	while (!$result->EOF) {
		$order_id = $result->fields["order_id"];
		$order_status = 2;
		switch ($result->fields["order_status_id"]) {
			case '1':
				$order_status = 2;
			break;
			case '2':
			case '3':
				$order_status = 1;
			break;
			case '4':
				$order_status = 0;
			break;
		}

		$orderStatusUrl = HTTPS_SERVER . DIR_WS_CATALOG . 'login.html';
		if (isset($result->fields['order_token'])
			&& !empty($result->fields['order_token'])) {
			$orderStatusUrl = HTTPS_SERVER . DIR_WS_CATALOG . 'orders_' . $result->fields['order_token'];
		}

		$orderList[$order_id] = array(
			'payment'                  => $result->fields['payment_method_code'],
			'payment_account'          => $result->fields['payment_method_account'],
			'payment_billing'          => $result->fields['payment_method_billing'],
			'order_number'             => put_orderNO($result->fields['order_id']),
			'email'                    => $result->fields['customer_email_address'],
			'currency'                 => $result->fields['currency_code'],
			'currency_value'           => $result->fields['currency_value'],
			'amount'                   => number_format($result->fields['order_total'] * $result->fields['currency_value'], '2', '.', ''),
			'discount'                 => number_format(($result->fields['order_discount'] + $result->fields['coupon_discount']) * $result->fields['currency_value'], '2', '.', ''),
			'fee'                      => number_format($result->fields['shipping_method_fee'] * $result->fields['currency_value'], '2', '.', ''),
			'insurance'                => number_format($result->fields['shipping_method_insurance_fee'] * $result->fields['currency_value'], '2', '.', ''),
			'billing_name'             => $result->fields['billing_firstname'] . ' ' . $result->fields['billing_lastname'],
			'billing_country'          => $result->fields['billing_country'],
			'billing_state'            => $result->fields['billing_region'],
			'billing_city'             => $result->fields['billing_city'],
			'billing_postcode'         => $result->fields['billing_postcode'],
			'billing_phone'            => $result->fields['billing_telephone'],
			'billing_address'          => trim($result->fields['billing_street_address'] . ' ' . $result->fields['billing_suburb']),
			'billing_fax'              => $result->fields['billing_fax'],
			'delivery_name'            => $result->fields['shipping_firstname'] . ' ' . $result->fields['shipping_lastname'],
			'delivery_country'         => $result->fields['shipping_country'],
			'delivery_state'           => $result->fields['shipping_region'],
			'delivery_city'            => $result->fields['shipping_city'],
			'delivery_postcode'        => $result->fields['shipping_postcode'],
			'delivery_phone'           => $result->fields['shipping_telephone'],
			'delivery_address'         => trim($result->fields['shipping_street_address'] . ' ' . $result->fields['shipping_suburb']),
			'delivery_fax'             => $result->fields['shipping_fax'],
			'date_create'              => $result->fields['date_added'],
			'ip'                       => $result->fields['ip_address'],
			'status'                   => $order_status,
			'order_status_url'         => $orderStatusUrl,
			'mail_status'              => $result->fields['send_confirm_mail'],
			'customer_http_referer'    => $result->fields['customer_http_referer'],
			'customer_http_user_agent' => $result->fields['customer_http_user_agent']
		);
		$orderList[$order_id]['product'] = array();
		$result->MoveNext();
	}
	
	if (!empty($orderList)) {
		$sql = "SELECT * FROM " . TABLE_ORDER_PRODUCT . " WHERE order_id IN (" . implode(',', array_keys($orderList)) . ")";
		$result = $db->Execute($sql);
		while (!$result->EOF) {
			$order_id  = $result->fields["order_id"];
			$attribute = json_decode($result->fields['attribute'], true);
			$result->fields['attribute'] = '';
			foreach ($attribute as $k => $v) {
				if (strlen($result->fields['attribute']) > 0) {
					$result->fields['attribute'] .= ';';
				}
				$result->fields['attribute'] .= $k . ':' . $v;
			}
			$orderList[$order_id]['product'][] = array(
				'sku'        => $result->fields['sku'],
				'name'       => $result->fields['name'],
				'image'      => $result->fields['image'],
				'price'      => number_format($result->fields['price'] * $orderList[$order_id]['currency_value'], '2', '.', ''),
				'qty'        => $result->fields['qty'],
				'attributes' => $result->fields['attribute']
			);
			$result->MoveNext();
		}
	}
	return $orderList;
}

if (isset($_GET['lastDate']) && !is_null($_GET['lastDate'])) {
    $now   = date('Y-m-d H:i:s', strtotime('-5 second'));
    $where = " where date_added > :date_added AND date_added < '" . $now . "'";
	$where = $db->bindVars($where, ':date_added', $_GET['lastDate'], 'string');
} else {
	$where = '';
}

header('Content-Type:text/html; charset=utf-8');
echo json_encode(getOrderList($where));
