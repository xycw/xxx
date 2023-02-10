<?php
/**
 * account_edit header_php.php
 */
if (!isset($_SESSION['customer_id'])) {
	redirect(href_link(FILENAME_LOGIN, '', 'SSL'));
}
if (isset($_POST['action']) && $_POST['action'] == 'process') {
	$error = false;
	$customer = db_prepare_input($_POST['customer']);
	$change_password = isset($_POST['change_password'])?db_prepare_input($_POST['change_password']):0;
	$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
	if ($securityToken != $_SESSION['securityToken']) {
		$error = true;
		$message_stack->add('account_edit', __('There was a security error.'));
	}
	if (strlen($customer['firstname']) < 1) {
		$error = true;
		$message_stack->add('account_edit', __('"First Name" is a required value. Please enter the first name.'));
	}
	if (strlen($customer['lastname']) < 1) {
		$error = true;
		$message_stack->add('account_edit', __('"Last Name" is a required value. Please enter the last name.'));
	}
	if (strlen($customer['email_address']) < 1) {
		$error = true;
		$message_stack->add('account_edit', __('"Email Address" is a required value. Please enter the email address.'));
	} elseif (!validate_email($customer['email_address']) || disable_email($customer['email_address'])) {
		$error = true;
		$message_stack->add('account_edit', __('"Email Address" is not a valid email address.'));
	} else {
		$sql = "SELECT COUNT(*) AS total
				FROM   " . TABLE_CUSTOMER . "
				WHERE  email_address = :email_address
				AND    customer_id <> :customerID";
		$sql = $db->bindVars($sql, ':email_address', $customer['email_address'], 'string');
		$sql = $db->bindVars($sql, ':customerID', $_SESSION['customer_id'], 'integer');
	    $check_email = $db->Execute($sql);
		if ($check_email->fields['total'] > 0) {
			$error = true;
			$message_stack->add('account_edit', __('There is already an account with this email address.'));
		}
	}
	if ($change_password==1) {
		$current_password = db_prepare_input($_POST['current_password']);
		$password = db_prepare_input($_POST['password']);
		$confirm = db_prepare_input($_POST['confirm']);
		if (strlen($current_password) < 6) {
			$error = true;
			$message_stack->add('account_edit', __('The minimum password length is 6.'));
		} else {
			$check_customer_query = "SELECT password FROM " . TABLE_CUSTOMER . " WHERE customer_id = :customerID";
			$check_customer_query = $db->bindVars($check_customer_query, ':customerID', $_SESSION['customer_id'], 'integer');
			$check_customer = $db->Execute($check_customer_query);
			if (!validate_password($current_password, $check_customer->fields['password'])) {
				$error = true;
				$message_stack->add('account_edit', 'Invalid current password');
			}
		}
		if (strlen($password) < 6) {
			$error = true;
			$message_stack->add('account_edit', __('The minimum password length is 6.'));
		} elseif ($password!=$confirm) {
			$error = true;
			$message_stack->add('account_edit', __('Please make sure your passwords match.'));
		}
	}
	if ($error==true) {
	//nothing
	} else {
		$sql_data_array = array(
			array('fieldName'=>'firstname', 'value'=>$customer['firstname'], 'type'=>'string'),
			array('fieldName'=>'lastname', 'value'=>$customer['lastname'], 'type'=>'string'),
			array('fieldName'=>'email_address', 'value'=>$customer['email_address'], 'type'=>'string'),
			array('fieldName'=>'last_modified', 'value'=>'NOW()', 'type'=>'noquotestring')
		);
		$_SESSION['customer_firstname'] = $customer['firstname'];
		$_SESSION['customer_lastname'] = $customer['lastname'];
		$_SESSION['customer_email_address'] = $customer['email_address'];
		if ($change_password==1) $sql_data_array[] = array('fieldName'=>'password', 'value'=>encrypt_password($password), 'type'=>'string');
		$db->perform(TABLE_CUSTOMER, $sql_data_array, 'UPDATE', 'customer_id = ' . (int)$_SESSION['customer_id']);
		$message_stack->add_session('account', __('The account information has been saved.'), 'success');
		redirect(href_link(FILENAME_ACCOUNT, '', 'SSL'));
	}
} else {
	$sql = "SELECT firstname, lastname,
				   email_address
			FROM   " . TABLE_CUSTOMER . "
			WHERE  status = 1
			AND    customer_id = :customerID
			LIMIT  1";
	$sql = $db->bindVars($sql, ':customerID', $_SESSION['customer_id'], 'integer');
	$result = $db->Execute($sql);
	if ($result->RecordCount() > 0) {
		$customer = array(
			'firstname'     => $result->fields['firstname'],
			'lastname'      => $result->fields['lastname'],
			'email_address' => $result->fields['email_address']
		);
	}
}
//Breadcrumb
$breadcrumb->add(__('My Dashboard'), 'sub', href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(__('Account Information'), 'root');
