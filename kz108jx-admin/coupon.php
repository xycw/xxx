<?php require('includes/application_top.php'); ?>
<?php
$coupon_id = isset($_GET['coupon_id'])?$_GET['coupon_id']:0;
$action = isset($_GET['action'])?$_GET['action']:'';
$availabType = array('0'=>'客户', '1'=>'购物车');
$availabDiscountType = array('0'=>'固定值', '1'=>'固定值 - 按件', '2'=>'百分比');
$availabStatus = array('0'=>'禁用', '1'=>'启用');
switch ($action) {
	case 'save':
		$error = false;
		$coupon = db_prepare_input($_POST['coupon']);
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add('category', '优惠券设置保存时出现安全错误。');
		}
		if (strlen($coupon['name']) < 1) {
			$error = true;
			$message_stack->add('coupon', '优惠券名称不能为空。');
		}
		if (strlen($coupon['code']) < 1) {
			$error = true;
			$message_stack->add('coupon', '优惠券代码不能为空。');
		} else {
			$sql = "SELECT COUNT(*) AS total FROM " . TABLE_COUPON . " WHERE code = :code AND coupon_id <> :coupon_id";
			$sql = $db->bindVars($sql, ':code', $coupon['code'], 'string');
			$sql = $db->bindVars($sql, ':coupon_id', isset($coupon['coupon_id'])?$coupon['coupon_id']:0, 'integer');
			$check_coupon = $db->Execute($sql);
			if ($check_coupon->fields['total'] > 0) {
				$error = true;
				$message_stack->add('coupon', '优惠券代码存在相同。');
			}
		}
		if (!array_key_exists($coupon['type'], $availabType)) $coupon['type'] = 0;
		if (!array_key_exists($coupon['discount_type'], $availabDiscountType)) $coupon['discount_type'] = 0;
		if (!array_key_exists($coupon['status'], $availabStatus)) $coupon['status'] = 0;
		if ($error==true) {
			//nothing
		} else {
			$sql_data_array = array(
				array('fieldName'=>'name', 'value'=>$coupon['name'], 'type'=>'string'),
				array('fieldName'=>'code', 'value'=>$coupon['code'], 'type'=>'string'),
				array('fieldName'=>'type', 'value'=>$coupon['type'], 'type'=>'integer'),
				array('fieldName'=>'discount_type', 'value'=>$coupon['discount_type'], 'type'=>'integer'),
				array('fieldName'=>'discount', 'value'=>$coupon['discount'], 'type'=>'decimal'),
				array('fieldName'=>'product_qty', 'value'=>$coupon['product_qty'], 'type'=>'integer'),
				array('fieldName'=>'product_amount', 'value'=>$coupon['product_amount'], 'type'=>'decimal'),
				array('fieldName'=>'usage_limit', 'value'=>$coupon['usage_limit'], 'type'=>'integer'),
				array('fieldName'=>'status', 'value'=>$coupon['status'], 'type'=>'integer')
			);
			//start_date
			if (not_null($coupon['start_date']) && validate_date($coupon['start_date'])) {
				$sql_data_array[] = array('fieldName'=>'start_date', 'value'=>$coupon['start_date'], 'type'=>'string');
			} else {
				$sql_data_array[] = array('fieldName'=>'start_date', 'value'=>'NULL', 'type'=>'noquotestring');
			}
			//expire_date
			if (not_null($coupon['expire_date']) && validate_date($coupon['start_date'])) {
				$sql_data_array[] = array('fieldName'=>'expire_date', 'value'=>$coupon['expire_date'], 'type'=>'string');
			} else {
				$sql_data_array[] = array('fieldName'=>'expire_date', 'value'=>'NULL', 'type'=>'noquotestring');
			}
			if ($coupon['coupon_id'] > 0) {
				$sql_data_array[] = array('fieldName'=>'last_modified', 'value'=>'NOW()', 'type'=>'noquotestring');
				$db->perform(TABLE_COUPON, $sql_data_array, 'UPDATE', 'coupon_id = ' . $coupon['coupon_id']);
			} else {
				$sql_data_array[] = array('fieldName'=>'date_added', 'value'=>'NOW()', 'type'=>'noquotestring');
				$db->perform(TABLE_COUPON, $sql_data_array);
				$coupon['coupon_id'] = $db->Insert_ID();
			}
			$message_stack->add_session('coupon', '优惠券设置已保存。', 'success');
			redirect(href_link(FILENAME_COUPON, 'coupon_id=' . $coupon['coupon_id']));
		}
	break;
	case 'delete':
		$error = false;
		$selected = $_POST['selected'];
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add_session('coupon', '删除优惠券时出现安全错误。');
		}
		if ($error==true) {
			//nothing
		} else {
			foreach ($selected as $val) {
				$db->Execute("DELETE FROM " . TABLE_COUPON . " WHERE coupon_id = " . (int)$val);
			}
			$message_stack->add_session('coupon', '优惠券已删除。', 'success');
		}
		redirect(href_link(FILENAME_COUPON));
	break;
	case 'set_status':
		$db->Execute("UPDATE " . TABLE_COUPON . " SET status = IF(status = 1, 0, 1) WHERE coupon_id = " . (int)$coupon_id);
		redirect(href_link(FILENAME_COUPON));
	break;
	default:
		if ($coupon_id > 0) {
			$sql = "SELECT coupon_id, name, code, type, discount_type, discount,
						   product_qty, product_amount, start_date, expire_date,
						   usage_limit, status
					FROM   " . TABLE_COUPON . "
					WHERE  coupon_id = :coupon_id";
			$sql = $db->bindVars($sql, ':coupon_id', $coupon_id, 'integer');
			$result = $db->Execute($sql);
			if ($result->RecordCount() > 0) {
				$coupon = array(
					'coupon_id'      => $result->fields['coupon_id'],
					'name'           => $result->fields['name'],
					'code'           => $result->fields['code'],
					'type'           => $result->fields['type'],
					'discount_type'  => $result->fields['discount_type'],
					'discount'       => $result->fields['discount'],
					'product_qty'    => $result->fields['product_qty'],
					'product_amount' => $result->fields['product_amount'],
					'start_date'     => $result->fields['start_date'],
					'expire_date'    => $result->fields['expire_date'],
					'usage_limit'    => $result->fields['usage_limit'],
					'status'         => $result->fields['status']
				);
			}
		} else {
			$sql = "SELECT coupon_id, name, code, type, discount_type,
						   discount, start_date, expire_date, status
					FROM   " . TABLE_COUPON . "
					ORDER BY coupon_id";
			$result = $db->Execute($sql);
			$couponList = array();
			while (!$result->EOF) {
				$couponList[] = array(
					'coupon_id'     => $result->fields['coupon_id'],
					'name'          => $result->fields['name'],
					'code'          => $result->fields['code'],
					'type'          => $result->fields['type'],
					'discount_type' => $result->fields['discount_type'],
					'discount'      => $result->fields['discount'],
					'start_date'    => $result->fields['start_date'],
					'expire_date'   => $result->fields['expire_date'],
					'status'        => $result->fields['status']
				);
				$result->MoveNext();
			}
		}
	break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>优惠券设置</title>
<meta name="robot" content="noindex, nofollow" />
<base href="<?php echo (($request_type=='SSL')?HTTPS_SERVER:HTTP_SERVER) . DIR_WS_ADMIN; ?>" />
<link href="css/styles.css" type="text/css" rel="stylesheet" />
<link href="css/styles-ie.css" type="text/css" rel="stylesheet" />
<link href="css/ui.custom.css" type="text/css" rel="stylesheet" />
<script src="js/jquery/jquery.js" type="text/javascript"></script>
<script src="js/jquery/ui.custom.min.js" type="text/javascript"></script>
<script src="js/jquery/base.js" type="text/javascript"></script>
</head>
<body>
<div class="wrapper">
	<?php require(DIR_FS_ADMIN_INCLUDES . 'noscript.php'); ?>
	<div class="page">
    	<?php require(DIR_FS_ADMIN_INCLUDES . 'header.php'); ?>
    	<div class="main-container">
    		<div class="main">
    		<?php if ($message_stack->size('coupon') > 0) echo $message_stack->output('coupon'); ?>
    		<?php if ($action == 'new' || $action == 'save' || $coupon_id > 0) { ?>
    			<form action="<?php echo href_link(FILENAME_COUPON, 'action=save'); ?>" method="post">
    			<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    				<input type="hidden" value="<?php echo isset($coupon['coupon_id'])?$coupon['coupon_id']:''; ?>" name="coupon[coupon_id]" />
    			</div>
    			<div class="page-title title-buttons">
	    			<h1>优惠券设置</h1>
	    			<button type="submit" class="button"><span><span>保存</span></span></button>
    				<button type="button" class="button btn-cancel" onclick="setLocation('<?php echo href_link(FILENAME_COUPON); ?>');"><span><span>取消</span></span></button>
	    		</div>
	    		<table class="form-list">
				<tbody>
					<tr>
						<td class="label"><label for="coupon-name">优惠券名称  <span class="required">*</span></label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($coupon['name'])?$coupon['name']:''; ?>" name="coupon[name]" id="coupon-name" /></td>
					</tr>
					<tr>
						<td class="label"><label for="coupon-code">优惠券代码  <span class="required">*</span></label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($coupon['code'])?$coupon['code']:''; ?>" name="coupon[code]" id="coupon-code" /></td>
					</tr>
					<tr>
						<td class="label"><label for="coupon-type">使用类型</label></td>
						<td class="value">
							<select name="coupon[type]" id="coupon-type">
								<?php foreach ($availabType as $_key=>$_val) { ?>
								<option<?php if (isset($coupon['type'])&&$coupon['type']==$_key) { ?> selected="selected"<?php } ?> value="<?php echo $_key; ?>"><?php echo $_val; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="label"><label for="coupon-discount_type">折扣类型</label></td>
						<td class="value">
							<select name="coupon[discount_type]" id="coupon-discount_type">
								<?php foreach ($availabDiscountType as $_key=>$_val) { ?>
								<option<?php if (isset($coupon['discount_type'])&&$coupon['discount_type']==$_key) { ?> selected="selected"<?php } ?> value="<?php echo $_key; ?>"><?php echo $_val; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="label"><label for="coupon-discount">折扣</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($coupon['discount'])?$coupon['discount']:''; ?>" name="coupon[discount]" id="coupon-discount" /></td>
					</tr>
					<tr>
						<td class="label"><label for="coupon-product_qty">产品个数</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($coupon['product_qty'])?$coupon['product_qty']:''; ?>" name="coupon[product_qty]" id="coupon-product_qty" /></td>
					</tr>
					<tr>
						<td class="label"><label for="coupon-product_amount">产品总价</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($coupon['product_amount'])?$coupon['product_amount']:''; ?>" name="coupon[product_amount]" id="coupon-product_amount" /></td>
					</tr>
					<tr>
						<td class="label"><label for="coupon-start_date">开始日期</label></td>
						<td class="value"><input type="text" class="input-text date" value="<?php echo isset($coupon['start_date'])?$coupon['start_date']:''; ?>" name="coupon[start_date]" id="coupon-start_date" /></td>
					</tr>
					<tr>
						<td class="label"><label for="coupon-expire_date">结束日期</label></td>
						<td class="value"><input type="text" class="input-text date" value="<?php echo isset($coupon['expire_date'])?$coupon['expire_date']:''; ?>" name="coupon[expire_date]" id="coupon-expire_date" /></td>
					</tr>
					<tr>
						<td class="label"><label for="coupon-usage_limit">可使用次数</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($coupon['usage_limit'])?$coupon['usage_limit']:''; ?>" name="coupon[usage_limit]" id="coupon-usage_limit" /></td>
					</tr>
					<tr>
						<td class="label"><label for="coupon-status">状态</label></td>
						<td class="value">
							<select name="coupon[status]" id="coupon-status">
								<?php foreach ($availabStatus as $_key=>$_val) { ?>
								<option<?php if (isset($coupon['status'])&&$coupon['status']==$_key) { ?> selected="selected"<?php } ?> value="<?php echo $_key; ?>"><?php echo $_val; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
				</tbody>
    			</table>
    			</form>
<script type="text/javascript"><!--
$('.date').datepicker({dateFormat: 'yy-mm-dd'});
//--></script>
    		<?php } else { ?>
				<form action="<?php echo href_link(FILENAME_COUPON, 'action=delete'); ?>" method="post">
	    		<div class="no-display">	
	    			<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
	    		</div>
	    		<div class="page-title title-buttons">
	    			<h1>优惠券</h1>
    				<button type="button" class="button button-new" onclick="setLocation('<?php echo href_link(FILENAME_COUPON, 'action=new'); ?>');"><span><span>新增</span></span></button>
    				<button type="submit" class="button" onclick="return confirm('删除或卸载后您将不能恢复，请确定要这么做吗？');"><span><span>删除</span></span></button>
	    		</div>
	    		<table class="data-table">
	    		<colgroup>
	    			<col width="10" />
	    			<col />
	    			<col />
	    			<col width="80" />
	    			<col width="100" />
	    			<col width="80" />
	    			<col width="80" />
	    			<col width="80" />
	    			<col width="60" />
	    			<col width="60" />
	    		</colgroup>
	    		<thead>
	    			<tr>
	    				<th><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></th>
	    				<th>优惠券名称</th>
	    				<th>优惠券代码</th>
	    				<th>使用类型</th>
	    				<th>折扣类型</th>
	    				<th>折扣</th>
	    				<th>开始日期</th>
	    				<th>结束日期</th>
	    				<th class="a-center">状态</th>
	    				<th class="a-center">管理</th>
	    			</tr>
	    		</thead>
	    		<?php if (count($couponList)>0) { ?>
	    		<tbody>
	    			<?php foreach ($couponList as $val) { ?>
	    			<tr>
	    				<td><input type="checkbox" value="<?php echo $val['coupon_id']; ?>" name="selected[]" /></td>
	    				<td><?php echo $val['name']; ?></td>
	    				<td><?php echo $val['code']; ?></td>
	    				<td><?php echo $availabType[$val['type']]; ?></td>
	    				<td><?php echo $availabDiscountType[$val['discount_type']]; ?></td>
	    				<td><?php echo $val['discount']; ?></td>
	    				<td><?php echo $val['start_date']; ?></td>
	    				<td><?php echo $val['expire_date']; ?></td>
	    				<td class="a-center">[ <a title="点击 状态:<?php echo $val['status']==1?$availabStatus[0]:$availabStatus[1]; ?>" href="<?php echo href_link(FILENAME_COUPON, 'action=set_status&coupon_id=' . $val['coupon_id']); ?>"><?php echo $availabStatus[$val['status']]; ?></a> ]</td>
	    				<td class="a-center">[ <a href="<?php echo href_link(FILENAME_COUPON, 'coupon_id=' . $val['coupon_id']); ?>">编辑</a> ]</td>
	    			</tr>
	    			<?php } ?>
	    		</tbody>
	    		<?php } else { ?>
	    		<tbody>
					<tr>
						<td class="a-center" colspan="10">没有结果！</td>
					</tr>
				</tbody>
	    		<?php } ?>
	    		</table>
	    		</form>
    		<?php } ?>
    		</div>
        </div>
        <?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
    </div>
</div>
</body>
</html>
<?php require('includes/application_bottom.php'); ?>