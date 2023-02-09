<?php require('includes/application_top.php'); ?>
<?php
//currency
$new_currency = currency_exists(STORE_CURRENCY);
if ($new_currency == false) $new_currency = currency_exists(STORE_CURRENCY, true);
require(DIR_FS_ADMIN_CLASSES . 'currencies.php');
$currencies = new currencies($new_currency);

$order_id = isset($_GET['order_id'])?$_GET['order_id']:0;
$action = isset($_GET['action'])?$_GET['action']:'';
switch ($action) {
	case 'save':
		$error = false;
		$order = db_prepare_input($_POST['order']);
		$order_status = db_prepare_input($_POST['order_status']);
		$remarks = db_prepare_input($_POST['remarks']);
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add_session('order', '订单状态更新时出现安全错误。');
		}
		if ($error==true) {
			//nothing
		} else {
			//orders
			$sql = "UPDATE " . TABLE_ORDERS . " SET order_status_id = :orderStatusID WHERE order_id = :orderID";
			$sql = $db->bindVars($sql, ':orderStatusID', $order_status, 'integer');
			$sql = $db->bindVars($sql, ':orderID', $order['order_id'], 'integer');
			$db->Execute($sql);
			//order_status_history
			$sql_data_array = array(
				array('fieldName'=>'order_id', 'value'=>$order['order_id'], 'type'=>'integer'),
				array('fieldName'=>'order_status_id', 'value'=>$order_status, 'type'=>'integer'),
				array('fieldName'=>'remarks', 'value'=>$remarks, 'type'=>'string'),
				array('fieldName'=>'date_added', 'value'=>'NOW()', 'type'=>'noquotestring')
			);
			$db->perform(TABLE_ORDER_STATUS_HISTORY, $sql_data_array);
			$message_stack->add_session('order', '订单状态更新成功。', 'success');
		}
		redirect(href_link(FILENAME_ORDER, 'order_id='.$order['order_id']));
	break;
	case 'delete':
		$error = false;
		$selected = $_POST['selected'];
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add_session('order', '删除订单时出现安全错误。');
		}
		if ($error==true) {
			//nothing
		} else {
			foreach ($selected as $val) {
				$db->Execute("DELETE FROM " . TABLE_ORDER_STATUS_HISTORY . " WHERE order_id = " . (int)$val);
				$db->Execute("DELETE FROM " . TABLE_ORDER_PRODUCT . " WHERE order_id = " . (int)$val);
				$db->Execute("DELETE FROM " . TABLE_ORDERS . " WHERE order_id = " . (int)$val);
			}
			$message_stack->add_session('order', '订单已删除。', 'success');
		}
		redirect(href_link(FILENAME_ORDER));
	break;
	default:
		if ($order_id > 0) {
			//order
			$sql = "SELECT order_id, customer_id, customer_firstname,
						   customer_lastname, customer_email_address,
						   billing_firstname, billing_lastname, billing_company,
						   billing_street_address, billing_suburb,
						   billing_city, billing_region_id, billing_region, billing_postcode,
						   billing_country_id, billing_country, billing_telephone, billing_fax,
						   shipping_firstname, shipping_lastname, shipping_company,
						   shipping_street_address, shipping_suburb, shipping_city, shipping_region_id,
						   shipping_region, shipping_postcode, shipping_country_id, shipping_country,
						   shipping_telephone, shipping_fax, payment_method_code, payment_method_name,
						   shipping_method_code, shipping_method_name, coupon_code, currency_code, currency_value,
						   order_subtotal, order_discount, coupon_discount, shipping_method_fee, shipping_method_insurance_fee,
						   order_total, date_added, order_status_id, ip_address
					FROM   " . TABLE_ORDERS . "
					WHERE  order_id = :orderID";
			$sql = $db->bindVars($sql, ':orderID', $order_id, 'integer');
			$result = $db->Execute($sql);
			if ($result->RecordCount() > 0) {
				$order = array(
					'order_id' => $result->fields['order_id'],
					'customer' => array(
						'customer_id'   => $result->fields['customer_id'],
						'firstname'     => $result->fields['customer_firstname'],
						'lastname'      => $result->fields['customer_lastname'],
						'email_address' => $result->fields['customer_email_address']
					),
					'billing' => array(
						'firstname'      => $result->fields['billing_firstname'],
						'lastname'       => $result->fields['billing_lastname'],
						'company'        => $result->fields['billing_company'],
						'street_address' => $result->fields['billing_street_address'],
						'suburb'         => $result->fields['billing_suburb'],
						'city'           => $result->fields['billing_city'],
						'region_id'      => $result->fields['billing_region_id'],
						'region'         => $result->fields['billing_region'],
						'postcode'       => $result->fields['billing_postcode'],
						'country_id'     => $result->fields['billing_country_id'],
						'country'        => $result->fields['billing_country'],
						'telephone'      => $result->fields['billing_telephone'],
						'fax'            => $result->fields['billing_fax']
					),
					'shipping' => array(
						'firstname'      => $result->fields['shipping_firstname'],
						'lastname'       => $result->fields['shipping_lastname'],
						'company'        => $result->fields['shipping_company'],
						'street_address' => $result->fields['shipping_street_address'],
						'suburb'         => $result->fields['shipping_suburb'],
						'city'           => $result->fields['shipping_city'],
						'region_id'      => $result->fields['shipping_region_id'],
						'region'         => $result->fields['shipping_region'],
						'postcode'       => $result->fields['shipping_postcode'],
						'country_id'     => $result->fields['shipping_country_id'],
						'country'        => $result->fields['shipping_country'],
						'telephone'      => $result->fields['shipping_telephone'],
						'fax'            => $result->fields['shipping_fax']
					),
					'payment_method' => array(
						'code' => $result->fields['payment_method_code'],
						'name' => $result->fields['payment_method_name']
					),
					'shipping_method' => array(
						'code' => $result->fields['shipping_method_code'],
						'name' => $result->fields['shipping_method_name'],
						'fee'  => $result->fields['shipping_method_fee'],
						'insurance_fee' => $result->fields['shipping_method_insurance_fee']
					),
					'coupon' => array(
						'code'     => $result->fields['coupon_code'],
						'discount' => $result->fields['coupon_discount']
					),
					'currency' => array(
						'code'  => $result->fields['currency_code'],
						'value' => $result->fields['currency_value']
					),
					'order_subtotal'  => $result->fields['order_subtotal'],
					'order_discount'  => $result->fields['order_discount'],
					'order_total'     => $result->fields['order_total'],
					'date_added'      => $result->fields['date_added'],
					'order_status_id' => $result->fields['order_status_id'],
					'ip_address'      => $result->fields['ip_address']
				);
				//order_product
				$sql = "SELECT product_id, sku, name, image,
							   price, qty, attribute  
						FROM   " . TABLE_ORDER_PRODUCT . "
						WHERE  order_id = :orderID
						ORDER BY order_product_id";
				$sql = $db->bindVars($sql, ':orderID', $order_id, 'integer');
				$productResult = $db->Execute($sql);
				$orderProduct = array();
				while (!$productResult->EOF) {
					$orderProduct[] = array(
						'product_id' => $productResult->fields['product_id'],
						'sku'        => $productResult->fields['sku'],
						'name'       => $productResult->fields['name'],
						'image'      => $productResult->fields['image'],
						'price'      => $productResult->fields['price'],
						'qty'        => $productResult->fields['qty'],
						'attribute'  => json_decode($productResult->fields['attribute'], true)
					);
					$productResult->MoveNext();
				}
				//order_status_history
				$sql = "SELECT osh.date_added, os.name, osh.remarks
						FROM   " . TABLE_ORDER_STATUS_HISTORY . " osh, " . TABLE_ORDER_STATUS . " os
						WHERE  osh.order_status_id = os.order_status_id
						AND    order_id = :orderID
						ORDER BY order_status_history_id";
				$sql = $db->bindVars($sql, ':orderID', $order_id, 'integer');
				$orderStatusResult = $db->Execute($sql);
				$orderStatusHistory = array();
				while (!$orderStatusResult->EOF) {
					$orderStatusHistory[] = array(
						'date_added' => $orderStatusResult->fields['date_added'],
						'name'       => $orderStatusResult->fields['name'],
						'remarks'    => $orderStatusResult->fields['remarks']
					);
					$orderStatusResult->MoveNext();
				}
			}
		} else {
			// filter
			$orderListFilter = '';
			if (isset($_GET['filter_order']) && not_null($_GET['filter_order'])) {
				$sql = " AND o.order_id = ':order_id'";
				$filterOrderId = get_orderNO(trim($_GET['filter_order']));
				$orderListFilter .= $db->bindVars($sql, ':order_id', $filterOrderId, 'integer');
			}
			if (isset($_GET['filter_type']) && not_null($_GET['filter_type'])) {
				$sql = " AND o.customer_id " . ($_GET['filter_type'] > 0 ? '>' : '=') . " 0";
				$orderListFilter .= $sql;
			}
			if (isset($_GET['filter_name']) && not_null($_GET['filter_name'])) {
				$sql = " AND (o.customer_firstname LIKE '%:name%' OR o.customer_lastname LIKE '%:name%')";
				$orderListFilter .= $db->bindVars($sql, ':name', trim($_GET['filter_name']), 'noquotestring');
			}
			if (isset($_GET['filter_email']) && not_null($_GET['filter_email'])) {
				$sql = " AND o.customer_email_address LIKE '%:email%'";
				$orderListFilter .= $db->bindVars($sql, ':email', trim($_GET['filter_email']), 'noquotestring');
			}
			if (isset($_GET['filter_contry']) && not_null($_GET['filter_contry'])) {
				$sql = " AND o.billing_country_id = ':contry'";
				$orderListFilter .= $db->bindVars($sql, ':contry', trim($_GET['filter_contry']), 'integer');
			}
			if (isset($_GET['filter_payment_method']) && not_null($_GET['filter_payment_method'])) {
				$sql = " AND o.payment_method_code = ':payment_method_code'";
				$orderListFilter .= $db->bindVars($sql, ':payment_method_code', trim($_GET['filter_payment_method']), 'noquotestring');
			}
			if (isset($_GET['filter_status']) && not_null($_GET['filter_status'])) {
				$sql = " AND o.order_status_id = ':order_status_id'";
				$orderListFilter .= $db->bindVars($sql, ':order_status_id', trim($_GET['filter_status']), 'integer');
			}

			$orderListQuery = "SELECT o.order_id, o.date_added, o.customer_id, o.customer_email_address,
						   	  		  o.customer_firstname, o.customer_lastname, o.billing_country,
						  			  o.payment_method_code, o.currency_code, o.currency_value, o.order_total,
						   	  		  os.name AS order_status_name
							   FROM   " . TABLE_ORDERS . " o, " . TABLE_ORDER_STATUS . " os
							   WHERE  o.order_status_id = os.order_status_id" . $orderListFilter . "
							   ORDER BY order_id DESC";

			//Pos Query
			$pos_to = strlen($orderListQuery);
			$pos_from = strpos($orderListQuery, ' FROM', 0);
			$posQuery = substr($orderListQuery, $pos_from, ($pos_to - $pos_from));
			//Total
			$sql = "SELECT COUNT(o.order_id) AS total " . $posQuery;
			$result = $db->Execute($sql);
			$pagerConfig = array(
				'total'          => $result->fields['total'],
				'availableLimit' => array(50, 200, 500),
				'currentLimit'   => 50
			);
			require(DIR_FS_ADMIN_CLASSES . 'pager.php');
			$pager = new pager($pagerConfig);
			$result = $db->Execute($orderListQuery, $pager->getLimitSql());
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
		}
	break;
}
//Order Status List
$sql = "SELECT order_status_id, name
		FROM order_status
		ORDER BY order_status_id";
$result = $db->Execute($sql);
$orderStatusList = array();
while (!$result->EOF) {
	$orderStatusList[] = array(
		'order_status_id' => $result->fields['order_status_id'],
		'name' => $result->fields['name']
	);
	$result->MoveNext();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>订单管理</title>
<meta name="robot" content="noindex, nofollow" />
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
    		<?php if ($message_stack->size('order') > 0) echo $message_stack->output('order'); ?>
    		<?php if ($action=='save' || $order_id > 0) { ?>
    			<form action="<?php echo href_link(FILENAME_ORDER, 'action=save'); ?>" method="post">
    			<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    				<input type="hidden" value="<?php echo isset($order['order_id'])?$order['order_id']:''; ?>" name="order[order_id]" />
    			</div>
    			<div class="page-title title-buttons">
	    			<h1>订单管理</h1>
    				<button type="button" class="button btn-cancel" onclick="setLocation('<?php echo href_link(FILENAME_ORDER); ?>');"><span><span>取消</span></span></button>
	    		</div>
	    		<div class="col2-set">
	    			<div class="col-1">
	    				<table class="data-table">
	    					<tr>
								<td class="label">订单号</td>
								<td class="value"><?php echo put_orderNO($order['order_id']); ?></td>
							</tr>
							<tr>
								<td class="label">下单日期</td>
								<td class="value"><?php echo datetime_short($order['date_added']); ?></td>
							</tr>
							<tr>
								<td class="label">订单状态</td>
								<td class="value"><?php echo get_order_status_name($order['order_status_id']); ?></td>
							</tr>
							<tr>
								<td class="label">IP地址</td>
								<td class="value"><?php echo $order['ip_address']; ?></td>
							</tr>
							<tr>
								<td class="label">账单地址</td>
								<td class="value"><?php echo address_format($order['billing']); ?></td>
							</tr>
							<tr>
								<td class="label">支付方式</td>
								<td class="value"><?php echo $order['payment_method']['name']; ?> - <?php echo $order['payment_method']['code']; ?></td>
							</tr>
	    				</table>
	    			</div>
	    			<div class="col-2">
	    				<table class="data-table">
	    					<tr>
								<td class="label">客户类型</td>
								<td class="value"><?php echo $order['customer']['customer_id']>0?'注册':'游客'; ?></td>
							</tr>
	    					<tr>
								<td class="label">客户名</td>
								<td class="value"><?php echo $order['customer']['firstname'] . ' ' . $order['customer']['lastname']; ?></td>
							</tr>
							<tr>
								<td class="label">邮箱</td>
								<td class="value"><?php echo $order['customer']['email_address']; ?></td>
							</tr>
							<tr>
								<td class="label">电话</td>
								<td class="value"><?php echo $order['billing']['telephone']; ?></td>
							</tr>
							<tr>
								<td class="label">运送地址</td>
								<td class="value"><?php echo address_format($order['shipping']); ?></td>
							</tr>
							<tr>
								<td class="label">运送方式</td>
								<td class="value"><?php echo $order['shipping_method']['name']; ?> - <?php echo $order['shipping_method']['code']; ?></td>
							</tr>
	    				</table>
	    			</div>
	    		</div>
	    		<br style="clear:both;" />
    			<table class="data-table">
    			<colgroup>
	    			<col width="60" />
	    			<col />
	    			<col />
	    			<col width="100" />
	    			<col width="60" />
	    			<col width="100" />
	    		</colgroup>
    			<thead>
    				<tr>
    					<th>产品图片</th>
    					<th>产品型号</th>
    					<th>产品名</th>
    					<th class="a-right">价格</th>
    					<th>数量</th>
    					<th class="a-right">合计</th>
    				</tr>
    			</thead>
    			<tbody>
    			<?php foreach ($orderProduct as $_product) { ?>
    				<tr>
	    				<td>
							<img width="<?php echo ADMIN_IMAGE_WIDTH; ?>" height="<?php echo ADMIN_IMAGE_HEIGHT; ?>" alt="<?php echo $_product['name']; ?>" src="<?php echo get_image($_product['image'], ADMIN_IMAGE_WIDTH, ADMIN_IMAGE_HEIGHT); ?>" />
						</td>
						<td><?php echo $_product['sku']; ?></td>
						<td>
							<?php echo $_product['name']; ?>
							<dl class="product-option">
							<?php foreach ($_product['attribute'] as $_option_name => $_option_value_name) {?>
								<dt><?php echo $_option_name; ?></dt>
								<dd><?php echo $_option_value_name; ?></dd>
							<?php } ?>
							</dl>
						</td>
						<td class="a-right"><?php echo $currencies->display_price($_product['price'], $order['currency']['code'], $order['currency']['value']); ?></td>
						<td><?php echo $_product['qty']; ?></td>
						<td class="a-right"><?php echo $currencies->display_price($_product['price']*$_product['qty'], $order['currency']['code'], $order['currency']['value']); ?></td>
					</tr>
    			<?php } ?>
    			</tbody>
    			<tfoot>
    				<tr>
    					<td colspan="5" class="a-right">合计</td>
    					<td class="a-right"><span class="price"><?php echo $currencies->display_price($order['order_subtotal'], $order['currency']['code'], $order['currency']['value']); ?></span></td>
    				</tr>
    				<?php if ($order['order_discount'] > 0) { ?>
					<tr>
						<td colspan="5" class="a-right">折扣</td>
						<td class="a-right"><span class="price">- <?php echo $currencies->display_price($order['order_discount'], $order['currency']['code'], $order['currency']['value']); ?></span></td>
					</tr>
					<?php } ?>
					<?php if ($order['coupon']['code'] != '') { ?>
					<tr>
						<td colspan="5" class="a-right">优惠券(<?php echo $order['coupon']['code']; ?>)</td>
						<td class="a-right"><span class="price">- <?php echo $currencies->display_price($order['coupon']['discount'], $order['currency']['code'], $order['currency']['value']); ?></span></td>
					</tr>
					<?php } ?>
					<tr>
						<td colspan="5" class="a-right">运费</td>
						<td class="a-right"><span class="price"><?php echo $currencies->display_price($order['shipping_method']['fee'], $order['currency']['code'], $order['currency']['value']); ?></span></td>
					</tr>
					<tr>
						<td colspan="5" class="a-right">保险费</td>
						<td class="a-right"><span class="price"><?php echo $currencies->display_price($order['shipping_method']['insurance_fee'], $order['currency']['code'], $order['currency']['value']); ?></span></td>
					</tr>
					<tr>
						<td colspan="5" class="a-right">总计</td>
						<td class="a-right"><span class="price"><?php echo $currencies->display_price($order['order_total'], $order['currency']['code'], $order['currency']['value']); ?></span></td>
					</tr>
    			</tfoot>
    			</table>
    			<br style="clear:both;" />
    			<table class="data-table">
    			<colgroup>
	    			<col width="1" />
	    			<col width="1" />
	    			<col />
	    		</colgroup>
    			<thead>
    				<tr>
    					<th>生成日期</th>
    					<th>状态</th>
    					<th>备注</th>
    				</tr>
    			</thead>
    			<tbody>
    				<?php foreach ($orderStatusHistory as $orderStatus) { ?>
    				<tr>
    					<td><?php echo $orderStatus['date_added']; ?></td>
    					<td><?php echo $orderStatus['name']; ?></td>
    					<td><?php echo $orderStatus['remarks']; ?></td>
    				</tr>
    				<?php } ?>
    			</tbody>
    			<tfoot>
    				<tr>
    					<td>状态</td>
    					<td>
    						<select name="order_status" id="order_status">
    						<?php foreach ($orderStatusList as $orderStatus) { ?>
    							<option value="<?php echo $orderStatus['order_status_id']; ?>"><?php echo $orderStatus['name']; ?></option>
    						<?php } ?>
    						</select>
    					</td>
    					<td><input type="text" class="input-text" name="remarks" /><button type="submit" class="button"><span><span>保存</span></span></button></td>
    				</tr>
    			</tfoot>
    			</table>
    			</form>
    		<?php } else { ?>
				<form action="<?php echo href_link(FILENAME_ORDER, 'action=delete'); ?>" method="post" id="orderFm">
	    		<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    			</div>
	    		<div class="page-title title-buttons">
	    			<h1>订单</h1>
	    			<button type="button" class="button" onclick="if(confirm('删除或卸载后您将不能恢复，请确定要这么做吗？')) $('#orderFm').submit();"><span><span>删除</span></span></button>
	    		</div>
	    		<?php require(DIR_FS_ADMIN_INCLUDES . 'pager.php'); ?>
	    		<table class="data-table">
	    		<colgroup>
	    			<col width="10" />
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
	    		</colgroup>
	    		<thead>
	    			<tr>
	    				<th><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></th>
	    				<th>订单号</th>
						<th>客户类型</th>
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
				<tr class="filter">
					<td></td>
					<td class="value"><input type="text" class="input-text" value="<?php echo isset($_GET['filter_order'])?$_GET['filter_order']:''; ?>" name="filter_order" /></td>
					<td class="value">
						<select name="filter_type">
							<option value="">全部</option>
							<option<?php if (isset($_GET['filter_type']) && $_GET['filter_type']=='0') { ?> selected="selected"<?php } ?> value="0">游客</option>
							<option<?php if (isset($_GET['filter_type']) && $_GET['filter_type']=='1') { ?> selected="selected"<?php } ?> value="1">注册</option>
						</select>
					</td>
					<td class="value"><input type="text" class="input-text" value="<?php echo isset($_GET['filter_name'])?$_GET['filter_name']:''; ?>" name="filter_name" /></td>
					<td class="value"><input type="text" class="input-text" value="<?php echo isset($_GET['filter_email'])?$_GET['filter_email']:''; ?>" name="filter_email" /></td>
					<td class="value"><?php echo cfg_pull_down('filter_contry', get_countries(), isset($_GET['filter_contry'])?$_GET['filter_contry']:0); ?></td>
					<td class="value"><?php echo cfg_pull_down('filter_payment_method', get_payment_methods(), isset($_GET['filter_payment_method'])?$_GET['filter_payment_method']:0); ?></td>
					<td></td>
					<td class="value">
						<select name="filter_status">
							<option value="">全部</option>
							<?php foreach($orderStatusList as $val){ ?>
								<option<?php if (isset($_GET['filter_status']) && $_GET['filter_status']==$val['order_status_id']) { ?> selected="selected"<?php } ?> value="<?php echo $val['order_status_id']; ?>"><?php echo $val['name']; ?></option>
							<?php } ?>
						</select>
					</td>
					<td></td>
					<td class="a-center"><button type="button" class="button" onclick="filter();"><span><span>筛选</span></span></button></td>
				</tr>
	    		<?php if (count($orderList)>0) { ?>
	    		<tbody>
	    			<?php foreach ($orderList as $val) { ?>
	    			<tr>
	    				<td><input type="checkbox" value="<?php echo $val['order_id']; ?>" name="selected[]" /></td>
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
	    		</tbody>
	    		<?php } else { ?>
	    		<tbody>
					<tr>
						<td class="a-center" colspan="11">没有结果！</td>
					</tr>
				</tbody>
	    		<?php } ?>
	    		</table>
	    		<?php require(DIR_FS_ADMIN_INCLUDES . 'pager.php'); ?>
	    		</form>
				<script type="text/javascript"><!--
					$(function(){
						$("[name='filter_contry']").prepend('<option value=""<?php echo isset($_GET['filter_contry'])?'':' selected="selected"'; ?>>全部</option>');
						$("[name='filter_payment_method']").prepend('<option value=""<?php echo isset($_GET['filter_payment_method'])?'':' selected="selected"'; ?>>全部</option>');
						$(document).keydown(function(event){
							if(event.keyCode==13){
								filter();
							}
						});
					});

					function filter() {
						var url = '<?php echo href_link(FILENAME_ORDER); ?>';
						var key = '';
						var val = '';
						$("[name^='filter_']").each(function(){
							key = $(this).attr('name');
							val = $(this).val();
							if (val) {
								if (url.indexOf('?')>0) {
									url += '&' + key + '=' + encodeURIComponent(val);
								} else {
									url += '?' + key + '=' + encodeURIComponent(val);
								}
							}
						});
						setLocation(url);
					}
					//--></script>
    		<?php } ?>
    		</div>
        </div>
        <?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
    </div>
</div>
</body>
</html>
<?php require('includes/application_bottom.php'); ?>