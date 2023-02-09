<?php
/**
 * sidebar related.php
 */
if (!defined('RELATED_SIDEBAR_LIMIT')) define('RELATED_SIDEBAR_LIMIT', '3');
$relatedSidebarList = array();
if ($current_page == FILENAME_PRODUCT
	&& RELATED_SIDEBAR_LIMIT > 0  && RELATED_SHOW == 2) {
	$sql = "SELECT product_id, name, image, price,
				   specials_price, specials_expire_date, filter_1
			FROM   " . TABLE_PRODUCT . "
			WHERE  status = 1
			AND    in_stock = 1
			AND    master_category_id = :categoryID
			AND    product_id <> :productID";
	$sql = $db->bindVars($sql, ':categoryID', $_GET['cID'], 'integer');
	$sql = $db->bindVars($sql, ':productID', $_GET['pID'], 'integer');
	$result = $db->ExecuteRandomMulti($sql, RELATED_SIDEBAR_LIMIT);
	$_productIds = array();
	$currentDay  = date('Y-m-d');
	while (!$result->EOF) {
		$specials_price = (!empty($result->fields['specials_expire_date']) && $currentDay > $result->fields['specials_expire_date']) ? '0' : $result->fields['specials_price'];
		$relatedSidebarList[] = array(
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
		$result->MoveNextRandom();
	}
	
	$_reviewList = getProductReview($_productIds);
	// 拼接
	foreach ($relatedSidebarList as $key => $val) {
		$relatedSidebarList[$key]['review'] = array(
			'average' => '0',
			'total'   => '0',
			'rating'  => '0'
		);
		if (isset($_reviewList[$val['product_id']])) {
			$relatedSidebarList[$key]['review'] = $_reviewList[$val['product_id']];
		}
	}
}
