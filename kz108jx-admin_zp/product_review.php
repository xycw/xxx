<?php require('includes/application_top.php'); ?>
<?php
$product_review_id = isset($_GET['product_review_id'])?$_GET['product_review_id']:0;
$action = isset($_GET['action'])?$_GET['action']:'';
$availabRating = array('1'=>'1星', '2'=>'2星', '3'=>'3星', '4'=>'4星', '5'=>'5星');
$availabStatus = array('0'=>'禁用', '1'=>'启用');
switch ($action) {
	case 'save':
		$error = false;
		$productReview = db_prepare_input($_POST['product_review']);
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add('product_review', '产品评论保存时出现安全错误。');
		}
		if (strlen($productReview['nickname']) < 1) {
			$error = true;
			$message_stack->add('product_review', '产品评论作者不能为空。');
		}
		if ($productReview['product_id'] > 0){
			$sql = "SELECT COUNT(*) AS total FROM " . TABLE_PRODUCT . " WHERE product_id = :productID";
			$sql = $db->bindVars($sql, ':productID', $productReview['product_id'], 'integer');
			$check_product = $db->Execute($sql);
			if ($check_product->fields['total'] > 0) {
				//nothing
			} else {
				$error = true;
				$message_stack->add('product_review', '请填写正确的产品ID。');
			}
		} else {
			$error = true;
			$message_stack->add('product_review', '请填写产品ID。');
		}
		if (strlen($productReview['content']) < 1) {
			$error = true;
			$message_stack->add('product_review', '产品评论内容不能为空。');
		}
		if (strlen($productReview['content']) < 1) {
			$error = true;
			$message_stack->add('product_review', '产品评论内容不能为空。');
		}
		if (!array_key_exists($productReview['rating'], $availabRating)) $productReview['rating'] = 1;
		if (!array_key_exists($productReview['status'], $availabStatus)) $productReview['status'] = 0;
		if ($error==true) {
			//nothing
		} else {
			$sql_data_array = array(
				array('fieldName'=>'product_id', 'value'=>$productReview['product_id'], 'type'=>'integer'),
				array('fieldName'=>'nickname', 'value'=>$productReview['nickname'], 'type'=>'string'),
				array('fieldName'=>'rating', 'value'=>$productReview['rating'], 'type'=>'integer'),
				array('fieldName'=>'content', 'value'=>$productReview['content'], 'type'=>'string'),
				array('fieldName'=>'status', 'value'=>$productReview['status'], 'type'=>'integer')
			);
			if($productReview['product_review_id'] > 0) {
				$db->perform(TABLE_PRODUCT_REVIEW, $sql_data_array, 'UPDATE', 'product_review_id = ' . $productReview['product_review_id']);
			} else {
				$sql_data_array[] = array('fieldName'=>'date_added', 'value'=>'NOW()', 'type'=>'noquotestring');
				$db->perform(TABLE_PRODUCT_REVIEW, $sql_data_array);
				$productReview['product_review_id'] = $db->Insert_ID();
			}
			$message_stack->add_session('product_review', '产品评论设置已保存', 'success');
			redirect(href_link(FILENAME_PRODUCT_REVIEW, 'product_review_id=' . $productReview['product_review_id']));
		}
	break;
	case 'delete':
		$error = false;
		$selected = $_POST['selected'];
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add_session('product_review', '删除产品评论时出现安全错误。');
		}
		if ($error==true) {
			//nothing
		} else {
			foreach ($selected as $val) {
				$db->Execute("DELETE FROM " . TABLE_PRODUCT_REVIEW . " WHERE product_review_id = " . (int)$val);
			}
			$message_stack->add_session('product_review', '产品评论已删除。', 'success');
		}
		redirect(href_link(FILENAME_PRODUCT_REVIEW));
	break;
	case 'set_status':
		$db->Execute("UPDATE " . TABLE_PRODUCT_REVIEW . " SET status = IF(status = 1, 0, 1) WHERE product_review_id = " . (int)$product_review_id);
		redirect(href_link(FILENAME_PRODUCT_REVIEW));
	break;
	default:
		if ($product_review_id > 0) {
			$sql = "SELECT product_review_id, product_id, rating,
						   nickname, content, status, date_added
					FROM   " . TABLE_PRODUCT_REVIEW . "
					WHERE  product_review_id = :productReviewID";
			$sql = $db->bindVars($sql, ':productReviewID', $product_review_id, 'integer');
			$result = $db->Execute($sql);
			if ($result->RecordCount() > 0) {
				$productReview = array(
					'product_review_id'  => $result->fields['product_review_id'],
					'product_id' => $result->fields['product_id'],
					'rating'     => $result->fields['rating'],
					'nickname'   => $result->fields['nickname'],
					'content'    => $result->fields['content'],
					'status'     => $result->fields['status'],
					'date_added' => $result->fields['date_added']
				);
			}
		} else {
			$sql = "SELECT COUNT(*) AS total FROM " . TABLE_PRODUCT_REVIEW;
			$result = $db->Execute($sql);
			$pagerConfig['total'] = $result->fields['total'];
			require(DIR_FS_ADMIN_CLASSES . 'pager.php');
			$pager = new pager($pagerConfig);
			$sql = "SELECT pr.product_review_id, pr.customer_id, pr.nickname,
						   pr.rating, pr.status, pr.date_added, p.name
					FROM   " . TABLE_PRODUCT_REVIEW . " pr, " . TABLE_PRODUCT . " p
					WHERE  pr.product_id = p.product_id
					ORDER BY pr.product_review_id DESC";
			$result = $db->Execute($sql, $pager->getLimitSql());
			$productReviewList = array();
			while (!$result->EOF) {
				$productReviewList[] = array(
					'product_review_id'    => $result->fields['product_review_id'],
					'customer_id'  => $result->fields['customer_id'],
					'name'         => $result->fields['nickname'],
					'rating'       => $result->fields['rating'],
					'status'       => $result->fields['status'],
					'date_added'   => $result->fields['date_added'],
					'product_name' => $result->fields['name']
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
<title>产品评论</title>
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
    		<?php if ($message_stack->size('product_review') > 0) echo $message_stack->output('product_review'); ?>
    		<?php if ($action == 'new' || $action == 'save' || $product_review_id > 0) { ?>
    			<form action="<?php echo href_link(FILENAME_PRODUCT_REVIEW, 'action=save'); ?>" method="post">
    			<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    				<input type="hidden" value="<?php echo isset($productReview['product_review_id'])?$productReview['product_review_id']:''; ?>" name="product_review[product_review_id]" />
    			</div>
    			<div class="page-title title-buttons">
	    			<h1>产品评论</h1>
	    			<button type="submit" class="button"><span><span>保存</span></span></button>
    				<button type="button" class="button btn-cancel" onclick="setLocation('<?php echo href_link(FILENAME_PRODUCT_REVIEW); ?>');"><span><span>取消</span></span></button>
	    		</div>
	    		<table class="form-list">
				<tbody>
					<tr>
						<td class="label"><label for="product-review-nickname">评论作者 <span class="required">*</span></label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($productReview['nickname'])?$productReview['nickname']:''; ?>" name="product_review[nickname]" id="product-review-nickname" /></td>
					</tr>
					<tr>
						<td class="label"><label for="product-review-product_id">产品 <span class="required">*</span></label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($productReview['product_id'])?$productReview['product_id']:''; ?>" name="product_review[product_id]" id="product-review-product_id" /></td>
					</tr>
					<tr>
						<td class="label"><label for="product-review-content">评论内容 <span class="required">*</span></label></td>
						<td class="value"><textarea cols="15" rows="2" name="product_review[content]" id="product-review-content"><?php echo isset($productReview['content'])?$productReview['content']:''; ?></textarea></td>
					</tr>
					<tr>
						<td class="label"><label for="product-review-rating">评级 <span class="required">*</span></label></td>
						<td class="value">
							<select name="product_review[rating]" id="product-review-rating">
								<?php foreach ($availabRating as $_key=>$_val) { ?>
								<option<?php if (isset($productReview['rating'])&&$productReview['rating']==$_key) { ?> selected="selected"<?php } ?> value="<?php echo $_key; ?>"><?php echo $_val; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="label"><label for="product-review-status">状态 <span class="required">*</span></label></td>
						<td class="value">
							<select name="product_review[status]" id="product-review-status">
								<option<?php if (isset($productReview['status'])&&$productReview['status']==1) { ?> selected="selected"<?php } ?> value="1">启用</option>
								<option<?php if (isset($productReview['status'])&&$productReview['status']!=1) { ?> selected="selected"<?php } ?> value="0">禁用</option>
							</select>
						</td>
					</tr>
				</tbody>
    			</table>
    			</form>
    		<?php } else { ?>
				<form action="<?php echo href_link(FILENAME_PRODUCT_REVIEW, 'action=delete'); ?>" method="post">
	    		<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    			</div>
	    		<div class="page-title title-buttons">
	    			<h1>产品评论</h1>
    				<button type="button" class="button button-new" onclick="setLocation('<?php echo href_link(FILENAME_PRODUCT_REVIEW, 'action=new'); ?>');"><span><span>新增</span></span></button>
    				<button type="submit" class="button" onclick="return confirm('删除或卸载后您将不能恢复，请确定要这么做吗？');"><span><span>删除</span></span></button>
	    		</div>
	    		<?php require(DIR_FS_ADMIN_INCLUDES . 'pager.php'); ?>
	    		<table class="data-table">
	    		<colgroup>
	    			<col width="10" />
	    			<col />
	    			<col />
	    			<col width="40" />
	    			<col width="40" />
	    			<col width="60" />
	    			<col width="140" />
	    			<col width="60" />
	    		</colgroup>
	    		<thead>
	    			<tr>
	    				<th><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></th>
	    				<th>产品名</th>
	    				<th>评论作者</th>
	    				<th class="a-center">类型</th>
	    				<th class="a-center">评级</th>
	    				<th class="a-center">状态</th>
	    				<th>评论日期</th>
	    				<th class="a-center">管理</th>
	    			</tr>
	    		</thead>
	    		<?php if (count($productReviewList)>0) { ?>
	    		<tbody>
	    			<?php foreach ($productReviewList as $val) { ?>
	    			<tr>
	    				<td><input type="checkbox" value="<?php echo $val['product_review_id']; ?>" name="selected[]" /></td>
	    				<td><?php echo $val['product_name']; ?></td>
	    				<td><?php echo $val['name']; ?></td>
	    				<td class="a-center"><?php echo $val['customer_id']>0?'客户':'游客'; ?></td>
	    				<td class="a-center"><?php echo $availabRating[$val['rating']]; ?></td>
	    				<td class="a-center">[ <a title="点击 状态:<?php echo $val['status']==1?$availabStatus[0]:$availabStatus[1]; ?>" href="<?php echo href_link(FILENAME_PRODUCT_REVIEW, 'action=set_status&product_review_id=' . $val['product_review_id']); ?>"><?php echo $availabStatus[$val['status']]; ?></a> ]</td>
	    				<td><?php echo datetime_short($val['date_added']); ?></td>
	    				<td class="a-center">[ <a href="<?php echo href_link(FILENAME_PRODUCT_REVIEW, 'product_review_id=' . $val['product_review_id']); ?>">编辑</a> ]</td>
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