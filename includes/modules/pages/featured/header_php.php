<?php
/**
 * featured header_php.php
 */
if (!defined('FEATURED_PAGE_LIMIT')) define('FEATURED_PAGE_LIMIT', '30');
if (!defined('MOBILE_FEATURED_PAGE_LIMIT')) define('MOBILE_FEATURED_PAGE_LIMIT', '30');
if (!defined('FEATURED_IDS')) define('FEATURED_IDS', '');
if (!defined('FEATURED_SKUS')) define('FEATURED_SKUS', '');

// 是否是M端
if (true == $_isMobileTemplate) {
	$pageLimit = MOBILE_FEATURED_PAGE_LIMIT;
} else {
	$pageLimit = FEATURED_PAGE_LIMIT;
}

$productList = array();
$_productIds = array();
if ($pageLimit>0) {
	if (FEATURED_IDS!='' || FEATURED_SKUS!='') {
		$sql = "SELECT product_id, name, image, price,
					   specials_price, specials_expire_date, filter_1
				FROM   " . TABLE_PRODUCT . "
				WHERE  status = 1
				AND    in_stock = 1";
		if (FEATURED_IDS!=''){
			$sql .= " AND product_id IN (:productIDS) ORDER BY FIELD(product_id, :productIDS)";
			$sql = $db->bindVars($sql, ':productIDS', FEATURED_IDS, 'noquotestring');
		} else {
			$sql .= " AND sku IN (:productSKUS) ORDER BY FIELD(sku, :productSKUS)";
			$sql = $db->bindVars($sql, ':productSKUS', FEATURED_SKUS, 'passthru');
		}
		$result = $db->Execute($sql, $pageLimit);
		$currentDay = date('Y-m-d');
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
	} else {
		$sql = "SELECT product_id, name, image, price,
					   specials_price, specials_expire_date, filter_1
				FROM   " . TABLE_PRODUCT . "
				WHERE  status = 1
				AND    in_stock = 1";
		$result = $db->ExecuteRandomMulti($sql, $pageLimit);
		$currentDay = date('Y-m-d');
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
			$result->MoveNextRandom();
		}
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
$breadcrumb->add(__('Featured'), 'root');
