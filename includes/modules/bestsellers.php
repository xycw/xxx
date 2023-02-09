<?php
/**
 * modules bestsellers.php
 */
if (!defined('BESTSELLERS_LIMIT')) define('BESTSELLERS_LIMIT', '8');
if (!defined('MOBILE_BESTSELLERS_LIMIT')) define('MOBILE_BESTSELLERS_LIMIT', '8');
if (!defined('BESTSELLERS_PER_ROW')) define('BESTSELLERS_PER_ROW', '4');
if (!defined('BESTSELLERS_IDS')) define('BESTSELLERS_IDS', '');
if (!defined('BESTSELLERS_SKUS')) define('BESTSELLERS_SKUS', '');
if (!defined('BESTSELLERS_IDS_ZP')) define('BESTSELLERS_IDS_ZP', '');
if (!defined('BESTSELLERS_SKUS_ZP')) define('BESTSELLERS_SKUS_ZP', '');

// 是否是M端
if (true == $_isMobileTemplate) {
	$pageLimit = MOBILE_BESTSELLERS_LIMIT;
} else {
	$pageLimit = BESTSELLERS_LIMIT;
}

//是否审核站
$bestsellersIDS = (IS_ZP == '0') ? BESTSELLERS_IDS : BESTSELLERS_IDS_ZP;
$bestsellersSKUS = (IS_ZP == '0') ? BESTSELLERS_SKUS : BESTSELLERS_SKUS_ZP;

$bestsellersList = array();
if ($pageLimit>0) {
	if ($bestsellersIDS!='' || $bestsellersSKUS!='') {
		$sql = "SELECT product_id, name, image, price,
					   specials_price, specials_expire_date, filter_1
				FROM   " . TABLE_PRODUCT . "
				WHERE  status = 1
				AND    in_stock = 1";
		if ($bestsellersIDS!=''){
			$sql .= " AND product_id IN (:productIDS) ORDER BY FIELD(product_id, :productIDS)";
			$sql = $db->bindVars($sql, ':productIDS', $bestsellersIDS, 'noquotestring');
		} else {
			$sql .= " AND sku IN (:productSKUS) ORDER BY FIELD(sku, :productSKUS)";
			$sql = $db->bindVars($sql, ':productSKUS', $bestsellersSKUS, 'passthru');
		}
	} else {
		$sql = "SELECT product_id, name, image, price,
					   specials_price, specials_expire_date, filter_1
				FROM   " . TABLE_PRODUCT . "
				WHERE  status = 1
				AND    in_stock = 1
				ORDER BY ordered DESC, sort_order ASC";
	}
	$result = $db->Execute($sql, $pageLimit, true, 604800);
	$_productIds = array();
	$currentDay  = date('Y-m-d');
	while (!$result->EOF) {
		$specials_price = (!empty($result->fields['specials_expire_date']) && $currentDay > $result->fields['specials_expire_date']) ? '0' : $result->fields['specials_price'];
		$bestsellersList[] = array(
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
	foreach ($bestsellersList as $key => $val) {
		$bestsellersList[$key]['review'] = array(
			'average' => '0',
			'total'   => '0',
			'rating'  => '0'
		);
		if (isset($_reviewList[$val['product_id']])) {
			$bestsellersList[$key]['review'] = $_reviewList[$val['product_id']];
		}
	}
}
