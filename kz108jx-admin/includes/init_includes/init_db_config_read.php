<?php
/**
 * init_includes init_db_config_read.php
 */
$configuration = $db->Execute("SELECT configuration_key, configuration_value FROM " . TABLE_CONFIGURATION);
while (!$configuration->EOF) {
	if (!defined(strtoupper($configuration->fields['configuration_key']))) {
		define(strtoupper($configuration->fields['configuration_key']), $configuration->fields['configuration_value']);
	}
	$configuration->MoveNext();
}

define('DB_SUFFIX', '');
define('IS_ZP', '0');
define('TABLE_CATEGORY', DB_PREFIX . 'category' . DB_SUFFIX);
define('TABLE_CMS_PAGE', DB_PREFIX . 'cms_page' . DB_SUFFIX);
define('TABLE_PRODUCT', DB_PREFIX . 'product' . DB_SUFFIX);
define('TABLE_PRODUCT_ATTRIBUTE', DB_PREFIX . 'product_attribute' . DB_SUFFIX);
define('TABLE_PRODUCT_OPTION', DB_PREFIX . 'product_option' . DB_SUFFIX);
define('TABLE_PRODUCT_OPTION_VALUE', DB_PREFIX . 'product_option_value' . DB_SUFFIX);
define('TABLE_PRODUCT_REVIEW', DB_PREFIX . 'product_review' . DB_SUFFIX);
define('TABLE_PRODUCT_TO_CATEGORY', DB_PREFIX . 'product_to_category' . DB_SUFFIX);
