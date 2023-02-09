<?php
/**
 * modules recent_viewed.php
 */
if (!defined('RECENT_VIEWED_LIMIT')) define('RECENT_VIEWED_LIMIT', '8');
if (!defined('MOBILE_RECENT_VIEWED_LIMIT')) define('MOBILE_RECENT_VIEWED_LIMIT', '8');
if (!defined('RECENT_VIEWED_PER_ROW')) define('RECENT_VIEWED_PER_ROW', '4');
if (!defined('RECENT_VIEWED_IDS')) define('RECENT_VIEWED_IDS', implode(',', $_SESSION['recent_viewed']));

// 是否是M端
if (true == $_isMobileTemplate) {
	$pageLimit = MOBILE_RECENT_VIEWED_LIMIT;
} else {
	$pageLimit = RECENT_VIEWED_LIMIT;
}

$recentViewedList = array();
if (RECENT_VIEWED_IDS!=''
	&& $pageLimit>0) {
	$sql = "SELECT product_id, name, image, price,
				   specials_price, specials_expire_date, filter_1
			FROM   " . TABLE_PRODUCT . "
			WHERE  status = 1
			AND    product_id IN (:productIDS)
			ORDER BY FIELD(product_id, :productIDS)";
	$sql = $db->bindVars($sql, ':productIDS', RECENT_VIEWED_IDS, 'noquotestring');
	$result = $db->Execute($sql, $pageLimit);
	$_productIds = array();
	$currentDay  = date('Y-m-d');
	while (!$result->EOF) {
		$specials_price = (!empty($result->fields['specials_expire_date']) && $currentDay > $result->fields['specials_expire_date']) ? '0' : $result->fields['specials_price'];
		$recentViewedList[] = array(
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
	foreach ($recentViewedList as $key => $val) {
		$recentViewedList[$key]['review'] = array(
			'average' => '0',
			'total'   => '0',
			'rating'  => '0'
		);
		if (isset($_reviewList[$val['product_id']])) {
			$recentViewedList[$key]['review'] = $_reviewList[$val['product_id']];
		}
	}
}
