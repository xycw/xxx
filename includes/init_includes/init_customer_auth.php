<?php
/**
 * init_includes init_customer_auth.php
 */
if (isset($_SESSION['customer_id'])) {
	if (!validate_customer($_SESSION['customer_id'])) {
		unset($_SESSION['customer_id']);
	}
}
