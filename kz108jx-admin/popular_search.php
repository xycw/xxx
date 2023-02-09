<?php require('includes/application_top.php'); ?>
<?php
$popular_search_id = isset($_GET['popular_search_id'])?$_GET['popular_search_id']:0;
$action = isset($_GET['action'])?$_GET['action']:'';
$availabStatus = array('0'=>'禁用', '1'=>'启用');
switch ($action) {
	case 'save':
		$error = false;
		$popularSearch = db_prepare_input($_POST['popular_search']);
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add('popular_search', '搜索热度保存时出现安全错误。');
		}
		if (strlen($popularSearch['search']) < 1) {
			$error = true;
			$message_stack->add('popular_search', '搜索词不能为空。');
		} else {
			$sql = "SELECT COUNT(*) AS total FROM " . TABLE_POPULAR_SEARCH . " WHERE search = :search AND popular_search_id <> :popularSearchId";
			$sql = $db->bindVars($sql, ':search', $popularSearch['search'], 'string');
			$sql = $db->bindVars($sql, ':popularSearchId', isset($popularSearch['popular_search_id'])?$popularSearch['popular_search_id']:0, 'integer');
			$check_popular_search = $db->Execute($sql);
			if ($check_popular_search->fields['total'] > 0) {
				$error = true;
				$message_stack->add('popular_search', '搜索词存在相同。');
			}
		}
		if (!array_key_exists($popularSearch['status'], $availabStatus)) $popularSearch['status'] = 0;
		if ($error==true) {
			//nothing
		} else {
			$sql_data_array = array(
				array('fieldName'=>'search', 'value'=>$popularSearch['search'], 'type'=>'string'),
				array('fieldName'=>'freq', 'value'=>$popularSearch['freq'], 'type'=>'integer'),
				array('fieldName'=>'status', 'value'=>$popularSearch['status'], 'type'=>'integer')
			);
			if ($popularSearch['popular_search_id'] > 0) {
				$db->perform(TABLE_POPULAR_SEARCH, $sql_data_array, 'UPDATE', 'popular_search_id = ' . $popularSearch['popular_search_id']);
			} else {
				$db->perform(TABLE_POPULAR_SEARCH, $sql_data_array);
				$popularSearch['popular_search_id'] = $db->Insert_ID();
			}
			$message_stack->add_session('popular_search', '搜索热度已保存。', 'success');
			redirect(href_link(FILENAME_POULAR_SEARCH, 'popular_search_id=' . $popularSearch['popular_search_id']));
		}
	break;
	case 'delete':
		$error = false;
		$selected = $_POST['selected'];
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add_session('popular_search', '删除搜索热度时出现安全错误。');
		}
		if ($error==true) {
			//nothing
		} else {
			foreach ($selected as $val) {
				$db->Execute("DELETE FROM " . TABLE_POPULAR_SEARCH . " WHERE popular_search_id = " . (int)$val);
			}
			$message_stack->add_session('popular_search', '搜索热度已删除。', 'success');
		}
		redirect(href_link(FILENAME_POULAR_SEARCH));
	break;
	case 'set_status':
		$db->Execute("UPDATE " . TABLE_POPULAR_SEARCH . " SET status = IF(status = 1, 0, 1) WHERE popular_search_id = " . (int)$popular_search_id);
		redirect(href_link(FILENAME_POULAR_SEARCH));
	break;
	default:
		if ($popular_search_id > 0) {
			$sql = "SELECT popular_search_id, search,
						   freq, status
					FROM   " . TABLE_POPULAR_SEARCH . "
					WHERE  popular_search_id = :popularSearchId";
			$sql = $db->bindVars($sql, ':popularSearchId', $popular_search_id, 'string');
			$result = $db->Execute($sql);
			if ($result->RecordCount() > 0) {
				$popularSearch = array(
					'popular_search_id' => $result->fields['popular_search_id'],
					'search' => $result->fields['search'],
					'freq'   => $result->fields['freq'],
					'status' => $result->fields['status']
				);
			}
		} else {
			$sql = "SELECT popular_search_id, search,
						   freq, status
					FROM   " . TABLE_POPULAR_SEARCH . "
					ORDER BY freq DESC, search ASC";
			$result = $db->Execute($sql);
			$popularSearchList = array();
			while (!$result->EOF) {
				$popularSearchList[] = array(
					'popular_search_id' => $result->fields['popular_search_id'],
					'search' => $result->fields['search'],
					'freq'   => $result->fields['freq'],
					'status' => $result->fields['status']
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
<title>搜索热度</title>
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
    		<?php if ($message_stack->size('popular_search') > 0) echo $message_stack->output('popular_search'); ?>
    		<?php if ($action == 'new' || $action == 'save' || $popular_search_id > 0) { ?>
    			<form action="<?php echo href_link(FILENAME_POULAR_SEARCH, 'action=save'); ?>" method="post">
    			<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    				<input type="hidden" value="<?php echo isset($popularSearch['popular_search_id'])?$popularSearch['popular_search_id']:''; ?>" name="popular_search[popular_search_id]" />
    			</div>
    			<div class="page-title title-buttons">
	    			<h1>搜索热度</h1>
	    			<button type="submit" class="button"><span><span>保存</span></span></button>
    				<button type="button" class="button btn-cancel" onclick="setLocation('<?php echo href_link(FILENAME_POULAR_SEARCH); ?>');"><span><span>取消</span></span></button>
	    		</div>
	    		<table class="form-list">
	    			<tr>
						<td class="label"><label for="popular_search-search">搜索词</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($popularSearch['search'])?$popularSearch['search']:''; ?>" name="popular_search[search]" id="popular_search-search" /></td>
					</tr>
					<tr>
						<td class="label"><label for="popular_search-freq">热度</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($popularSearch['freq'])?$popularSearch['freq']:''; ?>" name="popular_search[freq]" id="popular_search-freq" /></td>
					</tr>
	    			<tr>
						<td class="label"><label for="popular_search-status">状态</label></td>
						<td class="value">
							<select name="popular_search[status]" id="popular_search-status">
								<?php foreach ($availabStatus as $_key=>$_val) { ?>
								<option<?php if (isset($popularSearch['status'])&&$popularSearch['status']==$_key) { ?> selected="selected"<?php } ?> value="<?php echo $_key; ?>"><?php echo $_val; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
    			</table>
    			</form>
    		<?php } else { ?>
				<form action="<?php echo href_link(FILENAME_POULAR_SEARCH, 'action=delete'); ?>" method="post">
	    		<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    			</div>
	    		<div class="page-title title-buttons">
	    			<h1>搜索热度列表</h1>
    				<button type="button" class="button button-new" onclick="setLocation('<?php echo href_link(FILENAME_POULAR_SEARCH, 'action=new'); ?>');"><span><span>新增</span></span></button>
    				<button type="submit" class="button" onclick="return confirm('删除或卸载后您将不能恢复，请确定要这么做吗？');"><span><span>删除</span></span></button>
	    		</div>
	    		<table class="data-table">
	    			<colgroup>
	    			<col width="10" />
	    			<col />
	    			<col width="100" />
	    			<col width="60" />
	    			<col width="60" />
	    		</colgroup>
	    		<thead>
	    			<tr>
	    				<th><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></th>
	    				<th>搜索词</th>
	    				<th class="a-right">热度</th>
	    				<th class="a-center">状态</th>
	    				<th class="a-center">操作</th>
	    			</tr>
	    		</thead>
	    		<?php if (count($popularSearchList)>0) { ?>
	    		<tbody>
	    			<?php foreach ($popularSearchList as $val) { ?>
	    			<tr>
	    				<td><input type="checkbox" value="<?php echo $val['popular_search_id']; ?>" name="selected[]" /></td>
	    				<td><?php echo $val['search']; ?></td>
	    				<td class="a-right"><?php echo $val['freq']; ?></td>
	    				<td class="a-center">[ <a title="点击 状态:<?php echo $val['status']==1?$availabStatus[0]:$availabStatus[1]; ?>" href="<?php echo href_link(FILENAME_POULAR_SEARCH, 'action=set_status&popular_search_id=' . $val['popular_search_id']); ?>"><?php echo $availabStatus[$val['status']]; ?></a> ]</td>
	    				<td class="a-center">[ <a href="<?php echo href_link(FILENAME_POULAR_SEARCH, 'popular_search_id=' . $val['popular_search_id']); ?>">编辑</a> ]</td>
	    			</tr>
	    			<?php } ?>
	    		</tbody>
	    		<?php } else { ?>
	    		<tbody>
					<tr>
						<td class="a-center" colspan="5">没有结果！</td>
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