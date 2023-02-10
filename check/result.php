<?php
if (empty($_POST) && empty($_GET)) return false;
if (empty($_POST['orderNo'])) return false;

if (isset($_POST['orderNo'])) {
	$_POST['bank_order_id'] = $_POST['orderNo'];
}

if (!empty($_POST)) {
	_log(json_encode($_POST), 'zwb', '_POST');
}

if (isset($_POST['isPush']) && $_POST['isPush'] == '1') {
	// post回网关
	echo 'ok'; die;
}

$url        = 'http://wru8zys.gtopay.com/payment/creditCardInterface/result';
$res        = _post($url, $_POST);
$returnData = json_decode($res, true);

// 保存网关返回结果
_log(json_encode($res), 'zwb', '_RETURN');

if (empty($returnData) || !is_array($returnData) || (isset($returnData['error']) && $returnData['error'] == true) || is_null($returnData)) {
	_log(json_encode($_SERVER), 'zwb', '_ERROR');
	echo 'OK'; die;
}

$returnUrl    = urldecode($returnData['order']['ReturnUrl']);
unset($returnData['order']['ReturnUrl']);

$postForm     = $returnData['order'];
$paymentForm  = '<form method="post" action="' . $returnUrl . '" name="checkout" target="_top">' . "\n";
$paymentForm .= '<input type="hidden" value="' . $returnData['error'] . '" name="error">' . "\n";

foreach ($postForm as $key => $val) {
	$paymentForm .= '<input type="hidden" value="' . $val . '" name="order['. $key .']">' . "\n";
}

$paymentForm .= '</form>' . "\n";

$paymentForm .= '<script type="text/javascript">' . "\n";
$paymentForm .= 'window.onload = function(){' . "\n";
$paymentForm .= 'document.checkout.submit();' . "\n";
$paymentForm .= '};' . "\n";
$paymentForm .= '</script>' . "\n";

echo $paymentForm; die;

function _post($url, $data)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch ,CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch ,CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_TIMEOUT, 120);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	$response = curl_exec($ch);
	curl_close($ch);
	return $response;
}

function _log($message, $platform = 'zwb', $method)
{
	$logFile = str_replace('\\', '/', dirname(__FILE__)) . '/Log/' . $platform . 'PaymentRes' . date('Ymd') . $method . '.log';
	$content = "[". date('Y-m-d H:i:s') ."] : {$message}\r\n";
	error_log($content, 3, $logFile);
}