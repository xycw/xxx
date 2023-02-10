<?php
/**
 * modules product_prev_next.php
 */
$productPrevNextList = array(
	'count'   => 0,
	'current' => 0,
	'prev'    => 0,
	'next'    => 0
);
if ($current_page == FILENAME_PRODUCT) {
	$sql = "SELECT product_id FROM " . TABLE_PRODUCT . " WHERE master_category_id = :masterCategoryId AND status = 1";
	if (!defined('PRODUCT_LIST_SORT')) define('PRODUCT_LIST_SORT', 'position_asc');
	switch(PRODUCT_LIST_SORT) {
		case 'position_asc':
			$sql .= ' ORDER BY sort_order ASC';
		break;
		case 'ordered_desc':
			$sql .= ' ORDER BY ordered DESC, sort_order ASC';
		break;
		case 'date_added_desc':
			$sql .= ' ORDER BY date_added DESC, sort_order ASC';
		break;
		case 'price_asc':
			$sql .= ' ORDER BY IF(specials_price, specials_price, price) ASC, sort_order ASC';
		break;
		case 'price_desc':
			$sql .= ' ORDER BY IF(specials_price, specials_price, price) DESC, sort_order ASC';
		break;
		case 'viewed_desc':
			$sql .= ' ORDER BY viewed DESC, sort_order ASC';
		break;
	}
	$sql = $db->bindVars($sql, ':masterCategoryId', $productInfo['master_category_id'], 'integer');
	$result = $db->Execute($sql, false, true, 604800);
	$productPrevNextList['count'] = $result->RecordCount();
	while (!$result->EOF) {
		if ($result->fields['product_id'] == $productInfo['product_id']) {
			$productCurrent = $result->cursor;
			$productPrevNextList['current'] = $productCurrent + 1;
			$result->Move($productCurrent - 1);
			if ($result->EOF) $result->Move($productPrevNextList['count'] - 1);
			$productPrevNextList['prev'] = $result->fields['product_id'];
			
			$result->Move($productCurrent + 1);
			if ($result->EOF) $result->Move(0);
			$productPrevNextList['next'] = $result->fields['product_id'];
			break;
		}
		$result->MoveNext();
	}
}
