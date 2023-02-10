<?php
header('Content-Type:text/html; charset=utf-8');
$orderId = get_orderNO($_POST['OrderID']);
if ($orderId < 1) die;
$nextOrderId = $orderId + 1;
// 调整订单表和订单产品表以及订单状态历史表中的数据
$db->Execute("DELETE FROM " . TABLE_ORDERS . " WHERE order_id > {$orderId}");
$db->Execute("ALTER TABLE " . TABLE_ORDERS . " AUTO_INCREMENT = {$nextOrderId}");
$db->Execute("DELETE FROM " . TABLE_ORDER_PRODUCT . " WHERE order_id > {$orderId}");
$db->Execute("DELETE FROM " . TABLE_ORDER_STATUS_HISTORY . " WHERE order_id > {$orderId}");
// 获取myorder支付方式的数据
$paymentMethod = $db->Execute("SELECT * FROM " . TABLE_PAYMENT_METHOD . " WHERE status = 1 AND code = 'myorder' LIMIT 1");
if (!$paymentMethod->RecordCount()) die;
require(DIR_FS_CATALOG_MODULES . 'payment/myorder.php');
$payment = new myorder();
$payment->addLog(json_encode($_POST));
// 修改来路地址
$_SERVER['HTTP_REFERER'] = href_link('myorder', '', 'SSL');
if ($paymentMethod->fields['is_inside'] == '1') {
	$_SERVER['HTTP_REFERER'] = href_link('shopping_cart', '', 'SSL');
}
// 获取产品信息
$sql = "SELECT name FROM " . TABLE_PRODUCT . " WHERE status = 1 AND in_stock = 1";
$result = $db->ExecuteRandomMulti($sql, rand(1, 2));
$products = array();
while (!$result->EOF) {
	$products[] = $result->fields['name'] . ' x ' . rand(1, 2);
	$result->MoveNextRandom();
}
// 开始替换内容
$_POST['AcctNo']    = trim($paymentMethod->fields['account']);
$MD5key             = trim($paymentMethod->fields['md5key']);
$_POST['HashValue'] = $payment->szComputeMD5Hash($MD5key . $_POST['AcctNo'] . $_POST['OrderID'] . $_POST['Amount'] . $_POST['CurrCode']);
$_POST['URL']       = $_SERVER['HTTP_HOST'];
$_POST['OrderUrl']  = href_link('myorder_process', '', 'SSL');
$_POST['PName']     = implode(' ', $products);
$payment->addLog(json_encode($_POST));
// 开始提交
$url    = trim($paymentMethod->fields['submit_url']);
echo $payment->curlPost($url, $_POST);
