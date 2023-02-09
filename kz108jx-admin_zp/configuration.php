<?php require('includes/application_top.php'); ?>
<?php
$gID = (isset($_GET['gID'])) ? $_GET['gID'] : 1;
if (isset($_GET['action']) && $_GET['action'] == 'save') {
	$error = false;
	$configuration = db_prepare_input($_POST['configuration']);
	$configuration_group_id = db_prepare_input($_POST['configuration_group_id']);
	$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
	if ($securityToken != $_SESSION['securityToken']) {
		$error = true;
		$message_stack->add_session('configuration', '当前设置保存时出现安全错误。');
	}
	if ($error==true) {
		//nothing
	} else {
		foreach ($configuration as $key => $val) {
			$sql_data_array = array(
				array('fieldName'=>'configuration_value', 'value'=>$val, 'type'=>'string'),
				array('fieldName'=>'last_modified ', 'value'=>'NOW()', 'type'=>'noquotestring')
			);
			
			$where = 'configuration_key = :configuration_key AND configuration_group_id = :configuration_group_id';
			$where = $db->bindVars($where, ':configuration_key', $key, 'string');
			$where = $db->bindVars($where, ':configuration_group_id', $configuration_group_id, 'integer');
			$db->perform(TABLE_CONFIGURATION, $sql_data_array, 'UPDATE', $where);
		}
		//Update Db Cache
		$cache->sql_cache_flush_cache();
		$message_stack->add_session('configuration', '当前配置已保存。', 'success');
	}
	redirect(href_link(FILENAME_CONFIGURATION, 'gID='. (int)$configuration_group_id));
}

$sql = "SELECT configuration_group_title FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_id = " . (int)$gID;
$cgInfo = $db->Execute($sql);
$sql = "SELECT configuration_id, configuration_title,
			   configuration_key, configuration_value, function
		FROM   " . TABLE_CONFIGURATION . "
		WHERE  configuration_group_id = '" . (int)$gID . "'
		ORDER BY sort_order";
$cInfo = $db->Execute($sql);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>EasyShop后台管理</title>
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
    		<form action="<?php echo href_link(FILENAME_CONFIGURATION, 'gID='. (int)$gID . "&action=save"); ?>" method="post">
    		<div class="no-display">
    			<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    			<input type="hidden" value="<?php echo $gID; ?>" name="configuration_group_id" />
    		</div>
    		<div class="page-title title-buttons">
    			<h1><?php echo $cgInfo->fields['configuration_group_title']; ?></h1>
    			<button type="submit" class="button"><span><span>保存</span></span></button>
    		</div>
    		<?php if ($message_stack->size('configuration') > 0) echo $message_stack->output('configuration'); ?>
    		<table class="form-list">
    		<tbody>
    			<?php while (!$cInfo->EOF) { ?>
    			<tr>
    				<td class="label"><?php echo $cInfo->fields['configuration_title']; ?></td>
    				<td class="value">
    				<?php if ($cInfo->fields['function']) { ?>
    					<?php eval('echo ' . $cInfo->fields['function'] . '"' . $cInfo->fields['configuration_key'] . '", "' . htmlspecialchars($cInfo->fields['configuration_value']) . '");');?>
    				<?php } else {?>
    					<input type="text" class="input-text" value="<?php echo $cInfo->fields['configuration_value']; ?>" name="configuration[<?php echo $cInfo->fields['configuration_key']; ?>]" />
    				<?php } ?>
    				</td>
    			</tr>
    			<?php $cInfo->MoveNext(); ?>
    			<?php } ?>
    		</tbody>
    		</table>
    		</form>
        </div>
        <?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
    </div>
</div>
</body>
</html>
<?php require('includes/application_bottom.php'); ?>