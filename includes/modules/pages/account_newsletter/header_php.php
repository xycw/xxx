<?php
/**
 * account_newsletter header_php.php
 */
if (!isset($_SESSION['customer_id'])) {
	redirect(href_link(FILENAME_LOGIN, '', 'SSL'));
}
if (isset($_POST['action']) && $_POST['action'] == 'process') {
	$error = false;
	$newsletter = isset($_POST['newsletter'])?db_prepare_input($_POST['newsletter']):0;
	$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
	if ($securityToken != $_SESSION['securityToken']) {
		$error = true;
		$message_stack->add('account_newsletter', __('There was a security error.'));
	}
	if ($error==true) {
	//nothing
	} else {
		$sql_data_array = array(
			array('fieldName'=>'newsletter', 'value'=>$newsletter, 'type'=>'string'),
			array('fieldName'=>'last_modified', 'value'=>'NOW()', 'type'=>'noquotestring')
		);
		$_SESSION['customer_newsletter'] = $newsletter;
		$db->perform(TABLE_CUSTOMER, $sql_data_array, 'UPDATE', 'customer_id = ' . (int) $_SESSION['customer_id']);
		$message_stack->add_session('account', __('The subscription has been saved.'), 'success');
		redirect(href_link(FILENAME_ACCOUNT, '', 'SSL'));
	}
}
//Breadcrumb
$breadcrumb->add(__('My Dashboard'), 'sub', href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(__('Newsletter Subscriptions'), 'root');
