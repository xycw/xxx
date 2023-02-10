<?php
/**
 * logout header_php.php
 */
session_destroy();
//Breadcrumb
$breadcrumb->add(__('You are now logged out'), 'root');
