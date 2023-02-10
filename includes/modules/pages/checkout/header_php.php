<?php
/**
 * checkout header_php.php
 */
$shoppingCart['items'] = $_SESSION['shopping_cart']->getItems();
if (SHOPPING_CART_MODE == 1 && $shoppingCart['items'] > 0) {
	$shoppingCart['subtotal'] = $_SESSION['shopping_cart']->getSubTotal();
	$shoppingCart['discount'] = $_SESSION['shopping_cart']->getDiscount();
	$shoppingCart['coupon_discount'] = $_SESSION['shopping_cart']->getCouponDiscount();
	$shoppingCart['coupon_code'] = $_SESSION['shopping_cart']->getCouponCode();
	$shoppingCart['product'] = $_SESSION['shopping_cart']->getProduct();
} else {
	redirect(href_link(FILENAME_SHOPPING_CART));
}
//Create Order
include(DIR_FS_CATALOG_MODULES . get_module_directory('create_order.php'));
//Address List
if (isset($_SESSION['customer_id'])) {
	$sql = "SELECT address_id, firstname, lastname, company,
				   street_address, suburb, city, region_id, region,
				   postcode, country_id, country, telephone, fax
			FROM   " . TABLE_ADDRESS . "
			WHERE  customer_id = :customerID";
	$sql = $db->bindVars($sql, ':customerID', $_SESSION['customer_id'], 'integer');
	$result = $db->Execute($sql);
	$addressList = array();
	while (!$result->EOF) {
		$addressList[] = array(
			'address_id'     => $result->fields['address_id'],
			'firstname'      => $result->fields['firstname'],
			'lastname'       => $result->fields['lastname'],
			'company'        => $result->fields['company'],
			'street_address' => $result->fields['street_address'],
			'suburb'         => $result->fields['suburb'],
			'city'           => $result->fields['city'],
			'region_id'      => $result->fields['region_id'],
			'region'         => $result->fields['region'],
			'postcode'       => $result->fields['postcode'],
			'country_id'     => $result->fields['country_id'],
			'country'        => $result->fields['country'],
			'telephone'      => $result->fields['telephone'],
			'fax'            => $result->fields['fax']
		);
		$result->MoveNext();
	}
}

//Breadcrumb
$breadcrumb->add(__('Checkout'), 'root');
