<?php
define('HTTP_SERVER', 'http://' . E_HTTP_HOST);
define('HTTPS_SERVER', 'http://' . E_HTTP_HOST);
define('ENABLE_SSL', 'false');

//WS
define('DIR_WS_CATALOG', '/');
define('DIR_WS_CATALOG_IMAGES', HTTP_SERVER . DIR_WS_CATALOG . 'images/');
define('DIR_WS_CATALOG_IMAGES_CACHE', DIR_WS_CATALOG_IMAGES . 'cache/');
define('DIR_WS_ADMIN', DIR_WS_CATALOG . E_MANAGER . '_zp/');
define('DIR_WS_ADMIN_IMAGES', 'images/');

//FS
define('ROOT_IMAGE', str_replace('\\', '/', dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/image/');
define('DIR_FS_CATALOG', str_replace('\\', '/', dirname(dirname(dirname(__FILE__)))) . '/');
define('DIR_FS_CATALOG_IMAGES', DIR_FS_CATALOG . 'images/');
define('DIR_FS_CATALOG_IMAGES_CACHE', DIR_FS_CATALOG_IMAGES . 'cache/');
define('DIR_FS_CATALOG_CACHE', DIR_FS_CATALOG . 'cache/');
define('DIR_FS_CATALOG_INCLUDES', DIR_FS_CATALOG . 'includes/');
define('DIR_FS_CATALOG_CLASSES', DIR_FS_CATALOG_INCLUDES . 'classes/');
define('DIR_FS_CATALOG_FUNCTIONS', DIR_FS_CATALOG_INCLUDES . 'functions/');
define('DIR_FS_CATALOG_TEMPLATES', DIR_FS_CATALOG_INCLUDES . 'templates/');
define('DIR_FS_ADMIN', str_replace('\\', '/', dirname(dirname(__FILE__))) . '/');
define('DIR_FS_ADMIN_INCLUDES', DIR_FS_ADMIN . 'includes/');
define('DIR_FS_ADMIN_CLASSES', DIR_FS_ADMIN_INCLUDES . 'classes/');
define('DIR_FS_ADMIN_FUNCTIONS', DIR_FS_ADMIN_INCLUDES . 'functions/');
define('DIR_FS_ADMIN_INIT_INCLUDES', DIR_FS_ADMIN_INCLUDES . 'init_includes/');
