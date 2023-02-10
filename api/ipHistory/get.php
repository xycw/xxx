<?php
/**
 * 根据条件获取订单列表
 */
function getIpHistoryList($where) {
	global $db;
	$sql = "SELECT * FROM " . TABLE_IP_HISTORY . $where . " ORDER BY ip_history_id ASC LIMIT 100";
	$result = $db->Execute($sql);
	$ipHistoryList = array();
	while (!$result->EOF) {
		$ipHistoryList[] = array(
			'ip_address'      => $result->fields['ip_address'],
			'is_facebook'     => $result->fields['is_facebook'],
			'continent_code'  => $result->fields['continent_code'],
			'country_code'    => $result->fields['country_code'],
			'http_request'    => $result->fields['http_request'],
			'http_referer'    => $result->fields['http_referer'],
			'http_user_agent' => $result->fields['http_user_agent'],
			'is_zp'           => $result->fields['is_zp'],
			'date_added'      => $result->fields['date_added']
		);
		$result->MoveNext();
	}

	return $ipHistoryList;
}

if (isset($_GET['lastDate']) && !is_null($_GET['lastDate'])) {
	$where = " where date_added > :date_added";
	$where = $db->bindVars($where, ':date_added', $_GET['lastDate'], 'string');
} else {
	$where = '';
}

header('Content-Type:text/html; charset=utf-8');
echo json_encode(getIpHistoryList($where));
