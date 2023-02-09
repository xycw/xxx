<?php require('includes/application_top.php'); ?>
<?php
$payment_method_id = isset($_GET['payment_method_id'])?$_GET['payment_method_id']:0;
$action = isset($_GET['action'])?$_GET['action']:'';
$availabStatus = array('0'=>'禁用', '1'=>'启用');
$availabIsInside = array('0'=>'否', '1'=>'是');
$availabIsShield = array('0'=>'否', '1'=>'是');
switch ($action) {
	case 'save':
		$error = false;
		$paymentMethod = db_prepare_input($_POST['payment_method']);
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add('payment_method', '支付方式设置保存时出现安全错误。');
		}
		if (strlen($paymentMethod['code']) < 1) {
			$error = true;
			$message_stack->add('payment_method', '支付方式代码不能为空。');
		} else {
			$sql = "SELECT COUNT(*) AS total FROM " . TABLE_PAYMENT_METHOD . " WHERE code = :code AND payment_method_id <> :payment_methodID";
			$sql = $db->bindVars($sql, ':code', $paymentMethod['code'], 'string');
			$sql = $db->bindVars($sql, ':payment_methodID', isset($paymentMethod['payment_method_id'])?$paymentMethod['payment_method_id']:0, 'integer');
			$check_payment_method = $db->Execute($sql);
			if ($check_payment_method->fields['total'] > 0) {
				$error = true;
				$message_stack->add('payment_method', '运送方式代码存在相同。');
			}
		}
		if (strlen($paymentMethod['name']) < 1) {
			$error = true;
			$message_stack->add('payment_method', '支付方式名称不能为空。');
		}
		if (strlen($paymentMethod['discount']) > 0
			&& (!is_numeric($paymentMethod['discount']) || $paymentMethod['discount'] < 0 || $paymentMethod['discount'] > 100)) {
			$error = true;
			$message_stack->add('payment_method', '支付折扣格式错误。');
		}
		if (!array_key_exists($paymentMethod['status'], $availabStatus)) $paymentMethod['status'] = 0;
		if (!array_key_exists($paymentMethod['is_inside'], $availabIsInside)) $paymentMethod['is_inside'] = 0;
		if (!array_key_exists($paymentMethod['is_shield'], $availabIsShield)) $paymentMethod['is_shield'] = 0;
		if ($error==true) {
			//nothing
		} else {
			$sql_data_array = array(
				array('fieldName'=>'code', 'value'=>$paymentMethod['code'], 'type'=>'string'),
				array('fieldName'=>'name', 'value'=>$paymentMethod['name'], 'type'=>'string'),
				array('fieldName'=>'description', 'value'=>$paymentMethod['description'], 'type'=>'string'),
				array('fieldName'=>'account', 'value'=>$paymentMethod['account'], 'type'=>'string'),
				array('fieldName'=>'md5key', 'value'=>$paymentMethod['md5key'], 'type'=>'string'),
				array('fieldName'=>'submit_url', 'value'=>$paymentMethod['submit_url'], 'type'=>'string'),
				array('fieldName'=>'return_url', 'value'=>$paymentMethod['return_url'], 'type'=>'string'),
				array('fieldName'=>'discount', 'value'=>$paymentMethod['discount'], 'type'=>'integer'),
				array('fieldName'=>'status', 'value'=>$paymentMethod['status'], 'type'=>'integer'),
				array('fieldName'=>'is_inside', 'value'=>$paymentMethod['is_inside'], 'type'=>'integer'),
				array('fieldName'=>'is_shield', 'value'=>$paymentMethod['is_shield'], 'type'=>'integer'),
				array('fieldName'=>'sort_order', 'value'=>$paymentMethod['sort_order'], 'type'=>'integer'),
				array('fieldName'=>'order_max_amount', 'value'=>$paymentMethod['order_max_amount'], 'type'=>'integer'),
				array('fieldName'=>'mark1', 'value'=>$paymentMethod['mark1'], 'type'=>'string'),
				array('fieldName'=>'mark2', 'value'=>$paymentMethod['mark2'], 'type'=>'string'),
				array('fieldName'=>'mark3', 'value'=>$paymentMethod['mark3'], 'type'=>'string')
			);
			if ($paymentMethod['payment_method_id'] > 0) {
				$db->perform(TABLE_PAYMENT_METHOD, $sql_data_array, 'UPDATE', 'payment_method_id = ' . $paymentMethod['payment_method_id']);
			} else {
				$db->perform(TABLE_PAYMENT_METHOD, $sql_data_array);
				$paymentMethod['payment_method_id'] = $db->Insert_ID();
			}
			$message_stack->add_session('payment_method', '支付方式设置已保存。', 'success');
			redirect(href_link(FILENAME_PAYMENT_METHOD, 'payment_method_id=' . $paymentMethod['payment_method_id']));
		}
	break;
	case 'delete':
		$error = false;
		$selected = $_POST['selected'];
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add_session('payment_method', '删除支付方式时出现安全错误。');
		}
		if ($error==true) {
			//nothing
		} else {
			foreach ($selected as $val) {
				$db->Execute("DELETE FROM " . TABLE_PAYMENT_METHOD . " WHERE payment_method_id = " . (int)$val);
			}
			$message_stack->add_session('payment_method', '支付方式已删除。', 'success');
		}
		redirect(href_link(FILENAME_PAYMENT_METHOD));
	break;
	case 'set_default':
		$db->Execute("UPDATE " . TABLE_PAYMENT_METHOD . " SET is_default = 0");
		$db->Execute("UPDATE " . TABLE_PAYMENT_METHOD . " SET is_default = 1 WHERE payment_method_id = " . (int)$payment_method_id);
		redirect(href_link(FILENAME_PAYMENT_METHOD));
	break;
	case 'set_status':
		$db->Execute("UPDATE " . TABLE_PAYMENT_METHOD . " SET status = IF(status = 1, 0, 1) WHERE payment_method_id = " . (int)$payment_method_id);
		redirect(href_link(FILENAME_PAYMENT_METHOD));
	break;
	default:
		if ($payment_method_id > 0) {
			$sql = "SELECT payment_method_id, code, name, description, account, md5key,
						   submit_url, return_url, discount, status, is_inside, is_shield,
						   is_default, sort_order, order_max_amount, mark1, mark2, mark3
					FROM   " . TABLE_PAYMENT_METHOD . "
					WHERE  payment_method_id = :payment_methodID";
			$sql = $db->bindVars($sql, ':payment_methodID', $payment_method_id, 'integer');
			$result = $db->Execute($sql);
			if ($result->RecordCount() > 0) {
				$paymentMethod = array(
					'payment_method_id' => $result->fields['payment_method_id'],
					'code'              => $result->fields['code'],
					'name'              => $result->fields['name'],
					'description'       => $result->fields['description'],
					'account'           => $result->fields['account'],
					'md5key'            => $result->fields['md5key'],
					'submit_url'        => $result->fields['submit_url'],
					'return_url'        => $result->fields['return_url'],
					'discount'          => $result->fields['discount'],
					'status'            => $result->fields['status'],
					'is_inside'         => $result->fields['is_inside'],
					'is_shield'         => $result->fields['is_shield'],
					'is_default'        => $result->fields['is_default'],
					'sort_order'        => $result->fields['sort_order'],
					'order_max_amount'  => $result->fields['order_max_amount'],
					'mark1'             => $result->fields['mark1'],
					'mark2'             => $result->fields['mark2'],
					'mark3'             => $result->fields['mark3']
				);
			}
		} else {
			$sql = "SELECT payment_method_id, name, code,
						   status, is_default, sort_order
					FROM " . TABLE_PAYMENT_METHOD . " ORDER BY sort_order, payment_method_id";
			$result = $db->Execute($sql);
			$paymentMethodList = array();
			while (!$result->EOF) {
				$paymentMethodList[] = array(
					'payment_method_id' => $result->fields['payment_method_id'],
					'name'       => $result->fields['name'],
					'code'       => $result->fields['code'],
					'status'     => $result->fields['status'],
					'is_default' => $result->fields['is_default'],
					'sort_order' => $result->fields['sort_order']
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
<title>支付方式设置</title>
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
    		<?php if ($message_stack->size('payment_method') > 0) echo $message_stack->output('payment_method'); ?>
    		<?php if ($action == 'new' || $action == 'save' || $payment_method_id > 0) { ?>
    			<form action="<?php echo href_link(FILENAME_PAYMENT_METHOD, 'action=save'); ?>" method="post">
    			<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    				<input type="hidden" value="<?php echo isset($paymentMethod['payment_method_id'])?$paymentMethod['payment_method_id']:''; ?>" name="payment_method[payment_method_id]" />
    			</div>
    			<div class="page-title title-buttons">
	    			<h1>支付方式</h1>
	    			<button type="submit" class="button"><span><span>保存</span></span></button>
    				<button type="button" class="button btn-cancel" onclick="setLocation('<?php echo href_link(FILENAME_PAYMENT_METHOD); ?>');"><span><span>取消</span></span></button>
	    		</div>
	    		<table class="form-list">
				<tbody>
					<tr>
						<td class="label"><label for="payment_method-code">支付方式代码 <span class="required">*</span></label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($paymentMethod['code'])?$paymentMethod['code']:''; ?>" name="payment_method[code]" id="payment_method-code" /></td>
					</tr>
					<tr>
						<td class="label"><label for="payment_method-name">支付方式名称 <span class="required">*</span></label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($paymentMethod['name'])?$paymentMethod['name']:''; ?>" name="payment_method[name]" id="payment_method-name" /></td>
					</tr>
					<tr>
						<td class="label"><label for="payment_method-description">支付方式描述</label></td>
						<td class="value"><textarea cols="15" rows="2" name="payment_method[description]" id="payment_method-description"><?php echo isset($paymentMethod['description'])?$paymentMethod['description']:''; ?></textarea></td>
					</tr>
					<tr>
						<td class="label"><label for="payment_method-account">帐号/商户号 <span class="required">*</span></label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($paymentMethod['account'])?$paymentMethod['account']:''; ?>" name="payment_method[account]" id="payment_method-account" /></td>
					</tr>
					<tr>
						<td class="label"><label for="payment_method-md5key">密钥</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($paymentMethod['md5key'])?$paymentMethod['md5key']:''; ?>" name="payment_method[md5key]" id="payment_method-md5key" /></td>
					</tr>
					<tr>
						<td class="label"><label for="payment_method-submit_url">提交地址</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($paymentMethod['submit_url'])?$paymentMethod['submit_url']:''; ?>" name="payment_method[submit_url]" id="payment_method-submit_url" /></td>
					</tr>
					<tr>
						<td class="label"><label for="payment_method-return_url">返回地址</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($paymentMethod['return_url'])?$paymentMethod['return_url']:''; ?>" name="payment_method[return_url]" id="payment_method-return_url" /></td>
					</tr>
					<tr>
						<td class="label"><label for="payment_method-discount">支付折扣</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($paymentMethod['discount'])?$paymentMethod['discount']:''; ?>" name="payment_method[discount]" id="payment_method-discount" /></td>
					</tr>
					<tr>
						<td class="label"><label for="payment_method-status">状态</label></td>
						<td class="value">
							<select name="payment_method[status]" id="payment_method-status">
								<?php foreach ($availabStatus as $_key=>$_val) { ?>
								<option<?php if (isset($paymentMethod['status'])&&$paymentMethod['status']==$_key) { ?> selected="selected"<?php } ?> value="<?php echo $_key; ?>"><?php echo $_val; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="label"><label for="payment_method-is_inside">内嵌支付</label></td>
						<td class="value">
							<select name="payment_method[is_inside]" id="payment_method-is_inside">
								<?php foreach ($availabIsInside as $_key=>$_val) { ?>
								<option<?php if (isset($paymentMethod['is_inside'])&&$paymentMethod['is_inside']==$_key) { ?> selected="selected"<?php } ?> value="<?php echo $_key; ?>"><?php echo $_val; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="label"><label for="payment_method-is_shield">屏蔽国内</label></td>
						<td class="value">
							<select name="payment_method[is_shield]" id="payment_method-is_shield">
								<?php foreach ($availabIsShield as $_key=>$_val) { ?>
								<option<?php if (isset($paymentMethod['is_shield'])&&$paymentMethod['is_shield']==$_key) { ?> selected="selected"<?php } ?> value="<?php echo $_key; ?>"><?php echo $_val; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="label"><label for="payment_method-sort_order">排序</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($paymentMethod['sort_order'])?$paymentMethod['sort_order']:'0'; ?>" name="payment_method[sort_order]" id="payment_method-sort_order" /></td>
					</tr>
					<tr>
						<td class="label"><label for="payment_method-order_max_amount">最大付款金额</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($paymentMethod['order_max_amount'])?$paymentMethod['order_max_amount']:'0'; ?>" name="payment_method[order_max_amount]" id="payment_method-order_max_amount" /></td>
					</tr>
					<tr>
						<td class="label"><label for="payment_method-mark1">附加字段1</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($paymentMethod['sort_order'])?$paymentMethod['mark1']:''; ?>" name="payment_method[mark1]" id="payment_method-mark1" /></td>
					</tr>
					<tr>
						<td class="label"><label for="payment_method-mark2">附加字段2</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($paymentMethod['sort_order'])?$paymentMethod['mark2']:''; ?>" name="payment_method[mark2]" id="payment_method-mark2" /></td>
					</tr>
					<tr>
						<td class="label"><label for="payment_method-mark3">附加字段3</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($paymentMethod['sort_order'])?$paymentMethod['mark3']:''; ?>" name="payment_method[mark3]" id="payment_method-mark3" /></td>
					</tr>
				</tbody>
    			</table>
    			</form>
    		<?php } else { ?>
				<form action="<?php echo href_link(FILENAME_PAYMENT_METHOD, 'action=delete'); ?>" method="post">
	    		<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    			</div>
	    		<div class="page-title title-buttons">
	    			<h1>支付方式</h1>
    				<button type="button" class="button button-new" onclick="setLocation('<?php echo href_link(FILENAME_PAYMENT_METHOD, 'action=new'); ?>');"><span><span>新增</span></span></button>
    				<button type="submit" class="button" onclick="return confirm('删除或卸载后您将不能恢复，请确定要这么做吗？');"><span><span>删除</span></span></button>
	    		</div>
	    		<table class="data-table">
	    		<colgroup>
	    			<col width="10" />
	    			<col width="100" />
	    			<col />
	    			<col width="60" />
	    			<col width="40" />
	    			<col width="120" />
	    		</colgroup>
	    		<thead>
	    			<tr>
	    				<th><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></th>
	    				<th>代码</th>
	    				<th>支付方式名称</th>
	    				<th class="a-center">状态</th>
	    				<th>排序</th>
	    				<th class="a-center">管理</th>
	    			</tr>
	    		</thead>
	    		<?php if (count($paymentMethodList)>0) { ?>
	    		<tbody>
	    			<?php foreach ($paymentMethodList as $val) { ?>
	    			<tr>
	    				<td><input type="checkbox" value="<?php echo $val['payment_method_id']; ?>" name="selected[]" /></td>
	    				<td><?php echo $val['code']; ?></td>
	    				<td><?php echo $val['name']; ?><?php if ($val['is_default']==1) { ?> <strong>(默认)</strong><?php } ?></td>
	    				<td class="a-center">[ <a title="点击 状态:<?php echo $val['status']==1?$availabStatus[0]:$availabStatus[1]; ?>" href="<?php echo href_link(FILENAME_PAYMENT_METHOD, 'action=set_status&payment_method_id=' . $val['payment_method_id']); ?>"><?php echo $availabStatus[$val['status']]; ?></a> ]</td>
	    				<td><?php echo $val['sort_order']; ?></td>
	    				<td class="a-center">
	    					[ <a href="<?php echo href_link(FILENAME_PAYMENT_METHOD, 'payment_method_id=' . $val['payment_method_id']); ?>">编辑</a> ] [ <a href="<?php echo href_link(FILENAME_PAYMENT_METHOD, 'action=set_default&payment_method_id=' . $val['payment_method_id']); ?>">设为默认</a> ]
	    				</td>
	    			</tr>
	    			<?php } ?>
	    		</tbody>
	    		<?php } else { ?>
	    		<tbody>
					<tr>
						<td class="a-center" colspan="6">没有结果！</td>
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