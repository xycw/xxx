<?php require('includes/application_top.php'); ?>
<?php
$action = isset($_GET['action'])?$_GET['action']:'';
switch ($action) {
	case 'loginPost':
		$error = false;
		$username = db_prepare_input($_POST['username']);
		$password = db_prepare_input($_POST['password']);
		$captcha  = db_prepare_input($_POST['captcha']);
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add('login', '登录时出现安全错误。');
		}
		if ($username != ADMIN_USERNAME || $password != ADMIN_PASSWORD) {
			$error = true;
			$message_stack->add('login', '用户名或者密码错误。');
		}
		if (strtolower($captcha) != $_SESSION['captchacode']) {
			$error = true;
			$message_stack->add('login', '验证码错误。');
		}
		if ($error==true) {
			//nothing
		} else {
			$_SESSION['admin'] = true;
		}
	break;
}
if (isset($_SESSION['admin'])) {
	redirect(href_link(FILENAME_INDEX));
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>欢迎来到商店后台管理系统</title>
<meta name="robot" content="noindex, nofollow" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
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
		<div class="main-container">
    		<div class="main" style="background:url(images/login.png) no-repeat 50px 30px; border:1px solid #ccc; padding:50px 10px 30px 180px; width:220px; margin:100px auto;">
    		<?php if ($message_stack->size('login') > 0) echo $message_stack->output('login'); ?>
			<form action="<?php echo href_link(FILENAME_LOGIN, 'action=loginPost'); ?>" method="post" style="width:220px;">
				<div class="no-display">
					<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
					<input type="hidden" value="loginPost" name="action" />
				</div>
				<div>
					用户名：
					<br />
					<input type="text" class="input-text" value="<?php echo isset($username)?$username:''; ?>" name="username" />
					<br />
					<br />
					密码：
					<br />
					<input type="password" class="input-text" value="" name="password" />
					<br />
					<br />
					验证码：
					<br />
					<input type="text" class="input-text" value="" name="captcha" />
					<img src="<?php echo href_link(FILENAME_CAPTCHA); ?>" onclick="this.src=this.src+'?';" alt="验证码" title="看不清楚?点击换一张" />
					<br />
					<br />
					<p style="text-align:right;"><button type="submit" class="button"><span><span>登录</span></span></button></p>
				</div>
			</form>
    		</div>
    	</div>
    	<?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
	</div>
</div>
</body>
</html>
<?php require('includes/application_bottom.php'); ?>