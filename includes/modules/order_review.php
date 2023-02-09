<?php
/**
 * modules order_review.php
 */
$orderReviewList = array();
$sql = "SELECT * FROM " . TABLE_ORDER_REVIEW . " ORDER BY date_added DESC";
$result = $db->Execute($sql, false, true, 604800);
$orderReviewList = array();
while (!$result->EOF) {
	$orderReviewList[] = array(
		'quality'       => $result->fields['quality'],
		'ship'          => $result->fields['ship'],
		'service'       => $result->fields['service'],
		'email_address' => $result->fields['email_address'],
		'content'       => $result->fields['content'],
		'date_added'    => $result->fields['date_added']
	);
	$result->MoveNext();
}
