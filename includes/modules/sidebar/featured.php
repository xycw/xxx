<?php
/**
 * sidebar featured.php
 */
if (!defined('FEATURED_SIDEBAR_LIMIT')) define('FEATURED_SIDEBAR_LIMIT', '3');
if (!defined('FEATURED_IDS')) define('FEATURED_IDS', '');
if (!defined('FEATURED_SKUS')) define('FEATURED_SKUS', '');
$featuredSidebarList = array();
if ((FEATURED_IDS!='' || FEATURED_SKUS!='')
	&& FEATURED_SIDEBAR_LIMIT>0) {
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
	$result = $db->Execute($sql, FEATURED_SIDEBAR_LIMIT, true, 604800);
	$_productIds = array();
	$currentDay  = date('Y-m-d');
	while (!$result->EOF) {
		$specials_price = (!empty($result->fields['specials_expire_date']) && $currentDay > $result->fields['specials_expire_date']) ? '0' : $result->fields['specials_price'];
		$featuredSidebarList[] = array(
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
	foreach ($featuredSidebarList as $key => $val) {
		$featuredSidebarList[$key]['review'] = array(
			'average' => '0',
			'total'   => '0',
			'rating'  => '0'
		);
		if (isset($_reviewList[$val['product_id']])) {
			$featuredSidebarList[$key]['review'] = $_reviewList[$val['product_id']];
		}
	}
}
