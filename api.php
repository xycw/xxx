<?php
/**
 * 加载配置文件
 */
if (file_exists('includes/configure.php')) {
    include('includes/configure.php');
	
	if (file_exists('includes/config.php')) include('includes/config.php');
} else {
	die('includes/configure.php not found');
}
/**
 * 系统初始化
 */
//引用页面名称常量
require(DIR_FS_CATALOG_INCLUDES . 'filenames.php');
//引用数据库表名称常量
require(DIR_FS_CATALOG_INCLUDES . 'database_tables.php');
//创建$db数据库对象
require(DIR_FS_CATALOG_INIT_INCLUDES . 'init_database.php');
//创建数据库中的常量
require(DIR_FS_CATALOG_INIT_INCLUDES . 'init_db_config_read.php');
//引用常用方法
require(DIR_FS_CATALOG_FUNCTIONS . 'general.php');
//引用分类方法
require(DIR_FS_CATALOG_FUNCTIONS . 'category.php');
//引用产品方法
require(DIR_FS_CATALOG_FUNCTIONS . 'product.php');
//引用用户方法
require(DIR_FS_CATALOG_FUNCTIONS . 'customer.php');
//引用优惠券方法
require(DIR_FS_CATALOG_FUNCTIONS . 'coupon.php');
//引用订单方法
require(DIR_FS_CATALOG_FUNCTIONS . 'order.php');
//允许操作的IP集合
$ip_array = array(
	'127.0.0.1',
	'47.90.0.40'
);
if (isset($_GET['token']) && $_GET['token'] == OA_EMAIL_API_TOKEN) {
	//nothing
} elseif (!in_array(get_ip_address(), $ip_array)) {
	die();
}
//操作
switch ($_GET['type']) {
	case 'getOrder':
		require('api/order/get.php');
	break;
	case 'getIpHistory':
		require('api/ipHistory/get.php');
	break;
	case 'updateOrder':
		require('api/order/update.php');
	break;
	case 'checkWebsite':
		header('Content-Type:text/html; charset=utf-8');
		echo 'ok';
	break;
	case 'myOrder':
		require('api/payment/myorder.php');
	break;
}
die ();
