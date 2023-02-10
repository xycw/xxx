<?php
error_reporting(E_ALL) && ini_set('display_errors', 'Off');
ini_set('error_log', 'php_errors.log');

require('includes/application_top.php');

/**
 * We now load header code for a given page. 
 * Page code is stored in includes/modules/pages/PAGE_NAME/directory 
 * 'header_php.php' files in that directory are loaded now.
 */

$directory_array = $template->get_template_part($page_directory, '/^header_php/');

foreach ($directory_array as $value) { 
	require($page_directory . '/' . $value);
}
/**
 * We now load the html_header.php file. This file contains code that would appear within the HTML <head></head> code 
 * it is overridable on a template and page basis. 
 * In that a custom template can define its own common/html_header.php file 
 */
var_dump($template->get_template_dir('html_header.php', DIR_WS_TEMPLATE, $current_page, 'common') . 'html_header.php');

require($template->get_template_dir('html_header.php', DIR_WS_TEMPLATE, $current_page, 'common') . 'html_header.php');

/**
 * Define the template that will govern the overall page layout, can be done on a page by page basis
 * or using a default template. The default template installed will be a standard 3 column layout. This
 * template also loads the page body code based on the variable $body_code.
 */
require($template->get_template_dir('tpl_main_page.php', DIR_WS_TEMPLATE, $current_page, 'common') . 'tpl_main_page.php');
?>
</html>
<?php
/**
 * Load general code run before page closes
 */
?>
<?php require('includes/application_bottom.php'); ?>