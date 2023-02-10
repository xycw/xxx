<?php
/**
 * init_includes init_template.php
 */
require(DIR_FS_CATALOG_CLASSES . 'template.php');
$template_dir = 'mobile/default';
$_isMobileTemplate = false;
if (true == $_isMobile && is_dir(DIR_WS_CATALOG_TEMPLATES . $template_dir)) {
	$template = new template('mobile/default');
	if (is_dir(DIR_WS_CATALOG_TEMPLATES . 'mobile/' . MOBILE_STORE_TEMPLATE_DIR)) {
		$template_dir = 'mobile/' . MOBILE_STORE_TEMPLATE_DIR;
	}
	$_isMobileTemplate = true;
	$_nameMaxLength    = MOBILE_PRODUCT_NAME_MAX_LENGTH;
	$_isShowSaveOff    = MOBILE_PRODUCT_SHOW_SAVE_OFF;
} else {
	$template_dir = 'default';
	$template = new template('default');
	if ('true' == ENABLE_CATEGORY_TEMPLATE && ($current_page == FILENAME_CATEGORY || $current_page == FILENAME_PRODUCT)) {
		$template_dir = $category_tree->getTemplateDir($_GET['cID']);
		if (!is_dir(DIR_WS_CATALOG_TEMPLATES . $template_dir)) {
			$template_dir = 'default';
		}
	}
	if (IS_ZP == '1') {
		if ($template_dir == 'default' && is_dir(DIR_WS_CATALOG_TEMPLATES . STORE_TEMPLATE_DIR_ZP)) {
			$template_dir = STORE_TEMPLATE_DIR_ZP;
		}
	} else {
		if ($template_dir == 'default' && is_dir(DIR_WS_CATALOG_TEMPLATES . STORE_TEMPLATE_DIR)) {
			$template_dir = STORE_TEMPLATE_DIR;
		}
	}

	$_nameMaxLength = PRODUCT_NAME_MAX_LENGTH;
	$_isShowSaveOff = PRODUCT_SHOW_SAVE_OFF;
}

define('DIR_WS_TEMPLATE', DIR_WS_CATALOG_TEMPLATES . $template_dir . '/');
define('DIR_WS_TEMPLATE_IMAGES', DIR_WS_TEMPLATE . 'images/');
define('DIR_WS_TEMPLATE_CSS', DIR_WS_TEMPLATE . 'css/');
define('DIR_WS_TEMPLATE_JS', DIR_WS_TEMPLATE . 'js/');

header("Content-Type: text/html; charset=utf-8");
