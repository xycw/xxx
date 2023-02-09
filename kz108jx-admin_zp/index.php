<?php require('includes/application_top.php'); ?>
<?php
//删除图片缓存
if (isset($_GET['action'])) {
	switch ($_GET['action']) {
		case 'clearImg':
			clearImg();
			$message_stack->add_session('index', '图片缓存清除成功。', 'success');
			redirect(href_link(FILENAME_INDEX));
			break;
		case 'clearSql':
			$cache->sql_cache_flush_cache();
			$message_stack->add_session('index', '数据缓存清除成功。', 'success');
			redirect(href_link(FILENAME_INDEX));
			break;
	}
}

//货币初始化
$new_currency = currency_exists(STORE_CURRENCY);
if ($new_currency == false) $new_currency = currency_exists(STORE_CURRENCY, true);
require(DIR_FS_ADMIN_CLASSES . 'currencies.php');
$currencies = new currencies($new_currency);
//订单总价
$sql = "SELECT SUM(order_total) AS total FROM orders";
$result = $db->Execute($sql);
$ordersTotal = $result->fields['total'];
//订单数量
$sql = "SELECT COUNT(*) AS total FROM orders";
$result = $db->Execute($sql);
$ordersCount = $result->fields['total'];
//用户数量
$sql = "SELECT COUNT(*) AS total FROM " . TABLE_CUSTOMER;
$result = $db->Execute($sql);
$customerCount = $result->fields['total'];
//商品评论数量
$sql = "SELECT COUNT(*) AS total FROM " . TABLE_PRODUCT_REVIEW . " WHERE status = 0";
$result = $db->Execute($sql);
$productReviewCount = $result->fields['total'];
//商品数量
$sql = "SELECT COUNT(*) AS total FROM " . TABLE_PRODUCT;
$result = $db->Execute($sql);
$productCount = $result->fields['total'];
//特价商品数量
$sql = "SELECT COUNT(*) AS total
		FROM   " . TABLE_PRODUCT . "
		WHERE  specials_price > 0
		AND    DATEDIFF(IF(ISNULL(specials_expire_date),
			   CURRENT_DATE(), specials_expire_date), CURRENT_DATE()) >= 0";
$result = $db->Execute($sql);
$specialsCount = $result->fields['total'];
//最近的10个订单
$sql = "SELECT o.order_id, o.date_added, o.customer_id, o.customer_email_address,
			   o.customer_firstname, o.customer_lastname, o.billing_country,
			   o.payment_method_code, o.currency_code, o.currency_value, o.order_total,
			   os.name AS order_status_name
		FROM   " . TABLE_ORDERS . " o, " . TABLE_ORDER_STATUS . " os
		WHERE  o.order_status_id = os.order_status_id
		ORDER BY order_id DESC";
$result = $db->Execute($sql, 10);
$orderList = array();
while (!$result->EOF) {
	$orderList[] = array(
		'order_id'               => $result->fields['order_id'],
		'date_added'             => $result->fields['date_added'],
		'customer_id'            => $result->fields['customer_id'],
		'customer_email_address' => $result->fields['customer_email_address'],
		'customer_firstname'     => $result->fields['customer_firstname'],
		'customer_lastname'      => $result->fields['customer_lastname'],
		'billing_country'        => $result->fields['billing_country'],
		'payment_method_code'    => $result->fields['payment_method_code'],
		'currency_code'          => $result->fields['currency_code'],
		'currency_value'         => $result->fields['currency_value'],
		'order_total'            => $result->fields['order_total'],
		'order_status_name'      => $result->fields['order_status_name']
	);
	$result->MoveNext();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>商店首页</title>
<meta name="robot" content="noindex, nofollow" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<base href="<?php echo (($request_type=='SSL')?HTTPS_SERVER:HTTP_SERVER) . DIR_WS_ADMIN; ?>" />
<link href="css/styles.css" type="text/css" rel="stylesheet" />
<link href="css/styles-ie.css" type="text/css" rel="stylesheet" />
<script src="js/jquery/jquery.js" type="text/javascript"></script>
<script src="js/jquery/base.js" type="text/javascript"></script>
</head>
<body>
<div class="wrapper">
	<?php require(DIR_FS_ADMIN_INCLUDES . 'noscript.php'); ?>
	<div class="page">
    	<?php require(DIR_FS_ADMIN_INCLUDES . 'header.php'); ?>
    	<div class="main-container">
    		<div class="main">
    			<?php if ($message_stack->size('index') > 0) echo $message_stack->output('index'); ?>
    			<div class="page-title">
    				<h1>商店首页</h1>
    			</div>
    			<div class="col2-set">
    				<div class="col-1">
    					<div class="box">
		            		<div class="box-title">
    							<h2>商店信息</h2>
    						</div>
    						<div class="box-content">
    						<table width="100%">
    						<tbody>
    							<tr>
    								<td>销售总额：</td>
    								<td class="a-right"><?php echo $currencies->display_price($ordersTotal); ?></td>
    							</tr>
    							<tr>
    								<td>总订单数：</td>
    								<td class="a-right"><?php echo $ordersCount; ?></td>
    							</tr>
    							<tr>
    								<td>总客户数：</td>
    								<td class="a-right"><?php echo $customerCount; ?></td>
    							</tr>
    							<tr>
    								<td>未处理产品评论：</td>
    								<td class="a-right"><?php echo $productReviewCount; ?></td>
    							</tr>
    							<tr>
    								<td>产品数量：</td>
    								<td class="a-right"><?php echo $productCount; ?></td>
    							</tr>
    							<tr>
    								<td>特价产品数量：</td>
    								<td class="a-right"><?php echo $specialsCount; ?></td>
    							</tr>
    						</tbody>
    						</table>
    						</div>
    					</div>
    				</div>
    				<div class="col-2">
    					<div class="box">
		            		<div class="box-title">
    							<h2>商店统计</h2>
    						</div>
    						<div class="box-content"></div>
    					</div>
    				</div>
    			</div>
    			<div class="box">
            		<div class="box-title">
						<h2>最新10个订单</h2>
					</div>
					<div class="box-content">
					<table class="data-table">
	    			<col width="140" />
					<col width="60" />
	    			<col />
	    			<col />
	    			<col />
	    			<col width="80" />
	    			<col width="100" />
	    			<col width="80" />
	    			<col width="140" />
	    			<col width="60" />
					<thead>
						<tr>
		    				<th>订单号</th>
							<th>类型</th>
		    				<th>客户名称</th>
		    				<th>订单邮箱</th>
		    				<th>国家</th>
		    				<th>支付方式</th>
		    				<th class="a-right">金额</th>
		    				<th>订单状态</th>
		    				<th>下单时间</th>
		    				<th class="a-center">管理</th>
		    			</tr>
					</thead>
					<tbody>
					<?php if (count($orderList)>0) { ?>
					<?php foreach ($orderList as $val) { ?>
						<tr>
							<td><?php echo put_orderNO($val['order_id']); ?></td>
							<td><?php echo $val['customer_id'] > 0 ? '注册' : '游客'; ?></td>
							<td><?php echo $val['customer_firstname']; ?> <?php echo $val['customer_lastname']; ?></td>
							<td><?php echo $val['customer_email_address']; ?></td>
							<td><?php echo $val['billing_country']; ?></td>
							<td><?php echo $val['payment_method_code']; ?></td>
							<td class="a-right"><?php echo $currencies->display_price($val['order_total'], $val['currency_code'], $val['currency_value']); ?></td>
							<td class="<?php echo strtolower($val['order_status_name']); ?>"><?php echo $val['order_status_name']; ?></td>
							<td><?php echo datetime_short($val['date_added']); ?></td>
							<td class="a-center">[ <a href="<?php echo href_link(FILENAME_ORDER, 'order_id=' . $val['order_id']); ?>">查看</a> ]</td>
						</tr>
					<?php } ?>
					<?php } else { ?>
                            <tr>
								<td colspan="9" class="a-center">没有结果！</td>
							</tr>
						<?php } ?>
					</tbody>
					</table>
					</div>
				</div>
    		</div>
        </div>
        <?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
    </div>
</div>
</body>
</html>
<?php require('includes/application_bottom.php'); ?>