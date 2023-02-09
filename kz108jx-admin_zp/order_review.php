<?php require('includes/application_top.php'); ?>
<?php
$order_review_id = isset($_GET['order_review_id'])?$_GET['order_review_id']:0;
$action = isset($_GET['action'])?$_GET['action']:'';
$availabRating = array('1'=>'1星', '2'=>'2星', '3'=>'3星', '4'=>'4星', '5'=>'5星');
switch ($action) {
	case 'save':
		$error = false;
		$orderReview = db_prepare_input($_POST['order_review']);
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add('order_review', '订单评论保存时出现安全错误。');
		}
		if (strlen($orderReview['email_address']) < 1) {
			$error = true;
			$message_stack->add('order_review', '订单评论邮箱不能为空。');
		} elseif (!validate_email($orderReview['email_address'])) {
			$error = true;
			$message_stack->add('order_review', '订单评论邮箱格式错误。');
		}
		if (strlen($orderReview['content']) < 1) {
			$error = true;
			$message_stack->add('order_review', '订单评论内容不能为空。');
		}
		if (!array_key_exists($orderReview['quality'], $availabRating)) $orderReview['quality'] = 1;
		if (!array_key_exists($orderReview['ship'], $availabRating)) $orderReview['ship'] = 1;
		if (!array_key_exists($orderReview['service'], $availabRating)) $orderReview['service'] = 1;
		if ($error==true) {
			//nothing
		} else {
			$sql_data_array = array(
				array('fieldName'=>'order_id', 'value'=>$orderReview['order_id'], 'type'=>'integer'),
				array('fieldName'=>'quality', 'value'=>$orderReview['quality'], 'type'=>'integer'),
				array('fieldName'=>'ship', 'value'=>$orderReview['ship'], 'type'=>'integer'),
				array('fieldName'=>'service', 'value'=>$orderReview['service'], 'type'=>'integer'),
				array('fieldName'=>'email_address', 'value'=>$orderReview['email_address'], 'type'=>'string'),
				array('fieldName'=>'content', 'value'=>$orderReview['content'], 'type'=>'string')
			);
			if($orderReview['order_review_id'] > 0) {
				$db->perform(TABLE_ORDER_REVIEW, $sql_data_array, 'UPDATE', 'order_review_id = ' . $orderReview['order_review_id']);
			} else {
				$sql_data_array[] = array('fieldName'=>'date_added', 'value'=>'NOW()', 'type'=>'noquotestring');
				$db->perform(TABLE_ORDER_REVIEW, $sql_data_array);
				$orderReview['order_review_id'] = $db->Insert_ID();
			}
			$message_stack->add_session('order_review', '订单评论设置已保存', 'success');
			redirect(href_link(FILENAME_ORDER_REVIEW, 'order_review_id=' . $orderReview['order_review_id']));
		}
	break;
	case 'delete':
		$error = false;
		$selected = $_POST['selected'];
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add_session('order_review', '删除订单评论时出现安全错误。');
		}
		if ($error==true) {
			//nothing
		} else {
			foreach ($selected as $val) {
				$db->Execute("DELETE FROM " . TABLE_ORDER_REVIEW . " WHERE order_review_id = " . (int)$val);
			}
			$message_stack->add_session('order_review', '订单评论已删除。', 'success');
		}
		redirect(href_link(FILENAME_ORDER_REVIEW));
	break;
	default:
		if ($order_review_id > 0) {
			$sql = "SELECT order_review_id, order_id,
						   quality, ship, service,
						   email_address, content, date_added
					FROM   " . TABLE_ORDER_REVIEW . "
					WHERE  order_review_id = :orderReviewID";
			$sql = $db->bindVars($sql, ':orderReviewID', $order_review_id, 'integer');
			$result = $db->Execute($sql);
			if ($result->RecordCount() > 0) {
				$orderReview = array(
					'order_review_id' => $result->fields['order_review_id'],
					'order_id'      => $result->fields['order_id'],
					'quality'       => $result->fields['quality'],
					'ship'          => $result->fields['quality'],
					'service'       => $result->fields['service'],
					'email_address' => $result->fields['email_address'],
					'content'       => $result->fields['content'],
					'date_added'    => $result->fields['date_added']
				);
			}
		} else {
			$sql = "SELECT COUNT(*) AS total FROM order_review";
			$result = $db->Execute($sql);
			$pagerConfig['total'] = $result->fields['total'];
			require(DIR_FS_ADMIN_CLASSES . 'pager.php');
			$pager = new pager($pagerConfig);
			$sql = "SELECT order_review_id, order_id,
						   quality, ship, service,
						   email_address, date_added
					FROM   " . TABLE_ORDER_REVIEW . "
					ORDER BY order_review_id DESC";
			$result = $db->Execute($sql, $pager->getLimitSql());
			$orderReviewList = array();
			while (!$result->EOF) {
				$orderReviewList[] = array(
					'order_review_id' => $result->fields['order_review_id'],
					'order_id'      => $result->fields['order_id'],
					'quality'       => $result->fields['quality'],
					'ship'          => $result->fields['ship'],
					'service'       => $result->fields['service'],
					'email_address' => $result->fields['email_address'],
					'date_added'    => $result->fields['date_added']
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
<title>订单评论</title>
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
    		<?php if ($message_stack->size('order_review') > 0) echo $message_stack->output('order_review'); ?>
    		<?php if ($action == 'new' || $action == 'save' || $order_review_id > 0) { ?>
    			<form action="<?php echo href_link(FILENAME_ORDER_REVIEW, 'action=save'); ?>" method="post">
    			<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    				<input type="hidden" value="<?php echo isset($orderReview['order_review_id'])?$orderReview['order_review_id']:''; ?>" name="order_review[order_review_id]" />
    			</div>
    			<div class="page-title title-buttons">
	    			<h1>订单评论</h1>
	    			<button type="submit" class="button"><span><span>保存</span></span></button>
    				<button type="button" class="button btn-cancel" onclick="setLocation('<?php echo href_link(FILENAME_ORDER_REVIEW); ?>');"><span><span>取消</span></span></button>
	    		</div>
	    		<table class="form-list">
				<tbody>
					<tr>
						<td class="label"><label for="order-review-order_id">订单号<span class="required">*</span></label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($orderReview['order_id'])?$orderReview['order_id']:''; ?>" name="order_review[order_id]" id="order-review-order_id" /></td>
					</tr>
					<tr>
						<td class="label"><label for="order-review-quality">品质评级 <span class="required">*</span></label></td>
						<td class="value">
							<select name="order_review[quality]" id="order-review-quality">
								<?php foreach ($availabRating as $_key=>$_val) { ?>
								<option<?php if (isset($orderReview['quality'])&&$orderReview['quality']==$_key) { ?> selected="selected"<?php } ?> value="<?php echo $_key; ?>"><?php echo $_val; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="label"><label for="order-review-ship">运送评级 <span class="required">*</span></label></td>
						<td class="value">
							<select name="order_review[ship]" id="order-review-ship">
								<?php foreach ($availabRating as $_key=>$_val) { ?>
								<option<?php if (isset($orderReview['ship'])&&$orderReview['ship']==$_key) { ?> selected="selected"<?php } ?> value="<?php echo $_key; ?>"><?php echo $_val; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="label"><label for="order-review-service">服务评级 <span class="required">*</span></label></td>
						<td class="value">
							<select name="order_review[service]" id="order-review-service">
								<?php foreach ($availabRating as $_key=>$_val) { ?>
								<option<?php if (isset($orderReview['service'])&&$orderReview['service']==$_key) { ?> selected="selected"<?php } ?> value="<?php echo $_key; ?>"><?php echo $_val; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="label"><label for="order-review-email_address">评论邮箱<span class="required">*</span></label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($orderReview['email_address'])?$orderReview['email_address']:''; ?>" name="order_review[email_address]" id="order-review-email_address" /></td>
					</tr>
					<tr>
						<td class="label"><label for="order-review-content">评论内容 <span class="required">*</span></label></td>
						<td class="value"><textarea cols="15" rows="2" name="order_review[content]" id="order-review-content"><?php echo isset($orderReview['content'])?$orderReview['content']:''; ?></textarea></td>
					</tr>
				</tbody>
    			</table>
    			</form>
    		<?php } else { ?>
				<form action="<?php echo href_link(FILENAME_ORDER_REVIEW, 'action=delete'); ?>" method="post">
	    		<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    			</div>
	    		<div class="page-title title-buttons">
	    			<h1>订单评论</h1>
    				<button type="button" class="button button-new" onclick="setLocation('<?php echo href_link(FILENAME_ORDER_REVIEW, 'action=new'); ?>');"><span><span>新增</span></span></button>
    				<button type="submit" class="button" onclick="return confirm('删除或卸载后您将不能恢复，请确定要这么做吗？');"><span><span>删除</span></span></button>
	    		</div>
	    		<?php require(DIR_FS_ADMIN_INCLUDES . 'pager.php'); ?>
	    		<table class="data-table">
	    		<colgroup>
	    			<col width="10" />
	    			<col width="140" />
	    			<col width="60" />
	    			<col width="60" />
	    			<col width="60" />
	    			<col />
	    			<col width="140" />
	    			<col width="60" />
	    		</colgroup>
	    		<thead>
	    			<tr>
	    				<th><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></th>
	    				<th>订单号</th>
	    				<th class="a-center">品质评级 </th>
	    				<th class="a-center">服务评级</th>
	    				<th class="a-center">服务评级</th>
	    				<th>评论邮箱</th>
	    				<th>评论日期</th>
	    				<th class="a-center">管理</th>
	    			</tr>
	    		</thead>
	    		<?php if (count($orderReviewList)>0) { ?>
	    		<tbody>
	    			<?php foreach ($orderReviewList as $val) { ?>
	    			<tr>
	    				<td><input type="checkbox" value="<?php echo $val['order_review_id']; ?>" name="selected[]" /></td>
	    				<td><?php echo put_orderNO($val['order_id']); ?></td>
	    				<td class="a-center"><?php echo $availabRating[$val['quality']]; ?></td>
	    				<td class="a-center"><?php echo $availabRating[$val['ship']]; ?></td>
	    				<td class="a-center"><?php echo $availabRating[$val['service']]; ?></td>
	    				<td><?php echo $val['email_address']; ?></td>
	    				<td><?php echo datetime_short($val['date_added']); ?></td>
	    				<td class="a-center">[ <a href="<?php echo href_link(FILENAME_ORDER_REVIEW, 'order_review_id=' . $val['order_review_id']); ?>">编辑</a> ]</td>
	    			</tr>
	    			<?php } ?>
	    		</tbody>
	    		<?php } else { ?>
	    		<tbody>
					<tr>
						<td class="a-center" colspan="8">没有结果！</td>
					</tr>
				</tbody>
	    		<?php } ?>
	    		</table>
	    		<?php require(DIR_FS_ADMIN_INCLUDES . 'pager.php'); ?>
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