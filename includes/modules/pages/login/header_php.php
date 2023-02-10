<?php
/**
 * login header_php.php
 */
if (isset($_SESSION['customer_id'])) {
	redirect(href_link(FILENAME_ACCOUNT, '', 'SSL'));
}
if (isset($_POST['action']) && $_POST['action'] == 'process') {
	$error = false;
	$username = db_prepare_input($_POST['username']);
	$password = db_prepare_input($_POST['password']);
	$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
	if ($securityToken != $_SESSION['securityToken']) {
		$error = true;
		$message_stack->add('login', __('There was a security error.'));
	}
	$sql = "SELECT customer_id, firstname, lastname, email_address,
				   password, newsletter, billing_address_id, shipping_address_id
			FROM   " . TABLE_CUSTOMER . "
			WHERE  email_address = :emailAddress
			LIMIT  1";
	$sql = $db->bindVars($sql, ':emailAddress', $username, 'string');
	$result = $db->Execute($sql);
	if (!($result->RecordCount() > 0)) {
		$error = true;
      	$message_stack->add('login', __('Invalid login or password.'));
	} elseif (!validate_password($password, $result->fields['password'])) {
		$error = true;
      	$message_stack->add('login', __('Invalid login or password.'));
	}
	if ($error==true) {
		//nothing
	} else {
		$_SESSION['customer_id'] = $result->fields['customer_id'];
		$_SESSION['customer_firstname'] = $result->fields['firstname'];
		$_SESSION['customer_lastname'] = $result->fields['lastname'];
		$_SESSION['customer_email_address'] = $result->fields['email_address'];
		$_SESSION['customer_newsletter'] = $result->fields['newsletter'];
		$_SESSION['customer_billing_address_id'] = $result->fields['billing_address_id'];
		$_SESSION['customer_shipping_address_id'] = $result->fields['shipping_address_id'];
		redirect(href_link(FILENAME_ACCOUNT, '', 'SSL'));
	}
}
//Breadcrumb
$breadcrumb->add(__('Login or Create an Account'), 'root');
