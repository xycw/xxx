<?php require('includes/application_top.php'); ?>
<?php
$cms_page_id = isset($_GET['cms_page_id'])?$_GET['cms_page_id']:0;
$action = isset($_GET['action'])?$_GET['action']:'';
$availabStatus = array('0'=>'禁用', '1'=>'启用');
switch ($action) {
	case 'save':
		$error = false;
		$cmsPage = db_prepare_input($_POST['cms_page']);
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if (strlen($cmsPage['name']) < 1) {
			$error = true;
			$message_stack->add('cms_page', 'CMS页面名称不能为空。');
		} else {
			$sql = "SELECT COUNT(*) AS total FROM " . TABLE_CMS_PAGE . " WHERE name = :name AND cms_page_id <> :cmsPageID";
			$sql = $db->bindVars($sql, ':name', $cmsPage['name'], 'string');
			$sql = $db->bindVars($sql, ':cmsPageID', isset($cmsPage['cms_page_id'])?$cmsPage['cms_page_id']:0, 'integer');
			$check_cms_page = $db->Execute($sql);
			if ($check_cms_page->fields['total'] > 0) {
				$error = true;
				$message_stack->add('cms_page', 'CMS页面名称存在相同。');
			}
		}
		if ($error==true) {
			//nothing
		} else {
			$sql_data_array = array(
				array('fieldName'=>'name', 'value'=>$cmsPage['name'], 'type'=>'string'),
				array('fieldName'=>'content', 'value'=>$cmsPage['content'], 'type'=>'string'),
				array('fieldName'=>'meta_title', 'value'=>$cmsPage['meta_title'], 'type'=>'string'),
				array('fieldName'=>'meta_keywords', 'value'=>$cmsPage['meta_keywords'], 'type'=>'string'),
				array('fieldName'=>'meta_description', 'value'=>$cmsPage['meta_description'], 'type'=>'string'),
				array('fieldName'=>'status', 'value'=>$cmsPage['status'], 'type'=>'integer'),
				array('fieldName'=>'sort_order', 'value'=>$cmsPage['sort_order'], 'type'=>'integer')
			);
			if ($cmsPage['cms_page_id'] > 0) {
				$db->perform(TABLE_CMS_PAGE, $sql_data_array, 'UPDATE', 'cms_page_id = ' . $cmsPage['cms_page_id']);
			} else {
				$db->perform(TABLE_CMS_PAGE, $sql_data_array);
				$cmsPage['cms_page_id'] = $db->Insert_ID();
			}
			//Update Db Cache
			$cache->sql_cache_flush_cache();
			$message_stack->add_session('cms_page', 'CMS页面设置已保存。', 'success');
			redirect(href_link(FILENAME_CMS_PAGE, 'cms_page_id=' . $cmsPage['cms_page_id']));
		}
	break;
	case 'delete':
		$error = false;
		$selected = $_POST['selected'];
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add_session('cms_page', '删除CMS页面时出现安全错误。');
		}
		if ($error==true) {
			//nothing
		} else {
			foreach ($selected as $val) {
				$db->Execute("DELETE FROM " . TABLE_CMS_PAGE . " WHERE cms_page_id = " . (int)$val);
			}
			$message_stack->add_session('cms_page', 'CMS页面已删除。', 'success');
		}
		redirect(href_link(FILENAME_CMS_PAGE));
	break;
	case 'set_status':
		$db->Execute("UPDATE " . TABLE_CMS_PAGE . " SET status = IF(status = 1, 0, 1) WHERE cms_page_id = " . (int)$cms_page_id);
		redirect(href_link(FILENAME_CMS_PAGE));
	break;
	default:
		if ($cms_page_id > 0) {
			$sql = "SELECT cms_page_id, name, content, meta_title,
						   meta_keywords, meta_description, status,
						   sort_order
					FROM   " . TABLE_CMS_PAGE . "
					WHERE  cms_page_id = :cmsPageID";
			$sql = $db->bindVars($sql, ':cmsPageID', $cms_page_id, 'integer');
			$result = $db->Execute($sql);
			if ($result->RecordCount() > 0) {
				$cmsPage = array(
					'cms_page_id'      => $result->fields['cms_page_id'],
					'name'             => $result->fields['name'],
					'content'          => $result->fields['content'],
					'meta_title'       => $result->fields['meta_title'],
					'meta_keywords'    => $result->fields['meta_keywords'],
					'meta_description' => $result->fields['meta_description'],
					'status'           => $result->fields['status'],
					'sort_order'       => $result->fields['sort_order']
				);
			}
		} else {
			$sql = "SELECT cms_page_id, name,
						   status, sort_order,
						   viewed
					FROM   " . TABLE_CMS_PAGE . "
					ORDER BY sort_order";
			$result = $db->Execute($sql);
			$cmsPageList = array();
			while (!$result->EOF) {
				$cmsPageList[] = array(
					'cms_page_id' => $result->fields['cms_page_id'],
					'name'        => $result->fields['name'],
					'status'      => $result->fields['status'],
					'sort_order'  => $result->fields['sort_order'],
					'viewed'      => $result->fields['viewed']
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
<title>CMS页面设置</title>
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
    		<?php if ($message_stack->size('cms_page') > 0) echo $message_stack->output('cms_page'); ?>
    		<?php if ($action == 'new' || $action == 'save' || $cms_page_id > 0) { ?>
    			<form action="<?php echo href_link(FILENAME_CMS_PAGE, 'action=save'); ?>" method="post">
    			<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    				<input type="hidden" value="<?php echo isset($cmsPage['cms_page_id'])?$cmsPage['cms_page_id']:''; ?>" name="cms_page[cms_page_id]" />
    			</div>
    			<div class="page-title title-buttons">
	    			<h1>CMS页面设置</h1>
	    			<button type="submit" class="button"><span><span>保存</span></span></button>
    				<button type="button" class="button btn-cancel" onclick="setLocation('<?php echo href_link(FILENAME_CMS_PAGE); ?>');"><span><span>取消</span></span></button>
	    		</div>
	    		<table class="form-list">
				<tbody>
					<tr>
						<td class="label"><label for="cms_page-name">CMS页面名称  <span class="required">*</span></label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($cmsPage['name'])?$cmsPage['name']:''; ?>" name="cms_page[name]" id="cms_page-name" /></td>
					</tr>
					<tr>
						<td class="label">
							<label for="cms_page-content">CMS页面内容</label><br />
							首页路径 - {base_url}<br />
							模板路径 - {template_url}<br />
							网站域名 - {store_website}<br />
							网站邮箱 - {store_email}<br />
							网站电话 - {store_telephone}<br />
							网站语言 - {store_language}<br />
							客户邮箱 - {customer_email_address}<br />
							客户姓名 - {customer_name}
						</td>
						<td class="value"><textarea cols="15" rows="2" name="cms_page[content]" id="cms_page-content"><?php echo isset($cmsPage['content'])?htmlspecialchars($cmsPage['content']):''; ?></textarea></td>
					</tr>
					<tr>
						<td class="label"><label for="cms_page-meta_title">Meta标签标题</label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($cmsPage['meta_title'])?$cmsPage['meta_title']:''; ?>" name="cms_page[meta_title]" id="cms_page-meta_title" /></td>
					</tr>
					<tr>
						<td class="label"><label for="cms_page-meta_keywords">Meta标签关键词</label></td>
						<td class="value"><textarea cols="15" rows="2" name="cms_page[meta_keywords]" id="cms_page-meta_keywords"><?php echo isset($cmsPage['meta_keywords'])?$cmsPage['meta_keywords']:''; ?></textarea></td>
					</tr>
					<tr>
						<td class="label"><label for="cms_page-meta_description">Meta标签描述</label></td>
						<td class="value"><textarea cols="15" rows="2" name="cms_page[meta_description]" id="cms_page-meta_description"><?php echo isset($cmsPage['meta_description'])?$cmsPage['meta_description']:''; ?></textarea></td>
					</tr>
					<tr>
						<td class="label"><label for="cms_page-status">状态</label></td>
						<td class="value">
							<select name="cms_page[status]" id="cms_page-status">
								<?php foreach ($availabStatus as $_key=>$_val) { ?>
								<option<?php if (isset($cmsPage['status'])&&$cmsPage['status']==$_key) { ?> selected="selected"<?php } ?> value="<?php echo $_key; ?>"><?php echo $_val; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="label"><label for="cms_page-sort_order">排序</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($cmsPage['sort_order'])?$cmsPage['sort_order']:'0'; ?>" name="cms_page[sort_order]" id="cms_page-sort_order" /></td>
					</tr>
				</tbody>
    			</table>
    			</form>
    		<?php } else { ?>
				<form action="<?php echo href_link(FILENAME_CMS_PAGE, 'action=delete'); ?>" method="post">
	    		<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    			</div>
	    		<div class="page-title title-buttons">
	    			<h1>CMS页面设置</h1>
    				<button type="button" class="button button-new" onclick="setLocation('<?php echo href_link(FILENAME_CMS_PAGE, 'action=new'); ?>');"><span><span>新增</span></span></button>
    				<button type="submit" class="button" onclick="return confirm('删除或卸载后您将不能恢复，请确定要这么做吗？');"><span><span>删除</span></span></button>
	    		</div>
	    		<table class="data-table">
	    		<colgroup>
	    			<col width="10" />
	    			<col />
	    			<col width="60" />
	    			<col width="60" />
	    			<col width="40" />
	    			<col width="60" />
	    		</colgroup>
	    		<thead>
	    			<tr>
	    				<th><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></th>
	    				<th>CMS页面名称</th>
	    				<th>浏览量</th>
	    				<th class="a-center">状态</th>
	    				<th>排序</th>
	    				<th class="a-center">管理</th>
	    			</tr>
	    		</thead>
	    		<?php if (count($cmsPageList)>0) { ?>
	    		<tbody>
	    			<?php foreach ($cmsPageList as $val) { ?>
	    			<tr>
	    				<td><input type="checkbox" value="<?php echo $val['cms_page_id']; ?>" name="selected[]" /></td>
	    				<td><?php echo $val['name']; ?></td>
	    				<td><?php echo $val['viewed']; ?></td>
	    				<td class="a-center">[ <a title="点击 状态:<?php echo $val['status']==1?$availabStatus[0]:$availabStatus[1]; ?>" href="<?php echo href_link(FILENAME_CMS_PAGE, 'action=set_status&cms_page_id=' . $val['cms_page_id']); ?>"><?php echo $availabStatus[$val['status']]; ?></a> ]</td>
	    				<td><?php echo $val['sort_order']; ?></td>
	    				<td class="a-center">[ <a href="<?php echo href_link(FILENAME_CMS_PAGE, 'cms_page_id=' . $val['cms_page_id']); ?>">编辑</a> ]</td>
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