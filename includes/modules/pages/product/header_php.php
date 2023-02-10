<?php
/**
 * product header_php.php
 */
if (!isset($_GET['pID'])) {
	redirect(href_link(FILENAME_PAGE_NOT_FOUND));
	exit;
}

if (isset($_POST['action'])
	&& $_POST['action']=='process') {
	$error = false;
	$review = db_prepare_input($_POST['review']);
	$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
	if ($securityToken != $_SESSION['securityToken']) {
		$error = true;
		$message_stack->add('product',  __('There was a security error.'));
	}
	if ($review['rating'] < 1 || $review['rating'] > 5) {
		$error = true;
		$message_stack->add('product', __('"Rating" is a required value. Please select rating.'));
	}
	if (strlen($review['nickname']) < 1) {
		$error = true;
		$message_stack->add('product', __('"Nickname" is a required value. Please enter the nickname.'));
	}
	if (strlen($review['content']) < 1) {
		$error = true;
		$message_stack->add('product', __('"Review" is a required value. Please enter review.'));
	}
	if ($error==true) {
	//nothing
	} else {
		$sql_data_array = array(
			array('fieldName'=>'product_id', 'value'=>$_GET['pID'], 'type'=>'integer'),
			array('fieldName'=>'customer_id', 'value'=>isset($_SESSION['customer_id'])?$_SESSION['customer_id']:0, 'type'=>'integer'),
			array('fieldName'=>'nickname', 'value'=>$review['nickname'], 'type'=>'string'),
			array('fieldName'=>'rating', 'value'=>$review['rating'], 'type'=>'integer'),
			array('fieldName'=>'content', 'value'=>$review['content'], 'type'=>'string'),
			array('fieldName'=>'status', 'value'=>0, 'type'=>'integer'),
			array('fieldName'=>'date_added', 'value'=>'NOW()', 'type'=>'noquotestring')
		);
		$db->perform(TABLE_PRODUCT_REVIEW, $sql_data_array);
		$message_stack->add_session('product', __('The review has been saved.'), 'success');
		redirect(href_link(FILENAME_PRODUCT, 'pID=' . $_GET['pID']));
	}
}

$sql = "SELECT *
		FROM   " . TABLE_PRODUCT . "
		WHERE  product_id = :productID
		AND    status = 1
		LIMIT 1";
$sql = $db->bindVars($sql, ':productID', $_GET['pID'], 'integer');
$result = $db->Execute($sql);
$currentDay = date('Y-m-d');
$productFilterFields = array();
$productFields = array_keys($db->metaColumns('product'));
foreach ($productFields as $field) {
	if (strstr($field, 'filter_')) {
		$productFilterFields[] = $field;
	}
}
if ($result->RecordCount()>0) {
	$specials_price = (!empty($result->fields['specials_expire_date']) && $currentDay > $result->fields['specials_expire_date']) ? '0' : $result->fields['specials_price'];
	$productInfo = array(
		'product_id'           => $result->fields['product_id'],
		'sku'                  => $result->fields['sku'],
		'name'                 => $result->fields['name'],
		'nameAlt'              => output_string($result->fields['name']),
		'short_description'    => $result->fields['short_description'],
		'description'          => $result->fields['description'],
		'image'                => $result->fields['image'],
		'price'                => $result->fields['price'],
		'specials_price'       => $specials_price,
		'specials_expire_date' => $result->fields['specials_expire_date'],
		'save_off'             => (PRODUCT_PAGE_SHOW_SAVE_OFF==1?round(100-($specials_price/$result->fields['price']*100)):0),
		'meta_title'           => $result->fields['meta_title'],
		'meta_keywords'        => $result->fields['meta_keywords'],
		'meta_description'     => $result->fields['meta_description'],
		'stock_qty'            => $result->fields['stock_qty'],
		'in_stock'             => $result->fields['in_stock'],
		'viewed'               => $result->fields['viewed'],
		'ordered'              => $result->fields['ordered'],
		'master_category_id'   => $result->fields['master_category_id'],
		'additional_image'     => get_additional_image($result->fields['image']),
		'attribute'            => get_product_attribute($result->fields['product_id']),
		'color'                => PRODUCT_SHOW_COLOR == 1 ? get_product_color($result->fields['sku']) : array(),
		'group_name'           => $result->fields['group_name'],
		'product_group'        => get_product_group($result->fields['group_name']),
		'review'               => get_product_review($result->fields['product_id']),
		'qty'                  => true
	);
	foreach ($productFilterFields as $field) {
		$productInfo[$field] = $result->fields[$field];
	}
} else {
	redirect(href_link(FILENAME_PAGE_NOT_FOUND));
	exit;
}
//viewed
if (!isset($_SESSION['product_viewed'][$_GET['pID']])) {
	$_SESSION['product_viewed'][$_GET['pID']] = $_GET['pID'];
	if (count($_SESSION['product_viewed']) < 50) {
		$sql = "UPDATE " . TABLE_PRODUCT . " SET viewed = viewed+1 WHERE product_id = :productID";
		$sql = $db->bindVars($sql, ':productID', $_GET['pID'], 'integer');
		$db->Execute($sql);
	}
}
//breadcrumb
$parcategories = $category_tree->getParcategories('', $productInfo['master_category_id']);
foreach ($parcategories as $parcategoryID) {
	$breadcrumb->add($category_tree->getCategoryName($parcategoryID), 'sub', href_link(FILENAME_CATEGORY, 'cID=' . $parcategoryID));
}
$breadcrumb->add($category_tree->getCategoryName($productInfo['master_category_id']), 'sub', href_link(FILENAME_CATEGORY, 'cID=' . $productInfo['master_category_id']));
$breadcrumb->add($productInfo['name'], 'root');
