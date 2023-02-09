<?php require('includes/application_top.php'); ?>
<?php
$region_id = isset($_GET['region_id'])?$_GET['region_id']:0;
$action = isset($_GET['action'])?$_GET['action']:'';
switch ($action) {
	case 'save':
		$error = false;
		$region = db_prepare_input($_POST['region']);
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add('region', '省份/地区设置保存时出现安全错误。');
		}
		if (strlen($region['code']) < 1) {
			$error = true;
			$message_stack->add('region', '省份/地区名称不能为空。');
		}
		if (strlen($region['name']) < 1) {
			$error = true;
			$message_stack->add('region', '省份/地区代码不能为空。');
		}
		if ($error==true) {
			//nothing
		} else {
			$sql_data_array = array(
				array('fieldName'=>'country_id', 'value'=>$region['country_id'], 'type'=>'integer'),
				array('fieldName'=>'code', 'value'=>$region['code'], 'type'=>'string'),
				array('fieldName'=>'name', 'value'=>$region['name'], 'type'=>'string')
			);
			if ($region['region_id'] > 0) {
				$db->perform(TABLE_REGION, $sql_data_array, 'UPDATE', 'region_id = ' . $region['region_id']);
			} else {
				$db->perform(TABLE_REGION, $sql_data_array);
				$region['region_id'] = $db->Insert_ID();
			}
			$message_stack->add_session('region', '省份/地区设置已保存。', 'success');
			redirect(href_link(FILENAME_REGION, 'region_id=' . $region['region_id']));
		}
	break;
	case 'delete':
		$error = false;
		$selected = $_POST['selected'];
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add_session('region', '删除省份/地区时出现安全错误。');
		}
		if ($error==true) {
			//nothing
		} else {
			foreach ($selected as $val) {
				$db->Execute("DELETE FROM " . TABLE_REGION . " WHERE region_id = " . (int)$val);
			}
			$message_stack->add_session('region', '省份/地区已删除。', 'success');
		}
		redirect(href_link(FILENAME_REGION));
	break;
	default:
		if ($region_id > 0) {
			$sql = "SELECT region_id, country_id,
						   code, name
					FROM   " . TABLE_REGION . "
					WHERE  region_id = :region_id";
			$sql = $db->bindVars($sql, ':region_id', $region_id, 'integer');
			$result = $db->Execute($sql);
			if ($result->RecordCount() > 0) {
				$region = array(
					'region_id' => $result->fields['region_id'],
					'country_id' => $result->fields['country_id'],
					'code' => $result->fields['code'],
					'name' => $result->fields['name']
				);
			}
		} else {
			$sql = "SELECT COUNT(*) AS total FROM " . TABLE_REGION;
			$result = $db->Execute($sql);
			$pagerConfig['total'] = $result->fields['total'];
			require(DIR_FS_ADMIN_CLASSES . 'pager.php');
			$pager = new pager($pagerConfig);
			$sql = "SELECT r.region_id, r.code, r.name,
						   c.name AS country_name
					FROM   " . TABLE_REGION . " r, " . TABLE_COUNTRY . " c
					WHERE  r.country_id = c.country_id
					ORDER BY r.country_id, r.name";
			$result = $db->Execute($sql, $pager->getLimitSql());
			$regionList = array();
			while (!$result->EOF) {
				$regionList[] = array(
					'region_id' => $result->fields['region_id'],
					'country_name' => $result->fields['country_name'],
					'code' => $result->fields['code'],
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
<title>省份/地区设置</title>
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
    		<?php if ($message_stack->size('region') > 0) echo $message_stack->output('region'); ?>
    		<?php if ($action == 'new' || $action == 'save' || $region_id > 0) { ?>
    			<form action="<?php echo href_link(FILENAME_REGION, 'action=save'); ?>" method="post">
    			<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    				<input type="hidden" value="<?php echo isset($region['region_id'])?$region['region_id']:''; ?>" name="region[region_id]" />
    			</div>
    			<div class="page-title title-buttons">
	    			<h1>省份/地区设置</h1>
	    			<button type="submit" class="button"><span><span>保存</span></span></button>
    				<button type="button" class="button btn-cancel" onclick="setLocation('<?php echo href_link(FILENAME_REGION); ?>');"><span><span>取消</span></span></button>
	    		</div>
	    		<table class="form-list">
				<tbody>
					<tr>
						<td class="label"><label for="region-country_id">所属国家 <span class="required">*</span></label></td>
						<td class="value">
							<?php $_availabCountry = get_countries(); ?>
							<select class="required-entry" name="region[country_id]" id="region-country_id">
								<option value="">请选择国家</option>
	    						<?php foreach ($_availabCountry as $key => $val) { ?>
	    						<option<?php if (isset($region['country_id'])&&$region['country_id']==$key) { ?> selected="selected"<?php } ?> value="<?php echo $key; ?>"><?php echo $val; ?></option>
	    						<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="label"><label for="region-code">省份/地区代码  <span class="required">*</span></label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($region['code'])?$region['code']:''; ?>" name="region[code]" id="region-code" /></td>
					</tr>
					<tr>
						<td class="label"><label for="region-name">省份/地区名称  <span class="required">*</span></label></td>
						<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($region['name'])?$region['name']:''; ?>" name="region[name]" id="region-name" /></td>
					</tr>
				</tbody>
    			</table>
    			</form>
    		<?php } else { ?>
				<form action="<?php echo href_link(FILENAME_REGION, 'action=delete'); ?>" method="post">
	    		<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    			</div>
	    		<div class="page-title title-buttons">
	    			<h1>省份/地区设置</h1>
    				<button type="button" class="button button-new" onclick="setLocation('<?php echo href_link(FILENAME_REGION, 'action=new'); ?>');"><span><span>新增</span></span></button>
    				<button type="submit" class="button" onclick="return confirm('删除或卸载后您将不能恢复，请确定要这么做吗？');"><span><span>删除</span></span></button>
	    		</div>
	    		<?php require(DIR_FS_ADMIN_INCLUDES . 'pager.php'); ?>
	    		<table class="data-table">
	    		<colgroup>
	    			<col width="10" />
	    			<col />
	    			<col />
	    			<col width="100" />
	    			<col width="60" />
	    		</colgroup>
	    		<thead>
	    			<tr>
	    				<th><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></th>
	    				<th>国家名称</th>
	    				<th>省份/地区名称</th>
	    				<th>省份/地区代码</th>
	    				<th class="a-center">管理</th>
	    			</tr>
	    		</thead>
	    		<tbody>
	    			<?php foreach ($regionList as $val) { ?>
	    			<tr>
	    				<td><input type="checkbox" value="<?php echo $val['region_id']; ?>" name="selected[]" /></td>
	    				<td><?php echo $val['country_name']; ?></td>
	    				<td><?php echo $val['name']; ?></td>
	    				<td><?php echo $val['code']; ?></td>
	    				<td class="a-center">[ <a href="<?php echo href_link(FILENAME_REGION, 'region_id=' . $val['region_id']); ?>">编辑</a> ]</td>
	    			</tr>
	    			<?php $result->MoveNext(); ?>
	    			<?php } ?>
	    		</tbody>
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