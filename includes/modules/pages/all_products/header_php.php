<?php
/**
 * all_products header_php.php
 */
$productListQuery = "SELECT p.product_id, p.name, p.short_description, p.image, p.price,
							p.specials_price, p.specials_expire_date, p.in_stock, p.filter_1
					 FROM   " . TABLE_PRODUCT . " p
					 WHERE  p.status = 1";
//subcategories
if (isset($_GET['cID']) && not_null($_GET['cID'])) {
	$subcategories = get_subcategories('', $_GET['cID']);
	if (count($subcategories) > 0) {
		$sql = "SELECT product_id FROM " . TABLE_PRODUCT_TO_CATEGORY . " WHERE category_id IN (:categoryIDS)";
		$sql = $db->bindVars($sql, ':categoryIDS', implode(',', $subcategories), 'noquotestring');
	} else {
		$sql = "SELECT product_id FROM " . TABLE_PRODUCT_TO_CATEGORY . " WHERE category_id = :categoryID";
		$sql = $db->bindVars($sql, ':categoryID', $_GET['cID'], 'integer');
	}
	$productListQuery .= " AND p.product_id IN ({$sql})";
}
//Breadcrumb
$breadcrumb->add(__('All Products'), 'root');
