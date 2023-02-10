<?php
/**
 * modules also_purchased.php
 */
if (!defined('ALSO_PURCHASED_LIMIT')) define('ALSO_PURCHASED_LIMIT', '8');
if (!defined('MOBILE_ALSO_PURCHASED_LIMIT')) define('MOBILE_ALSO_PURCHASED_LIMIT', '8');
if (!defined('ALSO_PURCHASED_PER_ROW')) define('ALSO_PURCHASED_PER_ROW', '4');

// 是否是M端
if (true == $_isMobileTemplate) {
	$pageLimit = MOBILE_ALSO_PURCHASED_LIMIT;
} else {
	$pageLimit = ALSO_PURCHASED_LIMIT;
}

$alsoPurchasedList = array();
$_productIds = array();
if ($pageLimit>0) {
	$sql = "SELECT p.product_id, p.name, p.image,
				   p.price, SUM(o.order_id) AS total,
				   p.specials_price, p.specials_expire_date, p.filter_1
			FROM   " . TABLE_ORDER_PRODUCT . " opa, " . TABLE_ORDER_PRODUCT . " opb, " . TABLE_ORDERS . " o, " . TABLE_PRODUCT . " p
			WHERE  opa.product_id = :productID
			AND    opa.order_id = opb.order_id
			AND    opb.product_id <> :productID
			AND    opb.product_id = p.product_id
			AND    opb.order_id = o.order_id
			AND    p.status = 1
			AND    p.in_stock = 1
			GROUP BY p.product_id
			ORDER BY total";
	$sql = $db->bindVars($sql, ':productID', $_GET['pID'], 'integer');
	$result = $db->Execute($sql, $pageLimit, true, 300);
	
	$currentDay = date('Y-m-d');
	while (!$result->EOF) {
		$specials_price = (!empty($result->fields['specials_expire_date']) && $currentDay > $result->fields['specials_expire_date']) ? '0' : $result->fields['specials_price'];
		$alsoPurchasedList[] = array(
			'product_id'           => $result->fields['product_id'],
			'nameAlt'              => output_string($result->fields['name']),
			'name'                 => trunc_string($result->fields['name'], $_nameMaxLength),
			'image'                => $result->fields['image'],
			'price'                => $result->fields['price'],
			'specials_price'       => $specials_price,
			'specials_expire_date' => $result->fields['specials_expire_date'],
			'save_off'             => ($_isShowSaveOff==1?round(100-($specials_price/$result->fields['price']*100)):0),
			'total'                => $result->fields['total'],
			'filter_1'             => $result->fields['filter_1'],
		);
		$_productIds[] = $result->fields['product_id'];
		$result->MoveNext();
	}
}

$_reviewList = getProductReview($_productIds);
// 拼接
foreach ($alsoPurchasedList as $key => $val) {
	$alsoPurchasedList[$key]['review'] = array(
		'average' => '0',
		'total'   => '0',
		'rating'  => '0'
	);
	if (isset($_reviewList[$val['product_id']])) {
		$alsoPurchasedList[$key]['review'] = $_reviewList[$val['product_id']];
	}
}
