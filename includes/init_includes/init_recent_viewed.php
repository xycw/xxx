<?php
/**
 * init_includes init_recent_viewed.php
 */
if (!isset($_SESSION['recent_viewed'])) $_SESSION['recent_viewed'] = array();
if ($current_page==FILENAME_PRODUCT
	&& isset($_GET['pID']) && not_null($_GET['pID'])) {
	array_unshift($_SESSION['recent_viewed'], $_GET['pID']);
	$_SESSION['recent_viewed'] = array_unique($_SESSION['recent_viewed']);
	if (count($_SESSION['recent_viewed'])>10) array_pop($_SESSION['recent_viewed']);
}
