<?php
// 自定义配置文件
require('includes/config.php');


/**
 * 设置时区为东八区
 */
date_default_timezone_set('PRC');
/**
 * 获取当前浏览器语言
 */
define('HTTP_ACCEPT_LANGUAGE', isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ? $_SERVER["HTTP_ACCEPT_LANGUAGE"] : '');
/**
 * microtime() 函数返回当前 Unix 时间戳和微秒数 
 */
define('PAGE_PARSE_START_TIME', microtime());
/**
 * 加载配置文件
 */

if (file_exists('includes/configure.php')) {
    include('includes/configure.php');
} else {
	die('includes/configure.php not found');
}
/**
 * 当前是否为SSL请求
 */
$request_type = (((isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1')))
				||(isset($_SERVER['HTTP_X_FORWARDED_BY']) && strpos(strtoupper($_SERVER['HTTP_X_FORWARDED_BY']), 'SSL') !== false)
				||(isset($_SERVER['HTTP_X_FORWARDED_HOST']) && (strpos(strtoupper($_SERVER['HTTP_X_FORWARDED_HOST']), 'SSL') !== false
				||strpos(strtoupper($_SERVER['HTTP_X_FORWARDED_HOST']), str_replace('https://', '', HTTPS_SERVER)) !== false))
				||(isset($_SERVER['SCRIPT_URI']) && strtolower(substr($_SERVER['SCRIPT_URI'], 0, 6)) == 'https:')
				||(isset($_SERVER["HTTP_SSLSESSIONID"]) && $_SERVER["HTTP_SSLSESSIONID"] != '')
				||(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443')) ? 'SSL' : 'NONSSL';
/**
 * 系统初始化
 */
//引用页面名称常量
require(DIR_FS_CATALOG_INCLUDES . 'filenames.php');
//引用数据库表名称常量
require(DIR_FS_CATALOG_INCLUDES . 'database_tables.php');
//创建$db数据库对象
require(DIR_FS_CATALOG_INIT_INCLUDES . 'init_database.php');
//移动端判断和IP解析
require(DIR_FS_CATALOG_INIT_INCLUDES . 'init_mobile.php');
//创建数据库中的常量
require(DIR_FS_CATALOG_INIT_INCLUDES . 'init_db_config_read.php');
//启用页面压缩
require(DIR_FS_CATALOG_INIT_INCLUDES . 'init_gzip.php');
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
//初始化session
require(DIR_FS_CATALOG_INIT_INCLUDES . 'init_session.php');
//初始化SEOURL
require(DIR_FS_CATALOG_INIT_INCLUDES . 'init_seo_url.php');
//初始化get参数
require(DIR_FS_CATALOG_INIT_INCLUDES . 'init_sanitize.php');
//初始化分类目录
require(DIR_FS_CATALOG_INIT_INCLUDES . 'init_category_tree.php');
//加载当前选择的模板
require(DIR_FS_CATALOG_INIT_INCLUDES . 'init_template.php');
//初始化货币
require(DIR_FS_CATALOG_INIT_INCLUDES . 'init_currency.php');
//初始化翻译
require(DIR_FS_CATALOG_INIT_INCLUDES . 'init_translate.php');
//初始化信息提示
require(DIR_FS_CATALOG_INIT_INCLUDES . 'init_message_stack.php');
//初始化购物车
require(DIR_FS_CATALOG_INIT_INCLUDES . 'init_shopping_cart.php');
//初始化面包屑
require(DIR_FS_CATALOG_INIT_INCLUDES . 'init_breadcrumb.php');
//验证客户
require(DIR_FS_CATALOG_INIT_INCLUDES . 'init_customer_auth.php');
//浏览过的产品
require(DIR_FS_CATALOG_INIT_INCLUDES . 'init_recent_viewed.php');
/**
 * 得到顾客的IP
 */
if (!isset($_SESSION['customer_ip_address'])) {
	$_SESSION['customer_ip_address'] = get_ip_address();
}
// 获取顾客的来路
if (!isset($_SESSION['customer_http_referer'])) {
	if (isset($_SERVER['HTTP_REFERER'])) {
		if (strstr($_SERVER['HTTP_REFERER'], HTTP_SERVER) != false) {
			$_SESSION['customer_http_referer'] = str_replace(HTTP_SERVER, '', $_SERVER['HTTP_REFERER']);
		} else {
			$_SESSION['customer_http_referer'] = $_SERVER['HTTP_REFERER'];
		}
	} else {
		$_SESSION['customer_http_referer'] = '';
	}
}
// 获取顾客的浏览器内容
if (!isset($_SESSION['customer_http_user_agent'])) {
	$_SESSION['customer_http_user_agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
}
