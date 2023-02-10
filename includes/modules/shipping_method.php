<?php
/**
 * modules shipping_method.php
 */
$sql = "SELECT shipping_method_id, code, name, description, fee, max_fee, sub_fee, insurance_fee,
			   free_shipping_qty, free_shipping_amount, is_item, is_default
		FROM   " . TABLE_SHIPPING_METHOD . "
		WHERE  status = 1
		ORDER BY sort_order";
$result = $db->Execute($sql);
$shippingMethodList = array();
while (!$result->EOF) {
	if (($shoppingCart['items'] > $result->fields['free_shipping_qty']
		&& $result->fields['free_shipping_qty'] > 0)
		|| ($shoppingCart['subtotal'] > $result->fields['free_shipping_amount']
		&& $result->fields['free_shipping_amount'] > 0)) {
		$fee = 0;
	} else {
		$fee = ($result->fields['is_item']==1?$result->fields['fee']*$shoppingCart['items']:$result->fields['fee']);
		// 获取超过件数费用
		$fee = get_shipping_sub_fee($fee, $result->fields['sub_fee'], $shoppingCart['items'], $shoppingCart['subtotal']);
		if ($result->fields['max_fee']>0&&$fee>$result->fields['max_fee']) {
			$fee = $result->fields['max_fee'];
		}
	}
	$insurance_fee = $shoppingCart['items'] * $result->fields['insurance_fee'];
	if ($result->RecordCount() == 1) {
		$is_default = 1;
	} elseif (isset($shippingMethod['shipping_method_id'])) {
		$is_default = $shippingMethod['shipping_method_id']==$result->fields['shipping_method_id']?1:0;
	} else {
		$is_default = $result->fields['is_default'];
	}

	if ($is_default == 1) {
		$shipping_method_default = $result->fields['shipping_method_id'];
		$shippingMethod['fee'] = $fee;
		$shippingMethod['insurance_fee'] = $insurance_fee;
	}

	$shippingMethodList[$result->fields['shipping_method_id']] = array(
		'code'        => $result->fields['code'],
		'name'        => $result->fields['name'],
		'description' => $result->fields['description'],
		'fee'         => $fee,
		'insurance_fee' => $insurance_fee,
		'is_default'  => $is_default
	);
	$result->MoveNext();
}
