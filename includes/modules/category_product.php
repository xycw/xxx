<?php
/**
 * modules category_product.php
 */
if (!defined('CATEGORY_PRODUCT_LIMIT')) define('CATEGORY_PRODUCT_LIMIT', '8');
if (!defined('MOBILE_CATEGORY_PRODUCT_LIMIT')) define('MOBILE_CATEGORY_PRODUCT_LIMIT', '8');
if (!defined('CATEGORY_PRODUCT_PER_ROW')) define('CATEGORY_PRODUCT_PER_ROW', '4');

// 是否是M端
if (true == $_isMobileTemplate) {
	$pageLimit = MOBILE_CATEGORY_PRODUCT_LIMIT;
} else {
	$pageLimit = CATEGORY_PRODUCT_LIMIT;
}

// 自定义
$categoryProductList = array();
$currentDay = date('Y-m-d');
if ($pageLimit > 0) {
	$sql    = "SELECT category_id, name FROM " . TABLE_CATEGORY . " WHERE parent_id = 0 AND status = 1 ORDER BY sort_order";
	$result = $db->Execute($sql, 10, true, 604800);
	while (!$result->EOF) {
		$subCategoryArr = $category_tree->getSubcategories('', $result->fields['category_id']);
		if (count($subCategoryArr) > 0) {
			$sql = "SELECT product_id, name, image, price,
						   specials_price, specials_expire_date, filter_1
					FROM   " . TABLE_PRODUCT . "
					WHERE  status = 1
					AND    in_stock = 1
					AND    product_id IN (SELECT product_id FROM " . TABLE_PRODUCT_TO_CATEGORY . " WHERE category_id IN (:categoryIDS))
					ORDER BY product_id DESC";
			$sql = $db->bindVars($sql, ':categoryIDS', implode(',', $subCategoryArr), 'noquotestring');
			$productResult = $db->Execute($sql, $pageLimit, true, 604800);
			$subProductArr = array();
			while (!$productResult->EOF) {
				$specialsPrice = (!empty($productResult->fields['specials_expire_date']) && $currentDay > $productResult->fields['specials_expire_date']) ? '0' : $productResult->fields['specials_price'];
				$subProductArr[$productResult->fields['product_id']] = array(
					'product_id' => $productResult->fields['product_id'],
					'nameAlt' => output_string($productResult->fields['name']),
					'name' => trunc_string($productResult->fields['name'], $_nameMaxLength),
					'image' => $productResult->fields['image'],
					'price' => $productResult->fields['price'],
					'specials_price' => $specialsPrice,
					'specials_expire_date' => $productResult->fields['specials_expire_date'],
					'filter_1' => $productResult->fields['filter_1'],
					'save_off' => ($_isShowSaveOff == 1 ? round(100 - ($specialsPrice / $productResult->fields['price'] * 100)) : 0),
				);
				$productResult->MoveNext();
			}
			$subReviewList = getProductReview(array_keys($subProductArr));
			foreach ($subProductArr as $key => $val) {
				$subProductArr[$key]['review'] = array(
					'average' => '0',
					'total' => '0',
					'rating' => '0'
				);
				if (isset($subReviewList[$key])) {
					$subProductArr[$key]['review'] = $subReviewList[$key];
				}
			}
			$categoryProductList[$result->fields['category_id']] = array(
				'name'        => $result->fields['name'],
				'productList' => $subProductArr
			);
		}
		$result->MoveNext();
	}
}
