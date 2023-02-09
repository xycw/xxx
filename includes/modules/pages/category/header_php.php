<?php
/**
 * category header_php.php
 */
if (!isset($_GET['cID'])) {
	redirect(href_link(FILENAME_PAGE_NOT_FOUND));
	exit;
}
$sql = "SELECT category_id, name, description, image,
			   meta_title, meta_keywords, meta_description
		FROM   " . TABLE_CATEGORY . "
		WHERE  category_id = :categoryID
		AND    status = 1
		LIMIT 1";
$sql = $db->bindVars($sql, ':categoryID', $_GET['cID'], 'integer');
$result = $db->Execute($sql);
if ($result->RecordCount()>0) {
	$categoryInfo = array(
		'category_id'      => $result->fields['category_id'],
		'name'             => $result->fields['name'],
		'nameAlt'          => output_string($result->fields['name']),
		'description'      => $result->fields['description'],
		'image'            => $result->fields['image'],
		'banner_image'     => (is_file(DIR_FS_CATALOG_IMAGES.'banners/'.$result->fields['image'])?'banners/'.$result->fields['image']:$result->fields['image']),
		'meta_title'       => $result->fields['meta_title'],
		'meta_keywords'    => $result->fields['meta_keywords'],
		'meta_description' => $result->fields['meta_description']
	);
} elseif(IS_ZP == '1'){
	$sql = "SELECT category_id, name, description, image,
				   meta_title, meta_keywords, meta_description
			FROM   " . TABLE_CATEGORY . "
			WHERE  status = 1
			LIMIT 10";
	$result = $db->Execute($sql);
	if ($result->RecordCount()>0) {
		$randCnt = rand(0, $result->RecordCount() - 1);
		$result->Move($randCnt);
		$categoryInfo = array(
			'category_id'      => $result->fields['category_id'],
			'name'             => $result->fields['name'],
			'nameAlt'          => output_string($result->fields['name']),
			'description'      => $result->fields['description'],
			'image'            => $result->fields['image'],
			'banner_image'     => (is_file(DIR_FS_CATALOG_IMAGES . 'banners/' . $result->fields['image']) ? 'banners/' . $result->fields['image'] : $result->fields['image']),
			'meta_title'       => $result->fields['meta_title'],
			'meta_keywords'    => $result->fields['meta_keywords'],
			'meta_description' => $result->fields['meta_description']
		);
		$_GET['cID'] = $result->fields['category_id'];
	} else {
		redirect(href_link(FILENAME_PAGE_NOT_FOUND));
		exit;
	}
} else {
	redirect(href_link(FILENAME_PAGE_NOT_FOUND));
	exit;
}
//Breadcrumb
$parcategories = $category_tree->getParcategories('', $_GET['cID']);
foreach ($parcategories as $parcategoryID) {
	$breadcrumb->add($category_tree->getCategoryName($parcategoryID), 'sub', href_link(FILENAME_CATEGORY, 'cID=' . $parcategoryID));
}
$breadcrumb->add($categoryInfo['name'], 'root');
//subcategories
$subcategories = $category_tree->getSubcategories('', $_GET['cID']);

$productListQuery = '';
$subcategoryList = array();
if (!defined('CATEGORY_LIST_PER_ROW')) define('CATEGORY_LIST_PER_ROW', '4');
if (count($subcategories) > 0) {
	if (CATEGORY_PAGE_SHOW_MODE==1 || CATEGORY_PAGE_SHOW_MODE==2) {
		$sql = "SELECT category_id, name, image
				FROM   " . TABLE_CATEGORY . "
				WHERE  parent_id = :parent_id
				AND    status = 1
				ORDER BY sort_order, name";
		$sql = $db->bindVars($sql, ':parent_id', $_GET['cID'], 'integer');
		$result = $db->Execute($sql, false, true, 86400);
		while (!$result->EOF) {
			$subcategoryList[] = array(
				'category_id' => $result->fields['category_id'],
				'name'        => $result->fields['name'],
				'nameAlt'     => output_string($result->fields['name']),
				'image'       => $result->fields['image']
			);
			$result->MoveNext();
		}
	}
	if (CATEGORY_PAGE_SHOW_MODE==0 || CATEGORY_PAGE_SHOW_MODE==2) {
		$sql = "SELECT product_id FROM " . TABLE_PRODUCT_TO_CATEGORY . " WHERE category_id IN (:categoryIDS)";
		$sql = $db->bindVars($sql, ':categoryIDS', implode(',', $subcategories), 'noquotestring');
		$productListQuery = "SELECT p.product_id, p.name, p.short_description, p.image, p.price,
								p.specials_price, p.specials_expire_date, p.in_stock, p.filter_1
						 FROM   " . TABLE_PRODUCT . " p
						 WHERE  p.status = 1
						 AND    p.product_id IN ({$sql})";
	}
} else {
	$sql = "SELECT product_id FROM " . TABLE_PRODUCT_TO_CATEGORY . " WHERE category_id = :categoryID";
	$sql = $db->bindVars($sql, ':categoryID', $_GET['cID'], 'integer');
	$productListQuery = "SELECT p.product_id, p.name, p.short_description, p.image, p.price,
								p.specials_price, p.specials_expire_date, p.in_stock, p.filter_1
						 FROM   " . TABLE_PRODUCT . " p
						 WHERE  p.status = 1
						 AND    p.product_id IN ({$sql})";
}
