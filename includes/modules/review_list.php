<?php
/**
 * modules review_list.php
 */
if (!defined('REVIEW_LIMIT')) define('REVIEW_LIMIT', '8');
$reviewList = array();
if (REVIEW_LIMIT > 0) {
	if (isset($_GET['cID'])) {
		if (count($subcategories) > 0) {
			$sql = "SELECT DISTINCT(p.product_id), p.name, p.image,
						   pr.nickname, pr.rating, pr.content, pr.date_added
					FROM   " . TABLE_PRODUCT . " p
					LEFT JOIN " . TABLE_PRODUCT_REVIEW . " pr ON p.product_id = pr.product_id
					LEFT JOIN " . TABLE_PRODUCT_TO_CATEGORY . " ptc ON p.product_id = ptc.product_id
					WHERE  pr.status = 1
					AND    ptc.category_id IN (:categoryIDS)
					ORDER BY pr.date_added DESC, pr.product_review_id DESC";
			$sql = $db->bindVars($sql, ':categoryIDS', implode(',', $subcategories), 'noquotestring');
		} else {
			$sql = "SELECT DISTINCT(p.product_id), p.name, p.image,
						   pr.nickname, pr.rating, pr.content, pr.date_added
					FROM   " . TABLE_PRODUCT . " p
					LEFT JOIN " . TABLE_PRODUCT_REVIEW . " pr ON p.product_id = pr.product_id
					LEFT JOIN " . TABLE_PRODUCT_TO_CATEGORY . " ptc ON p.product_id = ptc.product_id
					WHERE  pr.status = 1
					AND    ptc.category_id = :categoryID
					ORDER BY pr.date_added DESC, pr.product_review_id DESC";
			$sql = $db->bindVars($sql, ':categoryID', $_GET['cID'], 'integer');
		}
	} else {
		$sql = "SELECT p.product_id, p.name, p.image,
					   pr.nickname, pr.rating, pr.content, pr.date_added
				FROM   " . TABLE_PRODUCT . " p LEFT JOIN " . TABLE_PRODUCT_REVIEW . " pr ON p.product_id = pr.product_id
				WHERE  pr.status = 1
				ORDER BY pr.date_added DESC, pr.product_review_id DESC";	
	}
	$result = $db->Execute($sql, REVIEW_LIMIT);
	while (!$result->EOF) {
		$reviewList[] = array(
			'product_id' => $result->fields['product_id'],
			'nameAlt'    => output_string($result->fields['name']),
			'name'       => $result->fields['name'],
			'image'      => $result->fields['image'],
			'nickname'   => $result->fields['nickname'],
			'rating'     => $result->fields['rating'],
			'content'    => trunc_string($result->fields['content'], REVIEW_CONTENT_MAX_LENGTH),
			'date_added' => $result->fields['date_added']
		);
		$result->MoveNext();
	}
}
