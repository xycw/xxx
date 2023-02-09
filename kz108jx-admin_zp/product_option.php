<?php require('includes/application_top.php'); ?>
<?php
$product_option_id = isset($_GET['product_option_id'])?$_GET['product_option_id']:0;
$action = isset($_GET['action'])?$_GET['action']:'';
$availabType = array('select'=>'下拉', 'text'=>'文本', 'radio'=>'单选', 'checkbox'=>'复选', 'list'=>'列表', 'wholesale'=>'批发');
switch ($action) {
	case 'save':
		$error = false;
		$productOption = db_prepare_input($_POST['product_option']);
		$productOptionValueList = isset($_POST['product_option_value'])?db_prepare_input($_POST['product_option_value']):array();
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add('product_option', '选项设置保存时出现安全错误。');
		}
		if (!array_key_exists($productOption['type'], $availabType)) $productOption['type'] = 'select';
		if (strlen($productOption['name']) < 1) {
			$error = true;
			$message_stack->add('product_option', '选项名称不能为空。');
		} else {
			$sql = "SELECT COUNT(*) AS total FROM " . TABLE_PRODUCT_OPTION . " WHERE name = :name AND product_option_id <> :product_option_id";
			$sql = $db->bindVars($sql, ':name', $productOption['name'], 'string');
			$sql = $db->bindVars($sql, ':product_option_id', isset($productOption['product_option_id'])?$productOption['product_option_id']:0, 'integer');
			$check_product_option = $db->Execute($sql);
			if ($check_product_option->fields['total'] > 0) {
				$error = true;
				$message_stack->add('product_option', '选项名称存在相同。');
			}
		}
		if ($error==true) {
			//nothing
		} else {
			$sql_data_array = array(
				array('fieldName'=>'type', 'value'=>$productOption['type'], 'type'=>'string'),
				array('fieldName'=>'name', 'value'=>$productOption['name'], 'type'=>'string'),
				array('fieldName'=>'sort_order', 'value'=>$productOption['sort_order'], 'type'=>'integer')
			);
			if ($productOption['product_option_id'] > 0) {
				$db->perform(TABLE_PRODUCT_OPTION, $sql_data_array, 'UPDATE', 'product_option_id = ' . (int)$productOption['product_option_id']);
			} else {
				$db->perform(TABLE_PRODUCT_OPTION, $sql_data_array);
				$productOption['product_option_id'] = $db->Insert_ID();
			}
			if ($productOption['product_option_id'] > 0) {
				//product_option_value
				if ($productOption['type'] == 'text') {
					$db->Execute("DELETE FROM " . TABLE_PRODUCT_ATTRIBUTE . " WHERE product_option_id = " . (int)$productOption['product_option_id'] . " AND product_option_value_id <> 0");
					$db->Execute("DELETE FROM " . TABLE_PRODUCT_OPTION_VALUE . " WHERE product_option_id = " . (int)$productOption['product_option_id'] . " AND product_option_value_id <> 0");
				} elseif (count($productOptionValueList)==0) {
					$db->Execute("DELETE FROM " . TABLE_PRODUCT_ATTRIBUTE . " WHERE product_option_id = " . (int)$productOption['product_option_id']);
					$db->Execute("DELETE FROM " . TABLE_PRODUCT_OPTION_VALUE . " WHERE product_option_id = " . (int)$productOption['product_option_id']);
				} else {
					$notDeleltIds = array();
					foreach ($productOptionValueList as $productOptionValue) {
						$error = false;
						if (strlen($productOptionValue['name']) < 1) {
							$error = true;
						} else {
							$sql = "SELECT COUNT(*) AS total FROM " . TABLE_PRODUCT_OPTION_VALUE . " WHERE product_option_id = " . (int)$productOption['product_option_id'] . " AND name = :name AND product_option_value_id <> :product_option_value_id";
							$sql = $db->bindVars($sql, ':name', $productOptionValue['name'], 'string');
							$sql = $db->bindVars($sql, ':product_option_value_id', isset($productOptionValue['product_option_value_id'])?$productOptionValue['product_option_value_id']:0, 'integer');
							$check_product_option_value = $db->Execute($sql);
							if ($check_product_option_value->fields['total'] > 0) {
								$error = true;
							}
						}
						if ($error==true) {
							//nothing
						} else {
							$sql_data_array = array(
								array('fieldName'=>'product_option_id', 'value'=>$productOption['product_option_id'], 'type'=>'integer'),
								array('fieldName'=>'name', 'value'=>$productOptionValue['name'], 'type'=>'string'),
								array('fieldName'=>'sort_order', 'value'=>$productOptionValue['sort_order'], 'type'=>'integer')
							);
							if ($productOptionValue['product_option_value_id'] > 0) {
								$db->perform(TABLE_PRODUCT_OPTION_VALUE, $sql_data_array, 'UPDATE', 'product_option_value_id = ' . (int)$productOptionValue['product_option_value_id']);
							} else {
								$db->perform(TABLE_PRODUCT_OPTION_VALUE, $sql_data_array);
								$productOptionValue['product_option_value_id'] = $db->Insert_ID();
							}
							$notDeleltIds[] = $productOptionValue['product_option_value_id'];
						}
					}
					if (not_null($notDeleltIds)) {
						$db->Execute("DELETE FROM " . TABLE_PRODUCT_ATTRIBUTE . " WHERE product_option_id = " . (int)$productOption['product_option_id'] . " AND product_option_value_id NOT IN (" . implode(', ', $notDeleltIds) . ")");
						$db->Execute("DELETE FROM " . TABLE_PRODUCT_OPTION_VALUE . " WHERE product_option_id = " . (int)$productOption['product_option_id'] . " AND product_option_value_id NOT IN (" . implode(', ', $notDeleltIds) . ")");
					}
				}
			}
			$message_stack->add_session('product_option', '选项设置已保存', 'success');
			redirect(href_link(FILENAME_PRODUCT_OPTION, 'product_option_id=' . $productOption['product_option_id']));
		}
	break;
	case 'delete':
		$error = false;
		$selected = $_POST['selected'];
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add_session('product_option', '删除选项时出现安全错误。');
		}
		if ($error==true) {
			//nothing
		} else {
			foreach ($selected as $val) {
				$db->Execute("DELETE FROM " . TABLE_PRODUCT_ATTRIBUTE . " WHERE product_option_id = " . (int)$val);
				$db->Execute("DELETE FROM " . TABLE_PRODUCT_OPTION_VALUE . " WHERE product_option_id = " . (int)$val);
				$db->Execute("DELETE FROM " . TABLE_PRODUCT_OPTION . " WHERE product_option_id = " . (int)$val);
			}
			$message_stack->add_session('product_option', '选项已删除。', 'success');
		}
		redirect(href_link(FILENAME_PRODUCT_OPTION));
	break;
	default:
		if ($product_option_id > 0) {
			$sql = "SELECT product_option_id, type,
						   name, sort_order
					FROM   " . TABLE_PRODUCT_OPTION . "
					WHERE  product_option_id = :product_option_id";
			$sql = $db->bindVars($sql, ':product_option_id', $product_option_id, 'integer');
			$result = $db->Execute($sql);
			if ($result->RecordCount() > 0) {
				$productOption = array(
					'product_option_id' => $result->fields['product_option_id'],
					'type' => $result->fields['type'],
					'name' => $result->fields['name'],
					'sort_order' => $result->fields['sort_order']
				);
				$sql = "SELECT product_option_value_id,
							   name, sort_order
						FROM   " . TABLE_PRODUCT_OPTION_VALUE . "
						WHERE  product_option_id = :product_option_id
						ORDER BY sort_order";
				$sql = $db->bindVars($sql, ':product_option_id', $product_option_id, 'integer');
				$value_result = $db->Execute($sql);
				while (!$value_result->EOF) {
					$productOptionValueList[] = array(
						'product_option_value_id' => $value_result->fields['product_option_value_id'],
						'name' => $value_result->fields['name'],
						'sort_order' => $value_result->fields['sort_order']
					);
					$value_result->MoveNext();
				}
			}
		} else {
			$sql = "SELECT product_option_id, name,
						   sort_order
					FROM   " . TABLE_PRODUCT_OPTION . "
					ORDER BY sort_order";
			$result = $db->Execute($sql);
			$productOptionList = array();
			while (!$result->EOF) {
				$productOptionList[] = array(
					'product_option_id' => $result->fields['product_option_id'],
					'name' => $result->fields['name'],
					'sort_order' => $result->fields['sort_order']
				);
				$result->MoveNext();
			}
		}
	break;
}

if ($action == 'new' || $action == 'save' || $product_option_id > 0) {
	if (!isset($productOptionValueList)
		|| !is_array($productOptionValueList)) {
			$productOptionValueList = array();
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>选项设置</title>
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
    		<?php if ($message_stack->size('product_option') > 0) echo $message_stack->output('product_option'); ?>
    		<?php if ($action == 'new' || $action == 'save' || $product_option_id > 0) { ?>
    			<form action="<?php echo href_link(FILENAME_PRODUCT_OPTION, 'action=save'); ?>" method="post">
    			<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    				<input type="hidden" value="<?php echo isset($productOption['product_option_id'])?$productOption['product_option_id']:''; ?>" name="product_option[product_option_id]" />
    			</div>
    			<div class="page-title title-buttons">
	    			<h1>选项设置</h1>
	    			<button type="submit" class="button"><span><span>保存</span></span></button>
    				<button type="button" class="button btn-cancel" onclick="setLocation('<?php echo href_link(FILENAME_PRODUCT_OPTION); ?>');"><span><span>取消</span></span></button>
	    		</div>
	    		<table class="form-list">
				<tbody>
					<tr>
						<td class="label"><label for="product-option-name">选项名称 <span class="required">*</span></label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($productOption['name'])?$productOption['name']:''; ?>" name="product_option[name]" id="product-option-name" /></td>
					</tr>
					<tr>
						<td class="label"><label for="product-option-type">类型 <span class="required">*</span></label></td>
						<td class="value">
							<select name="product_option[type]" id="product-option-type" onchange="productOptionValue();">
								<?php foreach ($availabType as $key => $val) { ?>
								<option<?php if (isset($productOption['type'])&&$productOption['type']==$key) { ?> selected="selected"<?php } ?> value="<?php echo $key; ?>"><?php echo $val; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="label"><label for="product-option-sort_order">排序</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($productOption['sort_order'])?$productOption['sort_order']:'0'; ?>" name="product_option[sort_order]" id="product-option-sort_order" /></td>
					</tr>
				</tbody>
    			</table>
    			<table id="product-option-value" class="data-table">
	    		<thead>
	    			<tr>
	    				<th>选项值名称</th>
	    				<th>排序</th>
	    				<th class="a-center">管理</th>
	    			</tr>
	    		</thead>
	    		<?php $i=0; ?>
	    		<?php foreach ($productOptionValueList as $productOptionValue) { ?>
	    		<tbody id="product-option-value-row<?php echo $i; ?>">
	    			<tr>
	    				<td class="value">
	    					<input type="hidden" value="<?php echo $productOptionValue['product_option_value_id']; ?>" name="product_option_value[<?php echo $i; ?>][product_option_value_id]" />
							<input type="text" class="input-text" value="<?php echo $productOptionValue['name']; ?>" name="product_option_value[<?php echo $i; ?>][name]" />
	    				</td>
	    				<td class="value">
	    					<input type="text" class="input-text" value="<?php echo $productOptionValue['sort_order']; ?>" name="product_option_value[<?php echo $i; ?>][sort_order]" />
	    				</td>
	    				<td><button type="button" class="button" onclick="$('#product-option-value-row<?php echo $i; ?>').remove();"><span><span>移除</span></span></button></td>
	    			</tr>
	    		</tbody>
	    			<?php $i++; ?>
	    		<?php } ?>
	    		<tfoot>
	    			<tr>
	    				<td colspan="2"></td>
	    				<td><button type="button" class="button" onclick="addProductOptionValue();"><span><span>添加选项值</span></span></button></td>
	    			</tr>
	    		</tfoot>
	    		</table>
    			</form>
<script type="text/javascript"><!--
function productOptionValue() {
	if ($('#product-option-type').val()=='text') {
		$('#product-option-value').hide();
	} else {
		$('#product-option-value').show();
	}
}
productOptionValue();
var product_option_value_row = <?php echo $i; ?>;
function addProductOptionValue() {
	html = '<tbody id="product-option-value-row' + product_option_value_row + '">';
	html += '<tr>';
	html += '<td><input type="hidden" value="" name="product_option_value[' + product_option_value_row + '][product_option_value_id]" />';
	html += '<input type="text" class="input-text" value="" name="product_option_value[' + product_option_value_row + '][name]" />';
	html += '</td>';
	html += '<td><input type="text" class="input-text" value="" name="product_option_value[' + product_option_value_row + '][sort_order]" /></td>';
	html += '<td><button type="button" class="button" onclick="$(\'#product-option-value-row' + product_option_value_row + '\').remove();"><span><span>移除</span></span></button></td>';
	html += '</tr>';
	html += '</tbody>';
	$('#product-option-value tfoot').before(html);
	product_option_value_row++;
}
//--></script>
    		<?php } else { ?>
				<form action="<?php echo href_link(FILENAME_PRODUCT_OPTION, 'action=delete'); ?>" method="post">
	    		<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    			</div>
	    		<div class="page-title title-buttons">
	    			<h1>选项</h1>
    				<button type="button" class="button button-new" onclick="setLocation('<?php echo href_link(FILENAME_PRODUCT_OPTION, 'action=new'); ?>');"><span><span>新增</span></span></button>
    				<button type="submit" class="button" onclick="return confirm('删除或卸载后您将不能恢复，请确定要这么做吗？');"><span><span>删除</span></span></button>
	    		</div>
	    		<table class="data-table">
	    		<colgroup>
	    			<col width="10" />
	    			<col />
	    			<col width="40" />
	    			<col width="60" />
	    		</colgroup>
	    		<thead>
	    			<tr>
	    				<th><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></th>
	    				<th>选项名称</th>
	    				<th>排序</th>
	    				<th class="a-center">管理</th>
	    			</tr>
	    		</thead>
	    		<tbody>
	    			<?php foreach ($productOptionList as $val) { ?>
	    			<tr>
	    				<td><input type="checkbox" value="<?php echo $val['product_option_id']; ?>" name="selected[]" /></td>
	    				<td><?php echo $val['name']; ?></td>
	    				<td><?php echo $val['sort_order']; ?></td>
	    				<td class="a-center">[ <a href="<?php echo href_link(FILENAME_PRODUCT_OPTION, 'product_option_id=' . $val['product_option_id']); ?>">编辑</a> ]</td>
	    			</tr>
	    			<?php } ?>
	    		</tbody>
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