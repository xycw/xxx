<?php
// 获取域名
$domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$domainArr = explode('.', $domain);
if (count($domainArr) == 2) $domain = 'www.' . $domain;
define('E_HTTP_HOST', $domain);
define('E_MANAGER', 'web/zq-wsw01-admin');
// DB
define('DB_TYPE', 'mysql');
define('DB_PREFIX', '');
define('DB_CHARSET', 'utf8');
define('DB_SERVER', 'localhost');
define('DB_SERVER_USERNAME', 'root');
define('DB_SERVER_PASSWORD', 'root');
define('DB_DATABASE', 'easyshop');
define('DB_CACHE_METHOD', 'file');
// ADMIN
define('ADMIN_USERNAME', 'manager');
define('ADMIN_PASSWORD', '123');