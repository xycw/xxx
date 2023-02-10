<?php
/**
 * init_includes init_mobile.php
 */
$_isMobile = false;
require(DIR_FS_CATALOG_CLASSES . 'Mobile_Detect.php');
$mobileDetect = new Mobile_Detect();
if ('true' == ENABLE_MOBILE
	&& $mobileDetect->isMobile()) {
	$_isMobile = true;
}
