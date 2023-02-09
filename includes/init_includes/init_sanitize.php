<?php
/**
 * init_includes init_sanitize.php
 */
if (isset($_GET['main_page'])) $_GET['main_page'] = preg_replace('/[^0-9a-zA-Z_]/', '', $_GET['main_page']);
if (isset($_GET['pID'])) $_GET['pID'] = preg_replace('/[^0-9]/', '', $_GET['pID']);
if (isset($_GET['cID'])) $_GET['cID'] = preg_replace('/[^0-9]/', '', $_GET['cID']);
if (!isset($_GET['cID']) && isset($_GET['pID']) && $cID = get_product_cid($_GET['pID'])) $_GET['cID'] = $cID;
if (isset($_GET['aID'])) $_GET['aID'] = preg_replace('/[^0-9]/', '', $_GET['aID']);
if (isset($_GET['oID'])) $_GET['oID'] = preg_replace('/[^0-9]/', '', $_GET['oID']);

//We do some checks here to ensure $_GET['main_page'] has a sane value
if (!isset($_GET['main_page']) || !not_null($_GET['main_page'])) $_GET['main_page'] = 'index';
if (!is_dir(DIR_FS_CATALOG_MODULES .  'pages/' . $_GET['main_page'])) $_GET['main_page'] = 'page_not_found';

$current_page = $_GET['main_page'];
$page_directory = DIR_FS_CATALOG_MODULES . 'pages/' . $current_page;
$code_page_directory =  DIR_WS_CATALOG_INCLUDES . 'modules/pages/' . $current_page;
$this_is_home_page = $current_page == 'index'? true : false;
