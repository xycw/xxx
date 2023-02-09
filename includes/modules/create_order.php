<?php
/**
 * modules create_order.php
 */
if (isset($_POST['action']) && $_POST['action'] == 'process') {
	$error = false;
	$billing = db_prepare_input($_POST['billing']);
	$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
	if ($securityToken != $_SESSION['securityToken']) {
		$error = true;
		$message_stack->add($current_page, __('There was a security error.'));
	}
	if (isset($_SESSION['customer_id']) && isset($billing['address_id'])
		&& ($address = get_address($billing['address_id'], $_SESSION['customer_id']))) {
		$billing = $address;
	} else {
		if (strlen($billing['firstname']) < 1) {
			$error = true;
			$message_stack->add($current_page, __('Billing Information') . ':' . __('"First Name" is a required value. Please enter the first name.'));
		}
		if (strlen($billing['lastname']) < 1) {
			$error = true;
			$message_stack->add($current_page, __('Billing Information') . ':' . __('"Last Name" is a required value. Please enter the last name.'));
		}
		if (!isset($_SESSION['customer_id'])) {
			if (strlen($billing['email_address']) < 1) {
				$error = true;
				$message_stack->add($current_page, __('"Email Address" is a required value. Please enter the email address.'));
			} elseif (!validate_email($billing['email_address']) || disable_email($billing['email_address'])) {
				$error = true;
				$message_stack->add($current_page, __('"Email Address" is not a valid email address.'));
			}
		}
		if (strlen($billing['street_address']) < 1) {
			$error = true;
			$message_stack->add($current_page, __('Billing Information') . ':' . __('"Street Address" is a required value. Please enter the street address.'));
		}
		if (strlen($billing['city']) < 1) {
			$error = true;
			$message_stack->add($current_page, __('Billing Information') . ':' . __('"City" is a required value. Please enter the city.'));
		}
		if (has_region_country($billing['country_id'])) {
			if ($region_name = get_region_name($billing['region_id'], $billing['country_id'])) {
				$billing['region'] = $region_name;
			} else {
				$error = true;
				$message_stack->add($current_page, __('Billing Information') . ':' . __('"State/Province" is a required value. Please enter the state/province.'));
			}
		}
		if (strlen($billing['postcode']) < 1) {
			$error = true;
			$message_stack->add($current_page, __('Billing Information') . ':' . __('"Zip/Postal Code" is a required value. Please enter the zip/postal code.'));
		}
		if (!not_null($billing['country_id'])
			|| !($billing['country'] = get_country_name($billing['country_id']))) {
			$error = true;
			$message_stack->add($current_page, __('Billing Information') . ':' . __('"Country" is a required value. Please enter the country.'));
		}
		if (strlen($billing['telephone']) < 1) {
			$error = true;
			$message_stack->add($current_page, __('Billing Information') . ':' . __('"Telephone" is a required value. Please enter the telephone.'));
		}

		//Save In Address
		if (isset($_SESSION['customer_id']) && $error==false
			&& isset($_POST['save_in_address_book'])
			&& $_POST['save_in_address_book']==1) {
			//First Address
			$addressResult = $db->Execute("SELECT * FROM " . TABLE_ADDRESS . " WHERE customer_id = " . $_SESSION['customer_id'] . "");
			$firstAddress  = $addressResult->RecordCount() == 0 ? true : false;

			$sql_data_array = array(
				array('fieldName'=>'customer_id', 'value'=>$_SESSION['customer_id'], 'type'=>'integer'),
				array('fieldName'=>'firstname', 'value'=>$billing['firstname'], 'type'=>'string'),
				array('fieldName'=>'lastname', 'value'=>$billing['lastname'], 'type'=>'string'),
				array('fieldName'=>'company', 'value'=>$billing['company'], 'type'=>'string'),
				array('fieldName'=>'street_address', 'value'=>$billing['street_address'], 'type'=>'string'),
				array('fieldName'=>'suburb', 'value'=>$billing['suburb'], 'type'=>'string'),
				array('fieldName'=>'city', 'value'=>$billing['city'], 'type'=>'string'),
				array('fieldName'=>'region_id', 'value'=>$billing['region_id'], 'type'=>'integer'),
				array('fieldName'=>'region', 'value'=>$billing['region'], 'type'=>'string'),
				array('fieldName'=>'postcode', 'value'=>$billing['postcode'], 'type'=>'string'),
				array('fieldName'=>'country_id', 'value'=>$billing['country_id'], 'type'=>'integer'),
				array('fieldName'=>'country', 'value'=>$billing['country'], 'type'=>'string'),
				array('fieldName'=>'telephone', 'value'=>$billing['telephone'], 'type'=>'string'),
				array('fieldName'=>'fax', 'value'=>$billing['fax'], 'type'=>'string')
			);
			$db->perform(TABLE_ADDRESS, $sql_data_array);

			$addressId = $db->Insert_ID();

			if ($firstAddress) {
				$sql_data_array = array(
					array('fieldName' => 'billing_address_id', 'value' => $addressId, 'type' => 'integer'),
					array('fieldName' => 'shipping_address_id', 'value' => $addressId, 'type' => 'integer')
				);
				$db->perform(TABLE_CUSTOMER, $sql_data_array, 'UPDATE', 'customer_id = ' . $_SESSION['customer_id']);
				$_SESSION['customer_billing_address_id']  = $addressId;
				$_SESSION['customer_shipping_address_id'] = $addressId;
			}
		}
	}
	//Use For Shipping
	if (isset($_POST['use_for_shipping'])
		&& $_POST['use_for_shipping']==1) {
		$use_for_shipping = 1;
		$shipping = $billing;
	} else {
		$use_for_shipping = 0;
		$shipping = db_prepare_input($_POST['shipping']);
		if (isset($_SESSION['customer_id']) && isset($shipping['address_id'])
			&& ($address = get_address($shipping['address_id'], $_SESSION['customer_id']))) {
			$shipping = $address;
		} else {
			if (strlen($shipping['firstname']) < 1) {
				$error = true;
				$message_stack->add($current_page, __('Shipping Information') . ':' . __('"First Name" is a required value. Please enter the first name.'));
			}
			if (strlen($shipping['lastname']) < 1) {
				$error = true;
				$message_stack->add($current_page, __('Shipping Information') . ':' . __('"Last Name" is a required value. Please enter the last name.'));
			}
			if (strlen($shipping['street_address']) < 1) {
				$error = true;
				$message_stack->add($current_page, __('Shipping Information') . ':' . __('"Street Address" is a required value. Please enter the street address.'));
			}
			if (strlen($shipping['city']) < 1) {
				$error = true;
				$message_stack->add($current_page, __('Shipping Information') . ':' . __('"City" is a required value. Please enter the city.'));
			}
			if (has_region_country($shipping['country_id'])) {
				if ($region_name = get_region_name($shipping['region_id'], $shipping['country_id'])) {
					$shipping['region'] = $region_name;
				} else {
					$error = true;
					$message_stack->add($current_page, __('Shipping Information') . ':' . __('"State/Province" is a required value. Please enter the state/province.'));
				}
			}
			if (strlen($shipping['postcode']) < 1) {
				$error = true;
				$message_stack->add($current_page, __('Shipping Information') . ':' . __('"Zip/Postal Code" is a required value. Please enter the zip/postal code.'));
			}
			if (!not_null($shipping['country_id'])
				|| !($shipping['country'] = get_country_name($shipping['country_id']))) {
				$error = true;
				$message_stack->add($current_page, __('Shipping Information') . ':' . __('"Country" is a required value. Please enter the country.'));
			}
			if (strlen($shipping['telephone']) < 1) {
				$error = true;
				$message_stack->add($current_page, __('Shipping Information') . ':' . __('"Telephone" is a required value. Please enter the telephone.'));
			}
		}
	}
	//Shipping Method
	if (isset($_POST['shipping_method'])
		&& ($shippingMethod = get_shipping_method($_POST['shipping_method'], $shoppingCart['items'], $shoppingCart['subtotal']))) {
		//nothing
	} else {
		$error = true;
		$message_stack->add($current_page, __('"Shipping Method" is a required value.'));
	}
	//Payment Method
	if (isset($_POST['payment_method'])
		&& ($paymentMethod = get_payment_method($_POST['payment_method']))) {
		if ($paymentMethod['is_inside'] == 1) {
			require_once(DIR_FS_CATALOG_CLASSES . 'payment_method.php');
			$payment_method = new payment_method($paymentMethod['code']);
			$payment_method->after();
		}
	} else {
		$error = true;
		$message_stack->add($current_page, __('"Payment Method" is a required value.'));
	}
	//order limit 5
	$limitIp    = $db->Execute("SELECT COUNT(*) as total FROM " . TABLE_ORDERS . " WHERE ip_address = '" . $_SESSION['customer_ip_address'] . "' AND date_added > '" .date('Y-m-d'). "'");
	$limitEmail = $db->Execute("SELECT COUNT(*) as total FROM " . TABLE_ORDERS . " WHERE customer_email_address = '" . $billing['email_address'] . "' AND date_added > '" .date('Y-m-d'). "'");

	$limitIpNum    = $limitIp->fields['total'];
	$limitEmailNum = $limitEmail->fields['total'];

	if ($limitIpNum >= ORDER_LIMIT || $limitEmailNum >= ORDER_LIMIT) {
		$error = true;
		$message_stack->add($current_page, __('Note') . ':' . __('Your order exceeds the limit.'));
	}
	// cookie
	setcookie('billing', json_encode($billing), time() + 60 * 60 * 24 * 180, '/', '', false);
	setcookie('use_for_shipping', $use_for_shipping, time() + 60 * 60 * 24 * 180, '/', '', false);
	if ($use_for_shipping == 0) setcookie('shipping', json_encode($shipping), time() + 60 * 60 * 24 * 180, '/', '', false);
	if ($error==true) {
		//nothing
	} else {
		$order_total = $shoppingCart['subtotal'] - $shoppingCart['discount'] - $shoppingCart['coupon_discount'] + $shippingMethod['fee'] + $shippingMethod['insurance_fee'];
		$paymentDiscount = 0;
		if (is_numeric($paymentMethod['discount']) && $paymentMethod['discount'] > 0 && $paymentMethod['discount'] < 100) {
			$paymentDiscount = $order_total * $paymentMethod['discount'] / 100;
			$order_total = $order_total - $paymentDiscount;
			$shoppingCart['discount'] = $shoppingCart['discount'] + $paymentDiscount;
		}
		$sql_data_array = array(
			array('fieldName'=>'customer_id', 'value'=>isset($_SESSION['customer_id'])?$_SESSION['customer_id']:0, 'type'=>'integer'),
			array('fieldName'=>'customer_firstname', 'value'=>isset($_SESSION['customer_id'])?$_SESSION['customer_firstname']:$billing['firstname'], 'type'=>'string'),
			array('fieldName'=>'customer_lastname', 'value'=>isset($_SESSION['customer_id'])?$_SESSION['customer_lastname']:$billing['lastname'], 'type'=>'string'),
			array('fieldName'=>'customer_email_address', 'value'=>isset($_SESSION['customer_id'])?$_SESSION['customer_email_address']:$billing['email_address'], 'type'=>'string'),
			array('fieldName'=>'billing_firstname', 'value'=>$billing['firstname'], 'type'=>'string'),
			array('fieldName'=>'billing_lastname', 'value'=>$billing['lastname'], 'type'=>'string'),
			array('fieldName'=>'billing_company', 'value'=>$billing['company'], 'type'=>'string'),
			array('fieldName'=>'billing_street_address', 'value'=>$billing['street_address'], 'type'=>'string'),
			array('fieldName'=>'billing_suburb', 'value'=>$billing['suburb'], 'type'=>'string'),
			array('fieldName'=>'billing_city', 'value'=>$billing['city'], 'type'=>'string'),
			array('fieldName'=>'billing_region_id', 'value'=>$billing['region_id'], 'type'=>'integer'),
			array('fieldName'=>'billing_region', 'value'=>$billing['region'], 'type'=>'string'),
			array('fieldName'=>'billing_postcode', 'value'=>$billing['postcode'], 'type'=>'string'),
			array('fieldName'=>'billing_country_id', 'value'=>$billing['country_id'], 'type'=>'integer'),
			array('fieldName'=>'billing_country', 'value'=>$billing['country'], 'type'=>'string'),
			array('fieldName'=>'billing_telephone', 'value'=>$billing['telephone'], 'type'=>'string'),
			array('fieldName'=>'billing_fax', 'value'=>$billing['fax'], 'type'=>'string'),
			array('fieldName'=>'shipping_firstname', 'value'=>$shipping['firstname'], 'type'=>'string'),
			array('fieldName'=>'shipping_lastname', 'value'=>$shipping['lastname'], 'type'=>'string'),
			array('fieldName'=>'shipping_company', 'value'=>$shipping['company'], 'type'=>'string'),
			array('fieldName'=>'shipping_street_address', 'value'=>$shipping['street_address'], 'type'=>'string'),
			array('fieldName'=>'shipping_suburb', 'value'=>$shipping['suburb'], 'type'=>'string'),
			array('fieldName'=>'shipping_city', 'value'=>$shipping['city'], 'type'=>'string'),
			array('fieldName'=>'shipping_region_id', 'value'=>$shipping['region_id'], 'type'=>'integer'),
			array('fieldName'=>'shipping_region', 'value'=>$shipping['region'], 'type'=>'string'),
			array('fieldName'=>'shipping_postcode', 'value'=>$shipping['postcode'], 'type'=>'string'),
			array('fieldName'=>'shipping_country_id', 'value'=>$shipping['country_id'], 'type'=>'integer'),
			array('fieldName'=>'shipping_country', 'value'=>$shipping['country'], 'type'=>'string'),
			array('fieldName'=>'shipping_telephone', 'value'=>$shipping['telephone'], 'type'=>'string'),
			array('fieldName'=>'shipping_fax', 'value'=>$shipping['fax'], 'type'=>'string'),
			array('fieldName'=>'payment_method_code', 'value'=>$paymentMethod['code'], 'type'=>'string'),
			array('fieldName'=>'payment_method_name', 'value'=>$paymentMethod['name'], 'type'=>'string'),
			array('fieldName'=>'payment_method_description', 'value'=>$paymentMethod['description'], 'type'=>'string'),
			array('fieldName'=>'payment_method_account', 'value'=>$paymentMethod['account'], 'type'=>'string'),
			array('fieldName'=>'shipping_method_code', 'value'=>$shippingMethod['code'], 'type'=>'string'),
			array('fieldName'=>'shipping_method_name', 'value'=>$shippingMethod['name'], 'type'=>'string'),
			array('fieldName'=>'shipping_method_description', 'value'=>$shippingMethod['description'], 'type'=>'string'),
			array('fieldName'=>'coupon_code', 'value'=>$shoppingCart['coupon_code'], 'type'=>'string'),
			array('fieldName'=>'currency_code', 'value'=>$currencies->get_code(), 'type'=>'string'),
			array('fieldName'=>'currency_value', 'value'=>$currencies->get_value(), 'type'=>'decimal'),
			array('fieldName'=>'order_subtotal', 'value'=>$shoppingCart['subtotal'], 'type'=>'decimal'),
			array('fieldName'=>'order_discount', 'value'=>$shoppingCart['discount'], 'type'=>'decimal'),
			array('fieldName'=>'coupon_discount', 'value'=>$shoppingCart['coupon_discount'], 'type'=>'decimal'),
			array('fieldName'=>'shipping_method_fee', 'value'=>$shippingMethod['fee'], 'type'=>'decimal'),
			array('fieldName'=>'shipping_method_insurance_fee', 'value'=>$shippingMethod['insurance_fee'], 'type'=>'decimal'),
			array('fieldName'=>'order_total', 'value'=>$order_total, 'type'=>'decimal'),
			array('fieldName'=>'date_added', 'value'=>'NOW()', 'type'=>'noquotestring'),
			array('fieldName'=>'order_status_id', 'value'=>1, 'type'=>'integer'),
			array('fieldName'=>'order_token', 'value'=>md5(uniqid() . $_SESSION['customer_ip_address']), 'type'=>'string'),
			array('fieldName'=>'ip_address', 'value'=>$_SESSION['customer_ip_address'], 'type'=>'string'),
			array('fieldName'=>'customer_http_referer', 'value'=>$_SESSION['customer_http_referer'], 'type'=>'string'),
			array('fieldName'=>'customer_http_user_agent', 'value'=>$_SESSION['customer_http_user_agent'], 'type'=>'string')
		);
		$db->perform(TABLE_ORDERS, $sql_data_array);
		$_SESSION['order_id'] = $db->Insert_ID();
		//Order Status History
		$sql_data_array = array(
			array('fieldName'=>'order_id', 'value'=>$_SESSION['order_id'], 'type'=>'integer'),
			array('fieldName'=>'order_status_id', 'value'=>1, 'type'=>'integer'),
			array('fieldName'=>'date_added', 'value'=>'NOW()', 'type'=>'noquotestring')
		);
		$db->perform(TABLE_ORDER_STATUS_HISTORY, $sql_data_array);
		//Order Product
		foreach ($shoppingCart['product'] as $_product) {
			$sql_data_array = array(
				array('fieldName'=>'order_id', 'value'=>$_SESSION['order_id'], 'type'=>'integer'),
				array('fieldName'=>'product_id', 'value'=>$_product['product_id'], 'type'=>'integer'),
				array('fieldName'=>'sku', 'value'=>$_product['sku'], 'type'=>'string'),
				array('fieldName'=>'name', 'value'=>$_product['name'], 'type'=>'string'),
				array('fieldName'=>'image', 'value'=>$_product['image'], 'type'=>'string'),
				array('fieldName'=>'price', 'value'=>$_product['price'], 'type'=>'decimal'),
				array('fieldName'=>'qty', 'value'=>$_product['qty'], 'type'=>'integer'),
				array('fieldName'=>'attribute', 'value'=>json_encode($_product['attribute']), 'type'=>'string')
			);
			$db->perform(TABLE_ORDER_PRODUCT, $sql_data_array);
		}
		redirect(href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'));
	}
}

// defualt value
if (!isset($_SESSION['customer_id']) && !isset($billing) && isset($_COOKIE['billing'])) {
	$billing = json_decode($_COOKIE['billing'], true);
	$use_for_shipping = $_COOKIE['use_for_shipping'];
	if ($use_for_shipping == 0) $shipping = json_decode($_COOKIE['shipping'], true);
}
