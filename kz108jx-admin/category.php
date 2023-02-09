<?php require('includes/application_top.php'); ?>
<?php
$category_id = isset($_GET['category_id'])?$_GET['category_id']:0;
$action = isset($_GET['action'])?$_GET['action']:'';
$availabStatus = array('0'=>'禁用', '1'=>'启用');
switch ($action) {
	case 'save':
		$error = false;
		$category = db_prepare_input($_POST['category']);
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add('category', '分类设置保存时出现安全错误。');
		}
		if (strlen($category['sku']) < 1) {
			$error = true;
			$message_stack->add('category', '分类型号不能为空。');
		} else {
			$sql = "SELECT COUNT(*) AS total FROM " . TABLE_CATEGORY . " WHERE sku = :sku AND category_id <> :categoryID";
			$sql = $db->bindVars($sql, ':sku', $category['sku'], 'string');
			$sql = $db->bindVars($sql, ':categoryID', isset($category['category_id'])?$category['category_id']:0, 'integer');
			$check_category = $db->Execute($sql);
			if ($check_category->fields['total'] > 0) {
				$error = true;
				$message_stack->add('category', '分类型号存在相同。');
			}
		}
		if (strlen($category['name']) < 1) {
			$error = true;
			$message_stack->add('category', '分类名称不能为空。');
		}
		if (!array_key_exists($category['status'], $availabStatus)) $category['status'] = 0;
		if ($error==true) {
			//nothing
		} else {
			$sql_data_array = array(
				array('fieldName'=>'sku', 'value'=>$category['sku'], 'type'=>'string'),
				array('fieldName'=>'template_dir', 'value'=>$category['template_dir'], 'type'=>'string'),
				array('fieldName'=>'name', 'value'=>$category['name'], 'type'=>'string'),
				array('fieldName'=>'description', 'value'=>$category['description'], 'type'=>'string'),
				array('fieldName'=>'image', 'value'=>$category['image'], 'type'=>'string'),
				array('fieldName'=>'url', 'value'=>$category['url'], 'type'=>'string'),
				array('fieldName'=>'parent_id', 'value'=>$category['parent_id'], 'type'=>'integer'),
				array('fieldName'=>'meta_title', 'value'=>$category['meta_title'], 'type'=>'string'),
				array('fieldName'=>'meta_keywords', 'value'=>$category['meta_keywords'], 'type'=>'string'),
				array('fieldName'=>'meta_description', 'value'=>$category['meta_description'], 'type'=>'string'),
				array('fieldName'=>'status', 'value'=>$category['status'], 'type'=>'integer'),
				array('fieldName'=>'top', 'value'=>$category['top'], 'type'=>'integer'),
				array('fieldName'=>'sort_order', 'value'=>$category['sort_order'], 'type'=>'integer')
			);
			if ($category['category_id'] > 0) {
				$sql_data_array[] = array('fieldName'=>'last_modified', 'value'=>'NOW()', 'type'=>'noquotestring');
				$db->perform(TABLE_CATEGORY, $sql_data_array, 'UPDATE', 'category_id = ' . $category['category_id']);
			} else {
				$sql_data_array[] = array('fieldName'=>'date_added', 'value'=>'NOW()', 'type'=>'noquotestring');
				$db->perform(TABLE_CATEGORY, $sql_data_array);
				$category['category_id'] = $db->Insert_ID();
			}
			$db->Execute("UPDATE " . TABLE_PRODUCT . " SET status = " . (int)$category['status'] . " WHERE master_category_id = " . (int)$category['category_id']);
			$message_stack->add_session('category', '分类设置已保存。', 'success');
			redirect(href_link(FILENAME_CATEGORY, 'category_id=' . $category['category_id']));
		}
	break;
	case 'delete':
		$error = false;
		$selected = $_POST['selected'];
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add_session('category', '删除分类时出现安全错误。');
		}
		if ($error==true) {
			
		} else {
			foreach ($selected as $val) {
				$db->Execute("DELETE FROM " . TABLE_PRODUCT_TO_CATEGORY . " WHERE category_id = " . (int)$val);
				$db->Execute("DELETE FROM " . TABLE_PRODUCT . " WHERE master_category_id = " . (int)$val);
				$db->Execute("DELETE FROM " . TABLE_CATEGORY . " WHERE category_id = " . (int)$val);
			}
			$message_stack->add_session('category', '分类已删除。', 'success');
		}
		redirect(href_link(FILENAME_CATEGORY));
	break;
	default:
		if ($category_id > 0) {
			$sql = "SELECT category_id, sku, template_dir, name, description, image, url,
						   parent_id, meta_title, meta_keywords, meta_description,
						   status, top, sort_order
					FROM   " . TABLE_CATEGORY . "
					WHERE  category_id = :categoryID";
			$sql = $db->bindVars($sql, ':categoryID', $category_id, 'integer');
			$result = $db->Execute($sql);
			if ($result->RecordCount() > 0) {
				$category = array(
					'category_id' => $result->fields['category_id'],
					'sku' => $result->fields['sku'],
					'template_dir' => $result->fields['template_dir'],
					'name' => $result->fields['name'],
					'description' => $result->fields['description'],
					'image' => $result->fields['image'],
					'url' => $result->fields['url'],
					'parent_id' => $result->fields['parent_id'],
					'meta_title' => $result->fields['meta_title'],
					'meta_keywords' => $result->fields['meta_keywords'],
					'meta_description' => $result->fields['meta_description'],
					'status' => $result->fields['status'],
					'top' => $result->fields['top'],
					'sort_order' => $result->fields['sort_order']
				);
			}
		}
	break;
}

require(DIR_FS_ADMIN_CLASSES . 'category_tree.php');
$category_tree = new category_tree();
$availabCategoryList = $category_tree->getTree();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>分类管理</title>
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
    		<?php if ($message_stack->size('category') > 0) echo $message_stack->output('category'); ?>
    		<?php if ($action == 'new' || $action == 'save' || $category_id > 0) { ?>
    			<form action="<?php echo href_link(FILENAME_CATEGORY, 'action=save'); ?>" method="post">
    			<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    				<input type="hidden" value="<?php echo isset($category['category_id'])?$category['category_id']:''; ?>" name="category[category_id]" />
    			</div>
    			<div class="page-title title-buttons">
    				<h1>分类管理</h1>
    				<button type="submit" class="button"><span><span>保存</span></span></button>
					<button type="button" class="button btn-cancel" onclick="setLocation('<?php echo href_link(FILENAME_CATEGORY); ?>');"><span><span>取消</span></span></button>
    			</div>
    			<table class="form-list">
				<tbody>
					<tr>
						<td class="label"><label for="category-sku">型号 <span class="required">*</span></label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($category['sku'])?$category['sku']:''; ?>" name="category[sku]" id="category-sku" /></td>
					</tr>
					<tr>
						<td class="label"><label for="category-name">分类名称 <span class="required">*</span></label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($category['name'])?$category['name']:''; ?>" name="category[name]" id="category-name" /></td>
					</tr>
					<?php if (isset($category['parent_id']) && $category['parent_id'] == '0') { ?>
					<tr>
						<td class="label"><label for="category-template_dir">分类模板 </label></td>
						<td class="value"><?php echo cfg_pull_down('category[template_dir]', get_templates(), (isset($category['template_dir'])?$category['template_dir']:'')); ?></td>
					</tr>
					<?php } ?>
					<tr>
						<td class="label"><label for="category-description">分类描述</label></td>
						<td class="value"><textarea cols="15" rows="2" name="category[description]" id="category-description"><?php echo isset($category['description'])?$category['description']:''; ?></textarea></td>
					</tr>
					<tr>
						<td class="label"><label for="category-image">分类图片</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($category['image'])?$category['image']:''; ?>" name="category[image]" id="category-image" /></td>
					</tr>
					<tr>
						<td class="label"><label for="category-url">分类路径</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($category['url'])?$category['url']:''; ?>" name="category[url]" id="category-url" /></td>
					</tr>
					<tr>
						<td class="label"><label for="category-parent_id">分类级别</label></td>
						<td class="value">
							<select name="category[parent_id]" id="category-parent_id">
								<option value="0"> --- 无 --- </option>
								<?php foreach ($availabCategoryList as $key => $val) { ?>
								<option<?php if (isset($category['parent_id'])&&$category['parent_id']==$key) { ?> selected="selected"<?php } ?> value="<?php echo $key; ?>"><?php echo $val['name']; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="label"><label for="category-meta_title">Meta标签标题</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($category['meta_title'])?$category['meta_title']:''; ?>" name="category[meta_title]" id="category-meta_title" /></td>
					</tr>
					<tr>
						<td class="label"><label for="category-meta_keywords">Meta标签关键词</label></td>
						<td class="value"><textarea cols="15" rows="2" name="category[meta_keywords]" id="category-meta_keywords"><?php echo isset($category['meta_keywords'])?$category['meta_keywords']:''; ?></textarea></td>
					</tr>
					<tr>
						<td class="label"><label for="category-meta_description">Meta标签描述</label></td>
						<td class="value"><textarea cols="15" rows="2" name="category[meta_description]" id="category-meta_description"><?php echo isset($category['meta_description'])?$category['meta_description']:''; ?></textarea></td>
					</tr>
					<tr>
						<td class="label"><label for="category-status">状态</label></td>
						<td class="value">
							<select name="category[status]" id="category-status">
								<?php foreach ($availabStatus as $_key=>$_val) { ?>
								<option<?php if (isset($category['status'])&&$category['status']==$_key) { ?> selected="selected"<?php } ?> value="<?php echo $_key; ?>"><?php echo $_val; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
                    <tr>
						<td class="label"><label for="category-top">头部菜单</label></td>
						<td class="value">
                        	<select name="category[top]" id="category-top">
								<?php foreach ($availabStatus as $_key=>$_val) { ?>
								<option<?php if (isset($category['top'])&&$category['top']==$_key) { ?> selected="selected"<?php } ?> value="<?php echo $_key; ?>"><?php echo $_val; ?></option>
								<?php } ?>
							</select>
                        </td>
					</tr>
					<tr>
						<td class="label"><label for="category-sort_order">排序</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($category['sort_order'])?$category['sort_order']:'0'; ?>" name="category[sort_order]" id="category-sort_order" /></td>
					</tr>
				</tbody>
    			</table>
    			</form>
    		<?php } else { ?>
    			<form action="<?php echo href_link(FILENAME_CATEGORY, 'action=delete'); ?>" method="post">
	    		<div class="no-display">	
	    			<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
	    		</div>
	    		<div class="page-title title-buttons">
	    			<h1>分类列表</h1>
					<button type="button" class="button button-new" onclick="setLocation('<?php echo href_link(FILENAME_CATEGORY, 'action=new'); ?>');"><span><span>新增</span></span></button>
					<button type="submit" class="button" onclick="return confirm('删除或卸载后您将不能恢复，请确定要这么做吗？');"><span><span>删除</span></span></button>
	    		</div>
	    		<table class="data-table">
	    		<colgroup>
	    			<col width="10" />
	    			<col width="30" />
	    			<col />
	    			<col width="40" />
	    			<col width="60" />
	    			<col width="60" />
	    		</colgroup>
	    		<thead>
	    			<tr>
	    				<th><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></th>
	    				<th>ID</th>
	    				<th>分类名称</th>
	    				<th>排序</th>
                        <th class="a-center">头部菜单</th>
	    				<th class="a-center">状态</th>
	    				<th class="a-center">管理</th>
	    			</tr>
	    		</thead>
	    		<?php if (count($availabCategoryList)>0) { ?>
	    		<tbody>
	    			<?php foreach ($availabCategoryList as $key => $val) { ?>
	    			<tr>
	    				<td><input type="checkbox" value="<?php echo $key; ?>" name="selected[]" /></td>
	    				<td><?php echo $key; ?></td>
	    				<td><?php echo $val['name']; ?></td>
	    				<td><?php echo $val['sort_order']; ?></td>
                        <td class="a-center"><?php echo $availabStatus[$val['top']]; ?></td>
	    				<td class="a-center"><?php echo $availabStatus[$val['status']]; ?></td>
	    				<td class="a-center">[ <a href="<?php echo href_link(FILENAME_CATEGORY, 'category_id=' . $key); ?>">编辑</a> ]</td>
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