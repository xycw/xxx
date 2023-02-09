<?php require('includes/application_top.php'); ?>
<?php
$news_id = isset($_GET['news_id'])?$_GET['news_id']:0;
$action = isset($_GET['action'])?$_GET['action']:'';
switch ($action) {
	case 'save':
		$error = false;
		$news = db_prepare_input($_POST['news']);
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if (strlen($news['name']) < 1) {
			$error = true;
			$message_stack->add('news', '新闻页面名称不能为空。');
		} else {
			$sql = "SELECT COUNT(*) AS total FROM news WHERE name = :name AND news_id <> :newsID";
			$sql = $db->bindVars($sql, ':name', $news['name'], 'string');
			$sql = $db->bindVars($sql, ':newsID', isset($news['news_id'])?$news['news_id']:0, 'integer');
			$check_news = $db->Execute($sql);
			if ($check_news->fields['total'] > 0) {
				$error = true;
				$message_stack->add('news', '新闻页面名称存在相同。');
			}
		}
		if ($error==true) {
			//nothing
		} else {
			$sql_data_array = array(
				array('fieldName'=>'name', 'value'=>$news['name'], 'type'=>'string'),
				array('fieldName'=>'content', 'value'=>$news['content'], 'type'=>'string'),
				array('fieldName'=>'product_ids', 'value'=>$news['product_ids'], 'type'=>'string'),
				array('fieldName'=>'product_skus', 'value'=>$news['product_skus'], 'type'=>'string'),
				array('fieldName'=>'meta_title', 'value'=>$news['meta_title'], 'type'=>'string'),
				array('fieldName'=>'meta_keywords', 'value'=>$news['meta_keywords'], 'type'=>'string'),
				array('fieldName'=>'meta_description', 'value'=>$news['meta_description'], 'type'=>'string')
			);
			if ($news['news_id'] > 0) {
				$db->perform('news', $sql_data_array, 'UPDATE', 'news_id = ' . $news['news_id']);
			} else {
				$db->perform('news', $sql_data_array);
				$news['news_id'] = $db->Insert_ID();
			}
			//Update Db Cache
			$cache->sql_cache_flush_cache();
			$message_stack->add_session('news', '新闻页面设置已保存。', 'success');
			redirect(href_link(FILENAME_NEWS, 'news_id=' . $news['news_id']));
		}
	break;
	case 'delete':
		$error = false;
		$selected = $_POST['selected'];
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add_session('news', '删除新闻页面时出现安全错误。');
		}
		if ($error==true) {
			//nothing
		} else {
			foreach ($selected as $val) {
				$db->Execute("DELETE FROM news WHERE news_id = " . (int)$val);
			}
			$message_stack->add_session('news', '新闻页面已删除。', 'success');
		}
		redirect(href_link(FILENAME_NEWS));
	break;
	default:
		if ($news_id > 0) {
			$sql = "SELECT news_id, name, content, product_ids, product_skus,
						   meta_title, meta_keywords, meta_description
					FROM   news
					WHERE  news_id = :newsID";
			$sql = $db->bindVars($sql, ':newsID', $news_id, 'integer');
			$result = $db->Execute($sql);
			if ($result->RecordCount() > 0) {
				$news = array(
					'news_id'          => $result->fields['news_id'],
					'name'             => $result->fields['name'],
					'content'          => $result->fields['content'],
					'product_ids'       => $result->fields['product_ids'],
					'product_skus'     => $result->fields['product_skus'],
					'meta_title'       => $result->fields['meta_title'],
					'meta_keywords'    => $result->fields['meta_keywords'],
					'meta_description' => $result->fields['meta_description']
				);
			}
		} else {
			$sql = "SELECT news_id, name
					FROM   news
					ORDER BY news_id DESC";
			$result = $db->Execute($sql);
			$newsList = array();
			while (!$result->EOF) {
				$newsList[] = array(
					'news_id' => $result->fields['news_id'],
					'name'    => $result->fields['name']
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
<title>新闻页面设置</title>
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
    		<?php if ($message_stack->size('news') > 0) echo $message_stack->output('news'); ?>
    		<?php if ($action == 'new' || $action == 'save' || $news_id > 0) { ?>
    			<form action="<?php echo href_link(FILENAME_NEWS, 'action=save'); ?>" method="post">
    			<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    				<input type="hidden" value="<?php echo isset($news['news_id'])?$news['news_id']:''; ?>" name="news[news_id]" />
    			</div>
    			<div class="page-title title-buttons">
	    			<h1>新闻页面设置</h1>
	    			<button type="submit" class="button"><span><span>保存</span></span></button>
    				<button type="button" class="button btn-cancel" onclick="setLocation('<?php echo href_link(FILENAME_NEWS); ?>');"><span><span>取消</span></span></button>
	    		</div>
	    		<table class="form-list">
				<tbody>
					<tr>
						<td class="label"><label for="news-name">新闻页面名称  <span class="required">*</span></label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($news['name'])?$news['name']:''; ?>" name="news[name]" id="news-name" /></td>
					</tr>
					<tr>
						<td class="label"><label for="news-content">新闻页面内容</label></td>
						<td class="value"><textarea cols="15" rows="2" name="news[content]" id="news-content"><?php echo isset($news['content'])?$news['content']:''; ?></textarea></td>
					</tr>
					<tr>
						<td class="label"><label for="news-product_ids">产品IDS</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($news['product_ids'])?$news['product_ids']:''; ?>" name="news[product_ids]" id="news-product_ids" /></td>
					</tr>
					<tr>
						<td class="label"><label for="news-product_skus">产品SKUS</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($news['product_skus'])?$news['product_skus']:''; ?>" name="news[product_skus]" id="news-product_skus" /></td>
					</tr>
					<tr>
						<td class="label"><label for="news-meta_title">Meta标签标题</label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($news['meta_title'])?$news['meta_title']:''; ?>" name="news[meta_title]" id="news-meta_title" /></td>
					</tr>
					<tr>
						<td class="label"><label for="news-meta_keywords">Meta标签关键词</label></td>
						<td class="value"><textarea cols="15" rows="2" name="news[meta_keywords]" id="news-meta_keywords"><?php echo isset($news['meta_keywords'])?$news['meta_keywords']:''; ?></textarea></td>
					</tr>
					<tr>
						<td class="label"><label for="news-meta_description">Meta标签描述</label></td>
						<td class="value"><textarea cols="15" rows="2" name="news[meta_description]" id="news-meta_description"><?php echo isset($news['meta_description'])?$news['meta_description']:''; ?></textarea></td>
					</tr>
				</tbody>
    			</table>
    			</form>
    		<?php } else { ?>
				<form action="<?php echo href_link(FILENAME_NEWS, 'action=delete'); ?>" method="post">
	    		<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    			</div>
	    		<div class="page-title title-buttons">
	    			<h1>新闻页面设置</h1>
    				<button type="button" class="button button-new" onclick="setLocation('<?php echo href_link(FILENAME_NEWS, 'action=new'); ?>');"><span><span>新增</span></span></button>
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
	    				<th>新闻页面名称</th>
	    				<th class="a-center">管理</th>
	    			</tr>
	    		</thead>
	    		<?php if (count($newsList)>0) { ?>
	    		<tbody>
	    			<?php foreach ($newsList as $val) { ?>
	    			<tr>
	    				<td><input type="checkbox" value="<?php echo $val['news_id']; ?>" name="selected[]" /></td>
	    				<td><?php echo $val['name']; ?></td>
	    				<td class="a-center">[ <a href="<?php echo href_link(FILENAME_NEWS, 'news_id=' . $val['news_id']); ?>">编辑</a> ]</td>
	    			</tr>
	    			<?php $result->MoveNext(); ?>
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