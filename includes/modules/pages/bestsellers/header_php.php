<?php
/**
 * bestsellers header_php.php
 */
if (!defined('BESTSELLERS_PAGE_LIMIT')) define('BESTSELLERS_PAGE_LIMIT', '30');
if (!defined('MOBILE_BESTSELLERS_PAGE_LIMIT')) define('MOBILE_BESTSELLERS_PAGE_LIMIT', '30');
if (!defined('BESTSELLERS_IDS')) define('BESTSELLERS_IDS', '');
if (!defined('BESTSELLERS_SKUS')) define('BESTSELLERS_SKUS', '');

// 是否是M端
if (true == $_isMobileTemplate) {
	$pageLimit = MOBILE_BESTSELLERS_PAGE_LIMIT;
} else {
	$pageLimit = BESTSELLERS_PAGE_LIMIT;
}

$productList = array();
if ($pageLimit>0) {
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
	$result = $db->Execute($sql, $pageLimit);
	$_productIds = array();
	$currentDay  = date('Y-m-d');
	while (!$result->EOF) {
		$specials_price = (!empty($result->fields['specials_expire_date']) && $currentDay > $result->fields['specials_expire_date']) ? '0' : $result->fields['specials_price'];
		$productList[] = array(
			'product_id'           => $result->fields['product_id'],
			'nameAlt'              => output_string($result->fields['name']),
			'name'                 => trunc_string($result->fields['name'], $_nameMaxLength),
			'image'                => $result->fields['image'],
			'price'                => $result->fields['price'],
			'specials_price'       => $specials_price,
			'specials_expire_date' => $result->fields['specials_expire_date'],
			'filter_1'             => $result->fields['filter_1'],
			'save_off'             => ($_isShowSaveOff==1?round(100-($specials_price/$result->fields['price']*100)):0),
		);
		$_productIds[] = $result->fields['product_id'];
		$result->MoveNext();
	}
	
	$_reviewList = getProductReview($_productIds);
	// 拼接
	foreach ($productList as $key => $val) {
		$productList[$key]['review'] = array(
			'average' => '0',
			'total'   => '0',
			'rating'  => '0'
		);
		if (isset($_reviewList[$val['product_id']])) {
			$productList[$key]['review'] = $_reviewList[$val['product_id']];
		}
	}
}
//Breadcrumb
$breadcrumb->add(__('Bestsellers'), 'root');
