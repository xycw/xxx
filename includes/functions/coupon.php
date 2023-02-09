<?php
function get_shopping_cart_coupon($product_qty, $product_amount)
{
	global $db;
	$sql = "SELECT discount_type, discount
			FROM   " . TABLE_COUPON . "
			WHERE  type = 1
			AND    status = 1
			AND    usage_limit > 0
			AND    discount > 0
			AND    DATEDIFF(CURRENT_DATE(), IF(ISNULL(start_date), CURRENT_DATE(), start_date)) >= 0
			AND    DATEDIFF(IF(ISNULL(expire_date), CURRENT_DATE(), expire_date), CURRENT_DATE()) >= 0
			AND    product_qty <= :productQty
			AND    product_amount <= :productAmount
			ORDER BY product_qty DESC, product_amount DESC LIMIT 1";
	$sql = $db->bindVars($sql, ':productQty', $product_qty, 'integer');
	$sql = $db->bindVars($sql, ':productAmount', $product_amount, 'float');
	$result = $db->Execute($sql);
	$discount = 0;
	if ($result->RecordCount() > 0) {
		switch ($result->fields['discount_type']) {
			case '0':
				$discount = $result->fields['discount'];
			break;
			case '1':
				$discount = $result->fields['discount']*(int)$product_qty;
			break;
			case '2':
				$discount = $product_amount*$result->fields['discount']/100;
			break;
		}
		
		if ($discount > $product_amount) {
			$discount = 0;
		}
	}
	
	return $discount;
}

function get_customer_coupon($coupon_code, $product_qty, $product_amount)
{
	global $db;
	$sql = "SELECT discount_type, discount
			FROM   " . TABLE_COUPON . "
			WHERE  type = 0
			AND    status = 1
			AND    usage_limit > 0
			AND    discount > 0
			AND    DATEDIFF(CURRENT_DATE(), IF(ISNULL(start_date), CURRENT_DATE(), start_date)) >= 0
			AND    DATEDIFF(IF(ISNULL(expire_date), CURRENT_DATE(), expire_date), CURRENT_DATE()) >= 0
			AND    product_qty <= :productQty
			AND    product_amount <= :productAmount
			AND    code = :code
			ORDER BY product_qty DESC, product_amount DESC LIMIT 1";
	$sql = $db->bindVars($sql, ':productQty', $product_qty, 'integer');
	$sql = $db->bindVars($sql, ':productAmount', $product_amount, 'float');
	$sql = $db->bindVars($sql, ':code', $coupon_code, 'string');
	$result = $db->Execute($sql);
	$discount = 0;
	if ($result->RecordCount() > 0) {
		switch ($result->fields['discount_type']) {
			case '0':
				$discount = $result->fields['discount'];
			break;
			case '1':
				$discount = $result->fields['discount']*(int)$product_qty;
			break;
			case '2':
				$discount = $product_amount*$result->fields['discount']/100;
			break;
		}
		
		if ($discount > $product_amount) {
			$discount = 0;
		}
	}
	
	return $discount;
}
