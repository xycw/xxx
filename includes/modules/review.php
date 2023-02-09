<?php
/**
 * modules review.php
 */
$reviewList = array();
if ($current_page == FILENAME_PRODUCT) {
	$sql = "SELECT COUNT(*) AS total FROM " . TABLE_PRODUCT_REVIEW . " WHERE status = 1 AND product_id = :productID";
	$sql = $db->bindVars($sql, ':productID', $_GET['pID'], 'integer');
	$result = $db->Execute($sql);
	$pager_config['total'] = $result->fields['total'];
	require(DIR_FS_CATALOG_CLASSES . 'pager.php');
	$pager = new pager($pager_config);
	$sql = "SELECT product_review_id, rating, nickname, content, date_added
				   FROM   " . TABLE_PRODUCT_REVIEW . "
				   WHERE  status = 1
				   AND    product_id = :productID
				   ORDER BY date_added DESC, product_review_id DESC";
	$sql = $db->bindVars($sql, ':productID', $_GET['pID'], 'integer');
	$result = $db->Execute($sql, $pager->getLimitSql());
	$reviewList = array();
	while (!$result->EOF) {
		$reviewList[] = array(
			'id'            => $result->fields['product_review_id'],
			'nickname'      => $result->fields['nickname'],
			'rating'        => $result->fields['rating'],
			'content'       => $result->fields['content'],
			'date_added'    => $result->fields['date_added']
		);
		$result->MoveNext();
	}
}
