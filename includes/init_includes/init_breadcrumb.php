<?php
/**
 * init_includes init_breadcrumb.php
 */
require(DIR_FS_CATALOG_CLASSES . 'breadcrumb.php');
$breadcrumb = new breadcrumb();
$breadcrumb->add(__('Home'), 'home', href_link(FILENAME_INDEX));
