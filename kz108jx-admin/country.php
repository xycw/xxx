<?php require('includes/application_top.php'); ?>
<?php
$country_id = isset($_GET['country_id'])?$_GET['country_id']:0;
$action = isset($_GET['action'])?$_GET['action']:'';
switch ($action) {
	case 'save':
		$error = false;
		$country = db_prepare_input($_POST['country']);
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add('country', '国家设置保存时出现安全错误。');
		}
		if (strlen($country['name']) < 1) {
			$error = true;
			$message_stack->add('country', '国家名称不能为空。');
		}
		if ($error==true) {
			//nothing
		} else {
			$sql_data_array = array(
				array('fieldName'=>'name', 'value'=>$country['name'], 'type'=>'string'),
				array('fieldName'=>'iso_code_2', 'value'=>$country['iso_code_2'], 'type'=>'string'),
				array('fieldName'=>'iso_code_3', 'value'=>$country['iso_code_3'], 'type'=>'string')
			);
			if ($country['country_id'] > 0) {
				$db->perform(TABLE_COUNTRY, $sql_data_array, 'UPDATE', 'country_id = ' . $country['country_id']);
			} else {
				$db->perform(TABLE_COUNTRY, $sql_data_array);
				$country['country_id'] = $db->Insert_ID();
			}
			$message_stack->add_session('country', '国家设置已保存。', 'success');
			redirect(href_link(FILENAME_COUNTRY, 'country_id=' . $country['country_id']));
		}
	break;
	case 'delete':
		$error = false;
		$selected = $_POST['selected'];
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add_session('country', '删除国家时出现安全错误。');
		}
		if ($error==true) {
			//nothing
		} else {
			foreach ($selected as $val) {
				$db->Execute("DELETE FROM " . TABLE_REGION . " WHERE country_id = " . (int)$val);
				$db->Execute("DELETE FROM " . TABLE_COUNTRY . " WHERE country_id = " . (int)$val);
			}
			$message_stack->add_session('country', '国家已删除。', 'success');
		}
		redirect(href_link(FILENAME_COUNTRY));
	break;
	default:
		if ($country_id > 0) {
			$sql = "SELECT country_id, name,
						   iso_code_2, iso_code_3
					FROM   " . TABLE_COUNTRY . "
					WHERE  country_id = :country_id";
			$sql = $db->bindVars($sql, ':country_id', $country_id, 'integer');
			$result = $db->Execute($sql);
			if ($result->RecordCount() > 0) {
				$country = array(
					'country_id' => $result->fields['country_id'],
					'name'       => $result->fields['name'],
					'iso_code_2' => $result->fields['iso_code_2'],
					'iso_code_3' => $result->fields['iso_code_3']
				);
			}
		} else {
			$sql = "SELECT COUNT(*) AS total FROM " . TABLE_COUNTRY;
			$result = $db->Execute($sql);
			$pagerConfig['total'] = $result->fields['total'];
			require(DIR_FS_ADMIN_CLASSES . 'pager.php');
			$pager = new pager($pagerConfig);
			$sql = "SELECT country_id, name,
						   iso_code_2, iso_code_3
					FROM   " . TABLE_COUNTRY . "
					ORDER BY name";
			$result = $db->Execute($sql, $pager->getLimitSql());
			$countryList = array();
			while (!$result->EOF) {
				$countryList[] = array(
					'country_id' => $result->fields['country_id'],
					'name'       => $result->fields['name'],
					'iso_code_2' => $result->fields['iso_code_2'],
					'iso_code_3' => $result->fields['iso_code_3']
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
<title>国家设置</title>
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
    		<?php if ($message_stack->size('country') > 0) echo $message_stack->output('country'); ?>
    		<?php if ($action=='new' || $action == 'save' || $country_id > 0) { ?>
    			<form action="<?php echo href_link(FILENAME_COUNTRY, 'action=save'); ?>" method="post">
    			<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    				<input type="hidden" value="<?php echo isset($country['country_id'])?$country['country_id']:''; ?>" name="country[country_id]" />
    			</div>
    			<div class="page-title title-buttons">
	    			<h1>国家设置</h1>
	    			<button type="submit" class="button"><span><span>保存</span></span></button>
    				<button type="button" class="button btn-cancel" onclick="setLocation('<?php echo href_link(FILENAME_COUNTRY); ?>');"><span><span>取消</span></span></button>
	    		</div>
	    		<table class="form-list">
				<tbody>
					<tr>
						<td class="label"><label for="country-name">国家名称  <span class="required">*</span></label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($country['name'])?$country['name']:''; ?>" name="country[name]" id="country-name" /></td>
					</tr>
					<tr>
						<td class="label"><label for="country-iso_code_2">ISO代码(2)</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($country['iso_code_2'])?$country['iso_code_2']:''; ?>" name="country[iso_code_2]" id="country-iso_code_2" /></td>
					</tr>
					<tr>
						<td class="label"><label for="country-iso_code_3">ISO代码(3)</label></td>
						<td class="value"><input type="text" class="input-text" value="<?php echo isset($country['iso_code_3'])?$country['iso_code_3']:''; ?>" name="country[iso_code_3]" id="country-iso_code_3" /></td>
					</tr>
				</tbody>
    			</table>
    			</form>
    		<?php } else { ?>
				<form action="<?php echo href_link(FILENAME_COUNTRY, 'action=delete'); ?>" method="post">
	    		<div class="no-display">	
	    			<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
	    		</div>
	    		<div class="page-title title-buttons">
	    			<h1>国家</h1>
    				<button type="button" class="button button-new" onclick="setLocation('<?php echo href_link(FILENAME_COUNTRY, 'action=new'); ?>');"><span><span>新增</span></span></button>
    				<button type="submit" class="button" onclick="return confirm('删除或卸载后您将不能恢复，请确定要这么做吗？');"><span><span>删除</span></span></button>
	    		</div>
	    		<?php require(DIR_FS_ADMIN_INCLUDES . 'pager.php'); ?>
	    		<table class="data-table">
	    		<colgroup>
	    			<col width="10" />
	    			<col />
	    			<col width="80" />
	    			<col width="80" />
	    			<col width="60" />
	    		</colgroup>
	    		<thead>
	    			<tr>
	    				<th><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></th>
	    				<th>国家名称</th>
	    				<th>ISO代码(3)</th>
	    				<th>ISO代码(2)</th>
	    				<th class="a-center">管理</th>
	    			</tr>
	    		</thead>
	    		<?php if (count($countryList)>0) { ?>
	    		<tbody>
	    			<?php foreach ($countryList as $val) { ?>
	    			<tr>
	    				<td><input type="checkbox" value="<?php echo $val['country_id']; ?>" name="selected[]" /></td>
	    				<td><?php echo $val['name']; ?></td>
	    				<td><?php echo $val['iso_code_3']; ?></td>
	    				<td><?php echo $val['iso_code_2']; ?></td>
	    				<td class="a-center">[ <a href="<?php echo href_link(FILENAME_COUNTRY, 'country_id=' . $val['country_id']); ?>">编辑</a> ]</td>
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