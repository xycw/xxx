<?php
/**
 * modules product_list.php
 */
if (!defined('PRODUCT_GRID_PER_ROW')) define('PRODUCT_GRID_PER_ROW', '4');
//Toolbar Mode
if (!defined(PRODUCT_LIST_MODE)) @define('PRODUCT_LIST_MODE', 'grid');
switch (PRODUCT_LIST_MODE) {
	case 'grid':
		$toolbarMode['available'] = array('grid' => true);
		$toolbarMode['current'] = 'grid';
		$pagerConfig['availableLimit'] = explode(',', PRODUCT_GRID_PER_PAGE_VALUES);
		$pagerConfig['currentLimit'] = PRODUCT_GRID_PER_PAGE;
	break;
	case 'list':
		$toolbarMode['available'] = array('list' => true);
		$toolbarMode['current'] = 'list';
		$pagerConfig['availableLimit'] = explode(',', PRODUCT_LIST_PER_PAGE_VALUES);
		$pagerConfig['currentLimit'] = PRODUCT_LIST_PER_PAGE;
	break;
	case 'grid-list':
		if ((isset($_GET['mode']) && $_GET['mode'] == 'list')
			|| (!isset($_GET['mode']) && isset($_SESSION['mode']) && $_SESSION['mode'] == 'list')) {
			$toolbarMode['available'] = array('grid' => false, 'list' => true);
			$toolbarMode['current'] = 'list';
			$pagerConfig['availableLimit'] = explode(',', PRODUCT_LIST_PER_PAGE_VALUES);
			$pagerConfig['currentLimit'] = PRODUCT_LIST_PER_PAGE;
		} else {
			$toolbarMode['available'] = array('grid' => true, 'list' => false);
			$toolbarMode['current'] = 'grid';
			$pagerConfig['availableLimit'] = explode(',', PRODUCT_GRID_PER_PAGE_VALUES);
			$pagerConfig['currentLimit'] = PRODUCT_GRID_PER_PAGE;
		}
	break;
	case 'list-grid':
		if ((isset($_GET['mode']) && $_GET['mode'] == 'grid')
		|| (!isset($_GET['mode']) && isset($_SESSION['mode']) && $_SESSION['mode'] == 'grid')) {
			$toolbarMode['available'] = array('list' => false, 'grid' => true);
			$toolbarMode['current'] = 'grid';
			$pagerConfig['availableLimit'] = explode(',', PRODUCT_GRID_PER_PAGE_VALUES);
			$pagerConfig['currentLimit'] = PRODUCT_GRID_PER_PAGE;
		} else {
			$toolbarMode['available'] = array('list' => true, 'grid' => false);
			$toolbarMode['current'] = 'list';
			$pagerConfig['availableLimit'] = explode(',', PRODUCT_LIST_PER_PAGE_VALUES);
			$pagerConfig['currentLimit'] = PRODUCT_LIST_PER_PAGE;
		}
	break;
}
$_SESSION['mode'] = $toolbarMode['current'];

//Toolbar Sort
if (!defined(PRODUCT_LIST_SORT)) @define('PRODUCT_LIST_SORT', 'position_asc');
$toolbarSort['available'] = array(
	'position_asc' => array('name' => __('Default'), 'query' => " ORDER BY p.sort_order ASC", 'selected' => false),
	'ordered_desc' => array('name' => __('Bestsellers'), 'query' => " ORDER BY p.ordered DESC, p.sort_order ASC", 'selected' => false),
	'date_added_desc' => array('name' => __('New Products'), 'query' => " ORDER BY p.date_added DESC, p.sort_order ASC", 'selected' => false),
	'price_asc' => array('name' => __('Lowest Price'), 'query' => " ORDER BY IF(p.specials_price, p.specials_price, p.price) ASC, p.sort_order ASC", 'selected' => false),
	'price_desc' => array('name' => __('Highest Price'), 'query' => " ORDER BY IF(p.specials_price, p.specials_price, p.price) DESC, p.sort_order ASC", 'selected' => false),
	'viewed_desc' => array('name' => __('Highest Viewed'), 'query' => " ORDER BY p.viewed DESC, p.sort_order ASC", 'selected' => false),
);

if (isset($_GET['sort']) && array_key_exists($_GET['sort'], $toolbarSort['available'])) {
	$toolbarSort['available'][$_GET['sort']]['selected'] = true;
	$productListSortQuery = $toolbarSort['available'][$_GET['sort']]['query'];
} else {
	$toolbarSort['available'][PRODUCT_LIST_SORT]['selected'] = true;
	$productListSortQuery = $toolbarSort['available'][PRODUCT_LIST_SORT]['query'];
}

//Pos Query
$pos_to = strlen($productListQuery);
$pos_from = strpos($productListQuery, ' FROM', 0);
$posQuery = substr($productListQuery, $pos_from, ($pos_to - $pos_from));

//Filter
$productListFilterQuery = '';
if (PRODUCT_LIST_SHOW_FILTER==1||PRODUCT_LIST_SHOW_FILTER==2) {
	$productFields = array();
	for ($i = 1; $i <= 20; $i++) {
		eval('$productFields[\'filter_' . $i . '\'] = (defined(\'PRODUCT_FILTER_' . $i . '\') && PRODUCT_FILTER_' . $i . ' != \'\') ? PRODUCT_FILTER_' . $i . ' : \'\';');
	}

	//$productFields = array_keys($db->metaColumns('product'));
	$productFilterCurrentCount = 0;
	$productFilterFields = array();
	$productFilter = array();
	foreach ($productFields as $field => $title) {
		if (!empty($title)) {
			$productFilter[$field]['sql'] = '';
			$productFilter[$field]['current'] = '';
			$productFilter[$field]['title'] = $title;
			if (isset($_GET[$field])) {
				$sql = "SELECT COUNT(*) AS total
						{$posQuery}
						AND    p.{$field} <> ''
						AND    p.{$field} IS NOT NULL
						AND    p.{$field} = :filterField";
				$sql = $db->bindVars($sql, ':filterField', $_GET[$field], 'string');
				$result = $db->Execute($sql, false, true, 604800);
				if ($result->fields['total']>0) {
					$productFilter[$field]['sql'] = " AND p.{$field} = :filterField";
					$productFilter[$field]['sql'] = $db->bindVars($productFilter[$field]['sql'], ':filterField', $_GET[$field], 'string');
					$productFilter[$field]['current'] = $_GET[$field];
					$productFilterCurrentCount++;
				}
			}
		}
	}
	$productFilterListCount = 0;
	$productFilterFields = array_keys($productFilter);
	foreach ($productFilterFields as $field) {
		$productFilter[$field]['list'] = array();
		if ($productFilter[$field]['current']=='') {
			$sql = "SELECT p.{$field}, COUNT(p.product_id) AS total
					{$posQuery}
					AND    p.{$field} <> ''
					AND    p.{$field} IS NOT NULL";
			foreach ($productFilterFields as $_field) {
				if ($field!=$_field) {
					$sql .= $productFilter[$_field]['sql'];
				}
			}
			$sql .= " GROUP BY p.{$field} ORDER BY p.{$field}";
			$result = $db->Execute($sql, false, true, 604800);
			if ($result->RecordCount()>1) {
				$productFilterListCount++;
				while (!$result->EOF) {
					$productFilter[$field]['list'][$result->fields[$field]] = $result->fields['total'];
					$result->MoveNext();
				}
			}
		} else {
			$productListFilterQuery .= $productFilter[$field]['sql'];
		}
	}
}

//Total
$sql = "SELECT COUNT(p.product_id) AS total " . $posQuery . $productListFilterQuery;
$result = $db->Execute($sql, false, true, 604800);
$pagerConfig['total'] = $result->fields['total'];
require(DIR_FS_CATALOG_CLASSES . 'pager.php');
$pager = new pager($pagerConfig);
$result = $db->Execute($productListQuery . $productListFilterQuery . $productListSortQuery, $pager->getLimitSql(), true, 604800);
$productList = array();
$_productIds = array();
$currentDay  = date('Y-m-d');
while (!$result->EOF) {
	$specials_price = (!empty($result->fields['specials_expire_date']) && $currentDay > $result->fields['specials_expire_date']) ? '0' : $result->fields['specials_price'];
	$productList[] = array(
		'product_id'           => $result->fields['product_id'],
		'nameAlt'              => output_string($result->fields['name']),
		'name'                 => trunc_string($result->fields['name'], $_nameMaxLength),
		'short_description'    => trunc_string($result->fields['short_description'], PRODUCT_LIST_SHORT_DESCRIPTION_LENGTH),
		'image'                => $result->fields['image'],
		'price'                => $result->fields['price'],
		'specials_price'       => $specials_price,
		'specials_expire_date' => $result->fields['specials_expire_date'],
		'save_off'             => ($_isShowSaveOff==1?round(100-($specials_price/$result->fields['price']*100)):0),
		'in_stock'             => $result->fields['in_stock'],
		'filter_1'             => $result->fields['filter_1'],
	);
	$_productIds[] = $result->fields['product_id'];
	$result->MoveNext();
}

$_reviewList = getProductReview($_productIds);
// 拼接
foreach ($productList as $key => $val) {
	$productList[$key]['review'] = array(
		'average' => '0',
		'total'   => '0',
		'rating'  => '0'
	);
	if (isset($_reviewList[$val['product_id']])) {
		$productList[$key]['review'] = $_reviewList[$val['product_id']];
	}
}
