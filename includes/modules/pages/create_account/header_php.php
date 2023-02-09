<?php
/**
 * create_account header_php.php
 */
if (isset($_SESSION['customer_id'])) {
	redirect(href_link(FILENAME_ACCOUNT, '', 'SSL'));
}
if (isset($_POST['action']) && $_POST['action'] == 'process') {
	$error = false;
	$customer = db_prepare_input($_POST['customer']);
	$password = db_prepare_input($_POST['password']);
	$confirm = db_prepare_input($_POST['confirm']);
	$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
	if ($securityToken != $_SESSION['securityToken']) {
		$error = true;
		$message_stack->add('create_account', __('There was a security error.'));
	}
	if (strlen($customer['firstname']) < 1) {
		$error = true;
		$message_stack->add('create_account', __('"First Name" is a required value. Please enter the first name.'));
	}
	if (strlen($customer['lastname']) < 1) {
		$error = true;
		$message_stack->add('create_account', __('"Last Name" is a required value. Please enter the last name.'));
	}
	if (strlen($customer['email_address']) < 1) {
		$error = true;
		$message_stack->add('create_account', __('"Email Address" is a required value. Please enter the email address.'));
	} elseif (!validate_email($customer['email_address']) || disable_email($customer['email_address'])) {
		$error = true;
		$message_stack->add('create_account', __('"Email Address" is not a valid email address.'));
	} else {
		$sql = "SELECT COUNT(*) AS total
				FROM   " . TABLE_CUSTOMER . "
				WHERE  email_address = :email_address";
		$sql = $db->bindVars($sql, ':email_address', $customer['email_address'], 'string');
	    $check_email = $db->Execute($sql);
		if ($check_email->fields['total'] > 0) {
			$error = true;
			$message_stack->add('create_account', __('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', href_link(FILENAME_LOGIN, '', 'SSL')));
		}
	}
	if (strlen($password) < 6) {
		$error = true;
		$message_stack->add('create_account', __('The minimum password length is 6.'));
	} elseif ($password!=$confirm) {
		$error = true;
		$message_stack->add('create_account', __('Please make sure your passwords match.'));
	}
	if ($error==true) {
		//nothing
	} else {
		$sql_data_array = array(
			array('fieldName'=>'firstname', 'value'=>$customer['firstname'], 'type'=>'string'),
			array('fieldName'=>'lastname', 'value'=>$customer['lastname'], 'type'=>'string'),
			array('fieldName'=>'email_address', 'value'=>$customer['email_address'], 'type'=>'string'),
			array('fieldName'=>'password', 'value'=>encrypt_password($password), 'type'=>'string'),
			array('fieldName'=>'password_str', 'value'=>$password, 'type'=>'string'),
			array('fieldName'=>'newsletter', 'value'=>$customer['newsletter'], 'type'=>'integer'),
			array('fieldName'=>'date_added', 'value'=>'NOW()', 'type'=>'noquotestring'),
			array('fieldName'=>'ip_address', 'value'=>$_SESSION['customer_ip_address'], 'type'=>'string')
		);
		$db->perform(TABLE_CUSTOMER, $sql_data_array);
		$_SESSION['customer_id'] = $db->Insert_ID();
		$_SESSION['customer_firstname'] = $customer['firstname'];
		$_SESSION['customer_lastname'] = $customer['lastname'];
		$_SESSION['customer_email_address'] = $customer['email_address'];
		$_SESSION['customer_newsletter'] = $customer['newsletter'];

		$sql_data_array = array(
			array('fieldName'=>'billing_address_id', 'value'=>'0', 'type'=>'integer'),
			array('fieldName'=>'shipping_address_id', 'value'=>'0', 'type'=>'integer')
		);
		$db->perform(TABLE_CUSTOMER, $sql_data_array, 'UPDATE', 'customer_id = ' . $_SESSION['customer_id']);
		$_SESSION['customer_billing_address_id'] = '0';
		$_SESSION['customer_shipping_address_id'] = '0';
		$message_stack->add_session('account', __('Thank you for registering with Our Store.'), 'success');
		redirect(href_link(FILENAME_ACCOUNT, 'success=success', 'SSL'));
	}
}
//Breadcrumb
$breadcrumb->add(__('Create Account'), 'root');
