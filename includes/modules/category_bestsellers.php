<?php
/**
 * modules category_bestsellers.php
 */
if (!defined('IS_CATEGORY_BESTSELLERS')) define('IS_CATEGORY_BESTSELLERS', 0);
if (!defined('CATEGORY_BESTSELLERS_LIMIT')) define('CATEGORY_BESTSELLERS_LIMIT', '8');
if (!defined('CATEGORY_BEYOND_NUM')) define('CATEGORY_BEYOND_NUM', '0');

// 自定义
$bestsellersList = array();
$_productIds = array();
if (IS_CATEGORY_BESTSELLERS 
	&& is_numeric(CATEGORY_BEYOND_NUM) && CATEGORY_BEYOND_NUM >= $subcategoryListCount
	&& !empty($subcategories)) {
	
	$sql = "SELECT p.product_id, p.name, p.image, p.price,
				   p.specials_price, p.specials_expire_date, p.filter_1
			FROM   " . TABLE_PRODUCT . " p LEFT JOIN " . TABLE_PRODUCT_TO_CATEGORY . " ptc ON p.product_id = ptc.product_id
			WHERE  p.status = 1
			AND    p.in_stock = 1
			AND    ptc.category_id IN (:categoryID)
			ORDER BY p.ordered DESC, p.sort_order ASC";
	$sql = $db->bindVars($sql, ':categoryID', implode(',', $subcategories), 'noquotestring');
	$result = $db->Execute($sql, CATEGORY_BESTSELLERS_LIMIT);
	$currentDay = date('Y-m-d');
	while (!$result->EOF) {
		$specials_price = (!empty($result->fields['specials_expire_date']) && $currentDay > $result->fields['specials_expire_date']) ? '0' : $result->fields['specials_price'];
		$bestsellersList[] = array(
			'product_id'           => $result->fields['product_id'],
			'nameAlt'              => output_string($result->fields['name']),
			'name'                 => trunc_string($result->fields['name'], PRODUCT_NAME_MAX_LENGTH),
			'image'                => $result->fields['image'],
			'price'                => $result->fields['price'],
			'specials_price'       => $specials_price,
			'specials_expire_date' => $result->fields['specials_expire_date'],
			'filter_1'             => $result->fields['filter_1'],
			'save_off'             => (PRODUCT_SHOW_SAVE_OFF==1?round(100-($specials_price/$result->fields['price']*100)):0),
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
