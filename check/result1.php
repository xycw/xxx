<?php
if (empty($_POST) && empty($_GET)) return false;
if (empty($_POST['orderNo'])) return false;

if (isset($_POST['orderNo'])) {
	$_POST['bank_order_id'] = $_POST['orderNo'];
}

if (!empty($_POST)) {
	_log(json_encode($_POST), 'zd', '_POST');
}

if (isset($_POST['isPush']) && $_POST['isPush'] == '1') {
	// post回网关
	echo 'ok'; die;
}

$url        = 'http://nagqh1h.zuodaopay.com/safety/creditCardInterface/result';
$xmls       = _post($url, $_POST);
$returnData = _xmlToArr($xmls);

// 保存结果
_log(json_encode($returnData), 'zd', '_RETURN');

if (empty($returnData) || !is_array($returnData) || (isset($returnData['error']) && $returnData['error'] == true) || is_null($returnData)) {
	_log(json_encode($_SERVER), 'zd', '_ERROR');
	echo 'OK'; die;
}

$returnUrl    = urldecode($returnData['order']['entry_return_url']);
unset($returnData['order']['entry_return_url']);

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

/**
 * 将xml转为数组
 *
 * @param $xml
 * @return mixed
 */
function _xmlToArr($xml)
{
	$arr = json_decode(json_encode(simplexml_load_string($xml)), true);
	return _cleanArr($arr);
}

/**
 * 将数组中的空数组转为字符串
 *
 * @param $arr
 * @return array
 */
function _cleanArr($arr)
{
	$data = array();

	foreach ($arr as $key => $val) {
		if (is_array($val)) {
			if (empty($val)) {
				$data[$key] = '';
			} else {
				$data[$key] = _cleanArr($val);
			}
		} else {
			$data[$key] = $val;
		}
	}

	return $data;
}

function _log($message, $platform = 'zwb', $method)
{
	$logFile = str_replace('\\', '/', dirname(__FILE__)) . '/Log/' . $platform . 'PaymentRes' . date('Ymd') . $method . '.log';
	$content = "[". date('Y-m-d H:i:s') ."] : {$message}\r\n";
	error_log($content, 3, $logFile);
}