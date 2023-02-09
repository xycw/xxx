<?php
/**
 * page_not_found header_php.php
 */
if (strripos($_SERVER['SERVER_SOFTWARE'], 'Apache')) {
	// tell the browser that this page is showing as a result of a 404 error:
	header('HTTP/1.1 404 Not Found');
}
//Breadcrumb
$breadcrumb->add(__('Page Not Found'), 'root');
