<?php
/**
 * sidebar recent_viewed.php
 */
if (!defined('RECENT_VIEWED_SIDEBAR_LIMIT')) define('RECENT_VIEWED_SIDEBAR_LIMIT', '3');
if (!defined('RECENT_VIEWED_IDS')) define('RECENT_VIEWED_IDS', implode(',', $_SESSION['recent_viewed']));

$recentViewedSidebarList = array();
if (RECENT_VIEWED_IDS!=''
	&& RECENT_VIEWED_SIDEBAR_LIMIT>0) {
	$sql = "SELECT product_id, name, image, price,
				   specials_price, specials_expire_date, filter_1
			FROM   " . TABLE_PRODUCT . "
			WHERE  status = 1
			AND    product_id IN (:productIDS)
			ORDER BY FIELD(product_id, :productIDS)";
	$sql = $db->bindVars($sql, ':productIDS', RECENT_VIEWED_IDS, 'noquotestring');
	$result = $db->Execute($sql, RECENT_VIEWED_SIDEBAR_LIMIT);
	$_productIds = array();
	$currentDay  = date('Y-m-d');
	while (!$result->EOF) {
		$specials_price = (!empty($result->fields['specials_expire_date']) && $currentDay > $result->fields['specials_expire_date']) ? '0' : $result->fields['specials_price'];
		$recentViewedSidebarList[] = array(
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
	foreach ($recentViewedSidebarList as $key => $val) {
		$recentViewedSidebarList[$key]['review'] = array(
			'average' => '0',
			'total'   => '0',
			'rating'  => '0'
		);
		if (isset($_reviewList[$val['product_id']])) {
			$recentViewedSidebarList[$key]['review'] = $_reviewList[$val['product_id']];
		}
	}
}
