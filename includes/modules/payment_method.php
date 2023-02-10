<?php
/**
 * modules payment_method.php
 */
$sql = "SELECT payment_method_id, code, name,
			   description, discount, is_default, is_inside, is_shield, mark2, mark3, order_max_amount
		FROM   " . TABLE_PAYMENT_METHOD . "
		WHERE  status = 1
		ORDER BY sort_order";
$result = $db->Execute($sql);
$paymentMethodList = $shippingPaymentMethodJSON = array();
// order total
$grand_total = $shoppingCart['subtotal'] - $shoppingCart['discount'] - $shoppingCart['coupon_discount'] + $shippingMethod['fee'] + $shippingMethod['insurance_fee'];

if(!isset($shipping_method_default) || $shipping_method_default < 1){
	$shipping_method_default = 0;
	$shippingMethodList[0] = array(
		'fee' => 0,
		'insurance_fee' => 0
	);
}

while (!$result->EOF) {
	if ($result->fields['is_shield'] == 1 && stristr(HTTP_ACCEPT_LANGUAGE, 'zh')) {
		//nothing
	} else {
		if ($result->fields['order_max_amount'] == '0' || $result->fields['order_max_amount'] > $grand_total) {
			if ($result->RecordCount() == 1) {
				$is_default = 1;
			} elseif (isset($paymentMethod)) {
				$is_default = $paymentMethod['payment_method_id']==$result->fields['payment_method_id']?1:0;
			} else {
				$is_default = $result->fields['is_default'];
			}

			if ($is_default == 1) {
				$payment_method_default = $result->fields['payment_method_id'];
			}

			$payment_method_fee_free = false;
			foreach ($shoppingCart['product'] as $_product) {
				if(in_array($_product['master_category_id'],explode(',',$result->fields['mark3']))){
					$payment_method_fee_free = true;
					break;
				}
			}
			foreach ($shippingMethodList as $shipping_method_id => $shipping_method){
				$payment_method_fee = !$payment_method_fee_free && $result->fields['mark2'] > 0 ? ($shoppingCart['subtotal'] + $shipping_method['fee'] + $shipping_method['insurance_fee']) * $result->fields['mark2'] / 100 : 0;
				$order_total = $shoppingCart['subtotal'] - $shoppingCart['discount'] - $shoppingCart['coupon_discount'] + $shipping_method['fee'] + $shipping_method['insurance_fee'] + $payment_method_fee;
				$shippingPaymentMethodJSON[$shipping_method_id][$result->fields['payment_method_id']] = array(
					'shipping_method_fee' => $currencies->display_price($shipping_method['fee']),
					'shipping_method_insurance_fee' => $currencies->display_price($shipping_method['insurance_fee']),
					'payment_method_fee' => $currencies->display_price($payment_method_fee),
					'order_total' => $currencies->display_price($order_total)
				);
			}

			$paymentMethodList[$result->fields['payment_method_id']] = array(
				'code'        => $result->fields['code'],
				'name'        => $result->fields['name'],
				'description' => $result->fields['mark3']=='offline'?'<img src="images/payment/' . $result->fields['code'] . '.gif" />':$result->fields['description'],
				'discount'    => $result->fields['discount'],
				'is_default'  => $is_default,
				'is_inside'   => $result->fields['is_inside']
			);
		}
	}
	$result->MoveNext();
}

if(!isset($payment_method_default) || $payment_method_default < 1){
	$payment_method_default = 0;
	foreach ($shippingMethodList as $shipping_method_id => $shipping_method){
		$shippingPaymentMethodJSON[$shipping_method_id][0] = array(
			'shipping_method_fee' => $currencies->display_price($shipping_method['fee']),
			'shipping_method_insurance_fee' => $currencies->display_price($shipping_method['insurance_fee']),
			'payment_method_fee' => $currencies->display_price(0),
			'order_total' => $currencies->display_price($shoppingCart['subtotal'] - $shoppingCart['discount'] - $shoppingCart['coupon_discount'] + $shipping_method['fee'] + $shipping_method['insurance_fee'])
		);
	}
}
