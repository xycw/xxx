<?php
/**
 * specials header_php.php
 */
if (!defined('SPECIALS_PAGE_LIMIT')) define('SPECIALS_PAGE_LIMIT', '30');
if (!defined('MOBILE_SPECIALS_PAGE_LIMIT')) define('MOBILE_SPECIALS_PAGE_LIMIT', '30');
if (!defined('SPECIALS_IDS')) define('SPECIALS_IDS', '');
if (!defined('SPECIALS_SKUS')) define('SPECIALS_SKUS', '');

// 是否是M端
if (true == $_isMobileTemplate) {
	$pageLimit = MOBILE_SPECIALS_PAGE_LIMIT;
} else {
	$pageLimit = SPECIALS_PAGE_LIMIT;
}

$productList = array();
if ($pageLimit>0) {
	if (SPECIALS_IDS!='' || SPECIALS_SKUS!='') {
		$sql = "SELECT product_id, name, image, price,
					   specials_price, specials_expire_date, filter_1
				FROM   " . TABLE_PRODUCT . "
				WHERE  status = 1 " . (MAIN_PRODUCT_ONLY == 1 ? (IS_ZP == '1'? "":" AND main_sku=1 ") : '') . "
				AND    in_stock = 1
				AND    specials_price > 0
				AND    DATEDIFF(IF(ISNULL(specials_expire_date),
					   CURRENT_DATE(), specials_expire_date), CURRENT_DATE()) >= 0";
		if (SPECIALS_IDS!=''){
			$sql .= " AND product_id IN (:productIDS) ORDER BY FIELD(product_id, :productIDS)";
			$sql = $db->bindVars($sql, ':productIDS', SPECIALS_IDS, 'noquotestring');
		} else {
			$sql .= " AND sku IN (:productSKUS) ORDER BY FIELD(sku, :productSKUS)";
			$sql = $db->bindVars($sql, ':productSKUS', SPECIALS_SKUS, 'passthru');
		}
	} else {
		$sql = "SELECT product_id, name, image, price,
					   specials_price, specials_expire_date, filter_1
				FROM   " . TABLE_PRODUCT . "
				WHERE  status = 1 " . (MAIN_PRODUCT_ONLY == 1 ? (IS_ZP == '1'? "":" AND main_sku=1 ") : '') . "
				AND    in_stock = 1
				AND    specials_price > 0
				AND    DATEDIFF(IF(ISNULL(specials_expire_date),
					   CURRENT_DATE(), specials_expire_date), CURRENT_DATE()) >= 0
				ORDER BY specials_price, sort_order";
	}
	$result = $db->Execute($sql, $pageLimit);
	$_productIds = array();
	while (!$result->EOF) {
		$productList[] = array(
			'product_id'           => $result->fields['product_id'],
			'nameAlt'              => output_string($result->fields['name']),
			'name'                 => trunc_string($result->fields['name'], $_nameMaxLength),
			'image'                => $result->fields['image'],
			'price'                => $result->fields['price'],
			'specials_price'       => $result->fields['specials_price'],
			'specials_expire_date' => $result->fields['specials_expire_date'],
			'filter_1'             => $result->fields['filter_1'],
			'save_off'             => ($_isShowSaveOff==1?round(100-($result->fields['specials_price']/$result->fields['price']*100)):0),
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
$breadcrumb->add(__('Specials'), 'root');
