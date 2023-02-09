<?php require('includes/application_top.php'); ?>
<?php
$currency_id = isset($_GET['currency_id'])?$_GET['currency_id']:0;
$action = isset($_GET['action'])?$_GET['action']:'';
switch ($action) {
	case 'save':
		$error = false;
		$currency = db_prepare_input($_POST['currency']);
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add('currency', '货币设置保存时出现安全错误。');
		}
		if (strlen($currency['name']) < 1) {
			$error = true;
			$message_stack->add('currency', '货币名称不能为空。');
		}
		if (strlen($currency['code']) < 1) {
			$error = true;
			$message_stack->add('currency', '货币代码不能为空。');
		} else {
			$sql = "SELECT COUNT(*) AS total FROM " . TABLE_CURRENCY . " WHERE code = :code AND currency_id <> :currency_id";
			$sql = $db->bindVars($sql, ':code', $currency['code'], 'string');
			$sql = $db->bindVars($sql, ':currency_id', isset($currency['currency_id'])?$currency['currency_id']:0, 'integer');
			$check_currency = $db->Execute($sql);
			if ($check_currency->fields['total'] > 0) {
				$error = true;
				$message_stack->add('currency', '货币代码存在相同。');
			}
		}
		if ($error==true) {
			//nothing
		} else {
			$sql_data_array = array(
				array('fieldName'=>'name', 'value'=>$currency['name'], 'type'=>'string'),
				array('fieldName'=>'code', 'value'=>$currency['code'], 'type'=>'string'),
				array('fieldName'=>'symbol_left', 'value'=>$currency['symbol_left'], 'type'=>'string'),
				array('fieldName'=>'symbol_right', 'value'=>$currency['symbol_right'], 'type'=>'string'),
				array('fieldName'=>'thousands_point', 'value'=>$currency['thousands_point'], 'type'=>'string'),
				array('fieldName'=>'decimal_point', 'value'=>$currency['decimal_point'], 'type'=>'string'),
				array('fieldName'=>'decimal_places', 'value'=>$currency['decimal_places'], 'type'=>'integer'),
				array('fieldName'=>'value', 'value'=>$currency['value'], 'type'=>'decimal'),
				array('fieldName'=>'sort_order', 'value'=>$currency['sort_order'], 'type'=>'integer')
			);
			if ($currency['currency_id'] > 0) {
				$db->perform(TABLE_CURRENCY, $sql_data_array, 'UPDATE', 'currency_id = ' . $currency['currency_id']);
			} else {
				$db->perform(TABLE_CURRENCY, $sql_data_array);
				$currency['currency_id'] = $db->Insert_ID();
			}
			$message_stack->add_session('currency', '货币设置已保存。', 'success');
			redirect(href_link(FILENAME_CURRENCY, 'currency_id=' . $currency['currency_id']));
		}
	break;
	case 'delete':
		$error = false;
		$selected = $_POST['selected'];
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add_session('currency', '删除货币时出现安全错误。');
		}
		if ($error==true) {
			//nothing
		} else {
			foreach ($selected as $val) {
				$db->Execute("DELETE FROM " . TABLE_CURRENCY . " WHERE currency_id = " . (int)$val);
			}
			$message_stack->add_session('currency', '货币已删除。', 'success');
		}
		redirect(href_link(FILENAME_CURRENCY));
	break;
	default:
		if ($currency_id > 0) {
			$sql = "SELECT currency_id, name, code, symbol_left,
						   symbol_right, thousands_point, decimal_point,
						   decimal_places, value, sort_order
					FROM   " . TABLE_CURRENCY . "
					WHERE  currency_id = :currency_id";
			$sql = $db->bindVars($sql, ':currency_id', $currency_id, 'integer');
			$result = $db->Execute($sql);
			if ($result->RecordCount() > 0) {
				$currency = array(
					'currency_id'     => $result->fields['currency_id'],
					'name'            => $result->fields['name'],
					'code'            => $result->fields['code'],
					'symbol_left'     => $result->fields['symbol_left'],
					'symbol_right'    => $result->fields['symbol_right'],
					'thousands_point' => $result->fields['thousands_point'],
					'decimal_point'   => $result->fields['decimal_point'],
					'decimal_places'  => $result->fields['decimal_places'],
					'value'           => $result->fields['value'],
					'sort_order'      => $result->fields['sort_order']
				);
			}
		} else {
			$sql = "SELECT currency_id, name,
						   code, value, sort_order
					FROM   " . TABLE_CURRENCY . "
					ORDER BY sort_order";
			$result = $db->Execute($sql);
			$currencyList = array();
			while (!$result->EOF) {
				$currencyList[] = array(
					'currency_id' => $result->fields['currency_id'],
					'name'        => $result->fields['name'],
					'code'        => $result->fields['code'],
					'value'       => $result->fields['value'],
					'sort_order'  => $result->fields['sort_order']
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
<title>货币设置</title>
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
    		<?php if ($message_stack->size('currency') > 0) echo $message_stack->output('currency'); ?>
    		<?php if ($action == 'new' || $action == 'save' || $currency_id > 0) { ?>
    			<form action="<?php echo href_link(FILENAME_CURRENCY, 'action=save'); ?>" method="post">
    			<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    				<input type="hidden" value="<?php echo isset($currency['currency_id'])?$currency['currency_id']:''; ?>" name="currency[currency_id]" />
    			</div>
    			<div class="page-title title-buttons">
	    			<h1>货币设置</h1>
	    			<button type="submit" class="button"><span><span>保存</span></span></button>
    				<button type="button" class="button btn-cancel" onclick="setLocation('<?php echo href_link(FILENAME_CURRENCY); ?>');"><span><span>取消</span></span></button>
	    		</div>
	    		<table class="form-list">
				<tbody>
					<tr>
						<td class="label"><label for="currency-name">货币名称  <span class="required">*</span></label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($currency['name'])?$currency['name']:''; ?>" name="currency[name]" id="currency-name" /></td>
					</tr>
					<tr>
						<td class="label"><label for="currency-code">代码  <span class="required">*</span></label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($currency['code'])?$currency['code']:''; ?>" name="currency[code]" id="currency-code" /></td>
					</tr>
					<tr>
						<td class="label"><label for="currency-symbol_left">左符号</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($currency['symbol_left'])?$currency['symbol_left']:''; ?>" name="currency[symbol_left]" id="currency-symbol_left" /></td>
					</tr>
					<tr>
						<td class="label"><label for="currency-symbol_right">右符号</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($currency['symbol_right'])?$currency['symbol_right']:''; ?>" name="currency[symbol_right]" id="currency-symbol_right" /></td>
					</tr>
					<tr>
						<td class="label"><label for="currency-thousands_point">千分位符号</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($currency['thousands_point'])?$currency['thousands_point']:''; ?>" name="currency[thousands_point]" id="currency-thousands_point" /></td>
					</tr>
					<tr>
						<td class="label"><label for="currency-decimal_point">小数位符号</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($currency['decimal_point'])?$currency['decimal_point']:''; ?>" name="currency[decimal_point]" id="currency-decimal_point" /></td>
					</tr>
					<tr>
						<td class="label"><label for="currency-decimal_places">小数位</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($currency['decimal_places'])?$currency['decimal_places']:''; ?>" name="currency[decimal_places]" id="currency-decimal_places" /></td>
					</tr>
					<tr>
						<td class="label"><label for="currency-value">汇率</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($currency['value'])?$currency['value']:''; ?>" name="currency[value]" id="currency-value" /></td>
					</tr>
					<tr>
						<td class="label"><label for="currency-sort_order">排序</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($currency['sort_order'])?$currency['sort_order']:'0'; ?>" name="currency[sort_order]" id="currency-sort_order" /></td>
					</tr>
				</tbody>
    			</table>
    			</form>
    		<?php } else { ?>
				<form action="<?php echo href_link(FILENAME_CURRENCY, 'action=delete'); ?>" method="post">
	    		<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    			</div>
	    		<div class="page-title title-buttons">
	    			<h1>货币设置</h1>
    				<button type="button" class="button button-new" onclick="setLocation('<?php echo href_link(FILENAME_CURRENCY, 'action=new'); ?>');"><span><span>新增</span></span></button>
    				<button type="submit" class="button" onclick="return confirm('删除或卸载后您将不能恢复，请确定要这么做吗？');"><span><span>删除</span></span></button>
	    		</div>
	    		<table class="data-table">
	    		<colgroup>
	    			<col width="10" />
	    			<col />
	    			<col width="40" />
	    			<col />
	    			<col width="40" />
	    			<col width="60" />
	    		</colgroup>
	    		<thead>
	    			<tr>
	    				<th><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></th>
	    				<th>货币名称</th>
	    				<th>代码</th>
	    				<th>汇率</th>
	    				<th>排序</th>
	    				<th class="a-center">管理</th>
	    			</tr>
	    		</thead>
	    		<?php if (count($currencyList)>0) { ?>
	    		<tbody>
	    			<?php foreach ($currencyList as $val) { ?>
	    			<tr>
	    				<td><input type="checkbox" value="<?php echo $val['currency_id']; ?>" name="selected[]" /></td>
	    				<td><?php echo $val['name']; ?></td>
	    				<td><?php echo $val['code']; ?></td>
	    				<td><?php echo $val['value']; ?></td>
	    				<td><?php echo $val['sort_order']; ?></td>
	    				<td class="a-center">[ <a href="<?php echo href_link(FILENAME_CURRENCY, 'currency_id=' . $val['currency_id']); ?>">编辑</a> ]</td>
	    			</tr>
	    			<?php $result->MoveNext(); ?>
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