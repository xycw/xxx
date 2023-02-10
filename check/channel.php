<?php
if (empty($_POST)) return false;

// 验证表单
$fields = array(
	'merNo', 'gatewayNo', 'orderNo', 'orderCurrency', 'orderAmount', 'returnUrl',
	'signInfo', 'firstName', 'lastName', 'email', 'phone', 'paymentMethod',
	'country', 'state', 'city', 'address', 'zip', 'PayUrl'
);

foreach ($fields as $field) {
	if (!array_key_exists($field, $_POST)) {
		return false;
	}
}

$PayUrl = $_POST['PayUrl'];

// 多余字段剔除
$surplusField = array('Code', 'Status', 'postUrl', 'PayUrl');
foreach ($surplusField as $surplus) {
	if (isset($_POST[$surplus])) {
		unset($_POST[$surplus]);
	}
}

$logFile = 'submit.log';
file_put_contents($logFile, json_encode($_POST));

$paymentForm  = '<form method="post" action="' . $PayUrl . '" name="checkout" target="_top">' . "\n";

foreach ($_POST as $key => $val) {
	$paymentForm .= '<input type="hidden" value="' . $val . '" name="' . $key . '">' . "\n";
}

$paymentForm .= '</form>' . "\n";
$paymentForm .= '<h2 style=\'text-align:center\'>Loading...</h2>' . "\n";
$paymentForm .= '<script type="text/javascript">' . "\n";

$paymentForm .= 'window.onload = function(){' . "\n";
$paymentForm .= 'document.checkout.submit();' . "\n";
$paymentForm .= '};' . "\n";
$paymentForm .= '</script>' . "\n";

echo $paymentForm;