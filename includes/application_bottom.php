<?php
/**
 * 关闭数据库链接以及SESSION
 */
$db->close();
session_write_close();
/**
 * microtime() 函数返回当前 Unix 时间戳和微秒数 
 */
define('PAGE_PARSE_END_TIME', microtime());
