<?php
/**
 * init_includes init_database.php
 */
require(DIR_FS_CATALOG_CLASSES . 'db/' . DB_TYPE  . '/query_factory.php');
$db = new queryFactory();
if (!$db->connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE, false)) {
	die("database is error");
}
require(DIR_FS_CATALOG_CLASSES . 'db/cache.php');
$cache = new cache();
