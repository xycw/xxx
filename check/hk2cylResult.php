<?php
if (!isset($_GET['order_id']) && empty($_GET['order_id'])) return false;
include('Function.php');
$data                  = $_GET;
$data['orderNo']       = $_GET['order_id'];
$data['bank_order_id'] = $_GET['order_id'];

$url        = 'http://wru8zys.gtopay.com/payment/needvalidate/result';
$res        = _post($url, $data);
$returnData = json_decode($res, true);

// 保存网关返回结果
_log(json_encode($res), 'paymentReturn');

if (empty($returnData) || !is_array($returnData) || (isset($returnData['error']) && $returnData['error'] == true) || is_null($returnData)) {
	_log(json_encode($_SERVER), 'paymentError');
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
