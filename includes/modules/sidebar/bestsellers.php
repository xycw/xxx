<?php
/**
 * sidebar bestsellers.php
 */
if (!defined('BESTSELLERS_SIDEBAR_LIMIT')) define('BESTSELLERS_SIDEBAR_LIMIT', '3');
if (!defined('BESTSELLERS_IDS')) define('BESTSELLERS_IDS', '');
if (!defined('BESTSELLERS_SKUS')) define('BESTSELLERS_SKUS', '');
$bestsellersSidebarList = array();
if (BESTSELLERS_SIDEBAR_LIMIT>0) {
	if (BESTSELLERS_IDS!='' || BESTSELLERS_SKUS!='') {
		$sql = "SELECT product_id, name, image, price,
					   specials_price, specials_expire_date, filter_1
				FROM   " . TABLE_PRODUCT . "
				WHERE  status = 1
				AND    in_stock = 1";
		if (BESTSELLERS_IDS!=''){
			$sql .= " AND product_id IN (:productIDS) ORDER BY FIELD(product_id, :productIDS)";
			$sql = $db->bindVars($sql, ':productIDS', BESTSELLERS_IDS, 'noquotestring');
		} else {
			$sql .= " AND sku IN (:productSKUS) ORDER BY FIELD(sku, :productSKUS)";
			$sql = $db->bindVars($sql, ':productSKUS', BESTSELLERS_SKUS, 'passthru');
		}
	} else {
		$sql = "SELECT product_id, name, image, price,
					   specials_price, specials_expire_date, filter_1
				FROM   " . TABLE_PRODUCT . "
				WHERE  status = 1
				AND    in_stock = 1
				ORDER BY ordered DESC, sort_order ASC";
	}
	$result = $db->Execute($sql, BESTSELLERS_SIDEBAR_LIMIT, true, 604800);
	$_productIds = array();
	$currentDay  = date('Y-m-d');
	while (!$result->EOF) {
		$specials_price = (!empty($result->fields['specials_expire_date']) && $currentDay > $result->fields['specials_expire_date']) ? '0' : $result->fields['specials_price'];
		$bestsellersSidebarList[] = array(
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
	foreach ($bestsellersSidebarList as $key => $val) {
		$bestsellersSidebarList[$key]['review'] = array(
			'average' => '0',
			'total'   => '0',
			'rating'  => '0'
		);
		if (isset($_reviewList[$val['product_id']])) {
			$bestsellersSidebarList[$key]['review'] = $_reviewList[$val['product_id']];
		}
	}
}
