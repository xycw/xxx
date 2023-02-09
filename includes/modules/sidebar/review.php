<?php
/**
 * sidebar review.php
 */
if (!defined('REVIEW_SIDEBAR_LIMIT')) define('REVIEW_SIDEBAR_LIMIT', '3');
$reviewSidebarList = array();
if (REVIEW_SIDEBAR_LIMIT > 0) {
	$sql = "SELECT p.product_id, p.name, p.image,
				   pr.nickname, pr.rating, pr.content, pr.date_added
			FROM   " . TABLE_PRODUCT . " p LEFT JOIN " . TABLE_PRODUCT_REVIEW . " pr ON p.product_id = pr.product_id
			WHERE  pr.status = 1
			ORDER BY pr.date_added DESC, pr.product_review_id DESC";
	$result = $db->Execute($sql, REVIEW_SIDEBAR_LIMIT);
	while (!$result->EOF) {
		$reviewSidebarList[] = array(
			'product_id' => $result->fields['product_id'],
			'nameAlt'    => output_string($result->fields['name']),
			'name'       => trunc_string($result->fields['name'], PRODUCT_NAME_SIDEBAR_MAX_LENGTH),
			'image'      => $result->fields['image'],
			'nickname'   => $result->fields['nickname'],
			'rating'     => $result->fields['rating'],
			'content'    => trunc_string($result->fields['content'], REVIEW_CONTENT_SIDEBAR_MAX_LENGTH),
			'date_added' => $result->fields['date_added']
		);
		$result->MoveNext();
	}	
}
