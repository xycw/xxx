<?php require('includes/application_top.php'); ?>
<?php
$shipping_method_id = isset($_GET['shipping_method_id'])?$_GET['shipping_method_id']:0;
$action = isset($_GET['action'])?$_GET['action']:'';
$availabStatus = array('0'=>'禁用', '1'=>'启用');
$availabIsItem = array('0'=>'否', '1'=>'是');
switch ($action) {
	case 'save':
		$error = false;
		$shippingMethod = db_prepare_input($_POST['shipping_method']);
		$subFee  = db_prepare_input($_POST['sub_fee']);
		$subList = array();
		if (!empty($subFee)) {
			foreach ($subFee as $val) {
				if (is_numeric($val['num']) && $val['num'] > 0 && is_numeric($val['price']) && $val['price'] >= 0) {
					$subList[] = array(
						'num'   => (int)$val['num'],
						'price' => number_format($val['price'], 4, '.', '')
					);
				}
			}
		}
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add('shipping_method', '运送方式设置保存时出现安全错误。');
		}
		if (strlen($shippingMethod['code']) < 1) {
			$error = true;
			$message_stack->add('shipping_method', '运送方式代码不能为空。');
		} else {
			$sql = "SELECT COUNT(*) AS total FROM " . TABLE_SHIPPING_METHOD . " WHERE code = :code AND shipping_method_id <> :shipping_methodID";
			$sql = $db->bindVars($sql, ':code', $shippingMethod['code'], 'string');
			$sql = $db->bindVars($sql, ':shipping_methodID', isset($shippingMethod['shipping_method_id'])?$shippingMethod['shipping_method_id']:0, 'integer');
			$check_shipping_method = $db->Execute($sql);
			if ($check_shipping_method->fields['total'] > 0) {
				$error = true;
				$message_stack->add('shipping_method', '运送方式代码存在相同。');
			}
		}
		if (strlen($shippingMethod['name']) < 1) {
			$error = true;
			$message_stack->add('shipping_method', '运送方式名称不能为空。');
		}
		if (!array_key_exists($shippingMethod['status'], $availabStatus)) $shippingMethod['status'] = 0;
		if (!array_key_exists($shippingMethod['is_item'], $availabIsItem)) $shippingMethod['is_item'] = 0;
		if ($error==true) {
			//nothing
		} else {
			$sql_data_array = array(
				array('fieldName'=>'code', 'value'=>$shippingMethod['code'], 'type'=>'string'),
				array('fieldName'=>'name', 'value'=>$shippingMethod['name'], 'type'=>'string'),
				array('fieldName'=>'description', 'value'=>$shippingMethod['description'], 'type'=>'string'),
				array('fieldName'=>'fee', 'value'=>$shippingMethod['fee'], 'type'=>'decimal'),
				array('fieldName'=>'max_fee', 'value'=>$shippingMethod['max_fee'], 'type'=>'decimal'),
				array('fieldName'=>'sub_fee', 'value'=>json_encode($subList), 'type'=>'string'),
				array('fieldName'=>'insurance_fee', 'value'=>$shippingMethod['insurance_fee'], 'type'=>'decimal'),
				array('fieldName'=>'free_shipping_qty', 'value'=>$shippingMethod['free_shipping_qty'], 'type'=>'integer'),
				array('fieldName'=>'free_shipping_amount', 'value'=>$shippingMethod['free_shipping_amount'], 'type'=>'decimal'),
				array('fieldName'=>'free_shipping_country', 'value'=>$shippingMethod['free_shipping_country'], 'type'=>'string'),
				array('fieldName'=>'status', 'value'=>$shippingMethod['status'], 'type'=>'integer'),
				array('fieldName'=>'is_item', 'value'=>$shippingMethod['is_item'], 'type'=>'integer'),
				array('fieldName'=>'sort_order', 'value'=>$shippingMethod['sort_order'], 'type'=>'integer')
			);
			if ($shippingMethod['shipping_method_id'] > 0) {
				$db->perform(TABLE_SHIPPING_METHOD, $sql_data_array, 'UPDATE', 'shipping_method_id = ' . $shippingMethod['shipping_method_id']);
			} else {
				$db->perform(TABLE_SHIPPING_METHOD, $sql_data_array);
				$shippingMethod['shipping_method_id'] = $db->Insert_ID();
			}
			$message_stack->add_session('shipping_method', '运送方式设置已保存。', 'success');
			redirect(href_link(FILENAME_SHIPPING_METHOD, 'shipping_method_id=' . $shippingMethod['shipping_method_id']));
		}
	break;
	case 'delete':
		$error = false;
		$selected = $_POST['selected'];
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add_session('shipping_method', '删除运送方式时出现安全错误。');
		}
		if ($error==true) {
			//nothing
		} else {
			foreach ($selected as $val) {
				$db->Execute("DELETE FROM " . TABLE_SHIPPING_METHOD . " WHERE shipping_method_id = " . (int)$val);
			}
			$message_stack->add_session('shipping_method', '运送方式已删除。', 'success');
		}
		redirect(href_link(FILENAME_SHIPPING_METHOD));
	break;
	case 'set_default':
		$db->Execute("UPDATE " . TABLE_SHIPPING_METHOD . " SET is_default = 0");
		$db->Execute("UPDATE " . TABLE_SHIPPING_METHOD . " SET is_default = 1 WHERE shipping_method_id = " . (int)$shipping_method_id);
		redirect(href_link(FILENAME_SHIPPING_METHOD));
	break;
	case 'set_status':
		$db->Execute("UPDATE " . TABLE_SHIPPING_METHOD . " SET status = IF(status = 1, 0, 1) WHERE shipping_method_id = " . (int)$shipping_method_id);
		redirect(href_link(FILENAME_SHIPPING_METHOD));
	break;
	default:
		if ($shipping_method_id > 0) {
			$sql = "SELECT shipping_method_id, code, name, description, fee, max_fee, sub_fee, insurance_fee,
						   free_shipping_qty, free_shipping_amount, free_shipping_country, status,
						   is_item, is_default, sort_order
					FROM   " . TABLE_SHIPPING_METHOD . "
					WHERE  shipping_method_id = :shipping_methodID";
			$sql = $db->bindVars($sql, ':shipping_methodID', $shipping_method_id, 'integer');
			$result = $db->Execute($sql);
			if ($result->RecordCount() > 0) {
				$shippingMethod = array(
					'shipping_method_id' => $result->fields['shipping_method_id'],
					'code' => $result->fields['code'],
					'name' => $result->fields['name'],
					'description' => $result->fields['description'],
					'fee' => $result->fields['fee'],
					'max_fee' => $result->fields['max_fee'],
					'sub_fee' => $result->fields['sub_fee'],
					'insurance_fee' => $result->fields['insurance_fee'],
					'free_shipping_qty' => $result->fields['free_shipping_qty'],
					'free_shipping_amount' => $result->fields['free_shipping_amount'],
					'free_shipping_country' => $result->fields['free_shipping_country'],
					'status' => $result->fields['status'],
					'is_item' => $result->fields['is_item'],
					'is_default' => $result->fields['is_default'],
					'sort_order' => $result->fields['sort_order']
				);
			}
		} else {
			$sql = "SELECT shipping_method_id, name, code,
						   status, is_default, sort_order
					FROM   " . TABLE_SHIPPING_METHOD . "
					ORDER BY sort_order";
			$result = $db->Execute($sql);
			$shippingMethodList = array();
			while (!$result->EOF) {
				$shippingMethodList[] = array(
					'shipping_method_id' => $result->fields['shipping_method_id'],
					'name' => $result->fields['name'],
					'code' => $result->fields['code'],
					'status' => $result->fields['status'],
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
<title>运送方式设置</title>
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
    		<?php if ($message_stack->size('shipping_method') > 0) echo $message_stack->output('shipping_method'); ?>
    		<?php if ($action == 'new' || $action == 'save' || $shipping_method_id > 0) { ?>
    			<form action="<?php echo href_link(FILENAME_SHIPPING_METHOD, 'action=save'); ?>" method="post">
    			<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    				<input type="hidden" value="<?php echo isset($shippingMethod['shipping_method_id'])?$shippingMethod['shipping_method_id']:''; ?>" name="shipping_method[shipping_method_id]" />
    			</div>
    			<div class="page-title title-buttons">
	    			<h1>运送方式</h1>
	    			<button type="submit" class="button"><span><span>保存</span></span></button>
    				<button type="button" class="button btn-cancel" onclick="setLocation('<?php echo href_link(FILENAME_SHIPPING_METHOD); ?>');"><span><span>取消</span></span></button>
	    		</div>
	    		<table class="form-list">
				<tbody>
					<tr>
						<td class="label"><label for="shipping_method-code">运送方式代码  <span class="required">*</span></label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($shippingMethod['code'])?$shippingMethod['code']:''; ?>" name="shipping_method[code]" id="shipping_method-code" /></td>
					</tr>
					<tr>
						<td class="label"><label for="shipping_method-name">运送方式名称  <span class="required">*</span></label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($shippingMethod['name'])?$shippingMethod['name']:''; ?>" name="shipping_method[name]" id="shipping_method-name" /></td>
					</tr>
					<tr>
						<td class="label"><label for="shipping_method-description">运送方式描述</label></td>
						<td class="value"><textarea cols="15" rows="2" name="shipping_method[description]" id="shipping_method-description"><?php echo isset($shippingMethod['description'])?$shippingMethod['description']:''; ?></textarea></td>
					</tr>
					<tr>
						<td class="label"><label for="shipping_method-fee">费用</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($shippingMethod['fee'])?$shippingMethod['fee']:''; ?>" name="shipping_method[fee]" id="shipping_method-fee" /></td>
					</tr>
					<tr>
						<td class="label"><label for="shipping_method-max_fee">最大费用</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($shippingMethod['max_fee'])?$shippingMethod['max_fee']:''; ?>" name="shipping_method[max_fee]" id="shipping_method-max_fee" /></td>
					</tr>
					<tr>
						<td class="label"><label for="shipping_method-insurance_fee">保险费</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($shippingMethod['insurance_fee'])?$shippingMethod['insurance_fee']:''; ?>" name="shipping_method[insurance_fee]" id="shipping_method-insurance_fee" /></td>
					</tr>
					<tr>
						<td class="label"><label for="shipping_method-free_shipping_qty">免运费(最小数量)</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($shippingMethod['free_shipping_qty'])?$shippingMethod['free_shipping_qty']:''; ?>" name="shipping_method[free_shipping_qty]" id="shipping_method-free_shipping_qty" /></td>
					</tr>
					<tr>
						<td class="label"><label for="shipping_method-free_shipping_amount">免运费(最小金额)</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($shippingMethod['free_shipping_amount'])?$shippingMethod['free_shipping_amount']:''; ?>" name="shipping_method[free_shipping_amount]" id="shipping_method-free_shipping_amount" /></td>
					</tr>
					<tr>
						<td class="label"><label for="shipping_method-free_shipping_country">免运费(国家)</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($shippingMethod['free_shipping_country'])?$shippingMethod['free_shipping_country']:''; ?>" name="shipping_method[free_shipping_country]" id="shipping_method-free_shipping_country" /></td>
					</tr>
					<tr>
						<td class="label"><label for="shipping_method-status">状态</label></td>
						<td class="value">
							<select name="shipping_method[status]" id="shipping_method-status">
								<?php foreach ($availabStatus as $_key=>$_val) { ?>
								<option<?php if (isset($shippingMethod['status'])&&$shippingMethod['status']==$_key) { ?> selected="selected"<?php } ?> value="<?php echo $_key; ?>"><?php echo $_val; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="label"><label for="shipping_method-is_item">按件计算</label></td>
						<td class="value">
							<select name="shipping_method[is_item]" id="shipping_method-is_item">
								<?php foreach ($availabIsItem as $_key=>$_val) { ?>
								<option<?php if (isset($shippingMethod['is_item'])&&$shippingMethod['is_item']==$_key) { ?> selected="selected"<?php } ?> value="<?php echo $_key; ?>"><?php echo $_val; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="label"><label for="shipping_method-sort_order">排序</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($shippingMethod['sort_order'])?$shippingMethod['sort_order']:'0'; ?>" name="shipping_method[sort_order]" id="shipping_method-sort_order" /></td>
					</tr>
				</tbody>
    			</table>
    			<table id="shipping-sub-fee" class="data-table">
	    		<thead>
	    			<tr>
	    				<th>数量</th>
	    				<th>价格</th>
	    				<th class="a-center">管理</th>
	    			</tr>
	    		</thead>
	    		<?php $i=0; ?>
	    		<?php $subFeeList = json_decode($shippingMethod['sub_fee'], true); ?>
	    		<?php if (!empty($subFeeList)) { ?>
	    		<?php foreach ($subFeeList as $val) { ?>
	    		<tbody id="shipping-sub-fee-row<?php echo $i; ?>">
	    			<tr>
	    				<td class="value">
							<input type="text" class="input-text" value="<?php echo $val['num']; ?>" name="sub_fee[<?php echo $i ?>][num]" />
	    				</td>
	    				<td class="value">
	    					<input type="text" class="input-text" value="<?php echo $val['price']; ?>" name="sub_fee[<?php echo $i ?>][price]" />
	    				</td>
	    				<td><button type="button" class="button" onclick="$('#shipping-sub-fee-row<?php echo $i; ?>').remove();"><span><span>移除</span></span></button></td>
	    			</tr>
	    		</tbody>
	    			<?php $i++; ?>
	    		<?php } ?>
	    		<?php } ?>
	    		<tfoot>
	    			<tr>
	    				<td class="value"></td>
	    				<td class="value"></td>
	    				<td><button type="button" class="button" onclick="addProductOptionValue();"><span><span>添加数量</span></span></button></td>
	    			</tr>
	    		</tfoot>
	    		</table>
    			</form>
    			<script type="text/javascript"><!--
				var sub_fee_row = <?php echo $i; ?>;
				function addProductOptionValue() {
					html = '<tbody id="shipping-sub-fee-row' + sub_fee_row + '">';
					html += '<tr>';
					html += '<td class="value"><input type="text" class="input-text" value="" name="sub_fee[' + sub_fee_row + '][num]" /></td>';
					html += '<td class="value"><input type="text" class="input-text" value="" name="sub_fee[' + sub_fee_row + '][price]" /></td>';
					html += '<td><button type="button" class="button" onclick="$(\'#shipping-sub-fee-row' + sub_fee_row + '\').remove();"><span><span>移除</span></span></button></td>';
					html += '</tr>';
					html += '</tbody>';
					$('#shipping-sub-fee tfoot').before(html);
					sub_fee_row++;
				}
				//--></script>
    		<?php } else { ?>
				<form action="<?php echo href_link(FILENAME_SHIPPING_METHOD, 'action=delete'); ?>" method="post">
	    		<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    			</div>
	    		<div class="page-title title-buttons">
	    			<h1>运送方式</h1>
    				<button type="button" class="button button-new" onclick="setLocation('<?php echo href_link(FILENAME_SHIPPING_METHOD, 'action=new'); ?>');"><span><span>新增</span></span></button>
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
	    				<th>运送方式名称</th>
	    				<th class="a-center">状态</th>
	    				<th>排序</th>
	    				<th class="a-center">管理</th>
	    			</tr>
	    		</thead>
	    		<?php if (count($shippingMethodList)>0) { ?>
	    		<tbody>
	    			<?php foreach ($shippingMethodList as $val) { ?>
	    			<tr>
	    				<td><input type="checkbox" value="<?php echo $val['shipping_method_id']; ?>" name="selected[]" /></td>
	    				<td><?php echo $val['code']; ?></td>
	    				<td><?php echo $val['name']; ?><?php if ($val['is_default']==1) { ?> <strong>(默认)</strong><?php } ?></td>
	    				<td class="a-center">[ <a title="点击 状态:<?php echo $val['status']==1?$availabStatus[0]:$availabStatus[1]; ?>" href="<?php echo href_link(FILENAME_SHIPPING_METHOD, 'action=set_status&shipping_method_id=' . $val['shipping_method_id']); ?>"><?php echo $availabStatus[$val['status']]; ?></a> ]</td>
	    				<td><?php echo $val['sort_order']; ?></td>
	    				<td class="a-center">
	    					[ <a href="<?php echo href_link(FILENAME_SHIPPING_METHOD, 'shipping_method_id=' . $val['shipping_method_id']); ?>">编辑</a> ] [ <a href="<?php echo href_link(FILENAME_SHIPPING_METHOD, 'action=set_default&shipping_method_id=' . $val['shipping_method_id']); ?>">设为默认</a> ]
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