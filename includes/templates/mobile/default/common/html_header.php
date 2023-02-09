<?php require(DIR_FS_CATALOG_MODULES . 'meta.php'); ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo STORE_LANGUAGE; ?>" lang="<?php echo STORE_LANGUAGE; ?>">
<head>
<?php if (defined('FACEBOOK_ID') && strlen(FACEBOOK_ID) > 0) { ?>
	<!-- Facebook Pixel Code -->
	<script>
		!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');

		fbq('init', '<?php echo FACEBOOK_ID; ?>');
		fbq('track', 'PageView');
		<?php switch ($current_page) {
			case FILENAME_PRODUCT:
				echo "fbq('track', 'ViewContent');";
				break;
			case FILENAME_ACCOUNT:
				echo isset($_GET['success']) ? "fbq('track', 'CompleteRegistration');" : "";
				break;
			case 'checkout_result':
				if ($orderInfo['order_status_id'] == 3
					&& !isset($_SESSION['facebook_purchase'])) {
					echo "fbq('track', 'Purchase', {value: '" . $currencies->get_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value']) . "', currency: '" . $orderInfo['currency']['code'] . "'});";
				}
				break;
		} ?>
	</script>
	<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo FACEBOOK_ID; ?>&ev=PageView&noscript=1" /></noscript>
	<!-- End Facebook Pixel Code -->
<?php } ?>
	<title><?php echo $metaInfo['title']; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="keywords" content="<?php echo $metaInfo['keywords']; ?>" />
	<meta name="description" content="<?php echo $metaInfo['description']; ?>" />
	<meta http-equiv="imagetoolbar" content="no" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui">
    <meta name="handheldfriendly" content="true">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">
	<link rel="icon" href="<?php echo DIR_WS_TEMPLATE; ?>favicon.ico" type="image/x-icon" />
	<link rel="shortcut icon" href="<?php echo DIR_WS_TEMPLATE; ?>favicon.ico" type="image/x-icon" />
	<base href="<?php echo (($request_type=='SSL')?HTTPS_SERVER:HTTP_SERVER) . DIR_WS_CATALOG; ?>" />
	<link href="<?php echo $metaInfo['canonical']; ?>" rel="canonical" />
<?php // 加载当前模板css文件夹中所有名称为style*.css的样式文件 ?>
<?php $directory_css = $template->get_template_dir('.css', DIR_WS_TEMPLATE, $current_page, 'css'); ?>
<?php $directory_array = $template->get_template_part($directory_css, '/^style/', '.css'); ?>
<?php foreach ($directory_array as $_file) { ?>
	<link rel="stylesheet" type="text/css" href="<?php echo $directory_css . $_file; ?>" />
<?php } ?>
<?php //加载当前页面modules/pages/(当前页面)文件夹中所有名称为style*.css的样式文件 ?>
<?php $directory_array = $template->get_template_part($page_directory, '/^style/', '.css'); ?>
<?php foreach ($directory_array as $_file) { ?>
	<link rel="stylesheet" type="text/css" href="<?php echo $code_page_directory . '/' . $_file; ?>" />
<?php } ?>
	<script type="text/javascript" src="js/jquery/jquery.js"></script>
	<script type="text/javascript" src="js/jquery/validate.js"></script>
<?php if (STORE_LANGUAGE!='en') { ?>
    <script type="text/javascript" src="js/jquery/validate/messages_<?php echo STORE_LANGUAGE; ?>.js"></script>
<?php } ?>
<?php //加载当前模板js文件夹中所有名称为jscript_*.js的脚本文件 ?>
<?php $directory_array = $template->get_template_part(DIR_WS_TEMPLATE_JS, '/^jscript_/', '.js'); ?>
<?php foreach ($directory_array as $_file) { ?>
	<script type="text/javascript" src="<?php echo DIR_WS_TEMPLATE_JS . $_file; ?>"></script>
<?php } ?>
<?php //加载当前页面modules/pages/(当前页面)文件夹中所有名称为jscript_*.js的脚本文件 ?>
<?php $directory_array = $template->get_template_part($page_directory, '/^jscript_/', '.js'); ?>
<?php foreach ($directory_array as $_file) { ?>
	<script type="text/javascript" src="<?php echo $code_page_directory . '/' . $_file; ?>"></script>
<?php } ?>
<?php //加载当前页面modules/pages/(当前页面)文件夹中所有名称为jscript_*.php的脚本文件 ?>
<?php $directory_array = $template->get_template_part($page_directory, '/^jscript_/', '.php'); ?>
<?php foreach ($directory_array as $_file) { ?>
	<?php require($page_directory . '/' . $_file); ?>
<?php } ?>
</head>
