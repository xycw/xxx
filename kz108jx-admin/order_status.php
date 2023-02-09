<?php require('includes/application_top.php'); ?>
<?php
$order_status_id = isset($_GET['order_status_id'])?$_GET['order_status_id']:0;
$action = isset($_GET['action'])?$_GET['action']:'';
switch ($action) {
	case 'save':
		$error = false;
		$orderStatus = db_prepare_input($_POST['order_status']);
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add('order_status', '订单状态设置保存时出现安全错误。');
		}
		if (strlen($orderStatus['name']) < 1) {
			$error = true;
			$message_stack->add('order_status', '订单状态名称不能为空');
		} else {
			$sql = "SELECT COUNT(*) AS total FROM " . TABLE_ORDER_STATUS . " WHERE name = :name AND order_status_id <> :order_statusID";
			$sql = $db->bindVars($sql, ':name', $orderStatus['name'], 'string');
			$sql = $db->bindVars($sql, ':order_statusID', isset($orderStatus['order_status_id'])?$orderStatus['order_status_id']:0, 'integer');
			$check_order_status = $db->Execute($sql);
			if ($check_order_status->fields['total'] > 0) {
				$error = true;
				$message_stack->add('order_status', '订单状态名称存在相同。');
			}
		}
		if ($error==true) {
			//nothing
		} else {
			$sql_data_array = array(
				array('fieldName'=>'name', 'value'=>$orderStatus['name'], 'type'=>'string')
			);
			if ($orderStatus['order_status_id'] > 0) {
				$db->perform(TABLE_ORDER_STATUS, $sql_data_array, 'UPDATE', 'order_status_id = ' . $orderStatus['order_status_id']);
			} else {
				$db->perform(TABLE_ORDER_STATUS, $sql_data_array);
				$orderStatus['order_status_id'] = $db->Insert_ID();
			}
			$message_stack->add_session('order_status', '订单状态设置已保存。', 'success');
			redirect(href_link(FILENAME_ORDER_STATUS, 'order_status_id=' . $orderStatus['order_status_id']));
		}
	break;
	case 'delete':
		$error = false;
		$selected = $_POST['selected'];
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add_session('order_status', '删除订单状态时出现安全错误。');
		}
		if ($error==true) {
			//nothing
		} else {
			foreach ($selected as $val) {
				$db->Execute("DELETE FROM " . TABLE_ORDER_STATUS . " WHERE order_status_id = " . (int)$val);
			}
			$message_stack->add_session('order_status', '订单状态已删除。', 'success');
		}
		redirect(href_link(FILENAME_ORDER_STATUS));
	break;
	default:
		if ($order_status_id > 0) {
			$sql = "SELECT order_status_id, name
					FROM order_status
					WHERE order_status_id = :order_statusID";
			$sql = $db->bindVars($sql, ':order_statusID', $order_status_id, 'integer');
			$result = $db->Execute($sql);
			if ($result->RecordCount() > 0) {
				$orderStatus = array(
					'order_status_id' => $result->fields['order_status_id'],
					'name' => $result->fields['name']
				);
			}
		} else {
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
		}
	break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>订单状态设置</title>
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
    		<?php if ($message_stack->size('order_status') > 0) echo $message_stack->output('order_status'); ?>
    		<?php if ($action == 'new' || $action == 'save' || $order_status_id > 0) { ?>
    			<form action="<?php echo href_link(FILENAME_ORDER_STATUS, 'action=save'); ?>" method="post">
    			<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    				<input type="hidden" value="<?php echo isset($orderStatus['order_status_id'])?$orderStatus['order_status_id']:''; ?>" name="order_status[order_status_id]" />
    			</div>
    			<div class="page-title title-buttons">
	    			<h1>订单状态</h1>
	    			<button type="submit" class="button"><span><span>保存</span></span></button>
    				<button type="button" class="button btn-cancel" onclick="setLocation('<?php echo href_link(FILENAME_ORDER_STATUS); ?>');"><span><span>取消</span></span></button>
	    		</div>
	    		<table class="form-list">
				<tbody>
					<tr>
						<td class="label"><label for="order-status_name">订单状态名称  <span class="required">*</span></label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($orderStatus['name'])?$orderStatus['name']:''; ?>" name="order_status[name]" id="order-status_name" /></td>
					</tr>
				</tbody>
    			</table>
				</form>
    		<?php } else { ?>
				<form action="<?php echo href_link(FILENAME_ORDER_STATUS, 'action=delete'); ?>" method="post">
	    		<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    			</div>
	    		<div class="page-title title-buttons">
	    			<h1>订单状态</h1>
    				<button type="button" class="button button-new" onclick="setLocation('<?php echo href_link(FILENAME_ORDER_STATUS, 'action=new'); ?>');"><span><span>新增</span></span></button>
    				<button type="submit" class="button" onclick="return confirm('删除或卸载后您将不能恢复，请确定要这么做吗？');"><span><span>删除</span></span></button>
	    		</div>
	    		<table class="data-table">
	    		<colgroup>
	    			<col width="10" />
	    			<col />
	    			<col width="60" />
	    		</colgroup>
	    		<thead>
	    			<tr>
	    				<th><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></th>
	    				<th>订单状态名称</th>
	    				<th class="a-center">管理</th>
	    			</tr>
	    		</thead>
	    		<?php if (count($orderStatusList)>0) { ?>
	    		<tbody>
	    			<?php foreach ($orderStatusList as $val) { ?>
	    			<tr>
	    				<td><input type="checkbox" value="<?php echo $val['order_status_id']; ?>" name="selected[]" /></td>
	    				<td><?php echo $val['name']; ?></td>
	    				<td class="a-center">[ <a href="<?php echo href_link(FILENAME_ORDER_STATUS, 'order_status_id=' . $val['order_status_id']); ?>">编辑</a> ]</td>
	    			</tr>
	    			<?php } ?>
	    		</tbody>
	    		<?php } else { ?>
	    		<tbody>
					<tr>
						<td class="a-center" colspan="3">没有结果！</td>
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