<?php
/**
 * address_new header_php.php
 */
if (!isset($_SESSION['customer_id'])) {
	redirect(href_link(FILENAME_LOGIN, '', 'SSL'));
}
if (isset($_POST['action']) && $_POST['action'] == 'process') {
	$error = false;
	$address = db_prepare_input($_POST['address']);
	$default_billing = isset($_POST['default_billing'])?db_prepare_input($_POST['default_billing']):0;
	$default_shipping = isset($_POST['default_shipping'])?db_prepare_input($_POST['default_shipping']):0;
	$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
	if ($securityToken != $_SESSION['securityToken']) {
		$error = true;
		$message_stack->add('address_new', __('There was a security error.'));
	}
	if (strlen($address['firstname']) < 1) {
		$error = true;
		$message_stack->add('address_new', __('"First Name" is a required value. Please enter the first name.'));
	}
	if (strlen($address['lastname']) < 1) {
		$error = true;
		$message_stack->add('address_new', __('"Last Name" is a required value. Please enter the last name.'));
	}
	if (strlen($address['street_address']) < 1) {
		$error = true;
		$message_stack->add('address_new', __('"Street Address" is a required value. Please enter the street address.'));
	}
	if (strlen($address['city']) < 1) {
		$error = true;
		$message_stack->add('address_new', __('"City" is a required value. Please enter the city.'));
	}
	if (has_region_country($address['country_id'])) {
		if ($region_name = get_region_name($address['region_id'], $address['country_id'])) {
			$address['region'] = $region_name;
		} else {
			$error = true;
			$message_stack->add('address_new', __('"State/Province" is a required value. Please enter the state/province.'));
		}
	}
	if (strlen($address['postcode']) < 1) {
		$error = true;
		$message_stack->add('address_new', __('"Zip/Postal Code" is a required value. Please enter the zip/postal code.'));
	}
	if (!not_null($address['country_id'])
		|| !($address['country'] = get_country_name($address['country_id']))) {
		$error = true;
		$message_stack->add('address_new', __('"Country" is a required value. Please enter the country.'));
	}
	if (strlen($address['telephone']) < 1) {
		$error = true;
		$message_stack->add('address_new', __('"Telephone" is a required value. Please enter the telephone.'));
	}
	if ($error==true) {
	//nothing
	} else {
		$sql_data_array = array(
			array('fieldName'=>'customer_id', 'value'=>$_SESSION['customer_id'], 'type'=>'integer'),
			array('fieldName'=>'firstname', 'value'=>$address['firstname'], 'type'=>'string'),
			array('fieldName'=>'lastname', 'value'=>$address['lastname'], 'type'=>'string'),
			array('fieldName'=>'company', 'value'=>$address['company'], 'type'=>'string'),
			array('fieldName'=>'street_address', 'value'=>$address['street_address'], 'type'=>'string'),
			array('fieldName'=>'suburb', 'value'=>$address['suburb'], 'type'=>'string'),
			array('fieldName'=>'city', 'value'=>$address['city'], 'type'=>'string'),
			array('fieldName'=>'region_id', 'value'=>$address['region_id'], 'type'=>'integer'),
			array('fieldName'=>'region', 'value'=>$address['region'], 'type'=>'string'),
			array('fieldName'=>'postcode', 'value'=>$address['postcode'], 'type'=>'string'),
			array('fieldName'=>'country_id', 'value'=>$address['country_id'], 'type'=>'integer'),
			array('fieldName'=>'country', 'value'=>$address['country'], 'type'=>'string'),
			array('fieldName'=>'telephone', 'value'=>$address['telephone'], 'type'=>'string'),
			array('fieldName'=>'fax', 'value'=>$address['fax'], 'type'=>'string')
		);
		$db->perform(TABLE_ADDRESS, $sql_data_array);
		$address_id = $db->Insert_ID();
		if ($default_billing==1||$default_shipping==1) {
			$sql_data_array = array();
		if ($default_billing==1) {
				$sql_data_array[] = array('fieldName'=>'billing_address_id', 'value'=>$address_id, 'type'=>'integer');
				$_SESSION['customer_billing_address_id'] = $address_id;
			}
			if ($default_shipping==1){
				$sql_data_array[] = array('fieldName'=>'shipping_address_id', 'value'=>$address_id, 'type'=>'integer');
				$_SESSION['customer_shipping_address_id'] = $address_id;
			}
			$sql_data_array[] = array('fieldName'=>'last_modified', 'value'=>'NOW()', 'type'=>'noquotestring');
			$db->perform(TABLE_CUSTOMER, $sql_data_array, 'UPDATE', 'customer_id = ' . $_SESSION['customer_id']);
		}
		$message_stack->add_session('address', __('The address has been saved.'), 'success');
		redirect(href_link(FILENAME_ADDRESS, '', 'SSL'));
	}
}
//Breadcrumb
$breadcrumb->add(__('My Dashboard'), 'sub', href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(__('Address Book'), 'sub', href_link(FILENAME_ADDRESS, '', 'SSL'));
$breadcrumb->add(__('Add New Address'), 'root');
