<?php require('includes/application_top.php'); ?>
<?php
$languageFile = DIR_FS_CATALOG_INCLUDES . 'languages/' . STORE_LANGUAGE . '/translate.csv';
if (!file_exists($languageFile)) {
	$message_stack->add('language', STORE_LANGUAGE . '语言包不存在。');
} elseif(!($languageHandle = fopen($languageFile, 'r'))) {
	$message_stack->add('language', STORE_LANGUAGE . '语言包无法读取。');
}

$action = isset($_GET['action'])?$_GET['action']:'';
switch ($action) {
	case 'save':
		$error = false;
		$language = isset($_POST['language'])?$_POST['language']:array();
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add('language', '语言包保存时出现安全错误。');
		}

		$newLanguageFile = DIR_FS_CATALOG_INCLUDES . 'languages/' . STORE_LANGUAGE . '/translate_new.csv';
		if(!($newLanguageHandle = fopen($newLanguageFile, 'w'))) {
			$error = true;
			$message_stack->add('language', STORE_LANGUAGE . '语言包路径无写权限。');
		}

		if (!empty($language) && $error == false) {
			$i = 0;
			while ($data = fgetcsv($languageHandle)) {
				$data[1] = isset($language[$i])?$language[$i]:$data[1];
				fputcsv($newLanguageHandle, $data);
				$i++;
			}
			fclose($languageHandle);
			fclose($newLanguageHandle);
			unlink($languageFile);
			rename($newLanguageFile, $languageFile);
			$message_stack->add_session('language', STORE_LANGUAGE . '语言包保存成功。', 'success');
			redirect(href_link(FILENAME_LANGUAGE));
		}
	break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>语言包</title>
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
    		<?php if ($message_stack->size('language') > 0) echo $message_stack->output('language'); ?>
    			<form action="<?php echo href_link(FILENAME_LANGUAGE, 'action=save'); ?>" method="post">
    			<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    			</div>
    			<div class="page-title title-buttons">
	    			<h1>语言包</h1>
	    			<button type="submit" class="button"><span><span>保存</span></span></button>
	    		</div>
	    		<table class="form-list">
				<tbody>
				<?php $i = 0;while ($data = fgetcsv($languageHandle)) { ?>
				<tr>
					<td class="label"><label>原文(<?php echo $i; ?>)</label></td>
					<td class="value"><?php echo $data[0]; ?></td>
				</tr>
				<tr>
					<td class="label"><label>翻译(<?php echo $i; ?>)</label></td>
					<td class="value"><textarea name="language[<?php echo $i++; ?>]"><?php echo $data[1]; ?></textarea></td>
				</tr>
				<?php } ?>
				</tbody>
    			</table>
    			</form>
    		</div>
        </div>
        <?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
    </div>
</div>
</body>
</html>
<?php require('includes/application_bottom.php'); ?>