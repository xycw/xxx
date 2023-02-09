<?php
// 3d异步通知 加密方式和正常交易一致
// $data['entry_notice_url'] = $request_type == 'SSL' ? HTTPS_SERVER : HTTP_SERVER . '/notify_zdcheckout3f.php';

require('includes/application_top.php');

//获取返回信息
function getResultMessage($resultArray,$splitFlag){
	$message = 'Trade No.:' . $resultArray['tradeNo'] . $splitFlag .
			   'Payment Result:' . $resultArray['orderResult'];
	return $message;
}

function addHotPayLog($log)
{
    $fp = fopen(DIR_FS_CATALOG . 'cache/hotpay-notifylog-' . date('Y-m-d') . '.txt', 'a');
    flock($fp, LOCK_EX) ;
    fwrite($fp, '[' . date('Y-m-d H:i:s') . ']' . $log . "\r\n");
    flock($fp, LOCK_UN);
    fclose($fp);
}

//获取返回数据
if (! isset ( $_POST ) || empty ( $_POST ['orderNo'] ) || empty ( $_POST ['signInfo'] )) {
	$_POST = $_GET;
}

$result = $_POST;


addHotPayLog(http_build_query($result,'','&'));

if (!empty($result)) {
	require_once(DIR_FS_CATALOG_CLASSES . 'payment_method.php');
	$payment       = new payment_method('hotpay');
	$MD5key   = trim($payment->get_md5key());
	$signInfo      = $result['signInfo'];
	$orderSucceed  = $result['orderSucceed'];
	$str     = $result['merNo'].$result['terNo'].$result['tradeNo'].$result['orderNo'].$result['orderCurrency'].$result['orderAmount'].$result['orderSucceed'].$MD5key;
    $mySign  = strtoupper(hash('sha256',$str));

	$entryMD5src    = base64_decode($entryMD5key) . $entryOrderID . $entryCurrency . $entryAmount . $entryCode . $entryStatus;
	$secretValidate = strtoupper(hash('sha256', $entryMD5src));
    $order_id = get_orderNO($result['orderNo']);

	if ($signInfo == $mySign) {
		$order_id = get_orderNO($result['orderNo']);

		// 查询当前订单的状态是否成功
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
			if($orderSucceed == 1){
				$sql = "UPDATE " . TABLE_ORDERS . " SET order_status_id = :orderStatusID WHERE order_id = :orderID";
				$sql = $db->bindVars($sql, ':orderStatusID', '3', 'integer');
				$sql = $db->bindVars($sql, ':orderID', $order_id, 'integer');

				try {
					$modifyResult = $db->Execute($sql);
				} catch (Exception $e) {
					echo $e;
					die;
				}

				if ($modifyResult > 0) {
					$comments              = getResultMessage($_POST,' | ');
					$sql_data_array = array(
						array('fieldName' => 'order_id', 'value' => $order_id, 'type' => 'integer'),
						array('fieldName' => 'remarks', 'value' => $comments, 'type' => 'noquotestring'),
						array('fieldName' => 'order_status_id', 'value' => '3', 'type' => 'integer'),
						array('fieldName' => 'date_added', 'value' => 'NOW()', 'type' => 'noquotestring')
					);
					$db->perform(TABLE_ORDER_STATUS_HISTORY, $sql_data_array);

					die('ok');
				}
			}
		}

	} else {
		die('签名错误');
	}
}