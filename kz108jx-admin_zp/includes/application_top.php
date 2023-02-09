<?php
define('ROOT_PATH', dirname(dirname(str_replace('\\', '/', dirname(__FILE__)))) . '/');

// 自定义配置文件
require(ROOT_PATH . 'includes/config.php');

/**
 * 设置时区为东八区
 */
date_default_timezone_set('PRC');
/**
 * $_SERVER["HTTP_ACCEPT_LANGUAGE"] 获取当前浏览器语言
 */
define('HTTP_ACCEPT_LANGUAGE', $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
/**
 * microtime() 函数返回当前 Unix 时间戳和微秒数 
 */
define('PAGE_PARSE_START_TIME', microtime());

if (!isset($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];
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
require(DIR_FS_ADMIN_INCLUDES . 'filenames.php');
//引用数据库表名称常量
require(DIR_FS_CATALOG_INCLUDES . 'database_tables.php');
//创建$db数据库对象
require(DIR_FS_ADMIN_INIT_INCLUDES . 'init_database.php');
//创建数据库中的常量
require(DIR_FS_ADMIN_INIT_INCLUDES . 'init_db_config_read.php');
//启用页面压缩
require(DIR_FS_ADMIN_INIT_INCLUDES . 'init_gzip.php');
//引用常用方法
require(DIR_FS_ADMIN_FUNCTIONS . 'general.php');
//引用分类方法
require(DIR_FS_CATALOG_FUNCTIONS . 'category.php');
//引用订单方法
require(DIR_FS_CATALOG_FUNCTIONS . 'order.php');
//引用客户方法
require(DIR_FS_CATALOG_FUNCTIONS . 'customer.php');
//使用utf-8编码
header("Content-Type: text/html; charset=utf-8");
//初始化session
require(DIR_FS_ADMIN_INIT_INCLUDES . 'init_session.php');
//初始化信息提示
require(DIR_FS_ADMIN_INIT_INCLUDES . 'init_message_stack.php');
//系统用户
require(DIR_FS_ADMIN_INIT_INCLUDES . 'init_admin_auth.php');
