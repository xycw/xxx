<?php
/**
 * 加载配置文件
 */
if (file_exists('includes/configure.php')) {
    include('includes/configure.php');
} else {
	die('includes/configure.php not found');
}
/**
 * 系统初始化
 */
//启用页面压缩
require(DIR_FS_ADMIN_INIT_INCLUDES . 'init_gzip.php');
//初始化session
require(DIR_FS_ADMIN_INIT_INCLUDES . 'init_session.php');

//初始化 
$border = 1;
$how = 4;
$w = $how*15;
$h = 24;
$fontsize = 6;
$alpha = "abcdefghjkmnpqrstuvwxyz";
$number = "23456789";
$captchacode = "";
srand((double)microtime()*1000000);

$img = imagecreate($w, $h);
//绘制基本框架 
$bgcolor = imagecolorallocate($img, 255, 255, 255);
imagefill($img, 0, 0, $bgcolor);
if ($border) {  
	$black = imagecolorallocate($img, 0, 0, 0);
	imagerectangle($img, 0, 0, $w-1, $h-1, $black);
}
 
//逐位产生随机字符
for ($i=0; $i<$how; $i++) {
	$alpha_or_number = mt_rand(0, 1);
	$str = $alpha_or_number ? $alpha : $number;
	$which = mt_rand(0, strlen($str)-1);
	$code = substr($str, $which, 1);
	$j = !$i ? 4 : $j+15;
	$color3 = imagecolorallocate($img, mt_rand(0,100), mt_rand(0,100), mt_rand(0,100));
	imagechar($img, $fontsize, $j, 3, $code, $color3);
	$captchacode .= $code;
}

//绘背景干扰线
for ($i=0; $i<10; $i++) {
	$color1 = imagecolorallocate($img, mt_rand(0,255), mt_rand(0,255), mt_rand(0,255));
	imagearc($img, mt_rand(-5,$w), mt_rand(-5,$h), mt_rand(20,300), mt_rand(20,200), 55, 44, $color1);
}

$_SESSION['captchacode'] = $captchacode;
header("Content-type: image/gif");
imagegif($img);
imagedestroy($img);
die ();
