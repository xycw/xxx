<?php
function product_count_in_category($category_id, $include_inactive = false)
{
	global $db;
	$product_count = 0;
	$sql = "SELECT count(*) AS total
			FROM   " . TABLE_PRODUCT . " p, " . TABLE_PRODUCT_TO_CATEGORY . " ptc
			WHERE  p.product_id = ptc.product_id
			AND    ptc.category_id = :categoryID";
	if ($include_inactive == false) {
		$sql .= " AND p.status = '1'";
	}
	$sql = $db->bindVars($sql, ':categoryID', $category_id, 'integer');
	$product = $db->Execute($sql, false, true, 604800);
	$product_count += $product->fields['total'];
	
	$sql = "SELECT category_id
			FROM   " . TABLE_CATEGORY . "
			WHERE  parent_id = :categoryID";
	$sql = $db->bindVars($sql, ':categoryID', $category_id, 'integer');
	$child_category = $db->Execute($sql, false, true, 604800);
	if ($child_category->RecordCount() > 0) {
		while (!$child_category->EOF) {
			$product_count += product_count_in_category($child_category->fields['category_id'], $include_inactive);
			$child_category->MoveNext();
		}
	}

	return $product_count;
}