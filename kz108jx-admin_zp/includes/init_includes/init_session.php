<?php
/**
 * init_includes init_session.php
 */
session_save_path(DIR_FS_CATALOG_CACHE);
session_start();
setcookie(session_name(), session_id(), time() + 3600, "/");
if (!isset($_SESSION['securityToken'])) {
	$_SESSION['securityToken'] = md5(uniqid(rand(), true));
}
