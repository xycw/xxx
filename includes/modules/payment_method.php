<?php
/**
 * modules payment_method.php
 */
$sql = "SELECT payment_method_id, code, name,
			   description, discount, is_default, is_inside, is_shield, mark3, order_max_amount
		FROM   " . TABLE_PAYMENT_METHOD . "
		WHERE  status = 1
		ORDER BY sort_order";
$result = $db->Execute($sql);
$paymentMethodList = array();
// order total
$grand_total = $shoppingCart['subtotal'] - $shoppingCart['discount'] - $shoppingCart['coupon_discount'] + $shippingMethod['fee'] + $shippingMethod['insurance_fee'];

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
