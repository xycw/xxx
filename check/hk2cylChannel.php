<?php
if (empty($_POST)) return false;
include('Function.php');

// 验证表单
$fields = array('TermUrl', 'PaReq', 'MD');

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

_log($res, 'submit');

$paymentForm  = '<form method="post" action="' . $PayUrl . '" name="checkout" target="_self">' . "\n";

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