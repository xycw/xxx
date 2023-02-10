<?php
/**
 * sidebar new_products.php
 */
if (!defined('NEW_PRODUCTS_SIDEBAR_LIMIT')) define('NEW_PRODUCTS_SIDEBAR_LIMIT', '8');
if (!defined('NEW_PRODUCTS_IDS')) define('NEW_PRODUCTS_IDS', '');
if (!defined('NEW_PRODUCTS_SKUS')) define('NEW_PRODUCTS_SKUS', '');
$newProductsSidebarList = array();
if (NEW_PRODUCTS_SIDEBAR_LIMIT>0) {
	if (NEW_PRODUCTS_IDS!='' || NEW_PRODUCTS_SKUS!='') {
		$sql = "SELECT product_id, name, image, price,
					   specials_price, specials_expire_date, filter_1
				FROM   " . TABLE_PRODUCT . "
				WHERE  status = 1
				AND    in_stock = 1";
		if (NEW_PRODUCTS_IDS!=''){
			$sql .= " AND product_id IN (:productIDS) ORDER BY FIELD(product_id, :productIDS)";
			$sql = $db->bindVars($sql, ':productIDS', NEW_PRODUCTS_IDS, 'noquotestring');
		} else {
			$sql .= " AND sku IN (:productSKUS) ORDER BY FIELD(sku, :productSKUS)";
			$sql = $db->bindVars($sql, ':productSKUS', NEW_PRODUCTS_SKUS, 'passthru');
		}
	} else {
		$sql = "SELECT product_id, name, image, price,
					   specials_price, specials_expire_date, filter_1
				FROM   " . TABLE_PRODUCT . "
				WHERE  status = 1
				AND    in_stock = 1
				ORDER BY date_added DESC, sort_order ASC";
	}
	$result = $db->Execute($sql, NEW_PRODUCTS_SIDEBAR_LIMIT, true, 604800);
	$_productIds = array();
	$currentDay  = date('Y-m-d');
	while (!$result->EOF) {
		$specials_price = (!empty($result->fields['specials_expire_date']) && $currentDay > $result->fields['specials_expire_date']) ? '0' : $result->fields['specials_price'];
		$newProductsSidebarList[] = array(
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
	foreach ($newProductsSidebarList as $key => $val) {
		$newProductsSidebarList[$key]['review'] = array(
			'average' => '0',
			'total'   => '0',
			'rating'  => '0'
		);
		if (isset($_reviewList[$val['product_id']])) {
			$newProductsSidebarList[$key]['review'] = $_reviewList[$val['product_id']];
		}
	}
}
