<?php
/**
 * sidebar ordered_products.php
 */
if (!defined('ORDERED_PRODUCTS_SIDEBAR_LIMIT')) define('ORDERED_PRODUCTS_SIDEBAR_LIMIT', '8');
if (!defined('ORDERED_PRODUCTS_IDS')) define('ORDERED_PRODUCTS_IDS', '');
if (!defined('ORDERED_PRODUCTS_SKUS')) define('ORDERED_PRODUCTS_SKUS', '');
$orderedProductsSidebarList = array();
if (ORDERED_PRODUCTS_SIDEBAR_LIMIT>0) {
	if (ORDERED_PRODUCTS_IDS!='' || ORDERED_PRODUCTS_SKUS!='') {
		$sql = "SELECT product_id, name, image, price,
					   specials_price, specials_expire_date, filter_1
				FROM   " . TABLE_PRODUCT . "
				WHERE  status = 1
				AND    in_stock = 1";
		if (ORDERED_PRODUCTS_IDS!=''){
			$sql .= " AND product_id IN (:productIDS) ORDER BY FIELD(product_id, :productIDS)";
			$sql = $db->bindVars($sql, ':productIDS', ORDERED_PRODUCTS_IDS, 'noquotestring');
		} else {
			$sql .= " AND sku IN (:productSKUS) ORDER BY FIELD(sku, :productSKUS)";
			$sql = $db->bindVars($sql, ':productSKUS', ORDERED_PRODUCTS_SKUS, 'passthru');
		}
	} else {
		$sql = "SELECT * FROM (SELECT p.product_id, p.name, p.image, p.price,
									  p.specials_price, p.specials_expire_date, p.filter_1
									  op.order_id
							   FROM   " . TABLE_PRODUCT . " p RIGHT JOIN " . TABLE_ORDER_PRODUCT . " op ON p.product_id = op.product_id
							   WHERE  p.status = 1
							   AND    p.in_stock = 1
							   ORDER BY op.order_id DESC) ordered_products
				GROUP BY product_id
				ORDER BY order_id DESC";
	}
	$result = $db->Execute($sql, ORDERED_PRODUCTS_SIDEBAR_LIMIT);
	$_productIds = array();
	$currentDay  = date('Y-m-d');
	while (!$result->EOF) {
		$specials_price = (!empty($result->fields['specials_expire_date']) && $currentDay > $result->fields['specials_expire_date']) ? '0' : $result->fields['specials_price'];
		$orderedProductsSidebarList[] = array(
			'product_id'           => $result->fields['product_id'],
			'nameAlt'              => output_string($result->fields['name']),
			'name'                 => trunc_string($result->fields['name'], PRODUCT_NAME_SIDEBAR_MAX_LENGTH),
			'image'                => $result->fields['image'],
			'price'                => $result->fields['price'],
			'specials_price'       => $specials_price,
			'specials_expire_date' => $result->fields['specials_expire_date'],
			'filter_1'             => $result->fields['filter_1'],
			'save_off'             => (PRODUCT_SIDEBAR_SHOW_SAVE_OFF==1?round(100-($specials_price/$result->fields['price']*100)):0),
		);
		$_productIds[] = $result->fields['product_id'];
		$result->MoveNext();
	}
	
	$_reviewList = getProductReview($_productIds);
	// 拼接
	foreach ($orderedProductsSidebarList as $key => $val) {
		$orderedProductsSidebarList[$key]['review'] = array(
			'average' => '0',
			'total'   => '0',
			'rating'  => '0'
		);
		if (isset($_reviewList[$val['product_id']])) {
			$orderedProductsSidebarList[$key]['review'] = $_reviewList[$val['product_id']];
		}
	}
}
