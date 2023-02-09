<?php
// 3d异步通知 加密方式和正常交易一致
// $data['entry_notice_url'] = $request_type == 'SSL' ? HTTPS_SERVER : HTTP_SERVER . '/notify_zdcheckout3f.php';

if (empty($_POST)) {
	die('POST数据为空');
}
require('includes/application_top.php');

if (!empty($_POST)) {
	require_once(DIR_FS_CATALOG_CLASSES . 'payment_method.php');
	$payment       = new payment_method('zdcheckout3f');
	$entryMD5key   = trim($payment->get_md5key());
	$entryOrderID  = $_POST['entry_order_id'];
	$entryCurrency = $_POST['entry_currency'];
	$entryAmount   = $_POST['entry_amount'];
	$entryCode     = $_POST['entry_code'];
	$entryStatus   = $_POST['entry_status'];
	$entrySecret   = $_POST['entry_secret'];

	$entryMD5src    = base64_decode($entryMD5key) . $entryOrderID . $entryCurrency . $entryAmount . $entryCode . $entryStatus;
	$secretValidate = strtoupper(hash('sha256', $entryMD5src));

	if ($secretValidate == $entrySecret) {
		$tempId   = explode('-', $entryOrderID);
		$order_id = count($tempId) == 2 ? $tempId[1] : $tempId[0];

		global $db; // 查询当前订单的状态是否成功
		$querySql = "SELECT order_status_id
				FROM " . TABLE_ORDERS . "
				WHERE order_id = {$order_id}
				LIMIT  1";

		try {
			$result = $db->Execute($querySql);
		} catch (Exception $e) {
			echo $e;
			die;
		}
		$status = ''; // 订单状态
		if (!$result->EOF) {
			$status = $result->fields['order_status_id'];
		}

		if ($status == '3') { // 状态为成功不用修改
			die('ok');
		} else {
			//orders
			$sql = "UPDATE " . TABLE_ORDERS . " SET order_status_id = :orderStatusID WHERE order_id = :orderID";
			$sql = $db->bindVars($sql, ':orderStatusID', '3', 'integer');
			$sql = $db->bindVars($sql, ':orderID', $entryOrderID, 'integer');

			try {
				$modifyResult = $db->Execute($sql);
			} catch (Exception $e) {
				echo $e;
				die;
			}

			if ($modifyResult > 0) {
				$sql_data_array = array(
					array('fieldName' => 'order_id', 'value' => $entryOrderID, 'type' => 'integer'),
					array('fieldName' => 'order_status_id', 'value' => '3', 'type' => 'integer'),
					array('fieldName' => 'date_added', 'value' => 'NOW()', 'type' => 'noquotestring')
				);
				$db->perform(TABLE_ORDER_STATUS_HISTORY, $sql_data_array);

				die('ok');
			}
		}

	} else {
		die('签名错误');
	}
}

function ajaxReturn($data, $type = 'JSON')
{
	switch (strtoupper($type)) {
		case 'XML':
			header('Content-Type:text/xml; charset=utf-8');
			exit($this->_xmlEncode($data));
		case 'JSON':
		default:
			//header('Content-Type:application/json; charset=utf-8');
			header('Content-Type:text/html; charset=utf-8');
			exit(json_encode($data));
	}
}