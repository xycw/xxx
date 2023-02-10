<?php
/**
 * modules related.php
 */
if (!defined('RELATED_LIMIT')) define('RELATED_LIMIT', '8');
if (!defined('MOBILE_RELATED_LIMIT')) define('MOBILE_RELATED_LIMIT', '8');
if (!defined('RELATED_PER_ROW')) define('RELATED_PER_ROW', '4');

// 是否是M端
if (true == $_isMobileTemplate) {
	$pageLimit = MOBILE_RELATED_LIMIT;
	$show = MOBILE_RELATED_SHOW == 1 ? true : false;
} else {
	$pageLimit = RELATED_LIMIT;
	$show = RELATED_SHOW == 1 ? true : false;
}

$relatedList = array();
if ($current_page == FILENAME_PRODUCT
	&& $pageLimit > 0 && true == $show) {
	$sql = "SELECT product_id, name, image, price,
				   specials_price, specials_expire_date, filter_1
			FROM   " . TABLE_PRODUCT . "
			WHERE  status = 1
			AND    in_stock = 1
			AND    master_category_id = :categoryID
			AND    product_id <> :productID";
	$sql = $db->bindVars($sql, ':categoryID', $_GET['cID'], 'integer');
	$sql = $db->bindVars($sql, ':productID', $_GET['pID'], 'integer');
	$result = $db->ExecuteRandomMulti($sql, $pageLimit);
	$_productIds = array();
	$currentDay  = date('Y-m-d');
	while (!$result->EOF) {
		$specials_price = (!empty($result->fields['specials_expire_date']) && $currentDay > $result->fields['specials_expire_date']) ? '0' : $result->fields['specials_price'];
		$relatedList[] = array(
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
	
	$_reviewList = getProductReview($_productIds);
	// 拼接
	foreach ($relatedList as $key => $val) {
		$relatedList[$key]['review'] = array(
			'average' => '0',
			'total'   => '0',
			'rating'  => '0'
		);
		if (isset($_reviewList[$val['product_id']])) {
			$relatedList[$key]['review'] = $_reviewList[$val['product_id']];
		}
	}
}
